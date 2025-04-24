<?php

namespace App\Http\Controllers\Front;

use App\Models\Payment;
use App\Helpers\Mastercard\Mastercard;
use App\Helpers\Mastercard\Models\Customer;
use App\Helpers\Mastercard\Models\Order;
use App\Helpers\Mastercard\Models\Purchase;
use App\Helpers\Mastercard\Operation;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\PickupDeliveryController;
use App\Http\Controllers\Api\v1\UserSubscriptionController;
use App\Http\Controllers\Controller;
use App\Http\Middleware\CustomDomain;
use App\Http\Traits\OrderTrait;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartDeliveryFee;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\ClientCurrency;
use App\Models\Currency;
use App\Models\Order as ModelsOrder;
use App\Models\PaymentOption;
use App\Models\User;
use App\Models\UserVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class MastercardPaymentController extends Controller
{
    use OrderTrait;

    private Mastercard $client;
    private object $credentials;
    private string $gatewayUrl;
    private int $payopt_id;

    public function __construct(Request $request)
    {
        $pay_option = PaymentOption::where('code', 'mastercard')->where('status', 1)->get(['credentials', 'test_mode', 'status', 'id'])->first();
        if (!$pay_option) {
            (new CustomDomain)->handle($request, fn($_) => $_);
            $pay_option = PaymentOption::where('code', 'mastercard')->where('status', 1)->get(['credentials', 'test_mode', 'status', 'id'])->first();
        }
        $this->credentials = json_decode($pay_option->credentials);

        $this->gatewayUrl = mastercardGateway();
        $this->payopt_id  = $pay_option->id;

        $this->client = new Mastercard(
            (($pay_option->test_mode == 1) ? 'TEST' : '') . $this->credentials->mastercard_merchant_id,
            $this->credentials->mastercard_merchant_key,
            $this->gatewayUrl,
        );
    }

    public function createSession(Request $request, string $domain = '')
    {
        $payment_info = (object)$request->validate([
            'payment_from' => 'required',
            'amount' => 'numeric|required',
        ]);

        $user = Auth::user();

        $customer = (new Customer($user->name))
            ->setEmail($user->email)
            ->setMobilePhone($user->phone_number);

        $reference_id = $this->orderNumber($request);

        if ($payment_info->payment_from == 'tip') {
            [$reference_id, $order_id] = explode(':', $reference_id);
        }

        $currency_id = Session::get('customerCurrency');
        $currency    = Currency::find($currency_id);

        if (!$currency) {
            $client_primary_currency = ClientCurrency::where('is_primary', true)->get(['currency_id'])->first()->currency_id;
            $currency                = Currency::find($client_primary_currency);
        }

        $order_model         = new Order($reference_id, $currency->iso_code, (int)ceil($payment_info->amount));
        $authorization_model = (new Purchase($this->credentials->mastercard_merchant_id))
            ->setOrder($order_model)
            ->setCustomer($customer);

        $authorization_model
            ->getInteraction()
            ->setReturnUrl($return_url = url(sprintf('/payment/mastercard/return/%s', $reference_id)));

        Log::info("mastercard: return url: $return_url");

        switch ($payment_info->payment_from) {
            case 'wallet':
                $authorization_model
                    ->getOrder()
                    ->setDescription("Recharge your wallet");
                break;

            case 'tip':
                $authorization_model
                    ->getOrder()
                    ->setDescription("Tip for OrderID#". $order_id);
                break;

            case 'subscription':
                $authorization_model
                    ->getOrder()
                    ->setDescription("Payment for Subscription ID#" . $request->subscription_id);
                break;

            case 'cart':
            case 'pickup_delivery':
                break;

            default:
                return back()->withErrors(['generic' => 'unknown payment info']);
        }

        $sessionResponse = $this->client->request(Operation::INITIATE_CHECKOUT, $authorization_model);
        if (!$sessionResponse) return response()->json($this->client->error(), 500);

        $session_id = $sessionResponse->session->id;
        $success_indicator = $sessionResponse->successIndicator;

        $session_data = compact('session_id', 'success_indicator');

        $session_data['payment_come_from'] = $request->come_from ?? 'web';
        $session_data['user_id']           = auth()->user()->id;

        if ($request->has('subscription_id')) $session_data['subscription_id'] = $request->subscription_id;
        if ($request->has('cancelUrl')) $session_data['cancel_url'] = $request->cancelUrl;
        if ($request->payment_from == 'tip') $session_data['tip_order'] = $order_id;

        Cache::store('redis')->put('order-' . $reference_id, $session_data);

        $sessionResponse->referenceId = $reference_id;
        if ($request->come_from == 'app') return response()->json([
            'status' => 'Success',
            'data'   => sprintf('https://%s/checkout/pay/%s?checkoutVersion=1.0.0', $this->gatewayUrl, $session_id)
        ]);

        return response()->json($sessionResponse);
    }

    public function postPayment(Request $request, string $domain = '', string $order_id)
    {
        $session_data = Cache::store('redis')->get('order-' . $order_id);

        if (!$session_data) return redirect()->back();

        list(
            'session_id' => $session_id,
            'success_indicator' => $success_indicator,
            'payment_come_from' => $come_from,
        ) = $session_data;

        if ($success_indicator != $request->resultIndicator) {
            Log::error(sprintf('Mastercard payment for transaction_id: %s and session_id: %s was unsuccessfull', $order_id, $session_id));
            return $this->handlePaymentFailure($request, $order_id, $session_data);
        }

        $payment = Payment::where('transaction_id', $order_id)->first();
        $payment->viva_order_id = $order_id;
        $payment->payment_option_id = $this->payopt_id;

        if ($come_from == 'app') {
            $user_id = $session_data['user_id'];
            $user = User::with('wallet')->find($user_id);
            Auth::login($user);
        }

        $user = auth()->user();

        switch ($payment->type) {
            case 'wallet':
                $user->wallet->depositFloat(
                    $payment->balance_transaction,
                    [sprintf('Wallet has been credited <b>credited</b> for order number <b>%s</b>', $payment->transaction_id)]
                );

                if ($come_from == 'app') break;
                return redirect()->route('user.wallet');

            case 'cart':
                $order = ModelsOrder::where('order_number', $order_id)->first();
                $order->payment_status = 1;
                $order->save();

                $this->handleSuccessCart($order);

                if ($come_from == 'app') break;
                return redirect()->route('order.success', $order->id);

            case 'subscription':
                $subscription_id = $session_data['subscription_id'];
                $request         = new Request([
                    'transaction_id' => $payment->transaction_id,
                    'payment_option_id' => $this->payopt_id,
                    'subsid' => $payment->transaction_id,
                    'subscription_id' => $payment->transaction_id,
                    'amount' => $payment->amount,
                ]);

                (new UserSubscriptionController)->purchaseSubscriptionPlan($request, $subscription_id);

                if ($come_from == 'app') break;
                return redirect()->route('user.subscription.plans', $subscription_id);

            case 'pickup_delivery':
                $request = new Request([
                    'transaction_id' => $order_id,
                    'payment_option_id' => $this->payopt_id,
                    'amount' => $payment->amount,
                    'order_number' => $order_id,
                    'reload_route' => $payment->reload_route,
                ]);

                (new PickupDeliveryController)->orderUpdateAfterPaymentPickupDelivery($request);

                if ($come_from == 'app') break;
                return redirect()->route('front.booking.details', $payment->transaction_id);

            case 'tip':
                $request = new Request([
                    'tip_amount' => $payment->amount,
                    'order_number' => $session_data['tip_order'],
                    'transaction_id' => $order_id,
                ]);

                (new OrderController)->tipAfterOrder($request);

                if ($come_from == 'app') break;
                return redirect()->route('user.orders');

            default:
                return back();
        }

        return redirect()->route('payment.gateway.return.response', [
            'gateway' => 'mastercard',
            'status'  => 200,
            'order'   => $order_id,
        ]);
    }

    private function handlePaymentFailure(Request $request, string $order_id, array $session_data)
    {
        $payment  = Payment::where('transaction_id', $order_id)->first();
        $err_mesg = "Failed transaction";

        switch ($payment->type) {
            case 'cart':
                $order = ModelsOrder::where('order_number', $order_id)->first();
                $this->failedOrderWalletRefund($order);
                break;

            case 'pickup_delivery':
            case 'subscription':
                $payment->delete();
                break;
        }

        if ($session_data['payment_come_from'] == 'web') return redirect()->back()->with('error', $err_mesg);
        if ($cancel_url = $session_data['cancel_url'] ?? null) return redirect($cancel_url);

        return redirect()->route('payment.gateway.return.response', [
            'gateway' => 'mastercard',
            'order'   => $order_id,
            'status'  => 500,
        ]);
    }

    private function handleSuccessCart($order) {
        $orderController = new OrderController();
        $orderController->autoAcceptOrderIfOn($order->id);

        // Send Notification
        if (!empty($order->vendors)) {
            foreach ($order->vendors as $vendor_value) {
                $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                $user_vendors = UserVendor::where(['vendor_id' => $vendor_value->vendor_id])->pluck('user_id');
                $orderController->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
            }
        }

        $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id);
        $super_admin = User::where('is_superadmin', 1)->pluck('id');

        $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);

        $request = new Request(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);

        //Send Email to customer
        $orderController->sendSuccessEmail($request, $order);
        //Send Email to Vendor
        foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
            $orderController->sendSuccessEmail($request, $order, $vendor_id);
        }
        // send sms
        $orderController->sendSuccessSMS($request, $order);

        $cart = Cart::where('user_id', $order->user_id)->where('status', '0')->first();
        if (!empty($cart)) {
            Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
            CartAddon::where('cart_id', $cart->id)->delete();
            CartCoupon::where('cart_id', $cart->id)->delete();
            CartProduct::where('cart_id', $cart->id)->delete();
            CartProductPrescription::where('cart_id', $cart->id)->delete();
            CartDeliveryFee::where('cart_id', $cart->id)->delete();
        }
        // Mark the cart as deleted
        $cart->delete();
    }

    public function orderNumber($request)
    {
        try {
            $time = isset($request->transaction_id) ? $request->transaction_id : time();
            $user_id = auth()->id();
            $amount  = $request->amount ? $request->amount : $request->amt;
            if (isset($request->action)) {
                $request->request->add(['payment_from' => $request->action, 'come_from' => 'app']);
            }
            if ($request->payment_from == 'cart') {
                $time = $request->order_number;
                Payment::create([
                    'amount' => $amount,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'cart',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            } elseif ($request->payment_from == 'wallet') {
                Payment::create([
                    'amount' => $amount,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'wallet',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            } elseif ($request->payment_from == 'subscription') {
                $time = sprintf('%s_%d', $request->subscription_id, time());
                $payment = Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => round($amount, 2),
                    'type' => 'subscription',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            } elseif ($request->payment_from == 'tip') {
                $time = time();
                $res =  Payment::create([
                    'amount' => $amount,
                    'transaction_id' =>  $time,
                    'balance_transaction' => $amount,
                    'type' => 'tip',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);

                return $time . ':' . $request->order_number;
            } else if ($request->payment_from == 'pickup_delivery') {
                $time = $request->order_number;
                Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'pickup_delivery',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            }
            return $time;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
