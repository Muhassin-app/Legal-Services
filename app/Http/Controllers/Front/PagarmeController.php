<?php 

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\{UserSubscriptionController, OrderController, WalletController, FrontController};
use Auth, Log, Redirect, Session;
use App\Models\{PaymentOption, Cart, CaregoryKycDoc,SubscriptionPlansUser, Order, Payment, CartAddon, CartCoupon, CartProduct, CartProductPrescription, UserVendor, User,OrderProductAddon,OrderProduct,OrderProductPrescription,VendorOrderStatus,OrderVendor,OrderTax};

class PagarmeController extends FrontController
{
    use \App\Http\Traits\PagarmePaymentManager;
	use \App\Http\Traits\ApiResponser;

	private $api_key;
	private $secret_key;
	public function __construct()
  	{
		$pagarme_creds = PaymentOption::getCredentials('pagarme');
        if(@$pagarme_creds && !empty($pagarme_creds->credentials))
        {
            $creds_arr = json_decode($pagarme_creds->credentials);
            $this->api_key = $creds_arr->api_key??'';
            $this->secret_key = $creds_arr->secret_key??'';
        }
	}

    public function beforePayment(Request $request) 
    {
        $data = $request->all();
        $request['come_from'] = 'app';
        if($request->isMethod('post'))
        {
            $request['come_from'] = 'web';
        }else{
            $user = User::where('auth_token', $request->auth_token)->first();
            Auth::login($user);
        }
        if(count($data) > 0)
        {
            Session::put('pagarme_data',$request->all());
        }else{
            $data = Session::get('pagarme_data',$request->all());
        }
        return view('frontend.payment_gatway.pgarmne_view')->with(['data' => $request->all()]);
    }
    public function createPaymentCard(Request $request)
    {
        try{
            $request['card_number'] = str_replace(' ', '', $request->number);
            $data = $request->all();
            $card = $this->create_card($data);
            return $this->successResponse($card->id);
        }catch(Exception $ex){
            return $this->errorResponse();
        }
    }
    public function createPayment(Request $request)
    {
        // dd($request->all());
        try{
            if($request->come_from == "app")
            {
                if(!Auth::user())
                {
                    $user = User::where('auth_token', $request->auth_token)->first();
                    Auth::login($user);
                }
            }
            $user = Auth::user();
            $cart = Cart::where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($request->amount);
            $request['card_number'] = str_replace(' ', '', $request->number);
            $request['customer'] = $user;
            if(isset($request->phone_number) && !is_null($request->phone_number))
            {
                $request['phone'] = '+'.$request->dialCode.$request->phone_number;
            }else{
                $request['phone'] = '+'.$user->dial_code.$user->phone_number;
            }
            $request['amount'] = $amount*100;
            $request['items'] = [];
            $data = $request->all();
            if($request->payment_from == 'cart'){
                $item['id'] = "1";
                $item['title'] = "Cart Checkout";
                $item['unit_price'] = $amount*100;
                $item['quantity'] = 1;
                $item['tangible'] = true;
            }
            elseif($request->payment_from == 'wallet'){
                $item['id'] = "1";
                $item['title'] = "Wallet Checkout";
                $item['unit_price'] = $amount*100;
                $item['quantity'] = 1;
                $item['tangible'] = true;
            }
            elseif($request->payment_from == 'tip'){
                $item['id'] = "1";
                $item['title'] = "Tip Checkout";
                $item['unit_price'] = $amount*100;
                $item['quantity'] = 1;
                $item['tangible'] = true;
            }
            elseif($request->payment_from == 'subscription'){
                $item['id'] = "1";
                $item['title'] = "Subscription Checkout";
                $item['unit_price'] = $amount*100;
                $item['quantity'] = 1;
                $item['tangible'] = true;
            }
            array_push($data['items'],$item);
            // dd($data);
            $payment = $this->create_transaction_via_creditCard($data);
            $request['amount'] = $amount;
            if(!is_null($payment) && isset($payment->status) && $payment->status == 'paid')
            {
                $returnUrl = $this->sucessPayment($request,$payment);
                return Redirect::to(url($returnUrl));
            } else{
                return Redirect::to(route('payment.pagarme.beforePayment',$request->all()))->with('error','Invalid Card Detail');
                // return Redirect::back()->with('error','Invalid Card Detail');
            }
        }catch(Exception $ex){
           // Log::info('Controller Error');
           // Log::info($ex);
            return Redirect::to(route('payment.pagarme.beforePayment'))->with('error','Invalid Card Details.');
        }
        
        
    }
    public function sucessPayment($request, $pamyent)
    {
        if($request->come_from == "app")
        {
            $user = User::where('auth_token', $request->auth_token)->first();
            Auth::login($user);
        }
        $user = Auth::user();
    	$transactionId = $pamyent->id;
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
                    $returnUrl = route('payment.gateway.return.response').'/?gateway=pagarme'.'&status=200&transaction_id='.$transactionId.'&order='.$order_number;
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
                $returnUrl = route('payment.gateway.return.response').'/?gateway=pagarme'.'&status=200&transaction_id='.$transactionId;
            }else{
                $returnUrl = route('user.wallet');
            }
            return $returnUrl;
        }
        elseif($request->payment_from == 'tip'){
            $request->request->add(['order_number' => $request->order_number, 'tip_amount' => $request->amount, 'transaction_id' => $transactionId]);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
            $returnUrl = route('user.orders');
            return $returnUrl;
        }
        elseif($request->payment_from == 'subscription'){
            $request->request->add(['payment_option_id' => 12, 'transaction_id' => $transactionId]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=pagarme'.'&status=200&transaction_id='.$transactionId; 
            }else{
                $returnUrl = route('user.subscription.plans');
            }
            return $returnUrl;
        }
        return Redirect::to(route('order.return.success'));
    }
}
