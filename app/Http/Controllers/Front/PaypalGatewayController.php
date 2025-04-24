<?php

namespace App\Http\Controllers\Front;

use Illuminate\Support\Facades\Auth;

use Omnipay\Omnipay;
use Illuminate\Http\Request;
use App\Models\{PaymentOption, ClientCurrency, Payment, User};
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Front\FrontController;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

class PaypalGatewayController extends FrontController
{
    use ApiResponser;
    public $gateway;
    public $currency;
    public $testmode;

    public function __construct()
    {
        $paypal_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'paypal')->where('status', 1)->first();
        $creds_arr = json_decode($paypal_creds->credentials);
        $username = (isset($creds_arr->username)) ? $creds_arr->username : '';
        $password = (isset($creds_arr->password)) ? $creds_arr->password : '';
        $signature = (isset($creds_arr->signature)) ? $creds_arr->signature : '';
        $testmode = (isset($paypal_creds->test_mode) && ($paypal_creds->test_mode == '1')) ? true : false;
        $this->gateway = Omnipay::create('PayPal_Express');
        $this->gateway->setUsername($username);
        $this->gateway->setPassword($password);
        $this->gateway->setSignature($signature);
        $this->gateway->setTestMode($testmode); //set it to 'false' when go live

        $this->testmode = $testmode;

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function paypalPurchase(Request $request)
    {
        try {
            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);
            $returnUrlParams = '?amount=' . $amount;

            if ($request->has('tip')) {
                $returnUrlParams = $returnUrlParams . '&tip=' . $request->tip;
            }
            if($request->has('ordernumber') && $request->payment_form=='pickup_delivery'){
                $returnUrlParams = '/payment/paypal/CompletePurchase?amount='.$request->amount.'&order_number='.$request->ordernumber.'&action='.$request->payment_form.'&come_from=web&return_route='.$request->reload_route;
            }
            if ($request->has('reload_route')) {
                $pickupRoute = $request->reload_route;
                $response = $this->gateway->purchase([
                    'currency' => $this->currency, //'USD',
                    'amount' => $amount,
                    'cancelUrl' => url($request->cancelUrl),
                    'returnUrl' => url($returnUrlParams),
                ])->send();
            }else{
                $response = $this->gateway->purchase([
                    'currency' => $this->currency, //'USD',
                    'amount' => $amount,
                    'cancelUrl' => url($request->cancelUrl),
                    'returnUrl' => url($request->returnUrl . $returnUrlParams),
                ])->send();
            }
            if ($response->isSuccessful()) {

                return $this->successResponse($response->getData());
            } elseif ($response->isRedirect()) {
                $token = $response->getData();
                if(isset($token['TOKEN']) && $request->payment_form=="pickup_delivery"){
                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->user_id = $user->id ?? null;
                    $payment->transaction_id = $token['TOKEN'];
                    $payment->payment_option_id = 3;
                    $payment->order_id = $request->order_id;
                    $payment->balance_transaction = $request->amount?? '';
                    $payment->type = $request->payment_form;
                    $payment->save();
                }


                if (method_exists($response, 'redirect')) {
                    ['TOKEN' => $token] = $response->getData();

                    $params   = http_build_query(['token' => $token, 'cmd' => '_express-checkout', 'useraction' => 'commit']);
                    $url      = strtr('https://www{sandbox}paypal.com', ['{sandbox}' => $this->testmode ? '.sandbox.' : '']);
                    $location = sprintf('%s/cgi-bin/webscr?%s', $url, $params);

                    return $this->successResponse($location, 'Payment link generated', 200);
                }

                $responseClassReflection = new ReflectionClass($response);

                return $this->errorResponse('Gateway response is invalid', 500, config('app.debug') ? [
                    'found'           => $responseClassReflection->getName(),
                    'methods_found'   => $responseClassReflection->getMethods(),
                    'method_required' => 'redirect',
                ] : null);
            } else {
                $this->failMail();
                return $this->errorResponse('Payment failed', 400, $response->getData());
            }
        } catch (\Exception $ex) {
            Log::error($ex);

            $this->failMail();
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function paypalCompletePurchase(Request $request)
    {
        // Once the transaction has been approved, we need to complete it.
        if ($request->has(['token', 'PayerID'])) {
            $amount = $this->getDollarCompareAmount($request->amount);
            $returnUrlParams = '?amount=' . $amount;
            if ($request->has('tip')) {
                $returnUrlParams = $returnUrlParams . '&tip=' . $request->tip;
            }

            $transaction = $this->gateway->completePurchase(array(
                'amount'                => $amount,
                'payer_id'              => $request->PayerID,
                'transactionReference'  => $request->token,
                'currency' => $this->currency, //'USD',
            //  'cancelUrl' =>  url($request->cancelUrl),
            //  'returnUrl' => url($request->returnUrl . $returnUrlParams),
             ));
            $response = $transaction->send();
            if ($response->isSuccessful()) {
                // $this->successMail();
                if($request->action=='pickup_delivery'){
                    $dataResponse = $response->getData();
                    $payment = Payment::where('transaction_id',$request->token)->first();
                    if(!empty($request->return_route && $request->come_from)){
                        $this->completePickupDelivery($payment,$request,$request->come_from);
                        return redirect($request->return_route);
                    }else{
                        return $this->completePickupDelivery($payment,$request,$request->come_from);
                    }
                }
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

    // Pickup delivery
    public function completePickupDelivery($payment,$requestdata,$come_from){
        if(isset($requestdata->PayerID) && $requestdata->token)
        {
            $data['payment_option_id']   = 3;
            $data['transaction_id']      = $payment->transaction_id;
            $data['amount']              = $requestdata->amount;
            $data['order_number']        = $requestdata->order_number;
            $data['reload_route']        = 'routes';
            $request                     = new \Illuminate\Http\Request($data);
            $plaseOrderForPickup         = new PickupDeliveryController();
            $res                         = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);

            if($come_from == 'web' && !empty($requestdata->return_route))
            {
                $response['status']         = 'Success';
                $response['msg']            = 'Success Added Pickup Delivery.';
                $response['payment_from']   = 'pickup_delivery';
                $response['data']           = $res;
                return response()->json($response,200);
            }
            if($come_from == 'app')
            {
                $response['status']         = 'Success';
                $response['msg']            = 'Success Added Pickup Delivery.';
                $response['payment_from']   = 'pickup_delivery';
                $response['data']           = $res;
                return response()->json($response,200);
            }
        }
    }

    public function paymentTransactionSave(Request $request, $domain = ''){
        try{
            if( (isset($request->user_id)) && (!empty($request->user_id)) ){
                $user = User::find($request->user_id);
            }elseif((isset($request->auth_token)) && (!empty($request->auth_token))){
                $user = User::whereHas('device',function  ($qu) use ($request){
                    $qu->where('access_token', $request->auth_token);
                })->first();
            }else{
                $user = Auth::user();
            }

            $credit_amount = $request->amount;
            $payment = Payment::where('transaction_id',$request->transaction_id)->first();
            if(!$payment){
                $payment = new Payment();
            }
            $payment->date = date('Y-m-d');
            $payment->user_id = $user->id ?? null;
            $payment->transaction_id = $request->transaction_id;
            $payment->payment_option_id = $request->payment_option_id ?? null;
            $payment->balance_transaction = $credit_amount;
            $payment->type = 'paypal_payment';
            $payment->save();
        }catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

}
