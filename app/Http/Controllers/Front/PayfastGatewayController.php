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
use App\Http\Controllers\Front\{FrontController, OrderController, WalletController, UserSubscriptionController, PickupDeliveryController};
use App\Models\Client as CP;
use App\Models\{PaymentOption, Client, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, UserDevice, UserVendor, Transaction};

class PayfastGatewayController extends FrontController
{
    use ApiResponser;
    public $gateway;
    public $currency;

    public function __construct()
    {
        
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

    public function payfastPurchase(Request $request, $domain = ''){
        try{
            $payfast_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'payfast')->where('status', 1)->first();
            $creds_arr = json_decode($payfast_creds->credentials);
            $merchant_id = (isset($creds_arr->merchant_id)) ? $creds_arr->merchant_id : '';
            $merchant_key = (isset($creds_arr->merchant_key)) ? $creds_arr->merchant_key : '';
            $passphrase = (isset($creds_arr->passphrase)) ? $creds_arr->passphrase : '';
            $testmode = (isset($payfast_creds->test_mode) && ($payfast_creds->test_mode == '1')) ? true : false;
            $this->gateway = Omnipay::create('PayFast');
            $this->gateway->setMerchantId($merchant_id);
            $this->gateway->setMerchantKey($merchant_key);
            $this->gateway->setPassphrase($passphrase);
            $this->gateway->setTestMode($testmode); //set it to 'false' when go live
            
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';

            $rules = [
                'amount'   => 'required',
                'payment_form'   => 'required'
            ];

            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);

            $returnUrlParams = '?amount='.$amount;
            // $address_id = 0;
            // $tip = 0;
            $meta_data = array();
            $reference_number = $description = '';

            // if($request->has('tip')){
            //     $tip = $request->tip;
            // }

            // $returnUrl = route('order.return.success');
            // if($request->payment_form == 'wallet'){
            //     $returnUrl = route('user.wallet');
            // }

            $request_arr = array(
                'merchant_id' => $this->gateway->getMerchantId(),
                'merchant_key' => $this->gateway->getMerchantKey()
            );

            if($request->payment_form == 'cart'){
                $description = 'Order Checkout';
                // $rules['order_number'] = 'required';
                $returnUrl = route('order.return.success');
                $tip = 0;
                if($request->has('tip')){
                    $tip = $request->tip;
                }
                if( ($request->has('address_id')) && ($request->address_id > 0) ){
                    $address_id = $request->address_id;
                }

                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();

                $request_arr['return_url'] = $returnUrl;
                $request_arr['cancel_url'] = url($request->cancelUrl);
                $request_arr['notify_url'] = url("payment/payfast/notify");
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Cart';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_int3'] = $address_id; // address id
                $request_arr['custom_str1'] = $request->payment_form; // action
                $request_arr['custom_str2'] = $tip; // tip amount
                // $request_arr['custom_str3'] = $request->order_number;
            }
            elseif($request->payment_form == 'wallet'){
                $description = 'Wallet Checkout';
                $returnUrl = route('user.wallet');

                $request_arr['return_url'] = $returnUrl;
                $request_arr['cancel_url'] = url($request->cancelUrl);
                $request_arr['notify_url'] = url("payment/payfast/notify");
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Wallet';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->payment_form; // action
            }
            elseif($request->payment_form == 'tip'){
                $description = 'Tip Checkout';
                $rules['order_number'] = 'required';
                $returnUrl = route('user.orders');

                $request_arr['return_url'] = $returnUrl;
                $request_arr['cancel_url'] = url($request->cancelUrl);
                $request_arr['notify_url'] = url("payment/payfast/notify");
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Tip';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->payment_form; // action
                $request_arr['custom_str2'] = $request->order_number;
            }
            elseif($request->payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                $slug = $request->subscription_id;
                $returnUrl = route('user.subscription.plans');
                $subscription_plan = SubscriptionPlansUser::where('slug', $slug)->where('status', '1')->first();
                $rules['subscription_id'] = 'required';

                $request_arr['return_url'] = $returnUrl;
                $request_arr['cancel_url'] = url($request->cancelUrl);
                $request_arr['notify_url'] = url("payment/payfast/notify");
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Subscription';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->payment_form; // action
                $request_arr['custom_str2'] = $slug; // subscription plan slug
            }
            elseif($request->payment_form == 'pickup_delivery'){
                $description = 'Pickup Delivery Checkout';
                $rules['order_number'] = 'required';

                $request_arr['return_url'] = route('front.booking.details', $request->order_number);
                $request_arr['cancel_url'] = url($request->cancelUrl);
                $request_arr['notify_url'] = url("payment/payfast/notify");
                $request_arr['amount'] = $amount;
                $request_arr['item_name'] = 'Pickup Delivery';
                $request_arr['custom_int1'] = $user->id; // user id
                $request_arr['custom_int2'] = 6; //payment option id
                $request_arr['custom_str1'] = $request->payment_form; // action
                $request_arr['custom_str2'] = $request->order_number;
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }

            $request_arr['currency'] = $this->currency; //'ZAR';
            $request_arr['description'] = $description;

            // $request_arr = array(
            //     'merchant_id' => $this->gateway->getMerchantId(),
            //     'merchant_key' => $this->gateway->getMerchantKey(),
            //     'return_url' => $returnUrl,
            //     'cancel_url' => url($request->cancelUrl),
            //     'notify_url' => url("payment/payfast/notify"),
            //     'amount' => $amount,
            //     'item_name' => 'test item',
            //     'custom_int1' => $user->id, // user id
            //     'custom_int2' => 6, //payment option id
            //     'custom_int3' => $address_id, // address id
            //     'custom_str1' => $tip, // tip amount
            //     'custom_str2' => $request->payment_form,
            //     'currency' => $this->currency, //'ZAR',
            //     'description' => 'This is a test purchase transaction',
            //     // 'metadata' => ['user_id' => $user->id],
            // );

            $response = $this->gateway->purchase($request_arr)->send();
            unset($request_arr['description']);
            $passphrase = $this->gateway->getPassphrase();
            $signature = $this->generateSignature($request_arr, $passphrase);
            $request_arr['signature'] = $signature;

            if ($response->isSuccessful()) {
                return $this->successResponse($response->getData());
            }
            elseif ($response->isRedirect()) {
                $data['formData'] = $request_arr;
                $data['redirectUrl'] = $response->getRedirectUrl();
                $this->failMail();
                return $this->successResponse($data);
            }
            else {
                $this->failMail();
                return $this->errorResponse($response->getMessage(), 400);
            }
        }
        catch(\Exception $ex){
            $this->failMail();
            return $this->errorResponse($ex->getMessage(), 400);
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
                'payment_option_id' => $pfData->custom_int2,
                'transaction_id' => $pfData->pf_payment_id
            ]);
            if($pfData->custom_str1 == 'cart'){
                $pfData->request->add([
                    'address_id' => $pfData->custom_int3,
                    'tip' => $pfData->custom_str2,
                ]);
                $order = new OrderController();
                $placeOrder = $order->placeOrder($pfData);
                $response = $placeOrder->getData();
            }
            elseif($pfData->custom_str1 == 'wallet'){
                $pfData->request->add([
                    'wallet_amount' => $pfData->amount_gross
                ]);
                $wallet = new WalletController();
                $creditWallet = $wallet->creditWallet($pfData);
                $response = $creditWallet->getData();
            }
            elseif($pfData->custom_str1 == 'pickup_delivery'){
                $order_number = $pfData->custom_str2;
                $pfData->request->add(['payment_option_id' => 6, 'amount' => $pfData->amount_gross, 'order_number' => $order_number]);
                $pickupDeliveryController = new PickupDeliveryController();
                $pickupDeliveryResponse = $pickupDeliveryController->orderUpdateAfterPaymentPickupDelivery($pfData);
                $response = $this->successResponse($pickupDeliveryResponse, '', 200)->getData();
                // $response = response()->json(['status'=>'Success', 'message' => '', 'data'=> $pickupDeliveryResponse]);
                //\Log::info(json_encode($response));
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
                'payment_option_id' => $pfData->custom_int2,
                'transaction_id' => $pfData->pf_payment_id
            ]);
            if($pfData->custom_str1 == 'cart'){
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
                            $cart_id = $pfData->custom_int3;
                            Cart::where('id', $cart_id)->update(['schedule_type' => NULL, 'scheduled_date_time' => NULL]);
                            CartAddon::where('cart_id', $cart_id)->delete();
                            CartCoupon::where('cart_id', $cart_id)->delete();
                            CartProduct::where('cart_id', $cart_id)->delete();
                            CartProductPrescription::where('cart_id', $cart_id)->delete();

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
            elseif($pfData->custom_str1 == 'pickup_delivery'){
                $order_number = $pfData->custom_str2;
                $pfData->request->add(['payment_option_id' => 6, 'amount' => $pfData->amount_gross, 'order_number' => $order_number]);
                $pickupDeliveryController = new PickupDeliveryController();
                $pickupDeliveryResponse = $pickupDeliveryController->orderUpdateAfterPaymentPickupDelivery($pfData);
                $response = $this->successResponse($pickupDeliveryResponse, '', 200)->getData();
                // $response = response()->json(['status'=>'Success', 'message' => '', 'data'=> $pickupDeliveryResponse]);
                //\Log::info(json_encode($response));
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

            if($pfData->custom_str1 == 'cart'){
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

}
