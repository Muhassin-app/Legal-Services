<?php

namespace App\Http\Controllers\Front;

use Auth;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use App\Models\Transaction;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\{FrontController, OrderController, WalletController, UserSubscriptionController,PickupDeliveryController};
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser,CaregoryKycDoc};

class PaystackGatewayController extends FrontController
{
    use ApiResponser;
    public $gateway;
    public $currency;

    public function __construct()
    {
        $paystack_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'paystack')->where('status', 1)->first();
        $creds_arr = json_decode($paystack_creds->credentials);
        $secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $public_key = (isset($creds_arr->public_key)) ? $creds_arr->public_key : '';
        $testmode = (isset($paystack_creds->test_mode) && ($paystack_creds->test_mode == '1')) ? true : false;
        $this->gateway = Omnipay::create('Paystack');
        $this->gateway->setSecretKey($secret_key);
        $this->gateway->setPublicKey($public_key);
        $this->gateway->setTestMode($testmode); //set it to 'false' when go live
        // dd($this->gateway);

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function paystackPurchase(Request $request){
       //pr($request->all());
        try{
            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);
            $returnUrlParams = '?amount='.$amount;
            $returnRoute = route('payment.paystackCompletePurchase');
            if($request->has('tip')){
                $returnUrlParams = $returnUrlParams.'&tip='.$request->tip;
            }
            if ($request->has('order_number')) {
                $returnUrlParams = $returnUrlParams . '&ordernumber=' . $request->order_number;
            }
            if ($request->has('payment_form')) {
                $returnUrlParams = $returnUrlParams . '&payment_form=' . $request->payment_form;
                if($request->payment_form == 'cart'){
                    $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                    $returnUrlParams = $returnUrlParams . '&cart_id=' . $cart->id;
                }
            }
            if ($request->has('reload_route')) {
                $returnUrlParams = $returnUrlParams . '&reload_route=' . $request->reload_route;
            }
            if ($request->has('subscription_id')) {
                $returnUrlParams = $returnUrlParams . '&subscription_id=' . $request->subscription_id;
            }
            
            $returnUrlParams = $returnUrlParams.'&gateway=paystack&user_id='.$user->id;
            $returnRoute = $returnRoute .   $returnUrlParams;
          
            $response = $this->gateway->purchase([
                'amount' => $amount,
                'currency' => $this->currency, //'ZAR'
                'email' => $user->email,
                'returnUrl' =>  $returnRoute,
                'cancelUrl' => url($request->cancelUrl),
                'metadata' => ['user_id' => $user->id],
                'description' => 'This is a test purchase transaction.',
            ])->send();
            if ($response->isSuccessful()) {
                return $this->successResponse($response->getData());
            }
            elseif ($response->isRedirect()) {
                $this->failMail();
                return $this->successResponse($response->getRedirectUrl());
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
    public function paystackCompletePurchase(Request $request)
    {
        // Once the transaction has been approved, we need to complete it.
        if($request->has(['reference'])){
            $amount = $this->getDollarCompareAmount($request->amount);
            $transaction = $this->gateway->completePurchase(array(
                'amount'                => $amount,
                'transactionReference'  => $request->reference
            ));
            $response = $transaction->send();
            if ($response->isSuccessful()){
                $user_id = $cart_id = $payment_form = $order_number = $subscription_id = '';
                $transactionId = $response->getTransactionReference();
                $order_number = $request->ordernumber ;
                $payment_form =  $request->payment_form;
                $cart_id = $request->cart_id;
                $user_id = $request->user_id;
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
                            // Deduct wallet amount if payable amount is successfully done on gateway
                            if ( $order->wallet_amount_used > 0 ) {
                                $user = User::find( $user_id);
                                $wallet = $user->wallet;
                                $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                                if(!$transaction_exists){
                                    $wallet->withdrawFloat($order->wallet_amount_used, [
                                        'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                                        'order_number' => $order->order_number,
                                        'transaction_id' => $request->tracking_id,
                                        'payment_option' => 'paystack'
                                    ]);
                                }
                                }
                            // Remove cart
                            
                            CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                            Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            CartAddon::where('cart_id', $cart_id)->delete();
                            CartCoupon::where('cart_id', $cart_id)->delete();
                            CartProduct::where('cart_id', $cart_id)->delete();
                            CartProductPrescription::where('cart_id', $cart_id)->delete();
                            // send success sms
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
                        $returnUrl = route('order.success', $order->id);
                        return Redirect::to(url($returnUrl))->with('success', __('Transaction has been completed successfully'));
                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($payment_form == 'wallet'){
                    $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $walletController->creditWallet($request);

                    $returnUrl = route('user.wallet');
                    return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
                }
                elseif($payment_form == 'tip'){
                    $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                    $returnUrl = route('user.orders');
                    return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
                }
                elseif($payment_form == 'subscription'){
                    $subscription_id = $request->subscription_id;
                    $request->request->add(['user_id' => $user_id, 'payment_option_id' => 5, 'amount' => $amount, 'transaction_id' => $transactionId]);
                    $subscriptionController = new UserSubscriptionController();
                    $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription_id);
                    $returnUrl = route('user.subscription.plans');
                    return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
                    
                }
                elseif($request->payment_form == 'pickup_delivery'){
                    $request->request->add(['payment_option_id' => 5, 'amount' => $amount,'order_number' => $request->ordernumber, 'transaction_id' => $transactionId]);
                     // pr($request->all());
                    $plaseOrderForPickup = new PickupDeliveryController();
                    $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                 // pr($request->reload_route);
                  
                    $returnUrl = $request->reload_route;
                    return Redirect::to(url($returnUrl))->with('success', __('Transaction has been completed successfully'));
                }
            //    $this->successMail();
                return $this->successResponse($response->getTransactionReference());
            } else {
                $this->failMail();
                return $this->errorResponse($response->getMessage(), 400);
            }
        } else {
            $this->failMail();
            return $this->errorResponse('Transaction has been declined', 400);
        }
    }

    public function paystackCompletePurchase2(Request $request)
    {
       // pr($request-);
        // Once the transaction has been approved, we need to complete it.
        if($request->has(['reference'])){
            $amount = $this->getDollarCompareAmount($request->amount);
            $transaction = $this->gateway->completePurchase(array(
                'amount'                => $amount,
                'transactionReference'  => $request->reference
            ));
            $response = $transaction->send();
            if ($response->isSuccessful()){
            //    $this->successMail();
                return $this->successResponse($response->getTransactionReference());
            } else {
                $this->failMail();
                return $this->errorResponse($response->getMessage(), 400);
            }
        } else {
            $this->failMail();
            return $this->errorResponse('Transaction has been declined', 400);
        }
    }


    public function paystackCompletePurchaseApp(Request $request)
    {
        // Once the transaction has been approved, we need to complete it.
        if($request->has(['reference'])){
            $amount = $this->getDollarCompareAmount($request->amount);
            $transaction = $this->gateway->completePurchase(array(
                'amount'                => $amount,
                'transactionReference'  => $request->reference
            ));
            $response = $transaction->send();
            if ($response->isSuccessful()){
                $transactionId = $response->getTransactionReference();
                $url = 'payment/gateway/returnResponse?status=200&gateway=paystack&action='.$request->action.'&transaction_id='.$transactionId;
                if($request->action == 'cart'){
                    $order_number = $request->order_number;
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
                            $payment->balance_transaction = $request->amount;
                            $payment->type = 'cart';
                            $payment->save();

                            // Auto accept order
                            $orderController = new OrderController();
                            $orderController->autoAcceptOrderIfOn($order->id);

                            // Remove cart
                            Cart::where('id', $request->cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            CartAddon::where('cart_id', $request->cart_id)->delete();
                            CartCoupon::where('cart_id', $request->cart_id)->delete();
                            CartProduct::where('cart_id', $request->cart_id)->delete();
                            CartProductPrescription::where('cart_id', $request->cart_id)->delete();

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
                        $url = $url.'&order='.$order_number;
                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($request->action == 'wallet'){
                    $request->request->add(['wallet_amount'=>$request->amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $res = $walletController->creditWallet($request);
                }
                elseif($request->action == 'tip'){
                    $request->request->add(['tip_amount' => $request->amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $res = $orderController->tipAfterOrder($request);
                }
                elseif($request->action == 'subscription'){
                    $request->request->add(['payment_option_id' => 5, 'transaction_id' => $transactionId]);
                    $subscriptionController = new UserSubscriptionController();
                    $res = $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
                } elseif($request->action == 'pickup_delivery'){
                    $request->request->add(['payment_option_id' => 5, 'amount' => $amount,'order_number' => $request->order_number, 'transaction_id' => $transactionId]);
                     // pr($request->all());
                    $plaseOrderForPickup = new PickupDeliveryController();
                    $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                 // pr($request->reload_route);
                  
                    // $returnUrl = $request->reload_route;
                    // return Redirect::to(url($returnUrl))->with('success', __('Transaction has been completed successfully'));
                }
                return Redirect::to($url);
            } else {
                // $this->failMail();
                $url = 'payment/paystack/cancelPurchase/app?status=0&gateway=paystack&action='.$request->action;
                return Redirect::to($url);
            }
        } else {
            // $this->failMail();
            return $this->errorResponse('Transaction has been declined', 400);
        }
    }

    public function paystackCancelPurchaseApp(Request $request)
    {
        $url = 'payment/gateway/returnResponse?status=0&gateway=paystack&action='.$request->action;
        // If the transaction has been failed, we need to delete the order.
        if($request->has('status') && ($request->get('status') == '0') ){
            if($request->action == 'cart'){
                $order_number = $request->order_number;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                foreach ($order_products as $order_prod) {
                    $order_prod->delete();
                    // OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
                }
                $user = User::find($order->user_id);
                if($user){
                    if($order->wallet_amount_used > 0){
                        $wallet = $user->wallet;
                        $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for payment failed of order #'. $order->order_number]);
                    }
                }
                OrderProduct::where('order_id', $order->id)->delete();
                OrderProductPrescription::where('order_id', $order->id)->delete();
                VendorOrderStatus::where('order_id', $order->id)->delete();
                OrderVendor::where('order_id', $order->id)->delete();
                OrderTax::where('order_id', $order->id)->delete();
                Order::where('id', $order->id)->delete();
            }
        }
        return Redirect::to($url);
    }
}
