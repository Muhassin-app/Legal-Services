<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Log;
use Auth;
use Redirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\{BaseController, OrderController, WalletController, UserSubscriptionController};
use App\Models\Client as CP;
use App\Models\{PaymentOption, Client, CaregoryKycDoc, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, CartDeliveryFee, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, SubscriptionPlansUser, UserDevice, UserVendor, Transaction};

class MyCashGatewayController extends BaseController
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
                'action'   => 'required',
                'payment_form'   => 'required'
            ];

            if($this->currency != 'FJD'){
                return $this->errorResponse($this->currency. ' ' . __('currency not supported'), 400);
            }

            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);
            $request->request->add(['payment_form' => $request->action]);
            $payment_form = $request->payment_form;

            if(empty($user->phone_number)){
                $rules['phone_number'] = 'required';
            }

            $verifyOtpUrl = url($request->serverUrl . 'verify/payment/otp/app/mycash');

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
                            'payment_form' => $request->payment_form,
                            'auth_token' => $user->auth_token,
                            'come_from' => 'app'
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
        $user = Auth::user();

        $data = array(
            'method' => 'sendOTP',
            'api_key' => $this->api_key,
            'username' => $this->username,
            'password' => $this->password,
            'mobile_number' => $user->dial_code . $user->phone_number, //'6797016954',//'6797417595',//'6797142243',//
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
