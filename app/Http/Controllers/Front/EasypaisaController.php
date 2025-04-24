<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\FrontController;

use Auth;
use App\Models\User;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CartAddon;
use App\Models\UserVendor;
use App\Models\CartCoupon;
use App\Models\UserAddress;
use App\Models\CartProduct;
use Illuminate\Http\Request;
use App\Models\PaymentOption;
use App\Models\CaregoryKycDoc;
use Illuminate\Support\Carbon;
use App\Http\Traits\ApiResponser;
use App\Models\CartProductPrescription;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Redirect;
use Log;

class EasypaisaController extends FrontController
{
    use ApiResponser;

    private $storeId;
    private $token_url;
    private $confirm_url;

    public function __construct()
    {
        $payOpt = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'easypaisa')->where('status', 1)->first();
        if(@$payOpt->status){
            $json = json_decode($payOpt->credentials);
            $this->storeId = $json->easypaisa_store_id;
            if ($payOpt->test_mode == '1') {
                $this->token_url = 'https://easypaystg.easypaisa.com.pk/tpg/?';
            } else {
                $this->token_url = 'https://easypay.easypaisa.com.pk/tpg/?';
            }
        }
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
         Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'cart','date'=>date('Y-m-d')]);
   
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

        if(isset($request->app) && !empty($request->app))
        {
            $returnUrl = route('easypaisa.success',['from'=>'?auth_token='.$request->auth_token]);
        }else{ 
            $returnUrl = route('easypaisa.success');
        }

        return $time;
    }

    public function createHash(Request $request)
    {
        $date = convertDateTimeInTimeZone(date('Y-m-d'), 'Asia/Karachi','Y-m-d');
        $dateTime = convertDateTimeInTimeZone(date('H:i:s'), 'Asia/Karachi','H:i:s');
        $order_number =  $this->orderNumber($request);
        $hashUrl = 'amount='.$request->amt.'&orderRefNum='.$order_number.'&paymentMethod=InitialRequest&postBackURL='.route('easypaisa.success').'&storeId='.$this->storeId.'&timeStamp='.$date.'T'.$dateTime;
        $hashNo =  $this->genrateHash($hashUrl);
        $data = array(
            "storeId" => $this->storeId,
            "orderId" => $order_number,
            "transactionAmount" => $request->amt,
            "mobileAccountNo" => '',
            "emailAddress" => '',
            "transactionType" => 'InitialRequest',
            "tokenExpiry" => '',
            "bankIdentificationNumber" => '',
            "encryptedHashRequest" => $hashNo,
            "merchantPaymentMethod" => '',
            'postBackURL' => route('easypaisa.success'),
            'signature' => ''
        );
        $detail =  array('data'=>$data,'url'=>$this->token_url);
        return json_encode($detail);
    }


    public function genrateHash($url)
    {
        $cipher = "aes-128-ecb";
        $crypttext = openssl_encrypt($url, $cipher, 'W867WNCYCISAXGTV',OPENSSL_RAW_DATA);
        $hashRequest = base64_encode($crypttext);
        return $hashRequest;
    }

    public function payForm(Request $request)
    {
        $merchant_data = '';
        $number = $this->orderNumber($request); // order no
        $working_key = $this->access_key; //Shared by CCAVENUES
        $access_code = $this->access_code; //Shared by CCAVENUES
        $url = $this->url; //Shared by CCAVENUES
        $user = auth()->user();
        $address = UserAddress::where('is_primary', '1')->first();
        $merchant_data = 'merchant_id=' . $this->merchant_id . '&order_id=' . $number . '&amount=' . $request->amt . '&currency=' . getPrimaryCurrencyName() . '&redirect_url=' . route('ccavenue.success') . '&cancel_url=' . route('ccavenue.success') . '&language=EN&billing_name=' . $user->name . '&billing_address=' . $address->address . '&billing_city=' . $address->city . '&billing_state=' . $address->state . '&billing_zip=' . $address->pincode . '&billing_country=' . $address->country . '&billing_tel=' . $user->phone_number . '&billing_email=' . $user->email . '&delivery_name=' . $user->name . '&delivery_address=' . $address->address . '&delivery_city=' . $address->city . '&delivery_state=' . $address->state . '&delivery_zip=' . $address->pincode . '&delivery_country=' . $address->country . '&delivery_tel=' . $user->phone_number . '&merchant_param1=' . $number . '&merchant_param2=' . $request->from . '&merchant_param3=web&merchant_param4=' . auth()->id() . '&merchant_param5=&promo_code=&customer_identifier=&';
        $encrypted_data = $this->encrypt($merchant_data, $working_key); // Method for encrypting the data.


        return view('frontend.payment_gatway.ccavenue_view', compact('encrypted_data', 'access_code', 'url'));
    }

    public function payFormWebView(Request $request)
    {
        $merchant_data = '';
        $number = $this->orderNumber($request); // order no
        $working_key = $this->access_key; //Shared by CCAVENUES
        $access_code = $this->access_code; //Shared by CCAVENUES
        $url = $this->url; //Shared by CCAVENUES
        $user = auth()->user();
        $address = UserAddress::where('is_primary', '1')->first();
        $merchant_data = 'merchant_id=' . $this->merchant_id . '&order_id=' . $number . '&amount=' . $request->amt . '&currency=' . getPrimaryCurrencyName() . '&redirect_url=' . route('ccavenue.success') . '&cancel_url=' . route('ccavenue.success') . '&language=EN&billing_name=' . $user->name . '&billing_address=' . $address->address . '&billing_city=' . $address->city . '&billing_state=' . $address->state . '&billing_zip=' . $address->pincode . '&billing_country=' . $address->country . '&billing_tel=' . $user->phone_number . '&billing_email=' . $user->email . '&delivery_name=' . $user->name . '&delivery_address=' . $address->address . '&delivery_city=' . $address->city . '&delivery_state=' . $address->state . '&delivery_zip=' . $address->pincode . '&delivery_country=' . $address->country . '&delivery_tel=' . $user->phone_number . '&merchant_param1=' . $number . '&merchant_param2=' . $request->from . '&merchant_param3=mob&merchant_param4=&merchant_param5=&promo_code=&customer_identifier=&';
        $encrypted_data = $this->encrypt($merchant_data, $working_key); // Method for encrypting the data.


        return view('frontend.payment_gatway.ccavenue_view', compact('encrypted_data', 'access_code', 'url'));
    }


    public function successPage(Request $request)
    {   
        // orderRefrenceNumber , orderRefNumber
        $request->request->add(['orderRefNumber'=>$request->orderRefNumber??$request->orderRefrenceNumber]);
        $payment = Payment::where('transaction_id',$request->orderRefNumber)->first();
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



    public function completeOrderCart($request)
    {
        $order = Order::where('order_number', $request->orderRefNumber)->first();
        if (isset($request->status) && $request->status == '0000') {
            //Success from cart
            $order->payment_status = '1';
            $order->save();
            // Auto accept order
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);
            $cart = Cart::where('user_id', auth()->id())->select('id')->first();
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
            Payment::create(['amount' => 0, 'transaction_id' => $request->orderRefNumber, 'balance_transaction' => $order->payable_amount, 'type' => 'cart', 'date' => date('Y-m-d'), 'order_id' => $order->id]);

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

            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=200&order=' . $order->order_number;
                return Redirect::to($returnUrl);
            } else {
                return Redirect::to(route('order.success', [$order->id]));
            }
        } else {

            //Failed from cart
            $user = auth()->user();
            $wallet = $user->wallet;
            if (isset($order->wallet_amount_used)) {
                $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #' . $order->order_number]);
                $this->sendWalletNotification($user->id, $order->order_number);
            }
            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=00&order=' . $order->order_number;
                return Redirect::to($returnUrl);
            } else {
                return Redirect::to(route('showCart'))->with('error', $request->message);
            }
        }
    }


    public function completeOrderWallet($request)
    {
        if (isset($request->status) && $request->status == '0000') {
            $data = Payment::where('transaction_id', $request->orderRefNumber)->first();
            $user = auth()->user();
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->order_id . '</b>']);

            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=200&transaction_id=' . $request->orderRefNumber . '&action=wallet';
                return Redirect::to($returnUrl);
            } else {
                return Redirect::to(route('user.wallet'));
            }
        } else {
            $data = Payment::where('transaction_id', $request->orderRefNumber)->first();
            $data->delete();

            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=00&transaction_id=' . $request->orderRefNumber . '&action=wallet';
                return Redirect::to($returnUrl)->with('success','Wallet amount added successfuly.');
            } else {
                return Redirect::to(route('user.wallet'))->with('error', $request->message);
            }
        }
        return $this->successResponse($request->getTransactionReference());
    }


    public function completeOrderSubs($request)
    {
        $user = auth()->user();
        $data = Payment::where('transaction_id', $request->orderRefNumber)->first();
        if (isset($request->status) && $request->status == '0000') {
            $subscription = explode('_', $request->orderRefNumber);
            $request->request->add(['user_id' => $user->id, 'payment_option_id' =>23, 'amount' => $data->balance_transaction, 'transaction_id' => $request->orderRefNumber]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[2]);

            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=200&transaction_id=' . $request->orderRefNumber . '&action=subscription';
                return Redirect::to($returnUrl)->with('success','Subscription added successfuly.');;
            } else {
                return Redirect::to(route('user.subscription.plans'))->with('error', $request->message);
            }
        } else {
            $data->delete();

            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=00&transaction_id=' . $request->order_id . '&action=subscription';
                return Redirect::to($returnUrl);
            } else {
                return Redirect::to(route('user.subscription.plans'))->with('error', $request->message);
            }
        }
        return $this->successResponse($request->getTransactionReference());
    }

    public function completeOrderTip($request)
    {
        $data = Payment::where('transaction_id', $request->orderRefNumber)->first();
        if (isset($request->status) && $request->status == '0000') {
            $order_number = explode('_', $request->orderRefNumber);
            $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[2], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $request->orderRefNumber]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);

            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=200&order=' . $order_number[2] . '&action=tip';
                return Redirect::to($returnUrl)->with('success','Tip amount added successfuly.');;
            } else {
                return Redirect::to(route('user.orders'))->with('success', $request->message);
            }
        } else {
            $data->delete();

            if (isset($request->auth) && $request->auth != '') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=easypaisa' . '&status=00&transaction_id=' . $request->order_id . '&action=tip';
                return Redirect::to($returnUrl);
            } else {
                return Redirect::to(route('user.orders'))->with('error', $request->message);
            }
        }
        return $this->successResponse($request->getTransactionReference());
    }


}
