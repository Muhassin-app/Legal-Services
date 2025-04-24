<?php

namespace App\Http\Controllers\Api\v1;

use Auth;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{PaymentOption, Cart, ClientCurrency, SubscriptionPlansUser};

class PaystackGatewayController extends BaseController
{
    use ApiResponser;
    public $gateway;
    public $currency;

    public function __construct()
    {
        $paystack_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'paystack')->where('status', 1)->first();
        $creds_arr = json_decode($paystack_creds->credentials);
        $secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $public_key = (isset($creds_arr->public_key)) ? $creds_arr->public_key : '';
        $testmode = (isset($paystack_creds->test_mode) && ($paystack_creds->test_mode == '1')) ? true : false;
        $this->gateway = Omnipay::create('Paystack');
        $this->gateway->setSecretKey($secret_key);
        $this->gateway->setPublicKey($public_key);
        $this->gateway->setTestMode($testmode); //set it to 'false' when go live

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function paystackPurchase(Request $request){
        try{
            $rules = [
                'amount'   => 'required',
                'action'   => 'required'
            ];

            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);

            $request->request->add(['payment_form' => $request->action]);

            $meta_data = array();
            $reference_number = $description = $returnUrlParams = '';
            $returnUrl = $request->serverUrl . 'payment/paystack/completePurchase/app?amount='.$amount.'&status=200&gateway=paystack&action='.$request->action;
            $cancelUrl = $request->serverUrl . 'payment/paystack/cancelPurchase/app?status=0&gateway=paystack&action='.$request->action.'&order_number='. $request->order_number;

            if($request->payment_form == 'cart'){
                $description = 'Order Checkout';
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $request->request->add(['cart_id' => $cart->id, 'order_number'=>$request->order_number]);
                $meta_data['custom_fields']['cart_id'] = $cart->id;
                $returnUrlParams = $returnUrlParams.'&user_id='.$user->id.'&cart_id='.$cart->id.'&order_number='.$request->order_number;
                $rules['order_number'] = 'required';
                if($request->has('tip')){
                    $returnUrlParams = $returnUrlParams.'&tip='.$request->tip;
                }
            }
            elseif($request->payment_form == 'wallet'){
                $description = 'Wallet Checkout';
                $returnUrlParams = $returnUrlParams.'&user_id='.$user->id;
                $meta_data['custom_fields']['user_id'] = $user->id;
            }
            if($request->payment_form == 'tip'){
                $description = 'Tip Checkout';
                $returnUrlParams = $returnUrlParams.'&user_id='.$user->id.'&order_number='.$request->order_number;
                $meta_data['custom_fields']['order_number'] = $request->order_number;
            }
            elseif($request->payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                $slug = $request->subscription_id;
                $returnUrlParams = $returnUrlParams.'&user_id='.$user->id.'&subscription_id='.$slug;
                $subscription_plan = SubscriptionPlansUser::where('slug', $slug)->where('status', '1')->first();
                $meta_data['custom_fields']['subscription_id'] = $subscription_plan->id;
                $rules['subscription_id'] = 'required';
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }

            $meta_data['cancel_action'] = url($cancelUrl . $returnUrlParams);

            $response = $this->gateway->purchase([
                'amount' => $amount,
                'currency' => $this->currency,
                'email' => $user->email,
                'returnUrl' => url($returnUrl . $returnUrlParams),
                // 'cancelUrl' => url($cancelUrl . $returnUrlParams),
                'metadata' => $meta_data,
                'description' => $description,
            ])->send();
            if ($response->isSuccessful()) {
                return $this->successResponse($response->getData());
            }
            elseif ($response->isRedirect()) {
                $this->failMail();
                return $this->successResponse($response->getRedirectUrl());
            }
            else {
                $this->failMail();
                return $this->errorResponse($response->getMessage(), 400);
            }
        }
        catch(\Exception $ex){
            $this->failMail();
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function paystackCompletePurchase(Request $request)
    {
        // Once the transaction has been approved, we need to complete it.
        if($request->has(['reference'])){
            $amount = $this->getDollarCompareAmount($request->amount);
            $transaction = $this->gateway->completePurchase(array(
                'amount'                => $amount,
                'transactionReference'  => $request->reference
            ));
            $response = $transaction->send();
            if ($response->isSuccessful()){
                $this->successMail();
                return $this->successResponse($response->getTransactionReference());
            } else {
                $this->failMail();
                return $this->errorResponse($response->getMessage(), 400);
            }
        } else {
            $this->failMail();
            return $this->errorResponse('Transaction has been declined', 400);
        }
    }
}
