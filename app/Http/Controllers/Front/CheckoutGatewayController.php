<?php

namespace App\Http\Controllers\Front;

use Log;
use Auth;
//use WebhookCall;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\WalletController;
use App\Http\Controllers\Front\UserSubscriptionController;
use App\Models\{User, UserVendor, Cart,CaregoryKycDoc, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser};
use Illuminate\Support\Facades\Auth as FacadesAuth;

class CheckoutGatewayController extends FrontController
{
    use ApiResponser;
    public $PUBLIC_KEY;
    public $SECRET_KEY;
    public $test_mode;
    public $currency;

    public function __construct()
    {
        $checkout_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'checkout')->where('status', 1)->first();
        $creds_arr = json_decode($checkout_creds->credentials);
        $public_key = (isset($creds_arr->public_key)) ? $creds_arr->public_key : '';
        $secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $this->test_mode = (isset($checkout_creds->test_mode) && ($checkout_creds->test_mode == '1')) ? true : false;
        $this->PUBLIC_KEY = $public_key;
        $this->SECRET_KEY = $secret_key;

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function checkoutPurchase(Request $request)
    {
        try {
            $user = Auth::user();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($request->amount);

            $returnUrl = route('order.return.success');
            if ($request->payment_form == 'wallet') {
                $returnUrl = route('user.wallet');
            }
            $uniqid = uniqid();
            $customer_data = array(
                'name' => $user->name,
                'email' => $user->email,
                // 'phone' => $user->phone_number
                // 'identification' => '12123123'
            );
            $meta_data = array();
            $reference_number = $description = '';
            $returnUrlParams = '?gateway=checkout&amount=' . $request->amount . '&payment_form=' . $request->payment_form;

            if($request->payment_form == 'cart'){
                $description = 'Order Checkout';
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $request->request->add(['cart_id' => $cart->id]);
                $meta_data['cart_id'] = $cart->id;
                if($request->has('order_number')){
                    $reference_number = $request->order_number;
                }
                $returnUrlParams = $returnUrlParams . '&cart_id=' . $cart->id . '&order=' . $request->order_number;
            }
            elseif($request->payment_form == 'wallet'){
                $description = 'Wallet Checkout';
                $reference_number = $user->id;
            }
            if($request->payment_form == 'tip'){
                $description = 'Tip Checkout';
                $meta_data['order_number'] = $request->order_number;
                if($request->has('order_number')){
                    $reference_number = $request->order_number;
                }
                $returnUrlParams = $returnUrlParams . '&order=' . $request->order_number;
            }
            elseif($request->payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                if($request->has('subscription_id')){
                    $slug = $request->subscription_id;
                    $subscription_plan = SubscriptionPlansUser::with('features.feature')->where('slug', $slug)->where('status', '1')->first();
                    $meta_data['subscription_id'] = $subscription_plan->id;
                    $reference_number = $request->subscription_id;
                    $returnUrlParams = $returnUrlParams . '&subscription=' . $request->subscription_id;
                }
            }

            $data = array(
                'source' => array(
                    'type' => 'token',
                    'token' => $request->token
                ),
                'amount' => $amount * 100,
                'currency' => $this->currency,
                'payment_type' => 'Regular',
                'reference' => $reference_number,
                'description' => $description,
                'capture' => true,
                // 'capture_on' => date('Y-m-dTH:m:sZ'),
                'customer' => $customer_data,
                'payment_ip' => getUserIP(),
                'billing_descriptor' => array(
                    'name' => $user->name,
                    'address' => $user->address->first()->address,
                    'street' =>  $user->address->first()->street,
                    'city' => $user->address->first()->city,
                    'state' => $user->address->first()->state,
                    'zip' => $user->address->first()->pincode
                ),
                'shipping' => array(
                    'address' => array(
                        'name' => $user->name,
                        'address_line1' => $user->address->first()->address,
                        'address_line2' => $user->address->first()->address,
                        'street' =>  $user->address->first()->street,
                        'city' => $user->address->first()->city,
                        'state' => $user->address->first()->state,
                        'zip' => $user->address->first()->pincode
                    ),
                    'phone' => array(
                        'country_code' => $user->address->first()->dial_code,
                        'number' => $user->address->first()->phone_number
                    )
                ),
                'metdata' => $meta_data
            );

            $ch = curl_init($this->getCheckoutUrl());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'Authorization:' . $this->SECRET_KEY,
                    'Cko-Idempotency-Key: string'
                )
            );

            $result = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $result = json_decode($result);

            $returnUrl = $this->checkoutNotify($request, $result);
            if(isset($result->approved)){
                if($result->approved){
                    return $this->successResponse($returnUrl, __('Payment has been done successfully'), 201);
                }else{
                    return $this->errorResponse(__($result->response_summary), 422);
                }
            } else{
                $msg = '';
                if($http_status == '401'){
                    $msg = __('Unauthorized');
                }elseif($http_status == '400' || $http_status == '422'){
                    $msg = __('Invalid data was sent');
                }elseif($http_status == '429'){
                    $msg = __('Too many requests or duplicate request declined');
                }elseif($http_status == '502'){
                    $msg = __('Bad Gateway');
                }
                return $this->errorResponse($msg, $http_status);
            }
        }
        catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function checkoutNotify($request, $payment)
    {
        if (isset($payment->approved) && ($payment->approved)) {
            $transactionId = $payment->id;
            if($request->payment_form == 'cart'){
                $order_number = $request->order_number;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    $order->payment_status = 1;
                    $order->save();
                    $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                    if (!$payment_exists) {
                        $payment = new Payment();
                        $payment->date = date('Y-m-d');
                        $payment->order_id = $order->id;
                        $payment->transaction_id = $transactionId;
                        $payment->balance_transaction = $request->amount;
                        $payment->type = 'cart';
                        $payment->save();

                        // Auto accept order
                        $orderController = new OrderController();
                        $orderController->autoAcceptOrderIfOn($order->id);

                        // Remove cart
                        CaregoryKycDoc::where('cart_id',$request->cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                        Cart::where('id', $request->cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                        CartAddon::where('cart_id', $request->cart_id)->delete();
                        CartCoupon::where('cart_id', $request->cart_id)->delete();
                        CartProduct::where('cart_id', $request->cart_id)->delete();
                        CartProductPrescription::where('cart_id', $request->cart_id)->delete();
                        
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
                    }
                    $returnUrlParams = ''; //'?gateway=checkout&order=' . $order->id;
                    $returnUrl = route('order.success', $order->id);
                    return $returnUrl . $returnUrlParams;

                    // Send Email
                    //   $this->successMail();
                }
            } elseif($request->payment_form == 'wallet'){
                $request->request->add(['wallet_amount' => $request->amount, 'transaction_id' => $transactionId]);
                $walletController = new WalletController();
                $walletController->creditWallet($request);
                $returnUrl = route('user.wallet');
                return $returnUrl;
            }
            elseif($request->payment_form == 'tip'){
                $request->request->add(['order_number' => $request->order_number, 'tip_amount' => $request->amount, 'transaction_id' => $transactionId]);
                $orderController = new OrderController();
                $orderController->tipAfterOrder($request);
                $returnUrl = route('user.orders');
                return $returnUrl;
            }
            elseif($request->payment_form == 'subscription'){
                $request->request->add(['payment_option_id' => 17, 'transaction_id' => $transactionId]);
                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
                $returnUrl = route('user.subscription.plans');
                return $returnUrl;
            }
            return route('order.return.success');
        } 
        else {
            if($request->payment_form == 'cart'){
                $user = Auth::user();
                $order_number = $request->order_number;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if($order){
                    $wallet_amount_used = $order->wallet_amount_used;
                    if($wallet_amount_used > 0){
                        $transaction = Transaction::where('type', 'deposit')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                        if(!$transaction){
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order <b>'. $order->order_number. '</b>']);
                            $this->sendWalletNotification($user->id, $order->order_number);                          
                        }
                    }
                }

                // $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                // foreach ($order_products as $order_prod) {
                //     OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
                // }
                // OrderProduct::where('order_id', $order->id)->delete();
                // OrderProductPrescription::where('order_id', $order->id)->delete();
                // VendorOrderStatus::where('order_id', $order->id)->delete();
                // OrderVendor::where('order_id', $order->id)->delete();
                // OrderTax::where('order_id', $order->id)->delete();
                // Order::where('id', $order->id)->delete();
                return Redirect::to(route('showCart'));
            }
            elseif($request->payment_form == 'wallet'){
                return Redirect::to(route('user.wallet'));
            }
            elseif($request->payment_form == 'tip'){
                return Redirect::to(route('user.orders'));
            }
            elseif($request->payment_form == 'subscription'){
                return Redirect::to(route('user.subscription.plans'));
            }
            return Redirect::to(route('order.return.success'));
        }
    }

    private function getCheckoutUrl(){
        if ($this->test_mode == false){
            return 'https://api.checkout.com/payments';
        }else{
            return 'https://api.sandbox.checkout.com/payments';
        }
    }

}
