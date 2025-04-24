<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Auth;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\Mpesa;
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
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Redirect;
use Log;
use Illuminate\Support\Str;

class MpesaController extends Controller
{
   use ApiResponser, Mpesa;

    // private $merchant_key;
    // private $merchant_id;
    // private $client_key;
    // private $client_id;
    // private $url;
    // private $tokenUrl;
    // private $test_mode;

  

   public function credentials()
    {
         $viva = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'viva_wallet')->where('status', 1)->first();
         if(@$viva->status){
             $json = json_decode($viva->credentials);
             $this->client_key = $json->client_key;
             $this->client_id = $json->client_id;
             $this->merchant_key = $json->merchant_key;
             $this->merchant_id = $json->merchant_id;
             $this->test_mode = $viva->test_mode;
         }
    }

    //Initiate STK Push
    public function stkPushRequest(Request $request){

        $accountReference='Transaction#'.Str::random(10);
        $amount= '20';
        $phone=$this->formatPhone('708374149');

        //$mpesa=new MpesaStkpush();
        $stk=$this->lipaNaMpesa(1,$phone,$accountReference);
        // dd('hi');
        // $invalid=json_decode($stk);
        // if(@$invalid->errorCode){
        //     // dd($invalid->errorCode);
        //     echo  'Invalid phone number!';
        //     echo  'alert-danger';
        //     exit;
        //     return back();
        // }
       // dd($stk);
       return $stk;
    }

    public function checkTransactionStatus($transactionCode){

        // $mpesa=new MpesaStkpush();
        $status=$this->status($transactionCode);

        $tStatus = $status->{'ResponseCode'};

        return $tStatus;
    }

    public function formatPhone($phone)
    {
        $phone = 'hfhsgdgs' . $phone;
        $phone = str_replace('hfhsgdgs0', '', $phone);
        $phone = str_replace('hfhsgdgs', '', $phone);
        $phone = str_replace('+', '', $phone);
        if (strlen($phone) == 9) {
            $phone = '254' . $phone;
        }
        return $phone;
    }


   public function orderNumber($request)
   {
        if($request->from == 'cart')
        {
            $time = $request->order_number;
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'cart','date'=>date('Y-m-d'),'user_id'=>auth()->user()->id]);

        }elseif($request->from == 'wallet')
        {
            $time = ($request->transaction_id)??'W_'.time();
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'wallet','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);

        }elseif($request->from == 'tip')
        {
             $time = 'T_'.time().'_'.$request->order_number;
             Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'tip','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);

        }elseif($request->from == 'subscription')
        {
            $time = ($request->subscription_id)??'S_'.time().'_'.$request->subsid;
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'subscription','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);
            
        }
        return $time;
   }

  //  public function payForm(Request $request)
  //  {
  //   $number = $this->orderNumber($request); // order no
  //   return $this->createPayLink($request); 
  //  }

   public function createPayLink(Request $request)
   {
    // //\Log::info($request->all());
    $number =  $this->orderNumber($request);
    $user = auth()->user();
    $this->credentials();
            $data  = [
              'amount'              => intval($request->amt),
              'customerTrns'        => $number,
              'customer'            => [
                  'email'         => $user->email,
                  'fullName'      => $user->name,
                  'phone'         => $user->phone_number,
                  'countryCode'   => 'EN',
                  'requestLang'   => 'el-EN'
              ],
              'paymentTimeout'      => 0,
              'preauth'             => false,
              'allowRecurring'      => false,
              'maxInstallments'     => 0,
              'paymentNotification' => true,
              'tipAmount'           => 0,
              'disableExactAmount'  => false,
              'disableCash'         => false,
              'disableWallet'       => false,
              'sourceCode'          => 'Default',
              'merchantTrns'        => $number
          ];
      $response = $this->createOrderPaymentLink($data);
      if($response->orderCode){
          // if($request->from != ''){
          // //   $payId = Payment::where('transaction_id',$number)->first();
          // //   $payId->viva_order_id = $response->orderCode;
          // //   $payId->save();
          // // }else{
          // //   $orderId = Order::where('order_number',$number)->first();
          // //   $orderId->viva_order_id = $response->orderCode;
          // //   $orderId->save();

            $payId = Payment::where('transaction_id',$number)->first();
            $payId->viva_order_id = $response->orderCode;
            $payId->user_id = auth()->id();
            $payId->save();
          // }
      }

      return $this->sendResponse($response);
   }  

   public function sendResponse($response)
   {
    $this->credentials();
        if($this->test_mode=='1'){
          $this->api_url = 'https://demo.vivapayments.com/web/checkout?ref='.$response->orderCode;            
          }else{
          $this->api_url = 'https://vivapayments.com/web/checkout?ref='.$response->orderCode;
          }
         return $this->api_url;
        // header('Location: '.$this->api_url);
        //exit;
   }

   public function verifyWebhookUrl($response)
   {
      $key = $this->verificationWebhookKey();
      // $key =  json_encode($key);
       echo $key->Key;
   }


   public function webViewPay(Request $request)
   {
    // $data = $request->all();
    $request['from']=$request->from;
    $request['amt']=$request->amount??'100';
    $request['order_number']=$request->order_no??time(); // order no
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


   public function successPage(Request $request)
   {
    $payment = Payment::where('viva_order_id',$request->s)->first();
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

   public function fetchTransactionDetails($tid)
   {
      return $this->getTransactionDetails($tid);
   }


   public function completeOrderCart($request,$payment)
    {

      $order = Order::where('order_number',$payment->transaction_id)->first();
      //dd($order);
          if(isset($request->s) && $request->s != '')
          {
           
            $order->payment_status = '1';
            $order->viva_order_id = $request->s;
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
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();

            Payment::updateOrCreate(['viva_order_id'=>$request->s],['amount'=>0,'transaction_id'=>$request->s,'balance_transaction'=>$order->payable_amount,'type'=>'cart','date'=>date('Y-m-d'),'order_id'=>$order->id,'user_id'=>auth()->id()]);

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
            $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesa'.'&status=200&order='.$order->order_number;
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
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesa'.'&status=00&order='.$order->order_number;
              return Redirect::to($returnUrl);  
            }else{
              return Redirect::to(route('showCart'))->with('error',$request->message);
            }

          }

        return $this->successResponse($request->getTransactionReference());

    }


    public function completeOrderWallet(Request $request,$payment)
    {
       if(isset($request->s) && $request->s != '')
          {
            $data = Payment::where('viva_order_id',$request->s)->first();
            $user = auth()->user();
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->s . '</b>']);

            if(isset($request->transaction_id) && !empty($request->transaction_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesa'.'&status=200&transaction_id='.$request->s.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'))->with('success','Wallet amount added successfuly.');
            }

            
          }else{
            $data = Payment::where('viva_order_id',$request->s)->first();
            $data->delete();

            if(isset($request->transaction_id) && !empty($request->transaction_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesa'.'&status=00&transaction_id='.$request->merchant_reference.'&action=wallet';
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
      $data = Payment::where('viva_order_id',$request->s)->first();
      if(isset($request->s) && $request->s != '')
          {
            $subscription = explode('_',$data->transaction_id);
            $request->request->add(['user_id' => $user->id, 'payment_option_id' => 21, 'amount' => $data->balance_transaction, 'transaction_id' => $request->s]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[2]);

            if(isset($request->subscription_id) && !empty($request->subscription_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesa'.'&status=200&transaction_id='.$request->s.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('success','Subscription added successfuly.');
            }
          }else{
            $data->delete();

            if(isset($request->subscription_id) && !empty($request->subscription_id))
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=00&transaction_id='.$request->s.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
            }

          }
        return $this->successResponse($request->getTransactionReference());

    }

    public function completeOrderTip(Request $request,$payment)
    {
      $data = Payment::where('viva_order_id',$request->s)->first();
      if(isset($request->s) && $request->s != '')
          {
            $order_number = explode('_',$data->transaction_id);
            $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[2], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $data->transaction_id]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);

            if(isset($request->order_no) && !empty($request->order_no))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesa'.'&status=200&order='.$order_number[2].'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('success','Tip amount added successfuly.');
              }

          }else{
            $data->delete();

              if(isset($request->order_no) && !empty($request->order_no))
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesa'.'&status=00&transaction_id='.$data->transaction_id.'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('error', $request->message);
              }

          }
        return $this->successResponse($request->getTransactionReference());

    }


}
