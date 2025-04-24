<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Auth;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\Vivawallet;
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
use App\Http\Controllers\Front\FrontController;
use Log;

class VivawalletController extends FrontController
{
   use ApiResponser, Vivawallet;

    private $merchant_key;
    private $merchant_id;
    private $client_key;
    private $client_id;
    private $url;
    private $tokenUrl;
    private $test_mode;

   public function __construct()
   {
        // $viva = PaymentOption::select('credentials','test_mode','status')->where('code', 'viva_wallet')->where('status', 1)->first();
        // $json = json_decode($viva->credentials);
        // $this->client_key = $json->client_key;
        // $this->client_id = $json->client_id;
        // $this->merchant_key = $json->merchant_key;
        // $this->merchant_id = $json->merchant_id;
        // $this->test_mode = $viva->test_mode;
   }

   public function credentials()
    {
         $viva = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'viva_wallet')->where('status', 1)->first();
         $json = json_decode($viva->credentials);
         $this->client_key = $json->client_key;
         $this->client_id = $json->client_id;
         $this->merchant_key = $json->merchant_key;
         $this->merchant_id = $json->merchant_id;
         $this->test_mode = $viva->test_mode;
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
            $time = ($request->transaction_id)??'W_'.time();
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'wallet','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);

        }elseif($request->from == 'tip')
        {
             $time = 'T_'.time().'_'.$request->order_number;
             Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'tip','date'=>date('Y-m-d'),'user_id'=>auth()->id()]);

        }elseif($request->from == 'subscription')
        {
          $subtime = ($request->subscription_id)??time();
          $time = 'S_'.$subtime.'_'.$request->subsid;
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
          //\Log::info(json_encode($response));
      if($response->orderCode){
            $payId = Payment::where('transaction_id',$number)->first();
            $payId->viva_order_id = $response->orderCode;
            $payId->payment_from = 'web';
            $payId->user_id = auth()->id();
            $payId->save();
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


   public function createPayLinkApp(Request $request , $domain ="")
   {
    $request->request->add(['from'=>$request->action,'amt'=>number_format($request->amount,2),'subsid'=>$request->subscription_id??'']);

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
            $payId = Payment::where('transaction_id',$number)->first();
            $payId->viva_order_id = $response->orderCode;
            $payId->payment_from = 'app';
            $payId->user_id = auth()->id();
            $payId->save();
      }

      $link =  $this->sendResponse($response);
      return $this->successResponse($link);
   }


   public function successPage(Request $request)
   {
    $payment = Payment::where('viva_order_id',$request->s)->first();

    if(empty(auth()->id())){
      $user = User::where('id', $payment->user_id)->first();
      Auth::login($user);
    }

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
             CaregoryKycDoc::where('cart_id',$cart->id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();
            // send sms 
            $this->sendSuccessSMS($request, $order);

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

          if(isset($payment->payment_from) && $payment->payment_from=='app')
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=200&order='.$order->order_number;
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
            if(isset($payment->payment_from) && $payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=00&order='.$order->order_number;
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

            if(isset($payment->payment_from) && $payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=200&transaction_id='.$request->s.'&action=wallet';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.wallet'));
            }

            
          }else{
            $data = Payment::where('viva_order_id',$request->s)->first();
            $data->delete();

            if(isset($payment->payment_from) && $payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=00&transaction_id='.$request->merchant_reference.'&action=wallet';
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

            if(isset($payment->payment_from) && $payment->payment_from=='app')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=200&transaction_id='.$request->s.'&action=subscription';
              return Redirect::to($returnUrl); 
            }else{
              return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
            }
          }else{
            $data->delete();

            if(isset($payment->payment_from) && $payment->payment_from=='app')
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

            if(isset($payment->payment_from) && $payment->payment_from=='app')
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=200&order='.$order_number[2].'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('success', $request->message);
              }

          }else{
            $data->delete();

            if(isset($payment->payment_from) && $payment->payment_from=='app')
              {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=viva_wallet'.'&status=00&transaction_id='.$data->transaction_id.'&action=tip';
                return Redirect::to($returnUrl); 
              }else{
                return Redirect::to(route('user.orders'))->with('error', $request->message);
              }

          }
        return $this->successResponse($request->getTransactionReference());

    }


}
