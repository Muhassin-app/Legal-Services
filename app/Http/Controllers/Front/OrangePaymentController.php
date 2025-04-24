<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser, OrderTrait};
use App\Http\Traits\HomePage\HomePageTrait;
use App\Models\{Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, ClientLanguage, Order, Payment, PaymentOption, User, UserVendor};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Transaction;
use Illuminate\Support\Facades\Redirect;

class OrangePaymentController extends Controller
{
    use HomePageTrait;
    use ApiResponser;
    private $orangepay_MerchantKey;
    private $orangepay_MerchantToken;
    private $client_language;
    private $currency;
    private $url;
    public function __construct()
    {
        $payOption = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'orange_pay')->where('status', 1)->first();
        if (@$payOption && !empty($payOption->credentials)) {
            $credentials = json_decode($payOption->credentials);
            $this->orangepay_MerchantKey = $credentials->orangepay_MerchantKey;
            $this->orangepay_MerchantToken = $credentials->orangepay_MerchantToken;
            $client_language = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->first();
            $this->client_language = isset($client_language) ?  $client_language->language->sort_code ?? 'en' : 'en';
            if($payOption->test_mode =='1'){
                $this->url = 'https://api.orange.com/orange-money-webpay/dev/v1/webpayment';
                $this->currency = 'OUV';
            }
            else{
            $this->url = 'https://api.orange.com/orange-money-webpay/sl/v1/webpayment';
            $this->currency = 'SLE';

            }
        }
    }
    public function create_token(Request $request)
    {
        $curl = curl_init();
        $bearer = $this->orangepay_MerchantToken;
        $headers = [
            'Authorization' => 'Basic ' . $bearer,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = $key . ': ' . $value;
        }
        $url = 'https://api.orange.com/oauth/v3/token';
        $response = $this->makeCurlRequest($url, 'POST', [], $formattedHeaders);
        return $response;
    }

    public function orangePayPurchase(Request $request)
    {
        try {
            $user = Auth::user();
        //     if(isset($request->auth_token) && !empty($request->auth_token)){
        //       $user = User::where('auth_token', $request->auth_token)->first();
        //       Auth::login($user);
        //       $user->auth_token = $request->auth_token;
        //       $user->save();
        //    }
            $response = $this->paymentRequest($request);
            if (isset($response['status']) && $response['status'] == 201) {
                Payment::create(['amount' => 0, 'transaction_id' => $response['pay_token'], 'balance_transaction' => $request->total_amount, 'type' => $request->from, 'date' => date('Y-m-d'), 'payment_detail' => $request->order_number,'user_id'=>$user->id]);
                return $this->successResponse($response['payment_url']);
            } else {
                return $this->errorResponse("Something went wrong", 400);
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage().' '.$e->getLine(), 400);
        }
    }

    public function web_payment(Request $request)
    {
        try {
            $user = auth()->user();
            $response = $this->paymentRequest($request);
            if (isset($response['status']) && $response['status'] == 201) {
                Payment::create(['amount' => 0, 'transaction_id' => $response['pay_token'], 'balance_transaction' => $request->total_amount, 'type' => $request->from, 'date' => date('Y-m-d'), 'payment_detail' => $request->order_number,'user_id'=>$user->id]);
                return $this->successResponse($response['payment_url'], __('Payment has been initiated successfully'), 200);
            } else {
                return $this->errorResponse("Something went wrong", 400);
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
    public function paymentRequest($request)
    {
        $order_number = (string) $this->orderNumber($request);
        $data['action'] = $action = isset($request->action) ? $request->action : 'web';
        $data['from'] =$from = isset($request->from) ? $request->from : '';
        $total_amount = isset($request->total_amount)? $request->total_amount : $request->amt;
        $request->merge(['total_amount' =>  $total_amount,'from'=>$data['from'],'order_number'=>$order_number]);
        $data['order_number'] =$order_number;
        $data['auth_id'] =base64_encode(Auth::user()->id) ;
        $response = $this->create_token($request);
        $data = json_decode($response, true);
        $accessToken = $data['access_token'];
        $curl = curl_init();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];
        $data = [
            "merchant_key" => $this->orangepay_MerchantKey,
            "currency" => $this->currency ,
            "order_id" => $order_number,
            "amount" =>$total_amount,
            "return_url" => url('success-orangepay?order_id='.$order_number.'&from='. $from.'&action='. $action),
            "cancel_url" =>  url('cancel-orangepay?order_id='.$order_number.'&from='. $from.'&action='. $action),
            "notif_url" =>  url('success-orangepay?order_id='.$order_number.'&from='. $from.'&action='. $action),
            "lang" => $this->client_language,
            "reference" => $order_number,
        ];
        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = $key . ': ' . $value;
        }
        $url = $this->url;
        $response = $this->makeCurlRequest($url, 'POST', $data, $formattedHeaders);
        $response = json_decode($response, true);
        return  $response;
    }
    public function completeOrderCart($request)
    {
        if (isset($request['status']) && $request['status'] == 'SUCCESS') {
            $order = Order::where('order_number', $request['order_id'])->firstOrFail();
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

            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();

            // Deduct wallet amount if payable amount is successfully done on gateway
            if ($order->wallet_amount_used > 0) {
                $user = User::find(auth()->id());
                $wallet = $user->wallet;
                $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%' . $order->order_number . '%')->first();
                if (!$transaction_exists) {
                    $wallet->withdrawFloat($order->wallet_amount_used, [
                        'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                        'order_number' => $order->order_number,
                        'transaction_id' => $order->order_number,
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

            if (isset($request['action']) && $request['action'] == 'mob') {
                $returnUrl = route('payment.gateway.return.response') . '/?gateway=orange_pay' . '&status=200&from=cart&order=' . $order->order_number;
                return Redirect::to($returnUrl);
            } else {
                return Redirect::to(route('order.success', [$order->id]));
            }
        } else {
        //Failed from cart
        $user = auth()->user();
        $wallet = $user->wallet;
        if(isset($order->wallet_amount_used)){
        $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
        $this->sendWalletNotification($user->id, $order->order_number);
        }
        if(isset($request['action']) && $request['action']=='mob')
        {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=orange_pay'.'&status=204&from=cart';
            return Redirect::to($returnUrl);
        }else{
        return Redirect::to(route('showCart'))->with('error','Order Cancelled.');
        }
        }
    }
    public function successPage(Request $request)
    {
        try {
            \Log::info('auto response');
            \Log::info($request->all());
            $payment_data = Payment::where('transaction_id',$request->order_id)->firstOrFail();
            if($payment_data->type =='cart'){
                return $this->completeOrderCart($request);
            }elseif($payment_data->type=='wallet'){
                return $this->completeOrderWallet($request);
            }elseif ($payment_data->type == 'pickup_delivery') {

              return $this->completeOrderPickup($request);
            }
          } catch (\Throwable $th) {
            return $th->getMessage();
          }
    }

    public function completeOrderWallet($request)
    {
        \Log::info('wallet response');
        \Log::info($request->all());
        if (isset($request['status']) && $request['status'] == 'SUCCESS') {
            $data = Payment::where('transaction_id',$request['order_id'])->first();
            $user = auth()->user();
            $wallet = $user->wallet;
            $wallet->depositFloat($data->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $request['order_id'] . '</b>']);

            if(isset($request['action']) && $request['action']=='mob')
            {
              $returnUrl = route('payment.gateway.return.response').'/?gateway=orange_pay'.'&status=200&from=wallet&transaction_id='.$request['order_id'].'&action=wallet';
              return Redirect::to($returnUrl);
            }else{
              return Redirect::to(route('user.wallet'));
            }
          }else{
            $data = Payment::where('transaction_id',$request['order_id'])->delete();
            if(isset($request['action']) &&  $request['action']=='mob')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=orange_pay'.'&status=204&from=wallet';
                return Redirect::to($returnUrl);
            }else{
                return Redirect::to(route('user.wallet'))->with('error','Payment Cancelled.');
            }
          }
    }

    public function completeOrderPickup($request)
    {

        if (isset($request['status']) && $request['status'] == 'SUCCESS') {
            $order = Order::where('order_number',$request->order_id)->firstOrFail();
            $user = auth()->user();
            $order->payment_status = '1';
            $order->save();
            Payment::create(['amount' => 0, 'transaction_id' =>$request['txnid'], 'balance_transaction' => $order->payable_amount, 'type' => 'pickup_delivery', 'date' => date('Y-m-d'), 'order_id' => $order->id,'user_id'=>$user->id]);
            // Deduct wallet amount if payable amount is successfully done on gateway
            if ( $order->wallet_amount_used > 0 ) {
            $wallet = $user->wallet;
            $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
            if(!$transaction_exists){
                $wallet->withdrawFloat($order->wallet_amount_used, [
                    'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                    'order_number' => $order->order_number,
                    'transaction_id' => $request->transaction_id,
                    'payment_option' => 'orange_pay'
                ]);
            }
        }
            // Send Notification
            $plaseOrderForPickup = new PickupDeliveryController();
            $request->request->add(['transaction_id' => $request->transaction_id]);
            $plaseOrderForPickup =   $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
            if (isset($request['action']) && $request['action'] == 'mob') {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=orange_pay'.'&status=200&from=pickup_delivery'.'&transaction_id='.$request['txnid'];
            return Redirect::to($returnUrl);
            } else {
            return Redirect::to(route('front.booking.details', $order->order_number));
            }
      } else {
        if (isset($request['action']) && $request['action'] == 'mob') {
            $returnUrl = route('payment.gateway.return.response').'/?gateway=orange_pay'.'&status=204&from=pickup_delivery';
            return Redirect::to($returnUrl);
        } else {
          return Redirect::to(route('user.wallet'))->with('error', __('Order Cancelled'));
        }
      }
    }

    public function orderNumber($request)
    {
    $user = Auth::user();
     if (($request->from == 'cart') || ($request->from == 'pickup_delivery')) {
       $time = $request->order_number;
     }elseif($request->from == 'wallet')
         {
             $time = ($request->transaction_id)??'W_'.time();
             Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->total_amount,'type'=>'wallet','date'=>date('Y-m-d'),'user_id'=>$user->id]);

         }elseif($request->from == 'tip')
         {
              $time = 'T_'.time().'_'.$request->order_number;
              Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'tip','date'=>date('Y-m-d'),'user_id'=>$user->id]);

         }elseif($request->from == 'subscription')
         {
             $time = ($request->subscription_id)??'S_'.time();
             Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'subscription','date'=>date('Y-m-d'),'user_id'=>$user->id]);

         }
         elseif($request->from == 'giftCard')
         {
             $time = ($request->transaction_id)??'W_'.time();
             Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'giftCard','date'=>date('Y-m-d'),'user_id'=>$user->id]);

         }
         return $time;
    }
    public function makeCurlRequest($url, $method, $data, $headers)
    {
        try {
            if (count($data) == 0) {
                $postData = 'grant_type=client_credentials';
            } else {
                $postData = json_encode($data);
            }
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $postData,
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return $e->getMessage().' '.$e->getLine();
        }

    }

    public function cancelPage(Request $request)
    {
        try {
            $payment_data = Payment::where('payment_detail',$request->order_id)->firstOrFail();
            $request->merge(['order_id' => $payment_data->payment_detail]);
            $user = User::findOrFail($payment_data->user_id);
            Auth::login($user);
            if($request->from =='cart'){
                return $this->completeOrderCart($request);
            }elseif($request->from=='wallet'){
                return $this->completeOrderWallet($request);
            }elseif ($request->from == 'pickup_delivery'){
                return $this->completeOrderPickup($request);
            }
          } catch (\Throwable $th) {
          }
    }
}
