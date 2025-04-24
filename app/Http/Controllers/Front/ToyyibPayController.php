<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\WalletController;
use App\Http\Controllers\Front\UserSubscriptionController;
use App\Http\Controllers\Front\PickupDeliveryController;
use App\Models\{User, UserVendor,CaregoryKycDoc, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser, UserAddress,Transaction};
use Toyyibpay;
use Auth;
use Illuminate\Support\Facades\Log;

class ToyyibPayController extends FrontController
{
    use ApiResponser;
    public $api_key;
    public $currency;
    public $url;
    public $base_url;
    public $test_mode;
    
    public function __construct()
    {
        $base_url = url('/');        
        $toyyib_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'toyyibpay')->where('status', 1)->first();        
        $creds_arr = json_decode($toyyib_creds->credentials);         
        $this->api_key = (isset($creds_arr->toyyibpay_api_key)) ? $creds_arr->toyyibpay_api_key : '';
        $this->url = (isset($creds_arr->toyyibpay_redirect_uri)) ? $creds_arr->toyyibpay_redirect_uri : '';        
        $testmode = (isset($toyyib_creds->test_mode) && ($toyyib_creds->test_mode == '1')) ? true : false;
        $this->test_mode = $testmode; //set it to 'false' when go live
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
        
    }



    public function createCategory($data){        

        $some_data = array(
            'catname' => $data['category_name'], //CATEGORY NAME
            'catdescription' => $data['category_name'], //PROVIDE YOUR CATEGORY DESCRIPTION
            'userSecretKey' => $this->api_key //PROVIDE USER SECRET KEY HERE
          );  
        //   $some_data = array(
        //     'catname' => 'Order data', //CATEGORY NAME
        //     'catdescription' => 'Order Description' , //PROVIDE YOUR CATEGORY DESCRIPTION
        //     'userSecretKey' => $this->api_key //PROVIDE USER SECRET KEY HERE
        //   );          
          $curl = curl_init();        
          curl_setopt($curl, CURLOPT_POST, 1);
          curl_setopt($curl, CURLOPT_URL, $this->url.'/index.php/api/createCategory');  //PROVIDE API LINK HERE
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);        
          $result = curl_exec($curl);         
          $info = curl_getinfo($curl);
          curl_close($curl);
          $obj = json_decode($result);
          if($obj){
            return $obj->CategoryCode;
          }
    }

    public function createBill($codeCategory,$data)
    {   

            $rules = [
                'amount'   => 'required',
                'payment_form'   => 'required'
            ];

            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($data['amount']);
            $payment_form = $data['payment_form'];

            if(empty($user->phone_number)){
                $rules['phone_number'] = 'required';
            }

            //$returnUrl = route('payment.toyyibpay.callbackSuccess',$data['payment_form']);
            $customer_data = array(
                'customer_id' => 'customer_'.$user->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone_number
            );
            $order_tags = ['user_id' => strval($user->id), 'payment_form' => $payment_form];
            $reference_number = $description = '';
            $returnUrlParams = '?order_id={order_id}&order_token={order_token}&gateway=cashfree&amount=' . $data['amount'] . '&payment_form=' . $payment_form;

            if($payment_form == 'cart'){
                $description = 'Order Checkout';
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                //$request->request->add(['cart_id' => $cart->id]);
                $reference_number = $data['order_number'];
                $order_tags['cart_id'] = strval($cart->id);
                $order_tags['order_number'] = $reference_number;

                $order = Order::where('order_number', $reference_number)->first();
                $returnUrlParams = $returnUrlParams . '&cart_id=' .$cart->id; //. '&order_id={order_id}' .$reference_number. '&order_token=' .$reference_number;
            }
            elseif($payment_form == 'wallet'){
                $description = 'Wallet Checkout';
                 $reference_number = $user->id;
            }
            if($payment_form == 'tip'){
                $description = 'Tip Checkout';
                $order_tags['order_number'] = $data['order_number'];
                
                $order = Order::where('order_number', $reference_number)->first();
                 $reference_number = $data['order_number'];
                 $returnUrlParams = $returnUrlParams . '&order_id=' .$reference_number. '&order_token=' .$reference_number;
            }
            elseif($payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                if($data['subscription_id']){
                    $slug = $data['subscription_id'];
                    $subscription_plan = SubscriptionPlansUser::with('features.feature')->where('slug', $slug)->where('status', '1')->first();
                    $customer_data['subscription_id'] = $subscription_plan->id;
                    // $reference_number = $request->subscription_id;
                    $returnUrlParams = $returnUrlParams . '&subscription=' . $data['subscription_id'];
                    $order_tags['subscription_id'] = $data['subscription_id'];
                }
            }

            $validator = Validator::make($data, $rules, [
                'amount.required' => 'Amount is required',
                'payment_form.required' => 'Action is required',
                'phone_number.required' => 'Phone number is required'
            ]);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }
        
           // $uniquetoken = md5(uniqid(rand(), true));
           $subsciptionid = $data['subscription_id'] ?? ""; // Added for Gaurav Sir
            $some_data = array(
                'userSecretKey'=> $this->api_key,
                'categoryCode'=> $codeCategory,
                'billName'=> $data['product_name'],
                'billDescription'=> $data['product_name'],
                'billPriceSetting'=>0,
                'billPayorInfo'=>1,
                'billAmount'=>$data['amount']*100,
                'billReturnUrl'=> url($data['serverUrl'].'payment/toyyib/callback-success')."/".$data['payment_form']."?userid=".$user->id."&auth_token=".$data['auth_token']."&amt=".$data['amount']."&subscriptionid=".$subsciptionid,
                'billCallbackUrl'=> url($data['serverUrl'].'payment/toyyib/callback'),
                // 'billReturnUrl'=> url('payment/toyyib/callback-success')."/".$data['payment_form']."?userid=".$user->id,
                // 'billCallbackUrl'=> url('payment/toyyib/callback'),
                'billExternalReferenceNo' => $data['order_number']??"",
                'billTo'=> $user->name,
                'billEmail' => $user->email,
                'billPhone'=>  $user->phone_number,
                'billSplitPayment'=> 0,
                'billSplitPaymentArgs'=> '',
                'billPaymentChannel'=>'0',
                'billContentEmail'=>'Thank you for purchasing our product!',
                'billChargeToCustomer'=> 1,
                'billExpiryDate'=>'',
                'billExpiryDays'=>''
              );                
              
              $curl = curl_init();
              curl_setopt($curl, CURLOPT_POST, 1);
              curl_setopt($curl, CURLOPT_URL, $this->url.'/index.php/api/createBill');  
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
            
              $result = curl_exec($curl);
              $info = curl_getinfo($curl);
              $err = curl_error($curl);  
              curl_close($curl);
              
              $obj = json_decode($result);
              if($obj){
                return $obj[0]->BillCode;
              // return $this->successResponse($obj[0]->BillCode, 'Order has been created successfully');
              }else{
                return $err->message;
                //  dd($info);
                //return $this->errorResponse($err->message, 400);
              }
    




      
        
       
       
    }

  

    public function index(Request $request){

        if(!empty($request->all())){
            $data = $request->all();
            $data['serverUrl'] = "";
            $data['auth_token'] = "";
            //dd($data);
            $codeCategory = $this->createCategory($data);
            if(!empty($codeCategory)){
                $bill = $this->createBill($codeCategory,$data);
                if(!empty($bill)){
                    $payUrl = $this->url.'/'.$bill;                    
                    return response()->json(['status' => 'Success', 'payment_link' => $payUrl]);
                }else{
                    return $this->errorResponse($err->message, 400);
                }
            } 
        }
    }

    public function callback(Request $request){

       // http_response_code(200);
      //\Log::info($request->all());
     // dd($request->all());
    }

    
    public function callbackSuccess(Request $request,$payment_form,$domain = ''){
        $toyyibPayRes = $request;     
        $toyyibPayRes['payment_form'] = $toyyibPayRes['payment_form'] ?? $payment_form;
       // return $toyyibPayRes;
       if(isset($request->auth_token) && !empty($request->auth_token))
       {
           $user = Auth::loginUsingId($request->userid);
            // $find_user = User::where('auth_token', $request->auth_token)->first();
            // $user = Auth::login($find_user);
       }else{
            $user = Auth::user();      
       }
        
        // if(!$user)
        // {
            
        // }
            if($toyyibPayRes['status_id'] == '1' || $toyyibPayRes['status_id'] == '2' ){
                if($toyyibPayRes['payment_form'] == 'cart'){                 
                    $order_number = $toyyibPayRes['order_id'];
                    $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                    if ($order) {
                        $returnUrlParams = '';
                        $returnUrl = route('order.success', $order->id);                        
                        $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();

                        // Remove cart
                        CaregoryKycDoc::where('cart_id',$cart->id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                        Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                        CartAddon::where('cart_id', $cart->id)->delete();
                        CartCoupon::where('cart_id', $cart->id)->delete();
                        CartProduct::where('cart_id', $cart->id)->delete();
                        CartProductPrescription::where('cart_id', $cart->id)->delete();  
                        // send sms 
                        $this->sendSuccessSMS($request, $order);             

                        if($toyyibPayRes['status_id'] == '2' ){
                            return Redirect::to(url($returnUrl . $returnUrlParams))->with('success', 'Transaction has been pending');
                        }else{                            
                            Order::where('order_number', $order_number)->update(['payment_status' => 1]);
                            if(isset($request->auth_token) && !empty($request->auth_token))
                            {
                                $returnUrl = route('payment.gateway.return.response').'/?gateway=toyyibpay'.'&status=200&order='.$order->order_number;
                                return Redirect::to($returnUrl); 
                            }else{
                                return Redirect::to(url($returnUrl . $returnUrlParams))->with('success', 'Transaction has been completed successfully');
                            }
                            // return Redirect::to(url($returnUrl . $returnUrlParams))->with('success', 'Transaction has been completed successfully');
                        }
    
                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($toyyibPayRes['payment_form'] == 'wallet'){
                    $returnUrl = route('user.wallet');

                    if($toyyibPayRes['status_id'] == '2' ){
                        return Redirect::to(url($returnUrl))->with('success', 'Transaction has been pending');
                    }else{                        
                         $user = auth()->user();
                         $wallet = $user->wallet;
                         $wallet->depositFloat($request->amt, ['Wallet has been <b>Credited</b> by transaction reference <b>'.$request->transaction_id.'</b>']);
                        if(isset($request->auth_token) && !empty($request->auth_token))
                        {                                
                            $returnUrl = route('payment.gateway.return.response').'/?gateway=toyyibpay'.'&status=200&transaction_id='.$request->merchant_reference.'&action=wallet';
                            return Redirect::to($returnUrl); 
                        }else{
                            return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
                        }
                    }
                    
                }
                elseif($toyyibPayRes['payment_form'] == 'tip'){
                    $returnUrl = route('user.orders');
                    if($toyyibPayRes['status_id'] == '2' ){
                        return Redirect::to(url($returnUrl))->with('success', 'Transaction has been pending');
                    }else{
                        if(isset($request->auth_token) && !empty($request->auth_token))
                        { 
                            $returnUrl = route('payment.gateway.return.response').'/?gateway=toyyibpay'.'&status=200&order='.$toyyibPayRes['order_id'].'&action=tip';
                            return Redirect::to($returnUrl); 
                        }else{
                            return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
                        }                        
                    }
                   
                }
                elseif($toyyibPayRes['payment_form'] == 'subscription'){
                    $returnUrl = route('user.subscription.plans');
                    if($toyyibPayRes['status_id'] == '2' ){
                        return Redirect::to(url($returnUrl))->with('success', 'Transaction has been pending');
                    }else{
                        $request->request->add(['user_id' => $user->id, 'payment_option_id' => 26, 'amount' => $request->amt, 'transaction_id' => $request->transaction_id]);
                        $subscriptionController = new UserSubscriptionController();                        
                        $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscriptionid);

                        if(isset($request->auth_token) && !empty($request->auth_token))
                        { 
                            $returnUrl = route('payment.gateway.return.response').'/?gateway=toyyibpay'.'&status=200&transaction_id='.$request->transaction_id.'&action=subscription';
                            return Redirect::to($returnUrl); 
                        }else{
                            return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
                        }
                    }
                    
                }
            }else{

                if($toyyibPayRes['status_id'] == '3'){

                    if( $toyyibPayRes['payment_form'] == 'cart'){
                        $order = Order::where('order_number',  $toyyibPayRes['order_id'])->first();
                        if($order){
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
                    } elseif( $toyyibPayRes['payment_form'] == 'wallet'){
                        return Redirect::to(route('user.wallet'))->with('error', 'Transaction has been cancelled');
                    } elseif( $toyyibPayRes['payment_form'] == 'tip'){
                        return Redirect::to(route('user.orders'))->with('error', 'Transaction has been cancelled');
                    } elseif( $toyyibPayRes['payment_form'] == 'subscription'){
                        return Redirect::to(route('user.subscription.plans'))->with('error', 'Transaction has been cancelled');
                    }

                }
              
            }

     }

     public function orderForApp (Request $request){
        //$primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();        
        $user = Auth::user();        
        
        $data = $request->all();
        $data['address_id'] = "";
        $data['payment_form'] = $data['action'] ?? 'cart';
        $data['product_name'] = "Order Products";
        $data['category_name'] = "Category";
        //return $data;
        //dd($data);
        $codeCategory = $this->createCategory($data);
        if(!empty($codeCategory)){
            $bill = $this->createBill($codeCategory,$data);
            if(!empty($bill)){
                $payUrl = $this->url.'/'.$bill;                          
                return response()->json(['status' => 'Success', 'payment_link' => $payUrl]);
            }else{
                return $this->errorResponse($err->message, 400);
            }
        }
       
    }



}
