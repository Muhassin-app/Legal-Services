<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Traits\HitpayTrait;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\{Client as ModelsClient, ClientCurrency, Order, Payment, PaymentOption, User};
use Illuminate\Support\Facades\Auth;
use App\Models\CaregoryKycDoc;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\UserVendor;
use Illuminate\Support\Facades\Log;

class HitpayController extends Controller
{
    use HitpayTrait;

    private $hitpay_client;
    private $url;
    private $businessKey;
    private $saltKey;
    private $currency;

    public function __construct()
    {
        $payOption = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'hitpay')->where('status', 1)->first();
        if (@$payOption && !empty($payOption->credentials)) {
            $credentials = json_decode($payOption->credentials);
            $this->businessKey = $credentials->hitpay_business_key;
            $this->saltKey = $credentials->hitpay_salt_key;

            if ($payOption->test_mode == 1) {
                $this->url = 'https://api.sandbox.hit-pay.com/v1/payment-requests';
            } else {
                $this->url = 'https://api.hit-pay.com/v1/payment-requests';
            }
            $this->hitpay_client = new Client([
                'http_errors' => false
            ]);
        }
    }

    public function makePayment(Request $request, $domain = '')
    {
        $user = Auth::user();
        $businessKey = $this->businessKey;
        $url = $this->url;
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
        $orderNumber = strval($this->orderNumber($request));
        // $redirectUrl = $this->getSuccessUrl($orderNumber);
        $redirectUrl = url('/success-hitpay') . "?orderNumber=" . $orderNumber;
        $amount = number_format($request->amount, 2, '.', '');
        $code = $request->header('code')??'';
        if(!empty($code)){
            $client = ModelsClient::where('code',$code)->first();
            if(!empty($client->custom_domain)){
                $domain = $client->custom_domain;
            }else{
                $domain = $client->sub_domain.env('SUBMAINDOMAIN');
            }
        }

        $body = [
            'redirect_url' => $redirectUrl,
            'email' => $user->email,
            'phone' => $user->phone_number,
            'reference_number' => $orderNumber,
            'webhook' => "https://" . $domain . "/payment/hitpay/webhook",
            'currency' => $this->currency,
            'amount' => $amount
        ];
        $response = $this->createPaymentRequest($this->hitpay_client, $body, $url, $businessKey);
        $responseUrl = $response['url'];
        return response()->json([
            'status' => 'Success',
            'payment_url' => $responseUrl,

        ]);
    }

    /**
     * validateHitpayPayment webhook hit the function after checkout
     *
     * @param  mixed  Illuminate\Http\Request $request
     * @return mixed response to hitpay server
     *
     */
    public function validateHitpayPayment(Request $request)
    {
        try {
            if ($request->status == 'completed') {
                $this->paymentSuccessHitpay($request);
                return response()->json(['message' => 'Webhook handled successfully'], 200);
            }
        } catch (\Exception $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }
    }

    //afterPayment
    public function responseAfterPayment(Request $request)
    {
        if (isset($request->status) && $request->status == "completed") {
            $url = $this->getSuccessUrl($request->orderNumber);
            return redirect($url);
        }
    }
    //web hook
    public function paymentSuccessHitpay($request)
    {
        try {
            $transactionId = $request->reference_number;
            $payment = Payment::where('transaction_id', $transactionId)->first();
            if ($payment) {
                $payment->viva_order_id = $transactionId;
                $payment->payment_option_id = 69;
                $payment->save();
            }

            if ($payment->type == 'cart') {
                $order = Order::where('order_number', $transactionId)->first();
                if ($order) {
                    $order->payment_status = '1';
                    $order->save();
                }
            } elseif ($payment->type == 'wallet') {
                $user = User::findOrFail($payment->user_id);
                Auth::login($user);
                $user = Auth::user();
                $wallet = $user->wallet;
                $wallet->depositFloat($payment->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $payment->transaction_id . '</b>']);
            } elseif ($payment->type == 'subscription') {
                $parts = explode("_", (string) $payment->transaction_id);
                $subscription_id = $parts[0];
                $data['transaction_id'] = $payment->transaction_id;
                $data['payment_option_id'] = 69;
                $data['subsid'] = $payment->transaction_id;
                $data['subscription_id'] = $subscription_id;
                $data['amount'] = $payment->amount;
                $data['user_id'] = $payment->user_id;
                $request = new Request($data);
                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
            } elseif ($payment->type == 'pickup_delivery') {

                $data['payment_option_id'] = 69;
                $data['transaction_id'] = $transactionId;
                $data['amount'] = $payment->amount;
                $data['order_number'] = $transactionId;
                $data['reload_route'] = $payment->reload_route;

                $request = new Request($data);

                $plaseOrderForPickup = new PickupDeliveryController();
                $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
            } elseif ($payment->type == 'tip') {

                $data['tip_amount'] = $payment->amount;
                $data['order_number'] = $payment->transaction_id;
                $data['transaction_id'] = $transactionId;
                $data['user_id'] = $payment->user_id;
                $request = new Request($data);
                $orderController = new OrderController();
                $orderController->tipAfterOrder($request);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function orderNumber($request)
    {
        try {
            $time = isset($request->transaction_id) ? $request->transaction_id : time();
            $user_id = auth()->id();
            $amount = $request->amount ? $request->amount : $request->amt;
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
                $time = $request->subscription_id ? $request->subscription_id . '_' . $time : time();
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
                $time = $request->order_number;
                $res = Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => $request->amount,
                    'type' => 'tip',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
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

    public function orderSuccessCartDetail($order)
    {
        try {
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id', $order->user_id)->select('id')->first();
            $cartid = $cart->id;

            Cart::where('id', $cartid)->update([
                'schedule_type' => null,
                'scheduled_date_time' => null,
                'comment_for_pickup_driver' => null,
                'comment_for_dropoff_driver' => null,
                'comment_for_vendor' => null,
                'schedule_pickup' => null,
                'schedule_dropoff' => null,
                'specific_instructions' => null
            ]);

            CaregoryKycDoc::where('cart_id', $cartid)->update([
                'ordre_id' => $order->id,
                'cart_id' => ''
            ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();

            if (!empty($order->vendors)) {
                foreach ($order->vendors as $vendor_value) {
                    $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                    $user_vendors = UserVendor::where([
                        'vendor_id' => $vendor_value->vendor_id
                    ])->pluck('user_id');
                    $orderController->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
                }
            }

            $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id);
            $super_admin = User::where('is_superadmin', 1)->pluck('id');
            $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);

            // send sms
            $this->sendOrderSuccessSMS($order);
        } catch (\Exception $e) {
            return true;
        }
        return true;
    }
}
