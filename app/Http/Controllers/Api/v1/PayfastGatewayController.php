<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Log;
use Auth;
use Session;
use Redirect;
use Carbon\Carbon;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\{BaseController, OrderController, WalletController};
use App\Models\Client as CP;
use App\Models\{PaymentOption, Client, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, UserDevice, UserVendor, SubscriptionPlansUser};

class PayfastGatewayController extends BaseController
{
    use ApiResponser;
    public $gateway;
    public $currency;

    public function __construct()
    {
        $payfast_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'payfast')->where('status', 1)->first();
        $creds_arr = json_decode($payfast_creds->credentials);
        $merchant_id = (isset($creds_arr->merchant_id)) ? $creds_arr->merchant_id : '';
        $merchant_key = (isset($creds_arr->merchant_key)) ? $creds_arr->merchant_key : '';
        $passphrase = (isset($creds_arr->passphrase)) ? $creds_arr->passphrase : '';
        $testmode = (isset($payfast_creds->test_mode) && ($payfast_creds->test_mode == '1')) ? true : false;
        $this->gateway = Omnipay::create('PayFast');
        $this->gateway->setMerchantId($merchant_id);
        $this->gateway->setMerchantKey($merchant_key);
        $this->gateway->setPassphrase($passphrase);
        $this->gateway->setTestMode($testmode); //set it to 'false' when go live
        
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    function generateSignature($data, $passPhrase = null) {
        // Create parameter string
        $pfOutput = '';
        foreach( $data as $key => $val ) {
            if($val !== '') {
                $pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';
            }
        }
        // Remove last ampersand
        $getString = substr( $pfOutput, 0, -1 );
        if( ($passPhrase !== null) || ($passPhrase !== '') ) {
            $getString .= '&passphrase='. urlencode( trim( $passPhrase ) );
        }
        // return $getString;
        return md5( $getString );
    }

    public function payfastPurchase(Request $request){
        try{
            $rules = [
                'amount'   => 'required',
                'action'   => 'required'
            ];

            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);

            $request->request->add(['payment_form' => $request->action]);

            $meta_data = array();
            $reference_number = $description = '';
            $returnUrl = $request->serverUrl . 'payment/gateway/returnResponse?status=200&gateway=payfast';
            $cancelUrl = $request->serverUrl . 'payment/gateway/returnResponse?status=0&gateway=payfast';
            $notifyUrl = $request->serverUrl . 'payment/payfast/notify/app';

            $request_arr = array(
                'merchant_id' => $this->gateway->getMerchantId(),
                'merchant_key' => $this->gateway->getMerchantKey(),
                // 'return_url' => url($returnUrl . '&order='.$request->order_number.'&action='.$request->action),
                // 'cancel_url' => url($cancelUrl . '&order='.$request->order_number.'&action='.$request->action),
                // 'notify_url' => url($notifyUrl),
                // 'amount' => $amount
            );

            if($request->payment_form == 'cart'){
                $description = 'Order Checkout';
                $rules['order_number'] = 'required';
                $tip = 0;
                if($request->has('tip')){
                    $tip = $request->tip;
                }

                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();

                $request_arr['return_url'] = url($returnUrl . '&order='.$request->order_number.'&action='.$request->action);
                $request_arr['cancel_url'] = url($cancelUrl . '&order='.$request->order_number.'&action='.$request->action);
                $request_arr['notify_url'] = url($notifyUrl);
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Cart';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_int3'] = $cart->id; // cart id
                $request_arr['custom_str1'] = $request->action; // action
                $request_arr['custom_str2'] = $tip; // tip amount
                $request_arr['custom_str3'] = $request->order_number;
            }
            elseif($request->payment_form == 'wallet'){
                $description = 'Wallet Checkout';

                $request_arr['return_url'] = url($returnUrl . '&action='.$request->action);
                $request_arr['cancel_url'] = url($cancelUrl . '&action='.$request->action);
                $request_arr['notify_url'] = url($notifyUrl);
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Wallet';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->action; // action
            }
            elseif($request->payment_form == 'tip'){
                $description = 'Tip Checkout';
                $rules['order_number'] = 'required';

                $request_arr['return_url'] = url($returnUrl . '&order='.$request->order_number.'&action='.$request->action);
                $request_arr['cancel_url'] = url($cancelUrl . '&order='.$request->order_number.'&action='.$request->action);
                $request_arr['notify_url'] = url($notifyUrl);
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Tip';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->action; // action
                $request_arr['custom_str2'] = $request->order_number;
            }
            elseif($request->payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                $slug = $request->subscription_id;
                $subscription_plan = SubscriptionPlansUser::where('slug', $slug)->where('status', '1')->first();
                $rules['subscription_id'] = 'required';

                $request_arr['return_url'] = url($returnUrl . '&action='.$request->action);
                $request_arr['cancel_url'] = url($cancelUrl . '&action='.$request->action);
                $request_arr['notify_url'] = url($notifyUrl);
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Subscription';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->action; // action
                $request_arr['custom_str2'] = $slug; // subscription plan slug
            }
            elseif($request->payment_form == 'pickup_delivery'){
                $description = 'Pickup Delivery Checkout';
                $rules['order_number'] = 'required';

                $request_arr['return_url'] = url($returnUrl . '&order='.$request->order_number.'&action='.$request->action);
                $request_arr['cancel_url'] = url($cancelUrl . '&order='.$request->order_number.'&action='.$request->action);
                $request_arr['notify_url'] = url($notifyUrl);
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Pickup Delivery';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->action; // action
                $request_arr['custom_str2'] = $request->order_number;
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }

            // $request_arr = array(
            //     'merchant_id' => $this->gateway->getMerchantId(),
            //     'merchant_key' => $this->gateway->getMerchantKey(),
            //     'return_url' => url($request->serverUrl . 'payment/gateway/returnResponse?status=200&gateway=payfast&order='.$request->order_number),
            //     'cancel_url' => url($request->serverUrl . 'payment/gateway/returnResponse?status=0&gateway=payfast&order='.$request->order_number),
            //     'notify_url' => url($request->serverUrl . 'payment/payfast/notify/app'),
            //     'amount' => $amount,
            //     'item_name' => 'test item',
            //     'custom_int1' => $user->id, // user id
            //     'custom_int2' => $cart->id, // cart id
            //     'custom_int3' => 6, //payment option id
            //     'custom_str1' => $tip, // tip amount
            //     'custom_str2' => $request->action,
            //     'custom_str3' => $request->order_number,
            //     'currency' => 'ZAR',
            //     'description' => 'This is a test purchase transaction',
            //     // 'metadata' => ['user_id' => $user->id],
            // );

            $request_arr['currency'] = $this->currency; //'ZAR';
            $request_arr['description'] = $description;

            $response = $this->gateway->purchase($request_arr)->send();
            unset($request_arr['description']);
            $passphrase = $this->gateway->getPassphrase();
            $signature = $this->generateSignature($request_arr, $passphrase);
            $request_arr['signature'] = $signature;

            if ($response->isSuccessful()) {
                return $this->successResponse($response->getData());
            }
            elseif ($response->isRedirect()) {
                $data['formData'] = $request_arr;
                $data['redirectUrl'] = $response->getRedirectUrl();
                // $this->failMail();
                return $this->successResponse($data);
            }
            else {
                // $this->failMail();
                return $this->errorResponse($response->getMessage(), 400);
            }
        }
        catch(\Exception $ex){
            // $this->failMail();
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

}
