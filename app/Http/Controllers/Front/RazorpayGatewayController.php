<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Api\v1\VendorSubscriptionController;
use App\Http\Controllers\Client\VendorSubscriptionController as ClientVendorSubscriptionController;
use Log;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Razorpay\Api\Api;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\OrderController;
use App\Models\{User, UserVendor, Cart,CaregoryKycDoc, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, Vendor};

class RazorpayGatewayController extends FrontController
{
    use ApiResponser;
    public $API_KEY;
    public $API_SECRET_KEY;
    public $test_mode;
    public $api;
    public $currency;

    public function __construct()
    {
        $razorpay_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'razorpay')->where('status', 1)->first();
        $creds_arr = json_decode($razorpay_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $api_secret_key = (isset($creds_arr->api_secret_key)) ? $creds_arr->api_secret_key : '';
        $this->test_mode = (isset($razorpay_creds->test_mode) && ($razorpay_creds->test_mode == '1')) ? true : false;
        $this->API_KEY = $api_key;
        $this->API_SECRET_KEY = $api_secret_key;
        $this->api = new Api($api_key, $api_secret_key);
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'INR';

        $this->token = base64_encode($api_key.':'.$api_secret_key);
        $this->api_url = 'https://api.razorpay.com/v1/';

    }

    public function razorpayPurchase(Request $request)
    {
        try {
            $user = Auth::user();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($request->amount);
            $amount = (int)($amount*100);
            // $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_INT);
            $order_number = $request->order_number;
            if (!isset($order_number)) {
                $order_number = 0;
            }
            $api_key = $this->API_KEY;
            $orderResponse = $this->api->order->create(array('amount' => $amount, 'currency' => 'INR','payment' => array('capture' => 'automatic','capture_options' => array('automatic_expiry_period' => 12,'manual_expiry_period' => 7200,'refund_speed' => 'optimum'))));
            $data = $request->all();
            $data['order_number'] = $order_number;
            $data['order_id'] = $orderResponse->id;
            $data['amount'] = $orderResponse->amount;
            $data['currency'] = $orderResponse->currency;
            $data['payment_from'] = $request->payment_from;

            return $this->successResponse($data);
            // return $this->successResponse(url('/payment/razorpay/view?amount=' . $amount . '&order=' . $order_number . '&api_key=' . $api_key));
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function razorpayCompletePurchase(Request $request)
    {

        try {
            $user = Auth::user();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($request->amount);
            // $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_INT);
            // $amount = $amount/100;
            $returnUrl = route('order.return.success');
            if ($request->payment_from == 'wallet') {
                $returnUrl = route('user.wallet');
            }
            $orderData = [
                'amount'          => (int)$amount,
                'currency'        => 'INR'
            ];

            $payment = $this->api->payment->fetch($request->razorpay_payment_id);
            // $payment = $this->api->payment->fetch($request->razorpay_payment_id)->capture($orderData);

            // \Log::info('razor pa2222y status');
            // \Log::info([$payment]);
            // if($payment['status'] != 'captured') {
            //     $payment = $this->api->payment->fetch($request->razorpay_payment_id)->capture($orderData);
            //     \Log::info('razor paymen1111t22 status');
            //     \Log::info([$payment]);
            // }


            // $capture = $payment->capture(['amount'=>$payment['amount']]);
            if ($payment['status'] == 'captured') {
                $response =  $this->razorpayNotify($payment, $amount/100, $request, $orderData);
            } else {
                $response = $this->razorpayNotify_fail($payment, $amount/100, $request, $orderData);
            }
            return $this->successResponse($response);
        } catch (\Exception $ex) {
            \Log::info('error response'.$ex->getMessage().'---'.$ex->getLine());
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function razorpayNotify($payment, $amount, $request, $orderData)
    {
        $transactionId = $payment['id'];
        if($request->payment_from == 'cart')
        {
            $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $request->order_number)->first();
            if ($order) {
                $order->payment_status = 1;
                $order->save();
                $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                if (!$payment_exists) {
                    $user = Auth::user();

                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->order_id = $order->id;
                    $payment->transaction_id = $transactionId;
                    $payment->balance_transaction = $amount;
                    $payment->user_id = $user->id;
                    $payment->type = 'cart';
                    $payment->save();

                    // Auto accept order
                    $orderController = new OrderController();
                    $orderController->autoAcceptOrderIfOn($order->id);

                    // Remove cart
                    $cart = Cart::where('user_id',$user->id)->select('id')->first();
                    CaregoryKycDoc::where('cart_id',$cart->id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                    Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                    CartAddon::where('cart_id', $cart->id)->delete();
                    CartCoupon::where('cart_id', $cart->id)->delete();
                    CartProduct::where('cart_id', $cart->id)->delete();
                    CartProductPrescription::where('cart_id', $cart->id)->delete();

                    // send success sms
                    $this->sendSuccessSMS($request, $order);

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
                   // $request = new Request();
                    $request->request->add(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);
                   //Send Email to customer
                    $orderController->sendSuccessEmail($request, $order);
                    //Send Email to Vendor
                    foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                        $orderController->sendSuccessEmail($request, $order, $vendor_id);
                    }


                }
                $returnUrlParams = '?gateway=razorpay&order=' . $order->id;
                $returnUrl = route('order.return.success');
                // dd($returnUrl . $returnUrlParams);
                return $returnUrl.$returnUrlParams;
            } else {
                $returnUrlParams = '?gateway=razorpay&amount=' . $amount . '&checkout=' . $payment['id'];
                $returnUrl = route('user.wallet');
                return $returnUrl.$returnUrlParams;
            }
        }elseif($request->payment_from == 'pickup_delivery'){
            $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $request->order_number)->first();
            if ($order) {
                $order->payment_status = 1;
                $order->save();
                $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                if (!$payment_exists) {
                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->order_id = $order->id;
                    $payment->transaction_id = $transactionId;
                    $payment->balance_transaction = $amount;
                    $payment->type = 'pickup_delivery';
                    $payment->save();
                }
            }
        }elseif($request->payment_from == 'wallet'){
            $request->request->add(['wallet_amount' => $amount, 'transaction_id' => $transactionId]);
            $walletController = new WalletController();
            $walletController->creditWallet($request);
            $returnUrl = route('user.wallet');
            return $returnUrl;
        }elseif($request->payment_from == 'tip'){
            $request->request->add(['order_number' => $request->order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
            $returnUrl = route('user.orders');
            return $returnUrl;
        }elseif($request->payment_from == 'subscription'){
            $request->request->add(['payment_option_id' => 10, 'transaction_id' => $transactionId, 'amount' => $request->amount/100]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
            $returnUrl = route('user.subscription.plans');
            return $returnUrl;
        }elseif($request->payment_from == 'vendor_subscription'){
            $request->request->add(['payment_option_id' => 10, 'transaction_id' => $transactionId, 'amount' => $request->amount/100]);
            $subscriptionController = new ClientVendorSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $request->vendor_id,$request->subscription_id);
            return true;
        }
        return route('order.return.success');
    }

    public function razorpayNotify_fail($payment, $amount, $request, $orderData)
    {
        if($request->payment_from == 'cart'){
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $request->order_number)->first();
                $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                foreach ($order_products as $order_prod) {
                    OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
                }
                OrderProduct::where('order_id', $order->id)->delete();
                OrderProductPrescription::where('order_id', $order->id)->delete();
                VendorOrderStatus::where('order_id', $order->id)->delete();
                OrderVendor::where('order_id', $order->id)->delete();
                OrderTax::where('order_id', $order->id)->delete();
                Order::where('id', $order->id)->delete();
                $returnUrl = route('viewcart');
                return $returnUrl;
            }
            elseif($request->payment_from == 'wallet'){
                return route('user.wallet');
            }
            elseif($request->payment_from == 'tip'){
                return route('user.orders');
            }
            elseif($request->payment_from == 'subscription'){
                return route('user.subscription.plans');
            }
            return route('order.return.success');
    }


}
