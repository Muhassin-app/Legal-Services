<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\PaymentOption;
use Str;
use Auth;
use App\Models\{Order, Payment,User,Cart,UserVendor,CartProductPrescription,CartProduct,CartCoupon,OrderProduct,OrderProductAddon,OrderProductPrescription,VendorOrderStatus,OrderVendor,OrderTax,CartAddon,CaregoryKycDoc,ClientPreference,ClientCurrency};
use Session;
use Log;
use App\Http\Traits\{ApiResponser,OrderTrait};
use Illuminate\Support\Facades\Redirect;

class SkipCashController extends Controller
{
    use ApiResponser,OrderTrait;


    public function orderNumber($request)
   {
       $time = time();
       $user_id = auth()->id();
       $amount = $request->amt??$request->amount;
       if ($request->payment_from == 'cart') {
           $time = $request->order_number;
           Payment::create([
               'amount' => 0,
               'transaction_id' => $time,
               'balance_transaction' => $amount,
               'type' => 'cart',
               'date' => date('Y-m-d'),
               'user_id' => $user_id,
               'payment_from'=>$request->user_from??'web'
           ]);
       } elseif ($request->payment_from == 'wallet') {
           $time = $request->transaction_id ?? time();
           Payment::create([
               'amount' => 0,
               'transaction_id' => $time,
               'balance_transaction' => $amount,
               'type' => 'wallet',
               'date' => date('Y-m-d'),
               'user_id' => $user_id,
               'payment_from'=>$request->user_from??'web'

           ]);
       } elseif ($request->payment_from == 'tip') {
           $time = $request->order_number . '_' . time();
           Payment::create([
               'amount' => 0,
               'transaction_id' => $time,
               'balance_transaction' => $amount,
               'type' => 'tip',
               'date' => date('Y-m-d'),
               'user_id' => $user_id,
               'payment_from'=>$request->user_from??'web'

           ]);
       } elseif ($request->payment_from == 'subscription') {
           $time = $request->subsid??$request->subscription_id . '_' . time();
           Payment::create([
               'amount' => 0,
               'transaction_id' => $time,
               'balance_transaction' => $amount,
               'type' => 'subscription',
               'date' => date('Y-m-d'),
               'user_id' => $user_id,
               'payment_from'=>$request->user_from??'web'

           ]);
       } else if ($request->payment_from == 'pickup_delivery') {
           $time = $request->order_id??$request->order_number;
           Payment::create([
               'amount' => 0,
               'transaction_id' => $time,
               'balance_transaction' => $amount,
               'type' => 'pickup_delivery',
               'date' => date('Y-m-d'),
               'user_id' => $user_id,
               'payment_from'=>$request->user_from??'web'

           ]);
       }
       return $time;
   }

    public function showSkipCashPage(Request $request,$domain='',$app=''){

        try
        {
        $data = $request->all();
        $creds = PaymentOption::where('code', 'skip_cash')
        ->where('status', 1)
        ->first();
        $creds_arr = json_decode($creds->credentials);
        $skipCashClientId=$creds_arr->skip_cash_client_id;
        if($creds->test_mode==1){
            $url = $creds_arr->skip_cash_testing_url;
        }else{
            $url = $creds_arr->skip_cash_live_url;  
        }
    
        $keyId = $creds_arr->skip_cash_key_id;
        $secretKey = $creds_arr->skip_cash_api_secret;

        $order_number = $this->orderNumber($request);

        $addres = Order::with('address')->where('order_number',$order_number)->first();
        $fields = [
            "Uid" => Str::uuid()->toString(),
            'KeyId' => $keyId,
            'Amount' => $request->amount, 
            'FirstName' => Auth::user()->name,
            'LastName' =>  Auth::user()->name,
            'Phone' => Auth::user()->phone_number,

            'Email' =>  Auth::user()->email,
            'Street' => '123',
            'City' => 'Anytown',
            'State' => $addres->address->country_code ?? 'IN',
            'Country' => $addres->address->country_code ?? 'IN',
            'PostalCode' => '12345',
            'TransactionId' =>  (string)$order_number,
        
        ];
        // dd($fields);

        $signatureString = '';
        foreach ($fields as $key => $value) {
            if (!empty($value)) {
                $signatureString .= "$key=$value,";
            }
        }
        $signatureString = rtrim($signatureString, ',');
        // $signatureString = http_build_query($fields,'',', ');
        // Encrypt the signature string using HMACSHA256 with the secret key
        $signature = hash_hmac('sha256', $signatureString, $secretKey, true);
        
        $signatureBase64 = base64_encode($signature);
       
        $headers = [
            'Content-Type: application/json',
            "Authorization: $signatureBase64",
            'x-client-id: ' . $skipCashClientId
        ];

        // Set the request body
        $body = json_encode($fields);

        // Create the cURL handle
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the request
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $responseObj = json_decode($response);  
        if ($responseObj->returnCode != '200') {
            $message = 'Payment error';
            if(!empty($app))
                $responseArray['message'] = $responseObj->errorMessage??$message;

                return redirect()->back()->with('success', $message);   

        } else {
                $payUrl = $responseObj->resultObj->payUrl;
            
            if(!empty($app)){
                $responseArray['data'] = $payUrl??'';
                $responseArray['status'] = 'Success';
            }else{
                return redirect($payUrl);
            }
            return response()->json($responseArray);
        }
    }catch(\Exception $e)
    {
        $message = $e->getMessage();
        return $message;
    }
    }

    public function mobilePay(Request $request)
    {
        $request->request->add(['payment_from' => $request->action,'from'=>$request->action,'amt'=>number_format($request->amount,2),'subsid'=>$request->subscription_id??'','user_from'=>'app']);
        $data =  $this->showSkipCashPage($request,'','app');
       if(isset($data) && !empty($data))
        {
            return $data;
        }
    }

    public function successPage(Request $request)
    {
        if (isset($request) && $request->get('status')) {
            $payment = Payment::where('transaction_id', $request->get('transId'))->first();
            
            if ($payment->type == 'cart') {
                return $this->completeOrderCart($request, $payment);
            } elseif ($payment->type == 'wallet') {
                return $this->completeOrderWallet($request, $payment, $request->amount);
            } elseif ($payment->type == 'tip') {
                return $this->completeOrderTip($request, $payment);
            } elseif ($payment->type == 'subscription') {
                return $this->completeOrderSubs($request, $payment, $request);
            } elseif ($payment->type == 'pickup_delivery') {
                return $this->completePickupDelivery($request, $payment, $request);
            }
        } 
    }

    public function completeOrderCart($request, $payment)
    {
       $order = Order::where('order_number', $payment->transaction_id)->first();
       if (isset($request) && ($request->get('statusId') == '2')) 
       {

                $order->payment_status = '1';
                $order->save();
                $this->orderSuccessCartDetail($order);
                if($payment->payment_from != 'app'){

                        $returnUrl = route('order.success',[$order->id]);
                        return Redirect::to($returnUrl); 

                }else{

                    $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=200&order='.$order->order_number;
                    return Redirect::to($returnUrl);  
                }
                

       } else {

                $this->failedOrderWalletRefund($order);
                $this->sendWalletNotification($order->user_id, $order->order_number);
                if($payment->payment_from != 'app'){

                    return Redirect::to(route('showCart'))->with('error',$request->message);
                    
                } else {

                    $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=00&order='.$order->order_number;
                    return Redirect::to($returnUrl); 
                }
       }
   }


   public function completeOrderWallet($request, $payment)
  {
    if (isset($request) && ($request->get('statusId') == '2')){
           $user = User::findOrFail($payment->user_id);
           Auth::login($user);
           $wallet = $user->wallet;
           $wallet->depositFloat($payment->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $payment->transaction_id . '</b>']);
          if ($payment->payment_from == 'app') {
             $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=200&transaction_id='.$request->order_id.'&action=wallet';
             return Redirect::to($returnUrl);
           }else{
             return Redirect::to(route('user.wallet'))->with('success', 'Wallet amount added successfully.');
           }
      }else{
        if ($payment->payment_from == 'app') {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=200&transaction_id='.$request->order_id.'&action=wallet';
            return Redirect::to($returnUrl);
          }else{
            return Redirect::to(route('user.wallet'))->with('error', 'Amount Failed.');
          }
      }
  }

  public function completeOrderTip($request, $payment)
  {
    if (isset($request) && ($request->get('statusId') == '2')){
          $data['tip_amount'] = $request->amount;
          $data['order_number'] = $request->order_number;
          $data['transaction_id'] = $payment->transaction_id;

          $request = new \Illuminate\Http\Request($data);

          $orderController = new OrderController();
          $orderController->tipAfterOrder($request);
          if ($payment->payment_from == 'app') 
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=200&order='.$request->order_id.'&action=tip';
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('user.orders'))->with('success', 'Tip given successfully.');
          }
      }else{
      if ($payment->payment_from == 'app') {
        $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=200&transaction_id='.$request->order_id.'&action=tip';
        return Redirect::to($returnUrl);
      }else{
        return Redirect::to(route('user.orders'))->with('error', 'Failed.');
      }
    }

  }


  public function completeOrderSubs(Request $request,$payment)
  {
    if (isset($request) && ($request->get('statusId') == '2')){
          $subscription = explode('_',$payment->transaction_id);
          $request->request->add(['user_id' => $payment->user_id, 'payment_option_id' => 52, 'amount' => $payment->balance_transaction, 'transaction_id' => $request->transId]);
          $subscriptionController = new UserSubscriptionController();
          $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[0]);

          if(isset($payment->payment_from) && $payment->payment_from=='app')
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=200&transaction_id='.$request->transId.'&action=subscription';
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
          }
        }else{
          $payment->delete();

          if(isset($payment->payment_from) && $payment->payment_from=='app')
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=00&transaction_id='.$request->transId.'&action=subscription';
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
          }

        }
    //   return $this->successResponse($request->getTransactionReference());

  }

  public function completePickupDelivery($request, $payment)
  {
    $order = Order::where('order_number', $payment->transaction_id)->first();
    if (isset($request) && ($request->get('statusId') == '2')){
          $request->request->add(['order_number'=> $order->order_number, 'payment_option_id' => 52, 'amount' => $order->payable_amount, 'transaction_id' => $request->TransID]);
          $plaseOrderForPickup = new PickupDeliveryController();
          $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
         
          if($payment->payment_from=='app')
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=skip_cash'.'&status=200&order='.$payment->transaction_id;
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('front.booking.details',$order->order_number));
          }

        }else{
            //Failed transaction case
            $data = Payment::where('transaction_id',$payment->transaction_id)->first();
            $data->delete();

            return Redirect::to(route('front.booking.details'))->with('error',$request->message);
        }
  }



    public function handleWebhook(Request $request)
    {
            // Get the webhook payload
            $payload = json_decode($request->getContent(), true);
            // print_r($payload, true);

            // Log the payload to your application's logs

            // Do any additional processing based on the webhook payload
    }


    // public function failedPayment($request)
    // {
    // 	if($request->payment_from == 'cart'){
    //         $order_number = $request->order_number;
    //         $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
    //         $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
    //         foreach ($order_products as $order_prod) {
    //             OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
    //         }
    //         OrderProduct::where('order_id', $order->id)->delete();
    //         OrderProductPrescription::where('order_id', $order->id)->delete();
    //         VendorOrderStatus::where('order_id', $order->id)->delete();
    //         OrderVendor::where('order_id', $order->id)->delete();
    //         OrderTax::where('order_id', $order->id)->delete();
    //         Order::where('id', $order->id)->delete();
            
    //     }
      
    // }



    
}
