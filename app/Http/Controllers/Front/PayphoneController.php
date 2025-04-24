<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Auth;
use App\Http\Traits\ApiResponser;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserVendor;
use App\Models\CaregoryKycDoc;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Redirect;
use Log;
use App\Http\Controllers\Front\{FrontController, PickupDeliveryController};
use App\Models\ClientCurrency;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class PayphoneController extends FrontController
{
   use ApiResponser;

   private $id;
   private $token;
   private $url;

   public function __construct()
   {
      $payphone = PaymentOption::select('credentials', 'status')->where('code', 'payphone')->where('status', 1)->first();
      $json = json_decode($payphone->credentials);
      $this->id = $json->id;
      $this->token = $json->token;
      $this->app_url = 'https://pay.payphonetodoesposible.com/api/button/Prepare';
      $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD1';
        //\Log::info($primaryCurrency->currency->iso_code);
   }


   public function orderNumber($request)
    {
        $time = '';
        $amt = $request->amt??$request->amount;
       if(isset($request->auth_token) && !empty($request->auth_token)){
         $user = User::where('auth_token', $request->auth_token)->first();
         FacadesAuth::login($user);
       }else{
         $user = auth()->user();
       }
        $name = explode(' ',$user->name);
        $returnUrl = '';
        if($request->from == 'cart')
        {
         $request->amt = $amt;
         $time = $request->order_number;
         Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'cart','date'=>date('Y-m-d'),'user_id'=>auth()->id(),'payment_from'=>$request->device??'web']);
   
        }elseif($request->from == 'pickup_delivery')
        {
         $request->amt = $amt;
         $time = $request->order_number;
         Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'pickup_delivery','date'=>date('Y-m-d'),'user_id'=>auth()->id(),'payment_from'=>$request->device??'web']);
   
        }elseif($request->from == 'wallet')
        {
         $time = ($request->transaction_id)??'W_'.time();
         //Save transaction before payment success for get information only
         Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'wallet','date'=>date('Y-m-d'),'user_id'=>auth()->id(),'payment_from'=>$request->device??'web']);
         $request->amt = $amt;
   
        }elseif($request->from == 'tip')
        {
         $time = 'T_'.time().'_'.$request->order_number;
         Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'tip','date'=>date('Y-m-d'),'user_id'=>auth()->id(),'payment_from'=>$request->device??'web']);
        
         $request->amt = $amt;
         
        }elseif($request->from == 'subscription')
        {
         $time = 'S_'.time().'_'.$request->subsid??$request->subscription_id;
         Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'subscription','date'=>date('Y-m-d'),'user_id'=>auth()->id(),'payment_from'=>$request->device??'web']);
   
         $request->amt = $amt;
        }
        $request->request->add(['amt'=>getDollarCompareAmount($amt,$this->currency)]);
        return $time;
    }

    public function createHash(Request $request)
    {
      $request->request->add(['device'=>'web']);
        $order_number =  $this->orderNumber($request);
        $data = array(
            "amount" => getDollarCompareAmount($request->amt,$this->currency)*100,
            "amountWithoutTax" => getDollarCompareAmount($request->amt,$this->currency)*100,
            "currency" => $this->currency??'USD',
            "clientTransactionId" => $order_number,
            "responseUrl" => url($request->serverUrl.'payment/payphone/success'),
            "cancellationUrl" => url($request->serverUrl.'payment/payphone/success')
        );
          
        $url = $this->postCurl($data,$this->token);
        return $url;
    }


   public function createHashApp(Request $request)
   {
    $user = auth()->user();
    $request->request->add(['from'=>$request->action,'amt'=>$request->amount,'subsid'=>$request->subscription_id??'','device'=>'app']);
    $order_number =  $this->orderNumber($request);
    $data = array(
        "amount" => getDollarCompareAmount($request->amt,$this->currency)*100,
        "amountWithoutTax" => getDollarCompareAmount($request->amt,$this->currency)*100,
        "currency" => $this->currency??'USD',
        "clientTransactionId" => $order_number,
        "responseUrl" => url($request->serverUrl.'payment/payphone/success'),
        "cancellationUrl" => url($request->serverUrl.'payment/payphone/success')
    );

      $urlResp = $this->postCurl($data,$this->token); 
      if($urlResp->paymentId){
      $url = $urlResp->payWithCard;
      return $this->successResponse(url($request->serverUrl.'payment/payphone/api/?url='.$url.'&token='.$user->auth_token)); 
      }
      return $this->error($urlResp->message??'Somthing went wrong.');

   } 


   public function webViewPay(Request $request)
   {
    $token = $token??$request->token;
    if(isset($token) && !empty($token)){
        $user = User::where('auth_token', $token)->first();
        Auth::login($user);
        $user->auth_token = $token;
        $user->save();
     }
    $url = $request->url;
    return view('frontend.payment_gatway.payphone_view', compact('url'));
   }

   public function refundWalletAmount(Request $request)
   {
    $order = Order::where('user_id',auth()->id())->latest()->first();
    $user = auth()->user();
            $wallet = $user->wallet;
            if(isset($order->wallet_amount_used)){
              $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
            }
          return  redirect()->back();
   }

   public function successPage(Request $request)
   {   
       $payment = Payment::where('transaction_id',$request->clientTransactionId)->first();
       if($payment->type=='cart'){
          return $this->completeOrderCart($request,$payment);
        }elseif($payment->type=='wallet'){
            return $this->completeOrderWallet($request,$payment);
        }elseif($payment->type=='tip'){
            return $this->completeOrderTip($request,$payment);
        }elseif($payment->type=='subscription'){
            return $this->completeOrderSubs($request,$payment);
        }elseif($payment->type=='pickup_delivery'){
          return $this->completeOrderPickup($request,$payment);
      }
   }


   public function completeOrderPickup(Request $request,$payment)
   {
        $order = Order::where('order_number',$request->clientTransactionId)->first();
        if(isset($request->clientTransactionId) && $request->id>0)
          {
          if ($order) {
              $order->payment_status = 1;
              $order->save();
              $payment_exists = Payment::where('transaction_id', $request->clientTransactionId)->first();
              if (!$payment_exists) {
                  $payment = new Payment();
                  $payment->date = date('Y-m-d');
                  $payment->type = 'pickup_delivery';
                  $payment->order_id = $order->id;
                  $payment->payment_option_id = 32;
                  $payment->user_id = $order->user_id;
                  $payment->transaction_id = $request->id;
                  $payment->balance_transaction = $order->payable_amount;
                  $payment->save();
              }
                
              $request->request->add(['order_number'=> $order->order_number, 'payment_option_id' => 32, 'amount' => $order->payable_amount, 'transaction_id' => $request->id]);
              
              $plaseOrderForPickup = new PickupDeliveryController();
              $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);

              if($payment->payment_from=='app')
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=200&order='.$order->order_number;
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('front.booking.details',$order->order_number));
              }
          }
      }else{
      //Failed transaction case

          $data = Payment::where('transaction_id',$request->clientTransactionId)->first();
          $data->delete();

          if($payment->payment_from=='app')
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=00&transaction_id='.$request->id.'&action=wallet';
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('user.wallet'))->with('error',$request->message);
          }



      }


   }

   public function completeOrderCart(Request $request,$payment)
    {

      $order = Order::where('order_number',$request->clientTransactionId)->first();
          if(isset($request->clientTransactionId) && $request->id>0)
          {
           
            $order->payment_status = '1';
            $order->save();

            // Auto accept order
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id',auth()->id())->select('id')->first();
            $cartid = $cart->id;
            CaregoryKycDoc::where('cart_id',$cart->id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
            Cart::where('id', $cartid)->update([
              'schedule_type' => null, 'scheduled_date_time' => null,
              'comment_for_pickup_driver' => null, 'comment_for_dropoff_driver' => null, 'comment_for_vendor' => null, 'schedule_pickup' => null, 'schedule_dropoff' => null, 'specific_instructions' => null
          ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();
            // send sms 
            $this->sendSuccessSMS($request, $order);
            Payment::create(['amount'=>0,'transaction_id'=>$request->id,'balance_transaction'=>$order->payable_amount,'type'=>'cart','date'=>date('Y-m-d'),'order_id'=>$order->id]);

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

          if($payment->payment_from=='app')
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=200&order='.$order->order_number;
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('order.success',[$order->id]));
          }

          }else{
            $user = auth()->user();
            $wallet = $user->wallet;
            if(isset($order->wallet_amount_used)){
              $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
              $this->sendWalletNotification($order->user_id, $order->order_number);
            }
            if($payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&order='.$order->order_number;
              return Redirect::to($returnUrl);  
            }else{
              return Redirect::to(route('showCart'))->with('error',$request->message);
            }

          }

        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderWallet(Request $request,$payment)
    {
          if(isset($request->clientTransactionId) && $request->id>0)
          {
            $data = Payment::where('transaction_id',$request->clientTransactionId)->first();
            $user = auth()->user();
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->clientTransactionId . '</b>']);

            if($payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=200&transaction_id='.$request->id.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('success','Wallet amount added.');
            }

            
          }else{
            $data = Payment::where('transaction_id',$request->clientTransactionId)->first();
            $data->delete();

            if($payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=00&transaction_id='.$request->id.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('error',$request->message);
            }

           
          }
        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderSubs(Request $request,$payment)
    {
      $user = auth()->user();
      $data = Payment::where('transaction_id',$request->clientTransactionId)->first();
      if(isset($request->clientTransactionId) && isset($request->id))
          {
            $subscription = explode('_',$request->clientTransactionId);
            $request->request->add(['user_id' => $user->id, 'payment_option_id' => 32, 'amount' => $data->balance_transaction, 'transaction_id' => $request->clientTransactionId]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[2]);

            if($payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=200&transaction_id='.$request->id.'&action=subscription';
              return Redirect::to($returnUrl);
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('success',$request->message);
            }
          }else{
            $data->delete();

            if($payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=00&transaction_id='.$request->id.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
            }

          }
        return $this->successResponse($request->getTransactionReference());

    }

    public function completeOrderTip(Request $request,$payment)
    {
      $data = Payment::where('transaction_id',$request->clientTransactionId)->first();
      if(isset($request->clientTransactionId) && isset($request->id))
          {
            $order_number = explode('_',$request->clientTransactionId);
            $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[2], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $request->clientTransactionId]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);

            if($payment->payment_from=='app')
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=200&order='.$order_number[2].'&action=tip';
                return Redirect::to($returnUrl);
              }else{
                return Redirect::to(route('user.orders'))->with('success', "Tip added Successfully.");
              }

          }else{
            $data->delete();

            if($payment->payment_from=='app')
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=payphone'.'&status=00&transaction_id='.$request->clientTransactionId.'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('error', $request->message);
              }

          }
        return $this->successResponse($request->getTransactionReference());

    }


    private function postCurl($data,$token=null):object{

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->app_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
      $headers = array();
      $headers[] = 'Accept: */*';
      if(!is_null($token)){

         $headers[] = "Authorization: Bearer ${token}";
          // dd( $headers);
      }
    $headers[] = 'Content-Type: application/json';
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $result = curl_exec($ch);
      if (curl_errno($ch)) {
          // echo 'Error:' . curl_error($ch);
          //\Log::info(curl_error($ch));
      }
      curl_close($ch);
      //\Log::info('result==');
      //\Log::info(json_encode($result));
      return json_decode($result); 
  }

}
