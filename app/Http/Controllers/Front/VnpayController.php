<?php

namespace App\Http\Controllers\Front;
use Log;
use Auth;
use App\Helpers\Easebuzz;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use App\Models\{PaymentOption,ClientCurrency,CaregoryKycDoc, Order, Cart, CartAddon, CartProduct, User,  Payment,  CartCoupon, CartProductPrescription, UserVendor, Transaction ,ClientLanguage};

use App\Http\Controllers\Front\{FrontController, OrderController, WalletController, UserSubscriptionController};

class VnpayController  extends FrontController
{
    use ApiResponser;

    private $MERCHANT_KEY;
    private $SALT;
    private $vnp_TmnCode;
    private $vnp_HashSecret;
    private $vnp_Url;
   
    private $vnp_apiUrl;
    private $startTime;
    private $expire;
    
    public function __construct() {
        $payOpt = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'vnpay')->where('status', 1)->first();
       
        $json = json_decode($payOpt->credentials);
        $this->vnp_TmnCode = $json->vnpay_website_id ?? null;//"COCOSIN"; //Website ID in VNPAY System
        $this->vnp_HashSecret =  $json->vnpay_server_key ?? null ;//"RAOEXHYVSDDIIENYWSLDIIZTANXUXZFJ"; //Secret key
        if($payOpt->test_mode == 1){
            $this->vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $this->vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        }else{
            // change url for production mode
            $this->vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";//"https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $this->vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        }
       
        //Config input format
        //Expire
        $this->startTime = date("YmdHis");
        $this->expire = date('YmdHis',strtotime('+15 minutes',strtotime( $this->startTime)));
    }

    function VnPay_gateway (){
        return view('frontend.payment_gatway.vnpay_view')->with([
            'expire'=>$this->expire,
            "startTime"=> $this->startTime,
        ]);
    }

    function order(Request $request){
        $primeLang = ClientLanguage::with('language')->where('is_primary', 1)->first();
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        if($primaryCurrency->currency->iso_code != 'VND' ) {
            $error =  __(' Currency format error!');
            return $this->errorResponse($error, 400);
        }
        $user = Auth::user();
        $amount =  $this->getDollarCompareAmount($request->amount);
       
        $vnp_HashSecret =$this->vnp_HashSecret;
        $vnp_TmnCode = $this->vnp_TmnCode;
        // pr($vnp_HashSecret);
        $vnp_Returnurl =route('vnpay_respont');
        $vnp_TxnRef    = $request->order_number ?? generateOrderNo();// order number 
        $vnp_OrderInfo = $request->order_desc ?? null ;
        $vnp_OrderType = $request->order_type ?? 'billpayment' ;
        $vnp_Amount    = $amount * 100; //"1806000";//
        //pr($vnp_Amount);
        $vnp_Locale   = ($primeLang->language->sort_code == 'en') ? 'en' : 'vn';
        $vnp_BankCode = $request->bank_code ?? null  ;
        $vnp_IpAddr   = $request->ip(); // $_SERVER['REMOTE_ADDR'] ;

        // //Add Params of 2.0.1 Version
        
        $startTime = date("YmdHis");
        $expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));
        $payment_form = $request->payment_form ?? 'cart';
        $cart_id  = '';
      
        if($payment_form == 'cart'){
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $cart_id =   $cart->id;
        }
        
        $order_info = [
            'payment_form'=>  $payment_form ,
            'user_id'=> auth()->user()->id,
            'subscription_id' =>$request->subscription_id ?? '',
            'cart_id' =>$cart_id,
        ];
        $vnp_OrderInfo = json_encode($order_info);
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" =>  $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $startTime,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => 'other',
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate"=>$expire,
        );
        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }
  
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $vnp_Url = $this->vnp_Url ;
        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        
        $response['status']='Success';
        $response['data']=$vnp_Url;
        
        return $this->successResponse($response, 'Order has been created successfully');
       
       
    }

    function vnpay_respont(Request $request){
       
       
        $inputData = array();
        $vnp_HashSecret = $this->vnp_HashSecret;
       
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

           
        $order_number = $inputData['vnp_TxnRef'];
        $meta_data = json_decode($inputData['vnp_OrderInfo']);
            
        $cart_id = $meta_data->cart_id ? $request->cart_id : '';
        $payment_form = $meta_data->payment_form;
       
        if($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00' ){
           
            if($payment_form == 'cart'){
               
               
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
              
                if ($order) {
                    CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                    $returnUrlParams = '';
                    $returnUrl = route('order.success', $order->id);
                    return Redirect::to(url($returnUrl . $returnUrlParams))->with('success', 'Transaction has been completed successfully');

                    // Send Email
                    //  $this->successMail();
                }
            } elseif($payment_form == 'wallet'){
                $returnUrl = route('user.wallet');
                return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
            }
            elseif($payment_form == 'tip'){
                $returnUrl = route('user.orders');
                return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
            }
            elseif($payment_form == 'subscription'){
                $returnUrl = route('user.subscription.plans');
                return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
            }
        }
        else{
            
            
            if($payment_form == 'cart'){
                $order = Order::where('order_number', $order_number)->first();
                if($order){
                    $user= user::find($order->user_id);
                    $wallet_amount_used = $order->wallet_amount_used;
                    if($wallet_amount_used > 0){
                        $transaction = Transaction::where('type', 'deposit')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                        if(!$transaction){
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order <b>'. $order->order_number. '</b>']);
                            $this->sendWalletNotification($order->user_id, $order->order_number);
                        }else{
                            return Redirect::to(route('showCart'))->with('error', 'Your order has already been cancelled');
                        }
                    }
                }
                
                return Redirect::to(route('showCart'))->with('error', 'Your order has been cancelled');
            } elseif($payment_form == 'wallet'){
                return Redirect::to(route('user.wallet'))->with('error', 'Transaction has been cancelled');
            } elseif($payment_form == 'tip'){
                return Redirect::to(route('user.orders'))->with('error', 'Transaction has been cancelled');
            } elseif($payment_form == 'subscription'){
                return Redirect::to(route('user.subscription.plans'))->with('error', 'Transaction has been cancelled');
            }
        }
    }
    function vnpay_respontAPP(Request $request){
       
      // pr($request->all());
        $inputData = array();
        $vnp_HashSecret = $this->vnp_HashSecret;
       
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

           
        $order_number = $inputData['vnp_TxnRef'];
        $meta_data = json_decode($inputData['vnp_OrderInfo']);
            
        $cart_id = $meta_data->cart_id ? $request->cart_id : '';
        $payment_form = $meta_data->payment_form;
        $returnUrl = url('payment/gateway/returnResponse');
       
        if($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00' ){
            $returnUrlParams = '?status=200&gateway=vnpay&action=' . $payment_form;
            if($payment_form == 'cart'){
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                    $returnUrlParams = $returnUrlParams . '&order=' . $order_number;
                }
            } 
            return Redirect::to(url($returnUrl . $returnUrlParams));
        }
        else{
            $returnUrlParams = '?status=0&gateway=vnpay&action=' .$request->payment_form;
            
            if($payment_form == 'cart'){
                $order = Order::where('order_number', $order_number)->first();
                if($order){
                    $user= user::find($order->user_id);
                    $wallet_amount_used = $order->wallet_amount_used;
                    if($wallet_amount_used > 0){
                        $transaction = Transaction::where('type', 'deposit')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                        if(!$transaction){
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order <b>'. $order->order_number. '</b>']);
                        }else{
                            return $this->errorResponse(__('Your order has already been cancelled'), 400);
                        }
                    }
                }
                $returnUrlParams = $returnUrlParams . '&order=' .  $order_number;
  
            } 
            return Redirect::to(url($returnUrl . $returnUrlParams));
        }
    }
    public function VnpayNotify(Request $request, $domain = '')
    {
        try{
           
            $inputData = array();
            $vnp_HashSecret = $this->vnp_HashSecret;
            
            foreach ($request->all() as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
            
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }
            $meta_data = json_decode($inputData['vnp_OrderInfo']);
        
            $cart_id = $meta_data->cart_id ;
            $payment_form = $meta_data->payment_form;
            $subscription_id = $meta_data->subscription_id;
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            $user_id =  $meta_data->user_id;
            $order_number = $inputData['vnp_TxnRef'];
 
            $amount = ($inputData['vnp_Amount'] / 100 );
            
            $transactionId = $inputData['vnp_TransactionNo'] ;
            
            if($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00'){
                if($payment_form == 'cart'){
                   
                    $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                    if ($order) {
                        $order->payment_status = 1;
                        $order->save();
                        $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                        if (!$payment_exists) {
                            
                            $payment = new Payment();
                            $payment->date = date('Y-m-d');
                            $payment->order_id = $order->id;
                            $payment->transaction_id = $transactionId;
                            $payment->balance_transaction = $amount;
                            $payment->type = 'cart';
                            $payment->save();
    
                            // Auto accept order
                            $orderController = new OrderController();
                            $orderController->autoAcceptOrderIfOn($order->id);
    
                            // Remove cart
                            CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                            Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            CartAddon::where('cart_id', $cart_id)->delete();
                            CartCoupon::where('cart_id', $cart_id)->delete();
                            CartProduct::where('cart_id', $cart_id)->delete();
                            CartProductPrescription::where('cart_id', $cart_id)->delete();
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
                        }
    
                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($payment_form == 'wallet'){
                    $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $walletController->creditWallet($request);
                }
                elseif($payment_form == 'tip'){
                    $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                }
                elseif($payment_form == 'subscription'){
                    $request->request->add(['user_id' => $user_id, 'payment_option_id' => 28, 'amount' => $amount, 'transaction_id' => $transactionId]);
                    $subscriptionController = new UserSubscriptionController();
                    $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription_id);
                }
            }
            else{
                $user = User::find($user_id);
                
                if($payment_form == 'cart'){
                    $order = Order::where('order_number', $order_number)->first();
                    if($order){
                        $wallet_amount_used = $order->wallet_amount_used;
                        if($wallet_amount_used > 0){
                            $transaction = Transaction::where('type', 'deposit')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                            if(!$transaction){
                                $wallet = $user->wallet;
                                $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order <b>'. $order->order_number. '</b>']);
                            }
                        }
                    }
                } 
            }
            
            
           
        }
        catch(Exception $ex){
            //\Log::info($ex->getMessage());
        }
        http_response_code(200);
    }
}
