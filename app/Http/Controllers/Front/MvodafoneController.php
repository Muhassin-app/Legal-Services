<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Front\FrontController;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Auth;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\Mvodafone;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserVendor;
use App\Models\CaregoryKycDoc;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Redirect;
use Log;

class MvodafoneController extends FrontController
{
   use ApiResponser, Mvodafone;

   private $secret_key;
   private $client_id;
   private $url;
   private $test_mode;


   public function __construct()
  {
       $viva = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'mvodafone')->where('status', 1)->first();
       if(@$viva->status){
           $json = json_decode($viva->credentials);
           $this->secret_key = $json->secret_key;
           $this->client_id = $json->client_id;
           $this->test_mode = $viva->test_mode;
       }
  }

   public function credentials()
   {
        $viva = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'mvodafone')->where('status', 1)->first();
        if(@$viva->status){
            $json = json_decode($viva->credentials);
            $this->secret_key = $json->secret_key;
            $this->client_id = $json->client_id;
            $this->test_mode = $viva->test_mode;
        }
   }

   public function createToken()
   {
    $this->credentials();
    return $this->getAuthTokenViva();
   }

   public function orderNumber($request)
   {
        if($request->from == 'cart')
        {
            $time = $request->order_number;
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'cart','date'=>date('Y-m-d'),'user_id'=>auth()->user()->id]);

        }elseif($request->from == 'wallet')
        {
            $time = $request->transaction_id??time();
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'wallet','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);

        }elseif($request->from == 'tip')
        {
             $time = time();
             Payment::create(['amount'=>0,'transaction_id'=>$request->order_number.'_'.$time,'balance_transaction'=>$request->amt,'type'=>'tip','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);

        }elseif($request->from == 'subscription')
        {
            $time = time();
            Payment::create(['amount'=>0,'transaction_id'=>$request->subsid.'_'.$time,'balance_transaction'=>$request->amt,'type'=>'subscription','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);
            
        }
        return $time;
   }

   public function createPayLink(Request $request)
   {
    $number =  $this->orderNumber($request);
    $user = auth()->user();
    $this->credentials();

      $data  = [
              'amount'              => $this->getDollarCompareAmount($request->amt),
              'order_no'            => $number,
              'returnUrl'           => route('mvodafone.success'),
          ];
      $response = $this->createPaymentLinkVodafone($data);
      if(isset($response) && $response->url){

        if($request->from == 'cart'){
           Payment::where('transaction_id',$number)->update(['viva_order_id'=>$response->reqId]);
          }elseif($request->from == 'wallet'){
          Payment::where('transaction_id',$number)->update(['viva_order_id'=>$response->reqId]);
         }elseif($request->from == 'tip'){
         Payment::where('transaction_id',$request->order_number.'_'.$number)->update(['viva_order_id'=>$response->reqId]);
        }elseif($request->from == 'subscription'){
        Payment::where('transaction_id',$request->subsid.'_'.$number)->update(['viva_order_id'=>$response->reqId]);
       }
        return $response;
      }else{
        return false;
      }
   } 

   public function createPayLinkApp(Request $request)
   {
    $request->request->add(['from'=>$request->action,'amt'=>$request->amount,'subsid'=>$request->subscription_id??'']);
    $number =  $this->orderNumber($request);
    $user = auth()->user();
    $this->credentials();

      $data  = [
              'amount'              => $this->getDollarCompareAmount($request->amt),
              'order_no'            => $number,
              'returnUrl'           => url($request->serverUrl.'payment/mvsuccess?auth_token='.$user->id.'&')
          ];
      $response = $this->createPaymentLinkVodafone($data);
      if(isset($response) && $response->url){

        if($request->from == 'cart'){
           Payment::where('transaction_id',$number)->update(['viva_order_id'=>$response->reqId]);
          }elseif($request->from == 'wallet'){
          Payment::where('transaction_id',$number)->update(['viva_order_id'=>$response->reqId]);
         }elseif($request->from == 'tip'){
         Payment::where('transaction_id',$request->order_number.'_'.$number)->update(['viva_order_id'=>$response->reqId]);
        }elseif($request->from == 'subscription'){
        Payment::where('transaction_id',$request->subsid.'_'.$number)->update(['viva_order_id'=>$response->reqId]);
       }
       return $this->successResponse($response->url);
      }else{
        return false;
      }
   }


   public function successPage(Request $request)
   {
     //dd($request->auth_token);
    if(isset($request->auth_token))
    {
      $user = User::find($request->auth_token);
      auth()->login($user);
    }
    $payment = Payment::where('viva_order_id',$request->rID)->first();
        if($payment->type=='cart'){
          return $this->completeOrderCart($request,$payment);
        }elseif($payment->type=='wallet'){
            return $this->completeOrderWallet($request,$payment);
        }elseif($payment->type=='tip'){
            return $this->completeOrderTip($request,$payment);
        }elseif($payment->type=='subscription'){
            return $this->completeOrderSubs($request,$payment);
        }
   }

   public function completeOrderCart($request,$payment)
    {
      $order = Order::where('order_number',$payment->transaction_id)->first();
          if(isset($request->rCode) && $request->rCode == '101')
          {
            $order->payment_status = '1';
            $order->viva_order_id = $request->rID;
            $order->save();

            // Auto accept order
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id',auth()->id())->select('id')->first();
            $cartid = $cart->id;
            Cart::where('id', $cartid)->update([
              'schedule_type' => null, 'scheduled_date_time' => null,
              'comment_for_pickup_driver' => null, 'comment_for_dropoff_driver' => null, 'comment_for_vendor' => null, 'schedule_pickup' => null, 'schedule_dropoff' => null, 'specific_instructions' => null
          ]);
            CaregoryKycDoc::where('cart_id',$cartid)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();
            
            // send sms 
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

          if(isset($request->auth_token) && !empty($request->auth_token))
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=200&order='.$order->order_number;
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('order.success',[$order->id]));
          }

          }else{
            $user = auth()->user();
            $wallet = $user->wallet;
            if(isset($order->wallet_amount_used)){
              $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
            }
            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=00&order='.$order->order_number;
              return Redirect::to($returnUrl);  
            }else{
              return Redirect::to(route('showCart'))->with('error',$request->message);
            }

          }

        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderWallet(Request $request,$payment)
    {
      if(isset($request->rCode) && $request->rCode == '101')
          {
            $data = Payment::where('viva_order_id',$request->rID)->first();
            $user = auth()->user();
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->rID . '</b>']);

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=200&transaction_id='.$request->rID.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('success','Wallet amount added successfuly.');
            }

            
          }else{
            $data = Payment::where('viva_order_id',$request->rID)->first();
            $data->delete();

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=00&transaction_id='.$request->rID.'&action=wallet';
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
      $data = Payment::where('viva_order_id',$request->rID)->first();
      if(isset($request->rCode) && $request->rCode == '101')
          {
            $subscription =explode('_',$data->transaction_id);
            $subscription =$subscription[0];
            $request->request->add(['user_id' => $user->id, 'payment_option_id' => 29, 'amount' => $data->balance_transaction, 'transaction_id' => $request->rID]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=200&transaction_id='.$request->rID.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('success','Subscription added successfuly.');
            }
          }else{
            $data->delete();

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=00&transaction_id='.$request->rID.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
            }

          }
        return $this->successResponse($request->getTransactionReference());

    }

    public function completeOrderTip(Request $request,$payment)
    {
      $data = Payment::where('viva_order_id',$request->rID)->first();
      if(isset($request->rCode) && $request->rCode == '101')
          {
            $order_number = explode('_',$data->transaction_id);
            $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[0], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $data->transaction_id]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);

            if(isset($request->auth_token) && !empty($request->auth_token))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=200&order='.$order_number[0].'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('success','Tip added successfuly.');
              }

          }else{
            $data->delete();

              if(isset($request->auth_token) && !empty($request->auth_token))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mvodafone'.'&status=00&transaction_id='.$data->transaction_id.'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('error', $request->message);
              }

          }
        return $this->successResponse($request->getTransactionReference());

    }


}
