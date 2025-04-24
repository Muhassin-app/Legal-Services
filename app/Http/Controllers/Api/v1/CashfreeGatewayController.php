<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Log;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\{BaseController, OrderController, WalletController, UserSubscriptionController};
use App\Models\Client as CP;
use App\Models\{PaymentOption, Client, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, SubscriptionPlansUser, UserDevice, UserVendor, Transaction};

class CashfreeGatewayController extends BaseController
{
    use ApiResponser;
    public $APP_ID;
    public $SECRET_KEY;
    public $TEST_MODE;
    public $currency;

    public function __construct()
    {
        $cashfree_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'cashfree')->where('status', 1)->first();
        $creds_arr = isset($cashfree_creds->credentials) ? json_decode($cashfree_creds->credentials) : '';
        $app_id = (isset($creds_arr->app_id)) ? $creds_arr->app_id : '';
        $secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $testmode = (isset($cashfree_creds->test_mode) && ($cashfree_creds->test_mode == '1')) ? true : false;
        $this->APP_ID = $app_id;
        $this->SECRET_KEY = $secret_key;
        $this->TEST_MODE = $testmode;
        
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function createOrder(Request $request, $domain = ''){
        try{
            $rules = [
                'amount'   => 'required',
                'action'   => 'required'
            ];

            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);
            $payment_form = $request->action;

            if(empty($user->phone_number)){
                $rules['phone_number'] = 'required';
            }

            $customer_data = array(
                'customer_id' => 'customer_'.$user->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone_number
            );
            $order_tags = ['user_id' => strval($user->id), 'payment_form' => $payment_form];
            $reference_number = $description = '';
            $returnUrlParams = '?order_id={order_id}&order_token={order_token}&gateway=cashfree&amount=' . $request->amount . '&payment_form=' . $payment_form;

            if($payment_form == 'cart'){
                $description = 'Order Checkout';
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $request->request->add(['cart_id' => $cart->id]);
                $reference_number = $request->order_number;
                $order_tags['cart_id'] = strval($cart->id);
                $order_tags['order_number'] = $reference_number;

                $order = Order::where('order_number', $reference_number)->first();
                $returnUrlParams = $returnUrlParams . '&cart_id=' .$cart->id; //. '&order_id={order_id}' .$reference_number. '&order_token=' .$reference_number;
            }
            elseif($payment_form == 'wallet'){
                $description = 'Wallet Checkout';
                // $reference_number = $user->id;
            }
            if($payment_form == 'tip'){
                $description = 'Tip Checkout';
                $order_tags['order_number'] = $request->order_number;
                
                $order = Order::where('order_number', $reference_number)->first();
                // $reference_number = $request->order_number;
                // $returnUrlParams = $returnUrlParams . '&order_id=' .$reference_number. '&order_token=' .$reference_number;
            }
            elseif($payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                if($request->has('subscription_id')){
                    $slug = $request->subscription_id;
                    $subscription_plan = SubscriptionPlansUser::with('features.feature')->where('slug', $slug)->where('status', '1')->first();
                    $customer_data['subscription_id'] = $subscription_plan->id;
                    // $reference_number = $request->subscription_id;
                    $returnUrlParams = $returnUrlParams . '&subscription=' . $request->subscription_id;
                    $order_tags['subscription_id'] = $request->subscription_id;
                }
            }

            $validator = Validator::make($request->all(), $rules, [
                'amount.required' => 'Amount is required',
                'action.required' => 'Action is required',
                'phone_number.required' => 'Phone number is required'
            ]);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }

            $data = array(
                'order_id' => $reference_number,
                'order_amount' => $amount,
                'order_currency' => $this->currency,
                'customer_details' => $customer_data,
                'order_note' => $description,
                'order_tags' => $order_tags,
                'order_meta' => array(
                    'return_url' => url($request->serverUrl.'payment/cashfree/return/app' . $returnUrlParams),
                    'notify_url' => url($request->serverUrl.'payment/cashfree/notify')
                )
            );

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->getPaymentURL() . "/orders",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    "Accept: application/json",
                    "Content-Type: application/json",
                    "x-api-version: 2022-01-01",
                    "x-client-id: ". $this->APP_ID,
                    "x-client-secret: ". $this->SECRET_KEY
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return $this->errorResponse($err->message, 400);
            } else {
                $response = json_decode($response);
                if(isset($response->payment_link)){
                    return $this->successResponse($response->payment_link, 'Order has been created successfully');
                }else{
                    return $this->errorResponse($response->message, 400);
                }
            }
        }
        catch(\Exception $ex){
            return $this->errorResponse('Server Error', 400);
        }
    }

    public function getPaymentURL(){
        if($this->TEST_MODE == false){
            return 'https://api.cashfree.com/pg';
        }else{
            return 'https://sandbox.cashfree.com/pg';
        }
    }

}
