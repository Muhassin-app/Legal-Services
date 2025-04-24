<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Front\FrontController;
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

class FlutterWaveController extends FrontController
{
   use ApiResponser;

   private $secret_key;
   private $public_key;
   private $enc_key;
   private $url;

   public function __construct()
   {
      $konga = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'flutterwave')->where('status', 1)->first();
      if(@$konga->status){
          $json = json_decode($konga->credentials);
          $this->secret_key = $json->secret_key;
          $this->public_key = $json->client_id;
          $this->enc_key = $json->enc_key;
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
     $returnUrl = '';
     if($request->from == 'cart')
     {
      $request->amt = $amt;
      $time = $request->order_number;
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'cart','date'=>date('Y-m-d')]);

     }elseif($request->from == 'pickup_delivery')
     {
      $request->amt = $amt;
      $time = $request->order_number;
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'pickup_delivery','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);
      //,'payment_from'=>$request->device??'web'
     }elseif($request->from == 'wallet')
     {
      $time = ($request->transaction_id)??'W_'.time();
      //Save transaction before payment success for get information only
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'wallet','date'=>date('Y-m-d')]);
      $request->amt = $amt;

     }elseif($request->from == 'tip')
     {
      $time = 'T_'.time().'_'.$request->order_number;
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'tip','date'=>date('Y-m-d')]);
      $request->amt = $amt;
      
     }elseif($request->from == 'subscription')
     {
      $time = ($request->subscription_id)??'S_'.time().'_'.$request->subsid;
      Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'subscription','date'=>date('Y-m-d')]);

      $request->amt = $amt;
     
     }
     $returnUrl = route('flutterwave.success');

     //Need to save entry in payment table

    $data = array(
        'public_key'=> $this->public_key,
        'tx_ref'=> $time,
        'amount'=> $request->amt??0,
        'currency'=> "NGN",
        'payment_options'=> "card, banktransfer, ussd",
        'redirect_url'=> $returnUrl,
        // 'meta'=> [
        //   'consumer_id'=> 23,
        //   'consumer_mac'=> "92a3-912ba-1192a",
        // ],
        'customer'=> [
          'email'=> $user->email??'',
          'phone_number'=> $user->phone_number,
          'name'=> $user->name,
        ],
        // 'customizations'=> [
        //   'title'=> "The Titanic Store",
        //   'description'=> "Payment for an awesome cruise",
        //   'logo'=> "https://www.logolynx.com/images/logolynx/22/2239ca38f5505fbfce7e55bbc0604386.jpeg",
        // ],
    );
    // dd($data);
      return json_encode($data);
   }  


  //  public function webViewPay(Request $request)
  //  {
  //       $request->request->add(['amt'=>$request->amount,'from'=>$request->from,'order_number'=>$request->order_no??time()]);
  //       $data = json_decode($this->createHash($request));
  //       $inputs = '
  //       <input type="text" value="'.$data->hash.'" name="hash"/>
  //       <input type="number" value="'.$data->amount.'" name="amount"/>
  //       <input type="text" value="mobile payment" name="description">
  //       <input type="email" value="'.$data->email.'" name="email">
  //       <input type="text" value="Kongadel" name="merchant_id">
  //       <input type="text" value="'.$data->reference.'" name="reference">
  //       <input type="text" value="'.$data->firstname.'" name="firstname">
  //       <input type="text" value="'.$data->lastname.'" name="lastname">
  //       <input type="text" value="'.$data->phone.'" name="phone">
  //       <input type="text" value="'.$data->callback.'" name="callback">
  //       <input type="text" value="'.$data->customerId.'" name="customerId">
  //       ';
  //       return view('frontend.payment_gatway.kongapay_view', compact('inputs'));
  //  }

  //  public function kongapayPurchase(Request $request)
  //  {
  //      $amount = $request->amount;
  //      $user = auth()->user();
  //      $action = isset($request->action) ? $request->action : ''; 
  //      $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&from='.$action;
  //      if($action == 'cart'){
  //          $params = $params . '&order_no=' . $request->order_number.'&app=1';
  //      }elseif($action == 'wallet'){
  //        //app = 2 is for wallet
  //       $params = $params .'&app=2&transaction_id=W_'.time();
  //      }elseif($action == 'subscription'){
  //       //app = 2 is for wallet
  //      $params = $params .'&app=3&subscription_id='.'S_'.time().'_'.$request->subscription_id;
  //     }elseif($action == 'tip'){
  //       //app = 2 is for wallet
  //      $params = $params .'&app=3&order_no='.$request->order_number;
  //     }

  //      return $this->successResponse(url($request->serverUrl.'payment/flutterwave/api/'.$params)); 
  //  }



   public function successPage(Request $request)
   {
    $payment = Payment::where('transaction_id',$request->tx_ref)->first();
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
      $order = Order::where('order_number',$request->tx_ref)->first();
        if(isset($request->tx_ref) && ($request->status == 'successful' || $request->status == 'completed'))
          {
            if ($order) {
                $order->payment_status = 1;
                $order->save();
                $payment_exists = Payment::where('transaction_id', $request->order_no)->first();
                if (!$payment_exists) {
                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->type = 'pickup_delivery';
                    $payment->order_id = $order->id;
                    $payment->payment_option_id = 30;
                    $payment->user_id = $order->user_id;
                    $payment->transaction_id = $request->TransID;
                    $payment->balance_transaction = $order->payable_amount;
                    $payment->save();
                }
                
                $request->request->add(['order_number'=> $order->order_number, 'payment_option_id' => 30, 'amount' => $order->payable_amount, 'transaction_id' => $request->TransID]);
                $plaseOrderForPickup = new PickupDeliveryController();
                $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                return Redirect::to(route('front.booking.details',$order->order_number));
            }
        }else{
            //Failed transaction case
            $data = Payment::where('transaction_id',$request->order_no)->first();
            $data->delete();

            return Redirect::to(route('user.wallet'))->with('error',$request->message);
        }
    }


   public function completeOrderCart(Request $request)
    {

      $order = Order::where('order_number',$request->tx_ref)->first();
          if(isset($request->tx_ref) && ($request->status == 'successful' || $request->status == 'completed'))
          {
           
            $order->payment_status = '1';
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
            Payment::create(['amount'=>0,'transaction_id'=>$request->tx_ref,'balance_transaction'=>$order->payable_amount,'type'=>'cart','date'=>date('Y-m-d'),'order_id'=>$order->id]);

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
            $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=200&order='.$order->order_number;    
            return Redirect::to($returnUrl); 
          }else{
            return Redirect::to(route('order.success',[$order->id]));
          }

          }else{
            $user = auth()->user();
            $wallet = $user->wallet;
            if(isset($order->wallet_amount_used)){
              $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
              $this->sendWalletNotification($user->id, $order->order_number);
            }
            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=00&order='.$order->order_number;
              return Redirect::to($returnUrl);  
            }else{
              return Redirect::to(route('showCart'))->with('error',$request->message);
            }

          }

        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderWallet(Request $request)
    {
        if(isset($request->tx_ref) && ($request->status == 'successful' || $request->status == 'completed'))
          {
            $data = Payment::where('transaction_id',$request->tx_ref)->first();
            $user = auth()->user();
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->tx_ref . '</b>']);

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=200&transaction_id='.$request->tx_ref.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('success','Wallet amount added successfuly.');
            }

            
          }else{
            $data = Payment::where('transaction_id',$request->tx_ref)->first();
            $data->delete();

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=00&transaction_id='.$request->tx_ref.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('error',$request->message);
            }

           
          }
        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderSubs(Request $request)
    {
      $user = auth()->user();
      $data = Payment::where('transaction_id',$request->tx_ref)->first();
      if(isset($request->tx_ref) && ($request->status == 'successful' || $request->status == 'completed'))
          {
            $subscription = explode('_',$request->tx_ref);
            $request->request->add(['user_id' => $user->id, 'payment_option_id' => 30, 'amount' => $data->balance_transaction, 'transaction_id' => $request->tx_ref]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[2]);

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=200&transaction_id='.$request->tx_ref.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('success','Subscription added successfuly.');
            }
          }else{
            $data->delete();

            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=00&transaction_id='.$request->tx_ref.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
            }

          }
        return $this->successResponse($request->getTransactionReference());

    }

    public function completeOrderTip(Request $request)
    {
      $data = Payment::where('transaction_id',$request->tx_ref)->first();
      if(isset($request->tx_ref) && ($request->status == 'successful' || $request->status == 'completed'))
          {
            $order_number = explode('_',$request->tx_ref);
            $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[2], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $request->tx_ref]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);

            if(isset($request->auth_token) && !empty($request->auth_token))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=200&order='.$order_number[2].'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('success','Tip added successfuly.');
              }

          }else{

            $data->delete();

              if(isset($request->auth_token) && !empty($request->auth_token))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=flutterwave'.'&status=00&transaction_id='.$request->merchant_reference.'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('error', $request->message);
              }

          }
        return $this->successResponse($request->getTransactionReference());

    }

}
