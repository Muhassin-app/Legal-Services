<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\{UserSubscriptionController, OrderController, WalletController, FrontController};
use Auth, Log, Redirect;
use App\Models\{PaymentOption,OrderProductAddon,OrderTax,VendorOrderStatus,OrderVendor,OrderProductPrescription, Cart, SubscriptionPlansUser, Order, Payment, CartAddon, CartCoupon, CartProduct, CartProductPrescription, UserVendor, User,OrderProduct};

class SquareController extends FrontController
{
    use \App\Http\Traits\SquarePaymentManager;
	use \App\Http\Traits\ApiResponser;

	private $application_id;
	private $access_token;
    private $location_id;
	private $square_url;
    private $square_creds;
	private $creds_arr;

	public function __construct()
  	{
		$this->square_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'square')->where('status', 1)->first();
        if(@$this->square_creds && !empty($this->square_creds->credentials))
        {
            $this->creds_arr = json_decode($this->square_creds->credentials);
            $this->application_id = $this->creds_arr->application_id??'';
            $this->access_token = $this->creds_arr->api_access_token??'';
            $this->location_id = $this->creds_arr->location_id??'';
            $this->square_url = $this->square_creds->test_mode ? "https://sandbox.web.squarecdn.com/v1/square.js" : "https://web.squarecdn.com/v1/square.js";
        }
    }
	public function beforePayment(Request $request)
    {
    	$data = $request->all();
    	$location  = $this->getLocation();
    	$data['application_id'] = $this->application_id;
    	$data['location_id'] = $this->location_id;
    	$data['currency'] = $location->getCurrency();
    	$data['country'] = $location->getCountry();
    	$data['square_url'] = $this->square_url;
        $data['come_from'] = 'app';
        if($request->isMethod('post'))
        {
            $data['come_from'] = 'web';
        }
       // Log::info("Square Before Payment");
       // Log::info($data);
    	// dd($data);
    	return view('frontend.payment_gatway.square_view')->with(['data' => $data]);
    }
    public function createPayment(Request $request)
    {
       // Log::info("Square Create Payment");
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
                    CaregoryKycDoc::where('cart_id',$cart->id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                    Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                    CartAddon::where('cart_id', $cart->id)->delete();
                    CartCoupon::where('cart_id', $cart->id)->delete();
                    CartProduct::where('cart_id', $cart->id)->delete();
                    CartProductPrescription::where('cart_id', $cart->id)->delete();

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
                if($request->come_from == 'app')
                {
                    $returnUrl = route('payment.gateway.return.response').'/?gateway=square'.'&status=200&transaction_id='.$transactionId.'&order='.$order_number;
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
                $returnUrl = route('payment.gateway.return.response').'/?gateway=square'.'&status=200&transaction_id='.$transactionId;
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
                $returnUrl = route('payment.gateway.return.response').'/?gateway=square'.'&status=200&transaction_id='.$transactionId;
            }else{
                $returnUrl = route('user.orders');
            }
            return $returnUrl;
        }
        elseif($request->payment_from == 'subscription'){
            $request->request->add(['payment_option_id' => 13, 'transaction_id' => $transactionId]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=square'.'&status=200&transaction_id='.$transactionId;
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
                $returnUrl = route('payment.gateway.return.response').'/?gateway=square&status=0';
            }else{
                $returnUrl = route('showCart');
            }
            return $returnUrl;
        }
        elseif($request->payment_from == 'wallet'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=square&status=0';
            }else{
                $returnUrl = route('user.wallet');
            }
            return $returnUrl;
        }
        elseif($request->payment_from == 'tip'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=square&status=0';
            }else{
                $returnUrl = route('user.orders');
            }
            return $returnUrl;
        }
        elseif($request->payment_from == 'subscription'){
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=square&status=0';
            }else{
                $returnUrl = route('user.subscription.plans');
            }
            return $returnUrl;
        }
        return route('order.return.success');
    }
}
