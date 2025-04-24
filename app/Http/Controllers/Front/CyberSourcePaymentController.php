<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentOption;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserVendor;
use App\Services\CyberSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client as GCLIENT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class CyberSourcePaymentController extends Controller
{
   use ApiResponser;
    protected $profile_id;
    protected $access_key;
    protected $secret_key;
    protected $merchant_id;
    protected $url;
    protected $bill_to_address_line1;
    protected $bill_to_address_city;
    protected $bill_to_address_state;
    protected $bill_to_address_country;
    protected $bill_to_address_postal_code;

    public function __construct()
    {
        $payOpt = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'cyber_source')->where('status', 1)->first();
        if(@$payOpt && (!empty($payOpt->credentials))){
          $json = json_decode($payOpt->credentials);
          $this->profile_id = $json->cyber_source_profile_id;
          $this->access_key = $json->cyber_source_access_key;
          $this->secret_key = $json->cyber_source_secret_key;
          $this->merchant_id = $json->cyber_source_merchant_id;
          $this->bill_to_address_line1 = $json->bill_to_address_line1;
          $this->bill_to_address_city = $json->bill_to_address_city;
          $this->bill_to_address_state = $json->bill_to_address_state;
          $this->bill_to_address_country = $json->bill_to_address_country;
          $this->bill_to_address_postal_code = $json->bill_to_address_postal_code;
          if($payOpt->test_mode =='1'){
            $this->url = 'https://testsecureacceptance.cybersource.com/pay'; 
            }
            else{
            $this->url = 'https://secureacceptance.cybersource.com/pay';
            }
    }
}
public function getIp(){
  foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
      if (array_key_exists($key, $_SERVER) === true){
          foreach (explode(',', $_SERVER[$key]) as $ip){
              $ip = trim($ip); // just to be safe
              if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                  return $ip;
              }
          }
      }
  }
  return request()->ip(); // it will return the server IP if the client IP is not found using this method.
}
public function processCheckPayment(Request $request){
$postData =  [
"profile_id"=> "C123DA71-C1D4-4B4E-B848-C79B3504C227",
"access_key"=> "10322c2b5e0236fa8938d03de9183ffa",
"transaction_uuid"=> "660d5dd77020d",
"signed_date_time"=> "2024-04-03T13:47:03Z",
"signed_field_names"=> "profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_cancel_page,override_custom_receipt_page",
"unsigned_field_names"=> "signature,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4",
"transaction_type"=> "sale",
"reference_number"=> "B1712152024746",
"auth_trans_ref_no"=> "B1712152024746",
"amount"=> "10.00",
"currency"=> "GHS",
"locale"=> "en-us",
"merchant_descriptor"=> "Swen",
"bill_to_forename"=> "Noreal",
"bill_to_surname"=> "Name",
"bill_to_email"=> "null@cybersource.com",
"bill_to_phone"=> "+662-2962-000",
"bill_to_address_line1"=> "1295 Charleston Rd",
"bill_to_address_line2"=> "1295 Charleston Rd",
"bill_to_address_city"=> "Mountain View",
"bill_to_address_state"=> "CA",
"bill_to_address_country"=> "US",
"bill_to_address_postal_code"=> "94043",
"override_custom_cancel_page"=> "http://192.168.103.84:8001/cybersource/process-payment",
"override_custom_receipt_page"=> "http://192.168.103.84:8001/cybersource/process-payment",
"customer_ip_address"=> "192.168.103.84",
"line_item_count"=> "2",
"merchant_defined_data1"=> "MDD#1",
"merchant_defined_data2"=> "MDD#2",
"merchant_defined_data3"=> "MDD#3",
"merchant_defined_data4"=> "MDD#4"
];
foreach($postData as $name => $value) {
  $params[$name] = $value;
}
  return view('frontend.payment_gatway.cyber_source.view', compact('params'));


}

    public function webPayment(Request $request)
    {
       $customer_ip_address = $this->getIp();

        $order_number = $this->orderNumber($request); 
        $user = Auth::user();
        $fullName = $this->extractNames($user->name);
        $from = isset($request->from) ? $request->from : '';
        $action = isset($request->action) ? $request->action : 'web';
   
        $client = Client::select('email','company_address')->first();
        $email = isset($user->email)?$user->email : $client->email;
        $auth_trans_ref_no =  $action.'cyber'.$user->id;

        if(isset($user->defaultAddress)){
          $address = $user->defaultAddress;
        }

        $bill_to_address_line1 = isset($user->defaultAddress)  ? $user->defaultAddress->address ?? $this->bill_to_address_line1 ??  $this->bill_to_address_line1 : $this->bill_to_address_line1;
        $bill_to_address_city = isset($user->defaultAddress)  ?  $user->defaultAddress->city ?? $this->bill_to_address_city ??  $this->bill_to_address_city : $this->bill_to_address_city;
        $bill_to_address_state = isset($user->defaultAddress)  ? $user->defaultAddress->state ?? $this->bill_to_address_state ??  $this->bill_to_address_state : $this->bill_to_address_state;
        $bill_to_address_country = isset($user->defaultAddress)  ? $user->defaultAddress->country_code ?? $this->bill_to_address_country ??  $this->bill_to_address_country : $this->bill_to_address_country;
        $bill_to_address_postal_code = isset($user->defaultAddress)  ? $user->defaultAddress->pincode ?? $this->bill_to_address_postal_code ??  $this->bill_to_address_postal_code : $this->bill_to_address_postal_code;
        
        $postData =  [
          "profile_id"=>  $this->profile_id,
          "access_key"=>  $this->access_key,
          "transaction_uuid"=> $request->order_number,
          "signed_date_time"=>  gmdate('Y-m-d\TH:i:s\Z'),
          "signed_field_names"=> "profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_cancel_page,override_custom_receipt_page",
          "unsigned_field_names"=> "signature,bill_to_forename,bill_to_surname,bill_to_email,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4",
          "transaction_type"=> "sale",
          "reference_number"=> $request->from,
          "auth_trans_ref_no"=> $auth_trans_ref_no,
          "amount"=> 200000,
          "currency"=> "SLL",
          "locale"=> getPrimaryLanguageName(),
          "merchant_descriptor"=> "Saloni",
          "bill_to_email"=> $email,
          "bill_to_forename"=> $fullName['first_name'],
          "bill_to_surname"=>$fullName['last_name'],
          "bill_to_address_line1"=> $bill_to_address_line1,
          "bill_to_address_line2"=> $bill_to_address_line1,
          "bill_to_address_city"=> $bill_to_address_city,
          "bill_to_address_country"=>$bill_to_address_country,
          "bill_to_address_state"=> $bill_to_address_state,
          "bill_to_address_postal_code"=>$bill_to_address_postal_code,
          'override_custom_cancel_page' => url('cybersource/process-payment'),
          'override_custom_receipt_page' => url('cybersource/process-payment'),     
          "customer_ip_address"=> $customer_ip_address,
          "line_item_count"=> "2",
          "merchant_defined_data1"=> "MDD#1",
          "merchant_defined_data2"=> "MDD#2",
          "merchant_defined_data3"=> "MDD#3",
          "merchant_defined_data4"=> "MDD#4"
          ];

             define('HMAC_SHA256', 'sha256');
             $url = $this->url;
            foreach($postData as $name => $value) {
                $params[$name] = $value;
            }
            $postData['signature'] =$signature= $this->sign($params);

            return view('frontend.payment_gatway.cyber_source.payment', compact('postData','url'));
    }

    public function webMobilePayment(Request $request)
    {
      $customer_ip_address = $this->getIp();
      $from = isset($request->from) ? $request->from : '';
      $action = isset($request->action) ? $request->action : 'mob';
      if(isset($request->auth_token) && !empty($request->auth_token)){
        $user = User::where('auth_token', $request->auth_token)->first();
        Auth::login($user);
        $user->auth_token = $request->auth_token;
        $user->save();
      }
       $user = Auth::user();
       $fullName = $this->extractNames($user->name);
       $client = Client::select('email','company_address')->first();
       $email = isset($user->email)?$user->email : $client->email;
       $auth_trans_ref_no =  $action.'cyber'.$user->id;

       if(isset($user->defaultAddress)){
         $address = $user->defaultAddress;
       }

       $bill_to_address_line1 = isset($user->defaultAddress)  ? $user->defaultAddress->address ?? $this->bill_to_address_line1 ??  $this->bill_to_address_line1 : $this->bill_to_address_line1;
       $bill_to_address_city = isset($user->defaultAddress)  ?  $user->defaultAddress->city ?? $this->bill_to_address_city ??  $this->bill_to_address_city : $this->bill_to_address_city;
       $bill_to_address_state = isset($user->defaultAddress)  ? $user->defaultAddress->state ?? $this->bill_to_address_state ??  $this->bill_to_address_state : $this->bill_to_address_state;
       $bill_to_address_country = isset($user->defaultAddress)  ? $user->defaultAddress->country_code ?? $this->bill_to_address_country ??  $this->bill_to_address_country : $this->bill_to_address_country;
       $bill_to_address_postal_code = isset($user->defaultAddress)  ? $user->defaultAddress->pincode ?? $this->bill_to_address_postal_code ??  $this->bill_to_address_postal_code : $this->bill_to_address_postal_code;

       
        
       $postData =  [
        "profile_id"=>  $this->profile_id,
        "access_key"=>  $this->access_key,
        "transaction_uuid"=> $request->order_number,
        "signed_date_time"=>  gmdate('Y-m-d\TH:i:s\Z'),
        "signed_field_names"=> "profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_cancel_page,override_custom_receipt_page",
        "unsigned_field_names"=> "signature,bill_to_forename,bill_to_surname,bill_to_email,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4",
        "transaction_type"=> "sale",
        "reference_number"=> $request->from,
        "auth_trans_ref_no"=> $auth_trans_ref_no,
        "amount"=> "10.00",
        "currency"=> "GHS",
        "locale"=> "en-us",
        "merchant_descriptor"=> "Saloni",
        "bill_to_email"=> $email,
        "bill_to_forename"=> $fullName['first_name'],
        "bill_to_surname"=>$fullName['last_name'],
        "bill_to_address_line1"=> $bill_to_address_line1,
        "bill_to_address_line2"=> $bill_to_address_line1,
        "bill_to_address_city"=> $bill_to_address_city,
        "bill_to_address_country"=>$bill_to_address_country,
        "bill_to_address_state"=> $bill_to_address_state,
        "bill_to_address_postal_code"=>$bill_to_address_postal_code,
        'override_custom_cancel_page' => url('cybersource/process-payment'),
        'override_custom_receipt_page' => url('cybersource/process-payment'),     
        "customer_ip_address"=> $customer_ip_address,
        "line_item_count"=> "2",
        "merchant_defined_data1"=> "MDD#1",
        "merchant_defined_data2"=> "MDD#2",
        "merchant_defined_data3"=> "MDD#3",
        "merchant_defined_data4"=> "MDD#4"
        ];

           define('HMAC_SHA256', 'sha256');
           $url = $this->url;
          foreach($postData as $name => $value) {
              $params[$name] = $value;
          }
          $postData['signature'] =$signature= $this->sign($params);

          return view('frontend.payment_gatway.cyber_source.payment', compact('postData','url'));
    }

    public function cyberSourcePurchase(Request $request)
    {
       $order_number = $this->orderNumber($request); 
        $amount = $request->amt;
        $user = auth()->user();
        $action = isset($request->action) ? $request->action : '';
        $from = isset($request->from) ? $request->from : '';
        $params = '?amt=' . $amount.'&auth_token='.$user->auth_token.'&from='.$from.'&action='.$action.'&order_number='.$order_number;
        return $this->successResponse(url('cybersource/payment/api'.$params));
    }
  
    public  function sign($postdata) {
        return $this->signData($this->buildDataToSign($postdata), $this->secret_key);
    }
    
    public function signData($data, $secretKey) {
        return base64_encode(hash_hmac(HMAC_SHA256, $data, $secretKey, true));
    }

    public function extractNames($full_name) {
        // Split the full name into parts
        $name_parts = explode(" ", $full_name);
    
        // First name is the first element
        $data['first_name'] = $name_parts[0];
    
        // Last name is the last element
        $data['last_name'] = end($name_parts);
    
        return $data;
    }
    
    public function buildDataToSign($params) {
        $signedFieldNames = explode(",", $params["signed_field_names"]);
        foreach ($signedFieldNames as $field) {
           $dataToSign[] = $field . "=" . $params[$field];
        }
    
        return implode(",", $dataToSign);
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

    public function processPayment(Request $request,$domain = '')
    {
      try {
        if(isset($request->auth_trans_ref_no)){
          $auth_trans_ref_no = explode("cyber", $request->auth_trans_ref_no);
            $request->merge(['from' => $auth_trans_ref_no[0]]);
            $request->merge(['user_id' => $auth_trans_ref_no[1]]);
            $user = User::findOrFail($request->user_id);
            Auth::login($user);
        }
        if($request->req_reference_number=='cart'){
            return $this->completeOrderCart($request);
        }elseif($request->req_reference_number=='wallet'){
            return $this->completeOrderWallet($request);
        }elseif ($request->req_reference_number == 'pickup_delivery') {
          return $this->completeOrderPickup($request);
        }
      } catch (\Throwable $th) {
        return $th->getMessage();
      }

    }
    public function completeOrderCart($request)
    {
        $order = Order::where('order_number',$request->req_transaction_uuid)->firstOrFail();
        if (isset($request->decision) && $request->decision == 'ACCEPT') {
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
                             'transaction_id' => $request->transaction_id,
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
 
                 if(isset($request->from) && $request->from=='mob')
                 {
                 $returnUrl = route('payment.gateway.return.response').'/?gateway=cyber_source'.'&status=200&order='.$order->order_number;
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
                 if(isset($request->from) && $request->from=='mob')
                 {
                 $returnUrl = route('payment.gateway.return.response').'/?gateway=cyber_source'.'&status=00&order='.$order->order_number;
                 return Redirect::to($returnUrl);
                 }else{
                 return Redirect::to(route('showCart'))->with('error',$request->message);
                 }
         }
    }

     public function completeOrderWallet($request)
   {
    if (isset($request->decision) && $request->decision == 'ACCEPT')
         {
           $data = Payment::where('transaction_id',$request->req_transaction_uuid)->first();
           $user = auth()->user();
           $wallet = $user->wallet;
           $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->req_transaction_uuid . '</b>']);

           if(isset($request->action) && $request->action=='mob')
           {
             $returnUrl = route('payment.gateway.return.response').'/?gateway=cyber_source'.'&status=200&transaction_id='.$request->req_transaction_uuid.'&action=wallet';
             return Redirect::to($returnUrl);
           }else{
             return Redirect::to(route('user.wallet'));
           }
         }else{
           $data = Payment::where('transaction_id',$request->req_transaction_uuid)->first();
           $data->delete();
           if(isset($request->from) && $request->from=='mob')
           {
             $returnUrl = route('payment.gateway.return.response').'/?gateway=cyber_source'.'&status=00&transaction_id='.$request->req_transaction_uuid.'&action=wallet';
             return Redirect::to($returnUrl);
           }else{
             return Redirect::to(route('user.wallet'))->with('error',$request->message);
           }
         }
   }

    public function completeOrderPickup($request)
    {
        $order = Order::where('order_number',$request->req_transaction_uuid)->firstOrFail();
      if (isset($request->decision) && $request->decision == 'ACCEPT') {
        $user = auth()->user();
        $order->payment_status = '1';
        $order->save();
        Payment::create(['amount' => 0, 'transaction_id' => $request->transaction_id, 'balance_transaction' => $order->payable_amount, 'type' => 'pickup_deleivery', 'date' => date('Y-m-d'), 'order_id' => $order->id,'user_id'=>$user->id]);
           // Deduct wallet amount if payable amount is successfully done on gateway
         if ( $order->wallet_amount_used > 0 ) {
         $wallet = $user->wallet;
         $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
         if(!$transaction_exists){
             $wallet->withdrawFloat($order->wallet_amount_used, [
                 'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                 'order_number' => $order->order_number,
                 'transaction_id' => $request->transaction_id,
                 'payment_option' => 'cyber_source'
             ]);
         }
       }
        // Send Notification
        $plaseOrderForPickup = new PickupDeliveryController();
        $request->request->add(['transaction_id' => $request->transaction_id,'order_number'=>$request->req_transaction_uuid]);
        $plaseOrderForPickup =   $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
        if (isset($request->from) && $request->from == 'mob') {
          $returnUrl = route('payment.gateway.return.response').'/?gateway=cyber_source'.'&status=200&transaction_id='.$request->transaction_id;
          return Redirect::to($returnUrl);
        } else {
          return Redirect::to(route('front.booking.details', $order->order_number));
        }
      } else {
        if (isset($request->action) && $request->action == 'mob') {
              $response['status'] = 204;
              $response['msg'] =  $request->message;
              $response['payment_from'] = 'pickup_delivery';
              $response['order'] = $order;
              return $response;
        } else {
          return Redirect::to(route('user.wallet'))->with('error', $request->message);
        }
      }
    }
}
