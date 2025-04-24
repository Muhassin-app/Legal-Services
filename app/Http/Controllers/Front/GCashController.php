<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session, Redirect;

class GCashController extends Controller
{
	use \App\Http\Traits\GCashpaymentManager;
	use \App\Http\Traits\ApiResponser;

    public function beforePayment(Request $request)
    {
    	$response = $this->createPaymentRequest($request->all());
    	return Redirect::to($response->data->checkouturl);
    }
    // public function webView(Request $request)
    // {
    // 	return view('frontend.gcash_view');
    // }
    public function createPayment(Request $request)
    {
       // Log::info("gcash Create Payment");
       // Log::info($request->all());
        if($request->come_from == "app")
        {
            $user = User::where('auth_token', $request->auth_token)->first();
            Auth::login($user);
        }
        $user = Auth::user();
        $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
        $amount = $this->getDollarCompareAmount($request->amount);
        $data = $request->all();
        $request['amount'] = $amount*100;
        
        if($request->payment_from == 'cart'){
            $request['description'] = 'Order Checkout';
            if($request->has('order_number')){
                $request['reference'] = $request->order_number;
            }
        }
        elseif($request->payment_from == 'wallet'){
            $request['description'] = 'Wallet Checkout';
            $request['reference'] = $user->id;
        }
        elseif($request->payment_from == 'tip'){
            $request['description'] = 'Tip Checkout';
            if($request->has('order_number')){
                $request['reference'] = $request->order_number;
            }
        }
        elseif($request->payment_from == 'subscription'){
            $request['description'] = 'Subscription Checkout';
            if($request->has('subscription_id')){
                $request['reference'] = $request->subscription_id;
            }
        }
        $payment_id = $this->createSquarePayment($request->all());
        $request['amount'] = $amount;
        if(isset($payment_id) && !is_null($payment_id))
        {
            $returnUrl = $this->sucessPayment($request,$payment_id);
        }
        else {
            $returnUrl = $this->failedPayment($request);
        }
        
        return Redirect::to(url($returnUrl));
    }
    public function sucessPayment($request, $transactionId)
    {
        if($request->come_from == "app")
        {
            $user = User::where('auth_token', $request->auth_token)->first();
            Auth::login($user);
        }
        $user = Auth::user();
        if($request->payment_from == 'cart'){
            $order_number = $request->order_number;
            $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
            if ($order) {
                $order->payment_status = 1;
                $order->save();
                $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                if (!$payment_exists) {
                    Payment::insert([
                        'date' => date('Y-m-d'),
                        'order_id' => $order->id,
                        'transaction_id' => $transactionId,
                        'balance_transaction' => $request->amount,
                        'type' => 'cart'
                    ]);

                    // Auto accept order
                    $orderController = new OrderController();
                    $orderController->autoAcceptOrderIfOn($order->id);

                    // Remove cart
                    $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                    Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                    CartAddon::where('cart_id', $cart->id)->delete();
                    CartCoupon::where('cart_id', $cart->id)->delete();
                    CartProduct::where('cart_id', $cart->id)->delete();
                    CartProductPrescription::where('cart_id', $cart->id)->delete();

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
                if($request->come_from == 'app')
                {
                    $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash'.'&status=200&transaction_id='.$transactionId.'&order='.$order_number;
                }else{
                    $returnUrl = route('order.return.success');
                }
                return $returnUrl;
            }
        }elseif($request->payment_from == 'wallet'){
            $request->request->add(['wallet_amount' => $request->amount, 'transaction_id' => $transactionId]);
            $walletController = new WalletController();
            $walletController->creditWallet($request);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash'.'&status=200&transaction_id='.$transactionId;
            }else{
                $returnUrl = route('user.wallet');
            }
            return $returnUrl;
        }elseif($request->payment_from == 'tip'){
            $request->request->add(['order_number' => $request->order_number, 'tip_amount' => $request->amount, 'transaction_id' => $transactionId]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash'.'&status=200&transaction_id='.$transactionId;
            }else{
                $returnUrl = route('user.orders');
            }
            return $returnUrl;
        }elseif($request->payment_from == 'subscription'){
            $request->request->add(['payment_option_id' => 13, 'transaction_id' => $transactionId]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash'.'&status=200&transaction_id='.$transactionId;
            }else{
                $returnUrl = route('user.subscription.plans');
            }
            return $returnUrl;
        }
        return Redirect::to(route('order.return.success'));
    }
    public function failedPayment($request)
    {
        if($request->payment_from == 'cart'){
            $order_number = $request->order_number;
            $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
            $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
            foreach ($order_products as $order_prod) {
                OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
            }
            OrderProduct::where('order_id', $order->id)->delete();
            OrderProductPrescription::where('order_id', $order->id)->delete();
            VendorOrderStatus::where('order_id', $order->id)->delete();
            OrderVendor::where('order_id', $order->id)->delete();
            OrderTax::where('order_id', $order->id)->delete();
            Order::where('id', $order->id)->delete();
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash&status=0';
            }else{
                $returnUrl = route('showCart');
            }
            return $returnUrl;
        }
        elseif($request->payment_form == 'wallet'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash&status=0';
            }else{
                $returnUrl = route('user.wallet');
            }
            return $returnUrl;
        }
        elseif($request->payment_form == 'tip'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash&status=0';
            }else{
                $returnUrl = route('user.orders');
            }
            return $returnUrl;
        }
        elseif($request->payment_form == 'subscription'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=gcash&status=0';
            }else{
                $returnUrl = route('user.subscription.plans');
            }
            return $returnUrl;
        }
        return route('order.return.success');
    }
}
