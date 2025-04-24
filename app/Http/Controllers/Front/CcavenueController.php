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
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserVendor;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Redirect;
use JWT\Token;
use Log;
use App\Http\Traits\Giftcard\GiftCardTrait;
use App\Models\Transaction;
use App\Models\UserGiftCard;
use Carbon\Carbon;

class CcavenueController extends Controller
{
   use ApiResponser,GiftCardTrait;

   private $access_key;
   private $merchant_id;
   private $url;
   private $access_code;

   public function __construct()
   {
      $payOpt = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'ccavenue')->where('status', 1)->first();
      if(@$payOpt && (!empty($payOpt->credentials))){

        $json = json_decode($payOpt->credentials);
        $this->access_key = $json->enc_key;
        $this->access_code = $json->access_code;
        $this->merchant_id = $json->merchant_id;
        if($payOpt->test_mode =='1')
        {
         if($json->custom_url=='ae'){
            $this->url = 'https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction';
       }else{
         $this->url = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
        }
        }else{
        if($json->custom_url=='ae'){
            $this->url = 'https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction';
        }else{
          $this->url='https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
        }
        }
      }

   }

   public function orderNumber($request)
   {
    if (($request->from == 'cart') || ($request->from == 'pickup_delivery')) {
      $time = $request->order_number;
    }elseif($request->from == 'wallet')
        {
            $time = ($request->transaction_id)??'W_'.time();
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'wallet','date'=>date('Y-m-d')]);

        }elseif($request->from == 'tip')
        {
             $time = 'T_'.time().'_'.$request->order_number;
             Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'tip','date'=>date('Y-m-d')]);

        }elseif($request->from == 'subscription')
        {
            $time = ($request->subscription_id)??'S_'.time();
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'subscription','date'=>date('Y-m-d')]);

        }
        elseif($request->from == 'giftCard')
        {
            $time = ($request->transaction_id)??'W_'.time();
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'giftCard','date'=>date('Y-m-d')]);

        }
        return $time;
   }

   public function createUserToken()
   {
    $user = auth()->user();
    $token1 = new Token;
    $token = $token1->make([
        'key' => 'royoorders-jwt',
        'issuer' => 'royoorders.com',
        'expiry' => strtotime('+1 month'),
        'issuedAt' => time(),
        'algorithm' => 'HS256',
    ])->get();
    $token1->setClaim('user_id', $user->id);
    $this->token = $token;
    $user->auth_token = $token;
    $user->save();
    return $user;
   }

   public function payForm(Request $request)
   {
    $user = $this->createUserToken();
    $merchant_data='';

    $number = $this->orderNumber($request); // order no
    $working_key=$this->access_key;//Shared by CCAVENUES
    $access_code=$this->access_code;//Shared by CCAVENUES
    $url=$this->url;//Shared by CCAVENUES
    if($request->from == 'pickup_delivery' && UserAddress::where('is_primary','1')->doesntExist()){
      $address = new \stdClass();
      $order =  Order::where('order_number',$number)->first();
      $addressess = json_decode($order->orderLocation->tasks,true);
      $address->address = $addressess[0]['address'];
      }else{
        $address = UserAddress::where('is_primary','1')->first();
      }
      if($request->from == "giftCard"){
      $gift_card = GiftCard::findOrFail($request->gift_card_id);
      $description = 'giftCard Checkout';
        $merchant_data = 'merchant_id='.$this->merchant_id.'&order_id='.$number.'&amount='.$request->amt.'&currency='.getPrimaryCurrencyName().'&redirect_url='.route('ccavenue.success').'&cancel_url='.route('ccavenue.success').'&language=EN&billing_name='.$user->name.'&billing_address='.$address->address.'&billing_city='.@$address->city.'&billing_state='.@$address->state.'&billing_zip='.@$address->pincode.'&billing_country='.@$address->country.'&billing_tel='.@$user->phone_number.'&billing_email='.@$user->email.'&delivery_name='.$user->name.'&delivery_address='.$address->address.'&delivery_city='.@$address->city.'&delivery_state='.@$address->state.'&delivery_zip='.@$address->pincode.'&delivery_country='.@$address->country.'&delivery_tel='.@$user->phone_number.'&merchant_param1='.$number.'&merchant_param2='.$request->from.'&merchant_param3=web&merchant_param4='.$user->id.'&merchant_param5='.$user->auth_token .'&description='.$description.'&gift_card_id='.$gift_card->amt.'&send_card_to_name='.$request->send_card_to_name.'&send_card_to_mobile='.$request->send_card_to_mobile ?? ''.'&send_card_to_email='.$request->send_card_to_email ?? ''.'&send_card_to_address='.$request->send_card_to_address ?? ''.'&send_card_is_delivery='.$request->send_card_is_delivery ?? '0'.'&promo_code=&customer_identifier=&';
      }
      $merchant_data = 'merchant_id='.$this->merchant_id.'&order_id='.$number.'&amount='.$request->amt.'&currency='.getPrimaryCurrencyName().'&redirect_url='.route('ccavenue.success').'&cancel_url='.route('ccavenue.success').'&language=EN&billing_name='.$user->name.'&billing_address='.$address->address.'&billing_city='.@$address->city.'&billing_state='.@$address->state.'&billing_zip='.@$address->pincode.'&billing_country='.@$address->country.'&billing_tel='.@$user->phone_number.'&billing_email='.@$user->email.'&delivery_name='.$user->name.'&delivery_address='.$address->address.'&delivery_city='.@$address->city.'&delivery_state='.@$address->state.'&delivery_zip='.@$address->pincode.'&delivery_country='.@$address->country.'&delivery_tel='.@$user->phone_number.'&merchant_param1='.$number.'&merchant_param2='.$request->from.'&merchant_param3=web&merchant_param4='.$user->id.'&merchant_param5='.$user->auth_token.'&promo_code=&customer_identifier=&';
      $encrypted_data=$this->encrypt($merchant_data,$working_key); // Method for encrypting the data.

    return view('frontend.payment_gatway.ccavenue_view', compact('encrypted_data','access_code','url'));
   }

   public function payFormWebView(Request $request)
   {
    if(isset($request->auth_token) && !empty($request->auth_token)){
        $user = User::where('auth_token', $request->auth_token)->first();
        Auth::login($user);
        $user->auth_token = $request->auth_token;
        $user->save();
     }
     //eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NDY3NDQ0OTgsImV4cCI6MTY0OTQyMjg5OCwiaXNzIjoicm95b29yZGVycy5jb20ifQ.60ADhLV0rlRHQjWtGUD1xgW6Eezs3DIwjyZoV4jILhI

    $merchant_data='';
    $number = $this->orderNumber($request); // order no
    $working_key=$this->access_key;//Shared by CCAVENUES
    $access_code=$this->access_code;//Shared by CCAVENUES
    $url=$this->url;//Shared by CCAVENUES
    $user = auth()->user();
    if($request->from == 'pickup_delivery' && UserAddress::where('is_primary','1')->doesntExist()){
      $address = new \stdClass();
      $order =  Order::where('order_number',$number)->first();
      $addressess = json_decode($order->orderLocation->tasks,true);
      $address->address = $addressess[0]['address'];
      }else{
        $address = UserAddress::where('is_primary','1')->first();
      }
     $merchant_data = 'merchant_id='.$this->merchant_id.'&order_id='.$number.'&amount='.$request->amt.'&currency='.getPrimaryCurrencyName().'&redirect_url='.route('ccavenue.success').'&cancel_url='.route('ccavenue.success').'&language=EN&billing_name='.$user->name.'&billing_address='.$address->address.'&billing_city='.@$address->city.'&billing_state='.@$address->state.'&billing_zip='.@$address->pincode.'&billing_country='.@$address->country.'&billing_tel='.@$user->phone_number.'&billing_email='.@$user->email.'&delivery_name='.$user->name.'&delivery_address='.@$address->address.'&delivery_city='.@$address->city.'&delivery_state='.@$address->state.'&delivery_zip='.@$address->pincode.'&delivery_country='.@$address->country.'&delivery_tel='.@$user->phone_number.'&merchant_param1='.$number.'&merchant_param2='.$request->from.'&merchant_param3=mob&merchant_param4'.$user->id.'=&merchant_param5='.$user->auth_token.'&promo_code=&customer_identifier=&';
      $encrypted_data=$this->encrypt($merchant_data,$working_key); // Method for encrypting the data.

    return view('frontend.payment_gatway.ccavenue_view', compact('encrypted_data','access_code','url'));
   }

   public function CcavenuePurchase(Request $request)
   {
       $amount = $request->amount;
       $user = auth()->user();
       $action = isset($request->action) ? $request->action : '';
       $params = '?amt=' . $amount.'&auth_token='.$user->auth_token.'&from='.$action;
       if($action == 'cart'){
           $params = $params . '&order_number=' . $request->order_number.'&app=1';
       }elseif($action == 'wallet'){
         //app = 2 is for wallet
        $params = $params .'&app=2&transaction_id=W_'.time();
       }elseif($action == 'subscription'){
        //app = 2 is for wallet
       $params = $params .'&app=3&subscription_id='.'S_'.time().'_'.$request->subscription_id;
      }elseif($action == 'tip'){
        //app = 2 is for wallet
       $params = $params .'&app=3&order_number='.$request->order_number;
      }elseif ($action == 'pickup_delivery') {
        //app = 4 is for pickup delivery
        $params = $params . '&order_number=' . $request->order_number . '&app=4';
      }elseif( $request->from == 'giftCard')
      {
        $params = $params .'&app=2&transaction_id=W_'.time();

      }

       return $this->successResponse(url($request->serverUrl.'payment/ccavenue/api/'.$params));
   }

   public function successForm(Request $request)
   {
        $encResponse=$request->encResp;			//This is the response sent by the CCAvenue Server
	    $rcvdString=$this->decrypt($encResponse,$this->access_key);		//Crypto Decryption used as per the specified working key.
	    $order_status="";
	    $decryptValues=explode('&', $rcvdString);

	    $dataSize=sizeof($decryptValues);
        $dataArray = array();
        for($i = 0; $i < $dataSize; $i++)
        {
        $information=explode('=',$decryptValues[$i]);
        $request->request->add([$information[0] => $information[1]]);
        }

        if(isset($request->merchant_param5) && !empty($request->merchant_param5)){
            $user = User::where('auth_token',$request->merchant_param5)->first();
            Auth::login($user);
        }

        if($request->merchant_param2=='cart'){
            return $this->completeOrderCart($request);
        }elseif($request->merchant_param2=='wallet'){
            return $this->completeOrderWallet($request);
        }elseif($request->merchant_param2=='tip'){
            return $this->completeOrderTip($request);
        }elseif($request->merchant_param2=='subscription'){
            return $this->completeOrderSubs($request);
        }elseif ($request->merchant_param2 == 'pickup_delivery') {
          return $this->completeOrderPickup($request);
        }elseif ($request->merchant_param2 == 'giftCard') {
          return $this->completeGiftCard($request);
        }
   }

   public function completeOrderPickup(Request $request)
   {

     $order = Order::where('order_number', $request->order_id)->first();
     if (isset($request->order_status) && $request->order_status == 'Success') {
       $order->payment_status = '1';
       $order->save();
       Payment::create(['amount' => 0, 'transaction_id' => $request->tracking_id, 'balance_transaction' => $order->payable_amount, 'type' => 'pickup_deleivery', 'date' => date('Y-m-d'), 'order_id' => $order->id]);
          // Deduct wallet amount if payable amount is successfully done on gateway
        if ( $order->wallet_amount_used > 0 ) {
        $user = User::find(auth()->id());
        $wallet = $user->wallet;
        $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
        if(!$transaction_exists){
            $wallet->withdrawFloat($order->wallet_amount_used, [
                'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                'order_number' => $order->order_number,
                'transaction_id' => $request->tracking_id,
                'payment_option' => 'ccavenue'
            ]);
        }
      }
       // Send Notification
       $plaseOrderForPickup = new PickupDeliveryController();
       $request->request->add(['transaction_id' => $request->tracking_id]);
       $plaseOrderForPickup =   $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
       if (isset($request->merchant_param3) && $request->merchant_param3 == 'mob') {
         $returnUrl = route('payment.gateway.return.response').'/?gateway=ccavenue'.'&status=200&transaction_id='.$request->tracking_id;
         return Redirect::to($returnUrl);
       } else {
         return Redirect::to(route('front.booking.details', $order->order_number));
       }
     } else {
       if (isset($request->merchant_param3) && $request->merchant_param3 == 'mob') {
             $response['status'] = 200;
             $response['msg'] = 'Success Added Pickup.';
             $response['payment_from'] = 'pickup_delivery';
             $response['order'] = $order;
       } else {
         return Redirect::to(route('user.wallet'))->with('error', $request->message);
       }
     }
   }

   public function completeOrderCart($request)
   {
    $order = Order::where('order_number',$request->order_id)->first();
       if(isset($request->order_status) && $request->order_status == 'Success')
       {
            //Success from cart
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

                CartAddon::where('cart_id', $cartid)->delete();
                CartCoupon::where('cart_id', $cartid)->delete();
                CartProduct::where('cart_id', $cartid)->delete();
                CartProductPrescription::where('cart_id', $cartid)->delete();

                Payment::create(['amount'=>0,'transaction_id'=>$request->tracking_id,'balance_transaction'=>$order->payable_amount,'type'=>'cart','date'=>date('Y-m-d'),'order_id'=>$order->id]);

                // Deduct wallet amount if payable amount is successfully done on gateway
                if ( $order->wallet_amount_used > 0 ) {
                    $user = User::find(auth()->id());
                    $wallet = $user->wallet;
                    $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                    if(!$transaction_exists){
                        $wallet->withdrawFloat($order->wallet_amount_used, [
                            'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                            'order_number' => $order->order_number,
                            'transaction_id' => $request->tracking_id,
                            'payment_option' => 'ccavenue'
                        ]);
                    }
                }

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

                if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
                {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=ccavenue'.'&status=200&order='.$order->order_number;
                return Redirect::to($returnUrl);
                }else{
                return Redirect::to(route('order.success',[$order->id]));
                }

        }else{

                //Failed from cart
                $user = auth()->user();
                $wallet = $user->wallet;
                if(isset($order->wallet_amount_used)){
                $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
                $this->sendWalletNotification($user->id, $order->order_number);
                }
                if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
                {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&order='.$order->order_number;
                return Redirect::to($returnUrl);
                }else{
                return Redirect::to(route('showCart'))->with('error',$request->message);
                }

        }
   }

   public function completeGiftCard($request)
   {

     if(isset($request->order_status) && $request->order_status == 'Success')
     {
         $gift_card = GiftCard::find($request->gift_card_id);
         if(isset($request->merchant_param5) && !empty($request->merchant_param5)){
          $user = User::where('auth_token',$request->merchant_param5)->first();
          Auth::login($user);
        }
        if( $gift_card ){
          if(isset($request->tracking_id)){
              $code =$this->getGiftCardCode($gift_card->title);
              $UserGiftCard               = new UserGiftCard();
              $UserGiftCard->user_id      = $user->id;
              $UserGiftCard->gift_card_id = $gift_card->id;
              $UserGiftCard->amount       = $gift_card->amount;
              $UserGiftCard->expiry_date  = $gift_card->expiry_date;
              $UserGiftCard->gift_card_code = $code;
              $UserGiftCard->buy_for_data = !empty($request->senderData) ? $request->senderData : '';
              $UserGiftCard->save();
              if(isset($request->send_card_to_email)){
                  $currSymbol = isset($request->currency) ? $request->currency : '$';
                  $gift_card->userCode =  $code;
                  $this->GiftCardMail($request->send_card_to_email,$request->send_card_to_name, $gift_card ,$user ,$currSymbol);
              }
              $payment                        = new Payment;
              $payment->user_id               = $user->id;
              $payment->balance_transaction   = $request->amount;
              $payment->transaction_id        = $request->tracking_id;
              $payment->reference_table_id    = $UserGiftCard->id;
              $payment->payment_option_id     = 22;
              $payment->date                  = Carbon::now()->format('Y-m-d');
              $payment->type                  = 'giftCard';
              $payment->save();
          }

          $message = __('Your Gift Card has been activated successfully.');
          if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
          {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=ccavenue'.'&status=200&transaction_id='.$request->order_id.'&action=giftCard';
            return Redirect::to($returnUrl);
          }else{
            return Redirect::to(route('giftCard.index'))->with('success',$message);
          }

      }else{
        $message = __('Something went wrong.');

        if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
        {
          $returnUrl = route('payment.gateway.return.response').'/?gateway=ccavenue'.'&status=500&action=giftCard';
          return Redirect::to($returnUrl);
        }else{
          return Redirect::to(route('giftCard.index'))->with('error',$request->message);
        }
      }
   }
  }
   public function completeOrderWallet($request)
   {
        if(isset($request->order_status) && $request->order_status == 'Success')
         {
           $data = Payment::where('transaction_id',$request->order_id)->first();
           $user = auth()->user();
           $wallet = $user->wallet;
           $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->order_id . '</b>']);

           if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
           {
             $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=200&transaction_id='.$request->order_id.'&action=wallet';
             return Redirect::to($returnUrl);
           }else{
             return Redirect::to(route('user.wallet'));
           }


         }else{
           $data = Payment::where('transaction_id',$request->order_id)->first();
           $data->delete();

           if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
           {
             $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&transaction_id='.$request->order_id.'&action=wallet';
             return Redirect::to($returnUrl);
           }else{
             return Redirect::to(route('user.wallet'))->with('error',$request->message);
           }


         }
       return $this->successResponse($request->getTransactionReference());

   }


   public function completeOrderSubs($request)
   {
     $user = auth()->user();
     $data = Payment::where('transaction_id',$request->order_id)->first();
     if(isset($request->order_status) && $request->order_status == 'Success')
         {
           $subscription = explode('_',$request->order_id);
           $request->request->add(['user_id' => $user->id, 'payment_option_id' => 22, 'amount' => $data->balance_transaction, 'transaction_id' => $request->order_id]);
           $subscriptionController = new UserSubscriptionController();
           $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[2]);

           if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
           {
             $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=200&transaction_id='.$request->order_id.'&action=subscription';
             return Redirect::to($returnUrl);
           }else{
             return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
           }
         }else{
           $data->delete();

           if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
           {
             $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&transaction_id='.$request->order_id.'&action=subscription';
             return Redirect::to($returnUrl);
           }else{
             return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
           }

         }
       return $this->successResponse($request->getTransactionReference());

   }

   public function completeOrderTip($request)
   {
     $data = Payment::where('transaction_id',$request->order_id)->first();
     if(isset($request->order_status) && $request->order_status == 'Success')
         {
           $order_number = explode('_',$request->order_id);
           $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[2], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $request->order_id]);
           $orderController = new OrderController();
           $orderController->tipAfterOrder($request);

           if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
             {
               $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=200&order='.$order_number[2].'&action=tip';
               return Redirect::to($returnUrl);
             }else{
               return Redirect::to(route('user.orders'))->with('success', $request->message);
             }

         }else{
           $data->delete();

           if(isset($request->merchant_param3) && $request->merchant_param3=='mob')
             {
               $returnUrl = route('payment.gateway.return.response').'/?gateway=kongapay'.'&status=00&transaction_id='.$request->order_id.'&action=tip';
               return Redirect::to($returnUrl);
             }else{
               return Redirect::to(route('user.orders'))->with('error', $request->message);
             }

         }
       return $this->successResponse($request->getTransactionReference());

   }




 //*********** Function *********************


   function encrypt($plainText,$key)
   {
       $key = $this->hextobin(md5($key));

       $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

       $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
       $encryptedText = bin2hex($openMode);
       return $encryptedText;
   }

   function decrypt($encryptedText,$key)
   {
       $key = $this->hextobin(md5($key));
       $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
       $encryptedText = $this->hextobin($encryptedText);
       $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
       return $decryptedText;
   }
   //*********** Padding Function *********************

    function pkcs5_pad ($plainText, $blockSize)
   {
       $pad = $blockSize - (strlen($plainText) % $blockSize);
       return $plainText . str_repeat(chr($pad), $pad);
   }

   //********** Hexadecimal to Binary function for php 4.0 version ********

   function hextobin($hexString)
       {
           $length = strlen($hexString);
           $binString="";
           $count=0;
           while($count<$length)
           {
               $subString =substr($hexString,$count,2);
               $packedString = pack("H*",$subString);
               if ($count==0)
           {
               $binString=$packedString;
           }

           else
           {
               $binString.=$packedString;
           }

           $count+=2;
           }
             return $binString;
         }


}
