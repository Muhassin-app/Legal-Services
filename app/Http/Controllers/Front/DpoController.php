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
use App\Models\ClientCurrency;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\UrlGenerator;
use Log;

class DpoController extends FrontController
{
    use ApiResponser;

    private $companyToken;
    private $appUrl;
    private $serviceType;
    private $token;

    public function __construct()
    {
        $payOpt = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'dpo')->where('status', 1)->first();
        if(@$payOpt->status){
            $json = json_decode($payOpt->credentials);
            $this->companyToken = $json->company_token;
            $this->serviceType = $json->service_type;
            $this->token = base64_encode($this->companyToken.':'.$this->serviceType);
            // if ($payOpt->test_mode == '1') {
                $this->appUrl = 'https://secure.3gdirectpay.com/';
            // } else {
            //     // $this->appUrl = 'https://sec.windcave.com/api/v1/sessions';
            // }
    
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'FJD';
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
   
        }elseif($request->from == 'pickup_delivery')
        {
         $request->amt = $amt;
         $time = $request->order_number;
         Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'pickup_delivery','date'=>date('Y-m-d'),'user_id'=>auth()->id(),'payment_from'=>$request->device??'web']);
   
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
            $time = 'S_'.time().'_'.(!empty($request->subsid)? $request->subsid : $request->subscription_id);
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$amt,'type'=>'subscription','date'=>date('Y-m-d')]);
            $request->amt = $amt;
        }
        $request->request->add(['amt'=>number_format($amt,2)]);
        return $time;
    }

    public function createTocken(Request $request, UrlGenerator $url)
    {
        $order_number =  $this->orderNumber($request);
        $redirectUrl = $url->to('/payment/dpo/redirect/?order_no='.$order_number);
        $user = User::where('auth_token', $request->auth_token)->first();
        $total_amount = $this->getDollarCompareAmount($request->amt);
        $total_amount = round($total_amount);
        $name = explode(' ',$user->name);
        $customerFirstName = $name[0];
        $customerLastName = !empty($name[1])? $name[1] : '';
        $xml = "<API3G>
                    <CompanyToken>".$this->companyToken."</CompanyToken>
                    <Request>createToken</Request>
                    <Transaction>
                        <PaymentAmount>".$total_amount."</PaymentAmount>
                        <PaymentCurrency>".$this->currency."</PaymentCurrency>
                        <CompanyRef>tr1ss1212bnbv</CompanyRef>
                        <RedirectURL>".$redirectUrl."</RedirectURL>
                        <BackURL>http://www.domain.com/backurl.php </BackURL>
                        <CompanyRefUnique>0</CompanyRefUnique>
                        <PTL>100000</PTL>
                        <CompanyAccRef>www</CompanyAccRef>
                        <PTLtype>minutes</PTLtype>
                        <DefaultPayment>XP</DefaultPayment>
                        <AllowRecurrent></AllowRecurrent>
                        <customerFirstName>".$customerFirstName."</customerFirstName>
                        <customerLastName>".$customerLastName."</customerLastName>
                        <customerEmail>".$user->email."</customerEmail>
                        <customerPhone>".$user->phone_number."</customerPhone>
                    </Transaction>
                    <Services>
                        <Service>
                            <ServiceType>".$this->serviceType."</ServiceType>
                            <ServiceDescription>Airlines Service</ServiceDescription>
                            <ServiceTypeName>Airlines Service</ServiceTypeName>
                            <ServiceDate>2022/06/25 06:52</ServiceDate>
                        </Service>
                    </Services>
                </API3G>";
                
        
        $result = $this->postCurl($xml);
        $paymentTocken = $this->xml2array($result);
        if(!empty($paymentTocken['TransToken'])){
            return json_encode($this->appUrl.'payv2.php?ID='.$paymentTocken['TransToken']);      
        }
    }

    public function createAppTocken(Request $request)
    {
        $request->from = $request->action;
        $order_number =  $this->orderNumber($request);
        $user = Auth::user();
        $redirectUrl = $request->serverUrl.'payment/dpo/redirect/?order_no='.$order_number.'&payment_via=app&status=200&utoken='.$user->auth_token;
        $total_amount = $this->getDollarCompareAmount($request->amt);
        $total_amount = round($total_amount);
        $name = explode(' ',$user->name);
        $customerFirstName = $name[0];
        $customerLastName = !empty($name[1])? $name[1] : '';
        $xml = "<API3G>
                    <CompanyToken>".$this->companyToken."</CompanyToken>
                    <Request>createToken</Request>
                    <Transaction>
                        <PaymentAmount>".$total_amount."</PaymentAmount>
                        <PaymentCurrency>".$this->currency."</PaymentCurrency>
                        <CompanyRef>tr1ss1212bnbv</CompanyRef>
                        <RedirectURL>".$redirectUrl."</RedirectURL>
                        <BackURL>http://www.domain.com/backurl.php </BackURL>
                        <CompanyRefUnique>0</CompanyRefUnique>
                        <PTL>100000</PTL>
                        <CompanyAccRef>www</CompanyAccRef>
                        <PTLtype>minutes</PTLtype>
                        <DefaultPayment>XP</DefaultPayment>
                        <AllowRecurrent></AllowRecurrent>
                        <customerFirstName>".$customerFirstName."</customerFirstName>
                        <customerLastName>".$customerLastName."</customerLastName>
                        <customerEmail>".$user->email."</customerEmail>
                        <customerPhone>".$user->phone_number."</customerPhone>
                    </Transaction>
                    <Services>
                        <Service>
                            <ServiceType>".$this->serviceType."</ServiceType>
                            <ServiceDescription>Airlines Service</ServiceDescription>
                            <ServiceTypeName>Airlines Service</ServiceTypeName>
                            <ServiceDate>2022/06/25 06:52</ServiceDate>
                        </Service>
                    </Services>
                </API3G>";
                
        
        $result = $this->postCurl($xml);
        $paymentTocken = $this->xml2array($result);
        if(!empty($paymentTocken['TransToken'])){
            return $this->successResponse($this->appUrl.'payv2.php?ID='.$paymentTocken['TransToken']);
        }
    }

    private function postCurl($xml){
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->appUrl.'API/v6/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>$xml,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/xml'
            // 'Cookie: AFIDENT=1A5B897A-277F-42B3-8E52-E67B0434944A'
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return simplexml_load_string($response);
        // return $response;
    }

    function xml2array ( $xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;

        return $out;
    }


    public function successPage(Request $request)
    {   
        if(isset($request->auth_token))
        {
            $user = User::find($request->auth_token);
            auth()->login($user);
        }
        //sucess Status 0000
        $request->request->add(['status'=>'0000']);
        $payment = Payment::where('transaction_id',$request->order_no)->first();
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

    public function failPage(Request $request)
    {   
        if(isset($request->auth_token))
            {
                $user = User::find($request->auth_token);
                auth()->login($user);
            }
        //Failed Status 101
        $request->request->add(['status'=>'101']);
        $payment = Payment::where('transaction_id',$request->order_no)->first();
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
        $order = Order::where('order_number',$request->order_no)->first();
        if(isset($request->order_no) && isset($request->TransID))
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
                    $payment->payment_option_id = 32;
                    $payment->user_id = $order->user_id;
                    $payment->transaction_id = $request->TransID;
                    $payment->balance_transaction = $order->payable_amount;
                    $payment->save();
                }
                
                $request->request->add(['order_number'=> $order->order_number, 'payment_option_id' => 32, 'amount' => $order->payable_amount, 'transaction_id' => $request->TransID]);
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



    public function completeOrderCart($request)
    {
        $order = Order::where('order_number', $request->order_no)->first();
        if (isset($request->order_no) && $request->status == '0000') {
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
            Payment::create(['amount' => 0, 'transaction_id' => $request->TransID, 'balance_transaction' => $order->payable_amount, 'type' => 'cart', 'date' => date('Y-m-d'), 'order_id' => $order->id]);

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

            if(!empty($request->payment_via)){
                 if (empty($request->TransID)) {
                    return $this->errorResponse('', 400);
                }
            }else{
                if (isset($request->TransID) && $request->TransID != '') {
                    return Redirect::to(route('order.success', [$order->id]));
                } else {
                    
                }
            }
            
        } else {
            $data = Payment::where('transaction_id', $request->order_no)->first();
            $data->delete();
            //Failed from cart
            $user = auth()->user();
            $wallet = $user->wallet;
            if (isset($order->wallet_amount_used)) {
                $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #' . $order->order_number]);
                $this->sendWalletNotification($user->id, $order->order_number);     
            }

            if(!empty($request->payment_via)){
                return $this->errorResponse('', 400);
            }else{
                return Redirect::to(route('showCart'))->with('error','Transaction failed.');
            }
            
        }
    }


    public function completeOrderWallet($request)
    {
        $data = Payment::where('transaction_id', $request->order_no)->first();
        if (isset($request->order_no) && $request->status == '0000') {  
            $user = auth()->user();
            if(!empty($request->payment_via)){
                $user = User::where('auth_token', $request->utoken)->first();
            }
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request->order_no . '</b>']);

            if(!empty($request->payment_via)){
                if (empty($request->TransID)) {
                   return $this->errorResponse('', 400);
                }
            }else{
                if (isset($request->TransID) && $request->TransID != '') {
                    return Redirect::to(route('user.wallet'))->with('success','Wallet updated successfully.');
                } else {
                    return Redirect::to(route('user.wallet'))->with('error','Transaction failed.');
                }
            }
        } else {
            $data->delete();
            if(!empty($request->payment_via)){
                return $this->errorResponse('', 400);
            }else{
                return Redirect::to(route('user.wallet'))->with('error','Transaction failed.');
            }
            
        }
        return $this->successResponse($request->getTransactionReference());
    }


    public function completeOrderSubs($request)
    {
        $user = auth()->user();
        if(!empty($request->payment_via)){
            $user = User::where('auth_token', $request->utoken)->first();
        }
        $data = Payment::where('transaction_id', $request->order_no)->first();
        if (isset($request->order_no) && $request->status == '0000') {
            $subscription = explode('_', $request->order_no);
            $request->request->add(['user_id' => $user->id, 'payment_option_id' =>42, 'amount' => $data->balance_transaction, 'transaction_id' => $request->order_no]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[2]);

            if(!empty($request->payment_via)){
                if (empty($request->TransID)) {
                    return $this->errorResponse('', 400);
                }
            }else{
                if (isset($request->TransID) && $request->TransID != '') {
                    return Redirect::to(route('user.subscription.plans'))->with('success', 'Subscription added successfully.');
                } else {
                }
            }
            
        } else {
            $data->delete();
            return Redirect::to(route('user.subscription.plans'))->with('error','Transaction failed.');
        }
        return $this->successResponse($request->getTransactionReference());
    }

    public function completeOrderTip($request)
    {
        $data = Payment::where('transaction_id', $request->order_no)->first();
        if (isset($request->order_no) && $request->status == '0000') {
            $order_number = explode('_', $request->order_no);
            $request->request->add(['user_id' => auth()->id(), 'order_number' => $order_number[2], 'tip_amount' => $data->balance_transaction, 'transaction_id' => $request->order_no]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
            if(!empty($request->payment_via)){
                if (empty($request->TransID)) {
                    return $this->errorResponse('', 400);
                }
            }else{
                if (isset($request->TransID) && $request->TransID != '') {
                    return Redirect::to(route('user.orders'))->with('success','Tip amount added successfuly.');
                } else {
                    
                }
            }
        } else {
            $data->delete();
            return Redirect::to(route('user.orders'))->with('error','Transaction failed.');
        }
        return $this->successResponse($request->getTransactionReference());
    }


}
