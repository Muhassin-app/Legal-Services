<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\{UserSubscriptionController, OrderController, WalletController, FrontController, PickupDeliveryController
};
use Auth, Log, Redirect;
use App\Models\{PaymentOption, Client, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, CartDeliveryFee, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, UserDevice, UserVendor, Transaction};

class AuthorizeGatewayController extends FrontController 
{
    use \App\Http\Traits\AuthorizePaymentManager;
	use \App\Http\Traits\ApiResponser;

	private $login_id;
	private $client_key;
	private $transaction_key;
	private $test_mode;
	public function __construct()
  	{
		$anet_creds = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'authorize_net')->where('status', 1)->first();
		if(@$anet_creds->status){
    	    $creds_arr = json_decode($anet_creds->credentials);
    	    $this->login_id = $creds_arr->login_id??'';
    	    $this->client_key = $creds_arr->client_key??'';
    	    $this->transaction_key = $creds_arr->transaction_key??'';
    	    $this->test_mode = $anet_creds->test_mode;
		}
	}
	public function beforePayment(Request $request)
    {
    	$data = $request->all();
       // Log::info($data);
        $data['come_from'] = 'app';
        $data['login_id'] = $this->login_id;
        $data['client_key'] = $this->client_key;
        $data['is_test'] = $this->test_mode;
        if($request->isMethod('post'))
        {
            $data['come_from'] = 'web';
        }
       // Log::info("Before Payment");
       // Log::info($data);
    	return view('frontend.payment_gatway.authorize_view')->with(['data' => $data]);
    }
    public function createPayment(Request $request)
    {
       // Log::info("Create Payment");
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
    	$request['amount'] = $amount;
    	$transaction_id = $this->create_payment($request->all()); 
    	if(!is_null($transaction_id))
    	{
            $returnUrl = $this->sucessPayment($request,$transaction_id);
        } else{
            $returnUrl = $this->failedPayment($request,$transaction_id);
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
                    $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();


                    // Remove cart
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
                    $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net'.'&status=200&transaction_id='.$transactionId.'&order='.$order_number;
                }else{
                    $returnUrl = route('order.return.success');
                }
                
                return $returnUrl;
            }
        } elseif($request->payment_from == 'wallet'){
            $request->request->add(['wallet_amount' => $request->amount, 'transaction_id' => $transactionId]);
            $walletController = new WalletController();
            $walletController->creditWallet($request);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net'.'&status=200&transaction_id='.$transactionId;
            }else{
                $returnUrl = route('user.wallet');
            }
            return $returnUrl;
        }
        elseif($request->payment_from == 'tip'){
            $request->request->add(['order_number' => $request->order_number, 'tip_amount' => $request->amount, 'transaction_id' => $transactionId]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net'.'&status=200&transaction_id='.$transactionId;
            }else{
                 $returnUrl = route('user.orders');
            }
            return $returnUrl;
        } 
        elseif($request->payment_from == 'subscription'){
            $request->request->add(['payment_option_id' => 18, 'transaction_id' => $transactionId]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net'.'&status=200&transaction_id='.$transactionId; 
            }else{
                $returnUrl = route('user.subscription.plans');
            }
            return $returnUrl;
        }elseif($request->payment_from == 'pickup_delivery'){
            $request->request->add(['payment_option_id' => 18, 'amount' => $request->amount,'order_number' => $request->order_number, 'transaction_id' => $transactionId]);
            $plaseOrderForPickup = new PickupDeliveryController();
            $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
            $returnUrl = $request->reload_route;
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net'.'&status=200&transaction_id='.$transactionId; 
            }
           // Log::info("Return Url");
           // Log::info($returnUrl);
            return $returnUrl;
        }elseif($request->payment_from == 'pending_amount_form'){
            $order_number = $request->order_number;
            $order = Order::select('id')->where('order_number', $order_number)->first();
            Order::where('id', $order->id)->update(['advance_amount' => null]);
            $message = 'Pending has been submitted successfully';
            $returnUrl = route('user.orders');
            return $returnUrl;

        }
       // Log::info("Ending");
        return Redirect::to(route('order.return.success'));
    }
    public function failedPayment($request, $pamyent)
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
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net&status=0';
            }else{
                $returnUrl = route('showCart');
            }
            return $returnUrl;
        }elseif($request->payment_from == 'wallet'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net&status=0';
            }else{
                $returnUrl = route('user.wallet');
            }
            return $returnUrl;
        }elseif($request->payment_from == 'tip'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net&status=0';
            }else{
                $returnUrl = route('user.orders');
            }
            return $returnUrl;
        }elseif($request->payment_from == 'subscription'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net&status=0';
            }else{
                $returnUrl = route('user.subscription.plans');
            }
            return $returnUrl;
        }elseif($request->payment_from == 'pickup_delivery'){
            $returnUrl = $request->reload_route;
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=authorize_net&status=0';
            }
            return $returnUrl;
        }
        return route('order.return.success');
    }
}
