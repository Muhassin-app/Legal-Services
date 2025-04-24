<?php

namespace App\Http\Controllers\Front;

use DB;
use Log;
use Auth;
use Session;
use Redirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\{FrontController, OrderController, WalletController, UserSubscriptionController};
use App\Models\Client as CP;
use App\Models\{PaymentOption, Client, CaregoryKycDoc, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, CartDeliveryFee, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, SubscriptionPlansUser, UserDevice, UserVendor, Transaction};

class MyCashGatewayController extends FrontController
{
    use ApiResponser;
    public $api_key;
    public $username;
    public $password;
    public $test_mode;
    public $merchant_phone;
    public $currency;

    public function __construct()
    {
        $creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'mycash')->where('status', 1)->first();
        $creds_arr = isset($creds->credentials) ? json_decode($creds->credentials) : '';
        $this->api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $this->username = (isset($creds_arr->username)) ? $creds_arr->username : '';
        $this->password = (isset($creds_arr->password)) ? $creds_arr->password : '';
        $this->test_mode = (isset($creds->test_mode) && ($creds->test_mode == '1')) ? true : false;
        $this->merchant_phone = (isset($creds_arr->merchant_phone)) ? $creds_arr->merchant_phone : '';
        
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function purchase(Request $request, $domain = ''){
        try{
            
            $rules = [
                'amount'   => 'required',
                'payment_form'   => 'required'
            ];

            if($this->currency != 'FJD'){
                return $this->errorResponse($this->currency. ' ' . __('currency not supported'), 400);
            }

            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);
            $payment_form = $request->payment_form;

            if(empty($user->phone_number)){
                $rules['phone_number'] = 'required';
            }

            $verifyOtpUrl = route('verify.payment.otp', 'mycash');

            $data = array(
                'method' => 'paymentRequest',
                'api_key' => $this->api_key,
                'username' => $this->username,
                'password' => $this->password,
                'customer_mobile' => $user->dial_code . $user->phone_number, //'6797016954',//'6797417595',//'6797142243',//
                'merchant_mobile' => $this->merchant_phone,
                'product_id' => 233,
                'amount' => $amount
            );
            
            $meta = 'customer_name:'.$user->name.'|payment_form:'.$payment_form;
            $order_number = $order_reference = $description = '';

            if($payment_form == 'cart'){
                $description = 'Order Checkout';
                $rules['order_number'] = 'required';
                $order_number = $request->order_number;
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $meta = $meta.'|cart_id:' . $cart->id;
                $meta = $meta.'|order_number:' . $order_number;
                $data['order_id'] = $order_number;
            }
            elseif($payment_form == 'wallet'){
                $description = 'Wallet Checkout';
                $data['order_id'] = 'W_'.time();
            }
            if($payment_form == 'tip'){
                $description = 'Tip Checkout';
                $rules['order_number'] = 'required';
                $order_number = $request->order_number;
                $meta = $meta.'|order_number:' . $order_number;
                $data['order_id'] = $order_number;
            }
            elseif($payment_form == 'subscription'){
                $rules['subscription_id'] = 'required';
                $description = 'Subscription Checkout';
                if($request->has('subscription_id')){
                    $meta = $meta.'|subscription_id:' . $request->subscription_id;
                    $data['order_id'] = 'S_'.time();
                }
            }

            $validator = Validator::make($request->all(), $rules, [
                'amount.required' => __('Amount is required'),
                'payment_form.required' => __('Action is required'),
                'phone_number.required' => __('Phone number is required')
            ]);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }

            $meta = $meta.'|description:' . $description;
            $data['narration'] = $meta;

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->getPaymentURL(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                \Log::error('MyCash Gateway Error - '. $err->message);
                return $this->errorResponse(__('Server Error'), 400);
            } else {
                $response = json_decode($response);
                if(isset($response->request_id)){
                    $request->request->add([
                        'order_reference' => $data['order_id'],
                        'request_id' => $response->request_id
                    ]);

                    $send_otp_resp = $this->sendOtp($request)->getData();
                    if ($send_otp_resp->status == 'Success') {
                        $rdata['formData'] = array(
                            'amount' => $amount,
                            'request_id' => $request->request_id,
                            'order_reference' => $data['order_id'],
                            'payment_form' => $request->payment_form
                        );
                        if($payment_form == 'subscription'){
                            $rdata['formData']['subscription_id'] = $request->subscription_id;
                        }
                        $rdata['redirectUrl'] = $verifyOtpUrl;
                        // $verifyOtpUrl = $verifyOtpUrl.'?order_id='.$data['order_id'].'&request_id='.$request->request_id;
                        return $this->successResponse($rdata, $send_otp_resp->message, 200);
                    }else{
                        \Log::error('MyCash Gateway Error - '. $send_otp_resp->message);
                        return $this->errorResponse(__('Server Error'), 400);
                    }
                }
                else{
                    \Log::error('MyCash Gateway Error - '. $response->message);
                    return $this->errorResponse(__($response->message), 400);
                }
            }
        }
        catch(\Exception $ex){
            Log::error($ex->getMessage());
            return $this->errorResponse('Server Error', 400);
        }
    }

    public function sendOtp($request, $domain = '')
    {
        if($request->has('auth_token')){
            $user = User::where('auth_token', $request->auth_token)->first();
        }else{
            $user = Auth::user();
        }

        $data = array(
            'method' => 'sendOTP',
            'api_key' => $this->api_key,
            'username' => $this->username,
            'password' => $this->password,
            'mobile_number' => $user->dial_code . $user->phone_number, //'6797016954',//'6797417595',//'6797142243',//
        );
        // //\Log::info($request->all());
        // //\Log::info($data);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->getPaymentURL(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $response = json_decode($response);

        if(!$err && $response && ($response->response_code == 0)){
            if(!$request->has('resend')){
                $payment = new Payment();
                $payment->date = date('Y-m-d');
                $payment->user_id = $user->id;
                $payment->payment_option_id = 36;
                $payment->balance_transaction = $request->amount;
                $payment->gateway_reference = $request->request_id;
                $payment->order_reference = $request->order_reference;
                
                if($request->payment_form == 'cart'){
                    $order = Order::where('order_number', $request->order_reference)->first();
                    if ($order) {
                        $payment->order_id = $order->id;
                    }
                    $payment->type = 'cart';
                } elseif($request->payment_form == 'wallet'){
                    $payment->type = 'wallet';
                }
                elseif($request->payment_form == 'tip'){
                    $order = Order::where('order_number', $request->order_reference)->first();
                    if ($order) {
                        $payment->order_id = $order->id;
                    }
                    $payment->type = 'tip';
                }
                elseif($request->payment_form == 'subscription'){
                    $payment->type = 'subscription';
                }
                $payment->save();
            }
            return $this->successResponse('', __('OTP has been sent to your mobile number'), 200);
        }
        else{
           // Log::info($err);
            return $this->errorResponse('Server Error', 400);
        }
    }

    public function verifyOtp(Request $request, $domain = '')
    {
        try{
            $rules = [
                'otp' => 'required',
                'amount' => 'required',
                'request_id' => 'required',
                'payment_form' => 'required',
                'order_reference' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules, [
                'otp.required' => __('Please enter the OTP'),
                'amount.required' => __('Invalid Parameters'),
                'request_id.required' => __('Invalid Parameters'),
                'payment_form.required' => __('Invalid Parameters'),
                'order_reference.required' => __('Invalid Parameters')
            ]);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }

            if($request->has('auth_token')){
                $user = User::where('auth_token', $request->auth_token)->first();
            }else{
                $user = Auth::user();
            }
            $request->request->add(['user_id' => $user->id]);
            $data = array(
                'method' => 'approvePayment',
                'api_key' => $this->api_key,
                'username' => $this->username,
                'password' => $this->password,
                'request_id' => $request->request_id,
                'customer_mobile' => $user->dial_code . $user->phone_number, //'6797016954',//'6797417595',//'6797142243',//
                'otp' => $request->otp
            );

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->getPaymentURL(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $response = json_decode($response);
            // dd($response->toArray());

            if($response && ($response->response_code == 0)){
                $transactionId = $response->transaction_id;
                $order_reference = $request->order_reference;
                $payment = Payment::where('gateway_reference', $request->request_id)->where('order_reference', $order_reference)->first();
                if($payment){
                    if($payment->otp_verified == 1){
                        return $this->errorResponse('Invalid payment request', 400);
                    }

                    $payment->otp = $request->otp;
                    $payment->otp_verified = 1;
                    $payment->transaction_id = $transactionId;
                    $payment->update();
                    
                    if($request->payment_form == 'cart'){
                        $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_reference)->first();
                        if ($order) {
                            $order->payment_status = 1;
                            $order->save();

                            // Deduct wallet amount if payable amount is successfully done on gateway
                            if ( $order->wallet_amount_used > 0 ) {
                                $wallet = $user->wallet;
                                $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                                if(!$transaction_exists){
                                    $wallet->withdrawFloat($order->wallet_amount_used, [
                                        'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                                        'order_number' => $order->order_number,
                                        'transaction_id' => $transactionId,
                                        'payment_option' => 'MyCash'
                                    ]);
                                }
                            }

                            // Auto accept order
                            $orderController = new OrderController();
                            $orderController->autoAcceptOrderIfOn($order->id);

                            // Remove cart
                            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                            CaregoryKycDoc::where('cart_id',$cart->id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                            Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            CartAddon::where('cart_id', $cart->id)->delete();
                            CartCoupon::where('cart_id', $cart->id)->delete();
                            CartProduct::where('cart_id', $cart->id)->delete();
                            CartProductPrescription::where('cart_id', $cart->id)->delete();
                            CartDeliveryFee::where('cart_id', $cart->id)->delete();

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

                            $request->request->add(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);
                            //Send Email to customer
                            $orderController->sendSuccessEmail($request, $order);
                            //Send Email to Vendor
                            foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                                $orderController->sendSuccessEmail($request, $order, $vendor_id);
                            }
                            //Send SMS to customer
                            $orderController->sendSuccessSMS($request, $order);
                            
                            $returnUrl = route('order.success', $order->id);
                        }
                    } elseif($request->payment_form == 'wallet'){
                        $request->request->add(['wallet_amount' => $payment->balance_transaction, 'transaction_id' => $transactionId]);
                        $walletController = new WalletController();
                        $walletController->creditWallet($request);
                        $returnUrl = route('user.wallet');
                    }
                    elseif($request->payment_form == 'tip'){
                        $request->request->add(['order_number' => $order_reference, 'tip_amount' => $payment->balance_transaction, 'transaction_id' => $transactionId]);
                        $orderController = new OrderController();
                        $orderController->tipAfterOrder($request);
                        $returnUrl = route('user.orders');
                    }
                    elseif($request->payment_form == 'subscription'){
                        $subscription_plan = SubscriptionPlansUser::select('price')->where('slug', $request->subscription_id)->where('status', '1')->first();
                        $request->request->add(['amount' => $subscription_plan->price, 'payment_option_id' => 36, 'transaction_id' => $transactionId]);
                        $subscriptionController = new UserSubscriptionController();
                        $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
                        $returnUrl = route('user.subscription.plans');
                    }
                    if(isset($request->come_from) && ($request->come_from == 'app')){
                        $returnUrl = route('payment.gateway.return.response').'/?gateway=mycash'.'&status=200&transaction_id='.$transactionId.'&action='.$request->payment_form;
                    }
                    return $this->successResponse($returnUrl, __('Payment has been done successfully'), 200);
                }
                else{
                    return $this->errorResponse('Invalid payment request', 400);
                }
            }
            else{
                Log::error($response->message);
                return $this->errorResponse($response->message, 400);
            }
        }
        catch(Exception $ex){
            Log::error($ex->getMessage());
            return $this->errorResponse('Server Error', 400);
        }
    }

    public function getPaymentURL(){
        if($this->test_mode == false){
            return 'https://www.gifts.digicelpacific.com/mycash/';
        }else{
            return 'https://www.gifts.digicelpacific.com/mycash/';
        }
    }

}
