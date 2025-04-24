<?php

namespace App\Http\Controllers\Front;

use DB;
use Log;
use Auth;
use Session;
use Redirect;
use Carbon\Carbon;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\{FrontController, OrderController, WalletController, UserSubscriptionController};
use App\Models\Client as CP;
use App\Models\{PaymentOption, Client, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, UserDevice, UserVendor, Transaction};

class PayUGatewayController extends FrontController
{
    use ApiResponser;
    public $gateway;
    public $currency;
    public $merchant_key;
    public $merchant_salt_v1;
    public $merchant_salt_v2;
    public $test_mode;

    public function __construct()
    {
        $payu_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'payu')->where('status', 1)->first();
        $creds_arr = json_decode($payu_creds->credentials);
        $this->merchant_key = (isset($creds_arr->merchant_key)) ? $creds_arr->merchant_key : '';
        $this->merchant_salt_v1 = (isset($creds_arr->merchant_salt_v1)) ? $creds_arr->merchant_salt_v1 : '';
        $this->merchant_salt_v2 = (isset($creds_arr->merchant_salt_v2)) ? $creds_arr->merchant_salt_v2 : '';
        $this->test_mode = (isset($payu_creds->test_mode) && ($payu_creds->test_mode == '1')) ? true : false;
        
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    function generateSignature($data, $passPhrase = null) {
        // Create parameter string
        $pfOutput = '';
        foreach( $data as $key => $val ) {
            if($val !== '') {
                $pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';
            }
        }
        // Remove last ampersand
        $getString = substr( $pfOutput, 0, -1 );
        if( ($passPhrase !== null) || ($passPhrase !== '') ) {
            $getString .= '&passphrase='. urlencode( trim( $passPhrase ) );
        }
        // return $getString;
        return md5( $getString );
    }

    public function purchase(Request $request, $domain = ''){
        try{
            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);
            $payment_form = $request->payment_form;
            $returnUrlParams = '?amount='.$amount;
            $subscription_slug = '';
            $description = '';
            $address_id = '';
            $cart_id = '';
            $tip = '';
            
            // $returnUrlParams = $returnUrlParams.'&gateway=payu';

            $returnUrl = route('order.return.success');
            $cancelUrl = route('order.return.success');

            if($payment_form == 'cart'){
                $description = 'Order Checkout';
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $cart_id = $cart->id;
                // $request->request->add(['cart_id' => $cart->id]);
                // $reference_number = $request->order_number;
                // $order_tags['cart_id'] = strval($cart->id);
                // $order_tags['order_number'] = $reference_number;

                // $order = Order::where('order_number', $reference_number)->first();
                // $returnUrlParams = $returnUrlParams . '&cart_id=' .$cart->id;

                if( ($request->has('address_id')) && ($request->address_id > 0) ){
                    $address_id = $request->address_id;
                }
            }
            elseif($payment_form == 'wallet'){
                $description = 'Wallet Checkout';
            }
            if($payment_form == 'tip'){
                $tip = $request->tip;
                $description = 'Tip Checkout';
            }
            elseif($payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                if($request->has('subscription_id')){
                    $subscription_slug = $request->subscription_id;
                }
            }

            $request_arr = array(
                'key' => 'Haaqd0kz', //$this->merchant_key,
                'txnid' => $request->order_number,
                'amount' => $amount,
                'productinfo' => 'Sample Product',
                'firstname' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                // 'lastname' => '',
                'surl' => $returnUrl,
                'furl' => $cancelUrl,
                'udf1' => $user->id, // user id
                'udf2' => $address_id, // address id
                'udf3' => ($subscription_slug != '') ? $subscription_slug : $cart_id, //subscription slug or cart ID
                'udf4' => $tip, // tip amount
                'udf5' => $payment_form,
                // 'custom_note' => $description,
                // 'metadata' => ['user_id' => $user->id],
            );

            $sha512_string = '';
            $i = 0;
            foreach($request_arr as $key => $val){
                $sha512_string .= $val;
                if($key != count($request_arr)-1){
                    $sha512_string .= '|';
                }
                else{
                    $sha512_string .= '||||||xcl3bIVFu6';//.$this->merchant_salt_v1;
                }
                $i++;
            }
            // dd($sha512_string);
            $hash = hash('sha512', $sha512_string);
            $request_arr['hash'] = $hash;

            $data['formData'] = $request_arr;
            $data['redirectUrl'] = $this->getRedirectUrl();
                
            return $this->successResponse($data);
        }
        catch(\Exception $ex){
            //\Log::info($ex->getMessage());
            return $this->errorResponse('Server Error', 400);
        }
    }

    public function payfastNotify(Request $request, $domain = '')
    {
        // Notify PayFast that information has been received
        //dd('sad');
        //\Log::info($request->all());
        header( 'HTTP/1.0 200 OK' );
        flush();

        // Posted variables from ITN
        $pfData = $request;
        $pfData->payment_status = 'COMPLETE';
        //update db
        switch( $pfData->payment_status )
        {
        case 'COMPLETE':
            // If complete, update your application, email the buyer and process the transaction as paid
            $pfData->request->add([
                'user_id' => $pfData->custom_int1,
                'payment_option_id' => $pfData->custom_int3,
                'transaction_id' => $pfData->pf_payment_id
            ]);
            if($pfData->custom_str2 == 'cart'){
                $pfData->request->add([
                    'address_id' => $pfData->custom_int2,
                    'tip' => $pfData->custom_str1,
                ]);
                $order = new OrderController();
                $placeOrder = $order->placeOrder($pfData);
                $response = $placeOrder->getData();
            }
            elseif($pfData->custom_str2 == 'wallet'){
                $pfData->request->add([
                    'wallet_amount' => $pfData->amount_gross
                ]);
                $wallet = new WalletController();
                $creditWallet = $wallet->creditWallet($pfData);
                $response = $creditWallet->getData();
            }

            if($response->status == 'Success'){
            //    $this->successMail();
                return $this->successResponse($response->data, 'Payment completed successfully.', 200);
            }else{
                $this->failMail();
                return $this->errorResponse($response->message, 400);
            }
        break;
        case 'FAILED':
            $this->failMail();
            // There was an error, update your application
            return $this->errorResponse('Payment failed', 400);
        break;
        default:
        $this->failMail();
            // If unknown status, do nothing (safest course of action)
            // return $this->errorResponse($response->getMessage(), 400);
        break;
        }
    }

    public function payfastNotifyApp(Request $request, $domain = '')
    {
        //\Log::info($request->all());
        // Notify PayFast that information has been received
        header( 'HTTP/1.0 200 OK' );
        flush();

        // Posted variables from ITN
        $pfData = $request;
        $pfData->payment_status = 'COMPLETE';
        //update db
        switch( $pfData->payment_status )
        {
        case 'COMPLETE':
            // If complete, update your application, email the buyer and process the transaction as paid
            $pfData->request->add([
                'user_id' => $pfData->custom_int1,
                'payment_option_id' => $pfData->custom_int3,
                'transaction_id' => $pfData->pf_payment_id
            ]);
            if($pfData->custom_str2 == 'cart'){
                $transactionId = $pfData->pf_payment_id;
                $order_number = $pfData->custom_str3;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if($order){
                    // if($payment_details['status']['code'] == 200){
                        $order->payment_status = 1;
                        $order->save();
                        $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                        if(!$payment_exists){
                            $payment = new Payment();
                            $payment->date = date('Y-m-d');
                            $payment->order_id = $order->id;
                            $payment->transaction_id = $transactionId;
                            $payment->balance_transaction = $pfData->amount_gross;
                            $payment->type = 'cart';
                            $payment->save();

                            // Auto accept order
                            $orderController = new OrderController();
                            $orderController->autoAcceptOrderIfOn($order->id);

                            // Remove cart
                            $user_id = $pfData->custom_int1;
                            $cart_id = $pfData->custom_int2;
                            Cart::where('id', $cart_id)->update(['schedule_type' => NULL, 'scheduled_date_time' => NULL]);
                            CartAddon::where('cart_id', $cart_id)->delete();
                            CartCoupon::where('cart_id', $cart_id)->delete();
                            CartProduct::where('cart_id', $cart_id)->delete();
                            CartProductPrescription::where('cart_id', $cart_id)->delete();
                            CartDeliveryFee::where('cart_id', $cart->id)->delete();

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

                            // Send Email
                        //    $this->successMail();
                    //     }
                    // }else{
                    }
                }
            }
            elseif($pfData->custom_str1 == 'wallet'){
                $pfData->request->add([
                    'wallet_amount' => $pfData->amount_gross
                ]);
                $wallet = new WalletController();
                $res = $wallet->creditWallet($pfData);
                $response = $res->getData();
            }
            elseif($pfData->custom_str1 == 'tip'){
                $pfData->request->add([
                    'tip_amount' => $pfData->amount_gross,
                    'order_number' => $pfData->custom_str2
                ]);
                $orderController = new OrderController();
                $res = $orderController->tipAfterOrder($pfData);
                $response = $res->getData();
            }
            elseif($pfData->custom_str1 == 'subscription'){
                $pfData->request->add([
                    'amount' => $pfData->amount_gross,
                    'payment_option_id' => 6
                ]);
                $subscriptionController = new UserSubscriptionController();
                $res = $subscriptionController->purchaseSubscriptionPlan($pfData, '', $pfData->custom_str2);
                $response = $res->getData();
            }

            if($response->status == 'Success'){
            //    $this->successMail();
                return $this->successResponse($response->data, 'Payment completed successfully.', 200);
            }else{
                // $this->failMail();
                return $this->errorResponse($response->message, 400);
            }
        break;
        case 'FAILED':

            if($pfData->custom_str2 == 'cart'){
                $order_number = $pfData->custom_str3;
                $user_id = $pfData->custom_int1;
                $order = Order::where('order_number', $order_number)->first();
                if($order){
                    $user = User::find($user_id);
                    $wallet_amount_used = $order->wallet_amount_used;
                    if($wallet_amount_used > 0){
                        $transaction = Transaction::where('type', 'deposit')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                        if(!$transaction){
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order <b>'. $order->order_number. '</b>']);
                            $this->sendWalletNotification($order->user_id, $order->order_number);
                        }
                    }
                }
                // $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                // foreach($order_products as $order_prod){
                //     OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
                // }
                // OrderProduct::where('order_id', $order->id)->delete();
                // OrderProductPrescription::where('order_id', $order->id)->delete();
                // VendorOrderStatus::where('order_id', $order->id)->delete();
                // OrderVendor::where('order_id', $order->id)->delete();
                // OrderTax::where('order_id', $order->id)->delete();
                // Order::where('id', $order->id)->delete();
            }

            $this->failMail();
            // There was an error, update your application
            return $this->errorResponse('Payment failed', 400);
        break;
        default:
        $this->failMail();
            // If unknown status, do nothing (safest course of action)
            // return $this->errorResponse($response->getMessage(), 400);
        break;
        }
    }

    private function getRedirectUrl(){
        if ($this->test_mode == false){
            return 'https://secure.payu.in/_payment';
        }else{
            return 'https://test.payu.in/_payment';
        }
    }

}
