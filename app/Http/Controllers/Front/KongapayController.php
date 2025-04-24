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
use App\Http\Controllers\Front\FrontController;

class KongapayController extends FrontController
{
   use ApiResponser;

   private $api_key;
   private $merchant_id;
   private $url;

   public function __construct()
   {
      $konga = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'kongapay')->where('status', 1)->first();
      if(@$konga->status){
          $json = json_decode($konga->credentials);
        $this->api_key = $json->api_key;
        $this->merchant_id = $json->merchant_id;
      }
   }

   public function createHash(Request $request)
   {
     $time = '';
     $amt = $request->amt??$request->amount;
    if(isset($request->auth_token) && !empty($request->auth_token)){
      $user = User::where('auth_token', $request->auth_token)->first();
      Auth::login($user);
    }else{
      $user = auth()->user();
    }
     $name = explode(' ',$user->name);
     $returnUrl = '';
     if($request->from == 'cart')
     {
      $request->amt = $amt*100;
      $time = $request->order_number;

      if(isset($request->app) && !empty($request->app))
      {
        $returnUrl = route('kongapay.successCart',['from'=>'?auth_token='.$request->auth_token]);
      }else{ 
        $returnUrl = route('kongapay.successCart');
      }

     }elseif($request->from == 'wallet')
     {
      $time = ($request->transaction_id)??'W_'.time();
      //Save transaction before payment success for get information only
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'wallet','date'=>date('Y-m-d')]);
      $request->amt = $amt*100;

      if(isset($request->app) && !empty($request->app))
      {
        $returnUrl = route('kongapay.successWallet',['transaction_id='.$time]);
      }else{ 
        $returnUrl = route('kongapay.successWallet');
      }

     }elseif($request->from == 'tip')
     {
      $time = 'T_'.time().'_'.$request->order_number;
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'tip','date'=>date('Y-m-d')]);
     
      $request->amt = $amt*100;
      if(isset($request->app) && !empty($request->app))
      {
        $returnUrl = route('kongapay.successTip',['order_no='.$time]);
      }else{ 
        $returnUrl = route('kongapay.successTip');
      }
      
     }elseif($request->from == 'subscription')
     {
      $time = ($request->subscription_id)??'S_'.time().'_'.$request->subsid;
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'subscription','date'=>date('Y-m-d')]);

      $request->amt = $amt*100;
      
      if(isset($request->app) && !empty($request->app))
      {
        $returnUrl = route('kongapay.successSubs',['subscription_id='.$time]);
      }else{ 
        $returnUrl = route('kongapay.successSubs');
      }

     
     }
       
     $key = $request->amt.'|'.$this->api_key.'|'.$time;

     //Need to save entry in payment table

     $data = (object)array(
            "hash"=> hash('Sha512',$key),
            "amount"=> $request->amt??0,
            "description"=> "web payment",
            "email"=> $user->email??'',
            "merchantId"=> $this->merchant_id,
            "reference"=> $time,
            "firstname" => $name[0]??'',
            "lastname" => $name[1]??'last name',
            "phone" => $user->phone_number,
            "enableFrame"=> true,
            "callback" => $returnUrl,
            "customerId" => $user->email
        );
      return json_encode($data);
   }  


   public function webViewPay(Request $request)
   {
     $request->request->add(['amt'=>$request->amount,'from'=>$request->from,'order_number'=>$request->order_no??time()]);
    // $data = $request->all();
    // $request['from']=$request->from;
    // $request['amt']=$request->amount??'100';
    // $request['order_number']=$request->order_no??time(); // order no
    $data = json_decode($this->createHash($request));
    $inputs = '
    <input type="text" value="'.$data->hash.'" name="hash"/>
    <input type="number" value="'.$data->amount.'" name="amount"/>
    <input type="text" value="mobile payment" name="description">
    <input type="email" value="'.$data->email.'" name="email">
    <input type="text" value="Kongadel" name="merchant_id">
    <input type="text" value="'.$data->reference.'" name="reference">
    <input type="text" value="'.$data->firstname.'" name="firstname">
    <input type="text" value="'.$data->lastname.'" name="lastname">
    <input type="text" value="'.$data->phone.'" name="phone">
    <input type="text" value="'.$data->callback.'" name="callback">
    <input type="text" value="'.$data->customerId.'" name="customerId">
    ';
    return view('frontend.payment_gatway.kongapay_view', compact('inputs'));
   }

   public function kongapayPurchase(Request $request)
   {
       $amount = $request->amount;
       $user = auth()->user();
       $action = isset($request->action) ? $request->action : ''; 
       $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&from='.$action;
       if($action == 'cart'){
           $params = $params . '&order_no=' . $request->order_number.'&app=1';
       }elseif($action == 'wallet'){
         //app = 2 is for wallet
        $params = $params .'&app=2&transaction_id=W_'.time();
       }elseif($action == 'subscription'){
        //app = 2 is for wallet
       $params = $params .'&app=3&subscription_id='.'S_'.time().'_'.$request->subscription_id;
      }elseif($action == 'tip'){
        //app = 2 is for wallet
       $params = $params .'&app=3&order_no='.$request->order_number;
      }

       return $this->successResponse(url($request->serverUrl.'payment/kongapay/api/'.$params)); 
   }

   public function completeOrderCart(Request $request)
    {

      $order = Order::where('order_number',$request->merchant_reference)->first();
          if(isset($request->merchant_reference) && $request->status == 'success')
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
            Payment::create(['amount'=>0,'transaction_id'=>$request->merchant_reference,'balance_transaction'=>$order->payable_amount,'type'=>'cart','date'=>date('Y-m-d'),'order_id'=>$order->id]);

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
            $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=200&order='.$order->order_number;
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
              $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&order='.$order->order_number;
              return Redirect::to($returnUrl);  
            }else{
              return Redirect::to(route('showCart'))->with('error',$request->message);
            }

          }

        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderWallet(Request $request)
    {
          if(isset($request->merchant_reference) && $request->status == 'success')
          {
            $data = Payment::where('transaction_id',$request->merchant_reference)->first();
            $user = auth()->user();
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->merchant_reference . '</b>']);

            if(isset($request->transaction_id) && !empty($request->transaction_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=200&transaction_id='.$request->merchant_reference.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('success','Wallet updated successfully.');
            }

            
          }else{
            $data = Payment::where('transaction_id',$request->merchant_reference)->first();
            $data->delete();

            if(isset($request->transaction_id) && !empty($request->transaction_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&transaction_id='.$request->merchant_reference.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('error','Wallet not updated.');
            }

           
          }
        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderSubs(Request $request)
    {
      $user = auth()->user();
      $data = Payment::where('transaction_id',$request->merchant_reference)->first();
      if(isset($request->merchant_reference) && $request->status == 'success')
          {
            $subscription = explode('_',$request->merchant_reference);
            $request->request->add(['user_id' => $user->id, 'payment_option_id' => 20, 'amount' => $data->balance_transaction, 'transaction_id' => $request->merchant_reference]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[2]);

            if(isset($request->subscription_id) && !empty($request->subscription_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=200&transaction_id='.$request->merchant_reference.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('success','Subscription added successfully.');
            }
          }else{
            $data->delete();

            if(isset($request->subscription_id) && !empty($request->subscription_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&transaction_id='.$request->merchant_reference.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('error','Somthing went wrong please try again.');
            }

          }
        return $this->successResponse($request->getTransactionReference());

    }

    public function completeOrderTip(Request $request)
    {
      $data = Payment::where('transaction_id',$request->merchant_reference)->first();
      if(isset($request->merchant_reference) && $request->status == 'success')
          {
            $order_number = explode('_',$request->merchant_reference);
            $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[2], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $request->merchant_reference]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);

            if(isset($request->order_no) && !empty($request->order_no))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=200&order='.$order_number[2].'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('success','Tip added successfully.');
              }

          }else{
            $data->delete();

              if(isset($request->order_no) && !empty($request->order_no))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&transaction_id='.$request->merchant_reference.'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('error','Somthing went wrong please try again.');
              }

          }
        return $this->successResponse($request->getTransactionReference());

    }

}
