<?php

namespace App\Http\Controllers\Front;

use Log;
use Auth;
//use WebhookCall;
use Omnipay\Omnipay;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\WalletController;
use App\Http\Controllers\Front\UserSubscriptionController;
use App\Models\{User, UserVendor, Cart, CartAddon,CaregoryKycDoc, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser};
use Illuminate\Support\Facades\Auth as FacadesAuth;

class PaylinkGatewayController extends FrontController
{
    use ApiResponser;
    public $API_KEY;
    public $API_SECRET_KEY;
    public $test_mode;
    public $currency;

    public function __construct()
    {
        $paylink_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'paylink')->where('status', 1)->first();
        $creds_arr = json_decode($paylink_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $api_secret_key = (isset($creds_arr->api_secret_key)) ? $creds_arr->api_secret_key : '';
        $this->test_mode = (isset($paylink_creds->test_mode) && ($paylink_creds->test_mode == '1')) ? true : false;
        $this->API_KEY = $api_key;
        $this->API_SECRET_KEY = $api_secret_key;

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function paylinkPurchase(Request $request)
    {
        try {
            $user = Auth::user();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($request->amount);

            $returnUrl = route('order.return.success');
            if ($request->payment_form == 'wallet') {
                $returnUrl = route('user.wallet');
            }
            $uniqid = uniqid();
            $customer_data = array(
                'firstName' => $user->name,
                'lastName' => '-',
                'email' => $user->email,
                'phone' => $user->phone_number
                // 'identification' => '12123123'
            );
            $reference_number = $description = '';
            $returnUrlParams = '?gateway=paylink&amount=' . $request->amount . '&payment_form=' . $request->payment_form;

            if($request->payment_form == 'cart'){
                $description = 'Order Checkout';
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $request->request->add(['cart_id' => $cart->id]);
                $customer_data['cart_id'] = $cart->id;
                if($request->has('order_number')){
                    $reference_number = $request->order_number;
                }
                $returnUrlParams = $returnUrlParams . '&cart_id=' . $cart->id . '&order=' . $request->order_number;
            }
            elseif($request->payment_form == 'wallet'){
                $description = 'Wallet Checkout';
                $reference_number = $user->id;
            }
            if($request->payment_form == 'tip'){
                $description = 'Tip Checkout';
                $customer_data['order_number'] = $request->order_number;
                if($request->has('order_number')){
                    $reference_number = $request->order_number;
                }
                $returnUrlParams = $returnUrlParams . '&order=' . $request->order_number;
            }
            elseif($request->payment_form == 'subscription'){
                $description = 'Subscription Checkout';
                if($request->has('subscription_id')){
                    $slug = $request->subscription_id;
                    $subscription_plan = SubscriptionPlansUser::with('features.feature')->where('slug', $slug)->where('status', '1')->first();
                    $customer_data['subscription_id'] = $subscription_plan->id;
                    $reference_number = $request->subscription_id;
                    $returnUrlParams = $returnUrlParams . '&subscription=' . $request->subscription_id;
                }
            }

            $data = array(
                'requestId' => 'CHK-' . $uniqid,
                'orderId' => $reference_number,
                'amount' => $amount,
                'currency' => $this->currency, //'AED',
                'description' => $description,
                'reference' => $reference_number,
                'returnUrl' => url('payment/paylink/return' . $returnUrlParams),
                'redirect' => false,
                'test' => $this->test_mode, // True, testing, false, production
                'customer' => $customer_data,
                'billingAddress' => array(
                    'name' => $user->address->first()->address,
                    'address1' => $user->address->first()->address,
                    'address2' => $user->address->first()->address,
                    'street' =>  $user->address->first()->street,
                    'city' => $user->address->first()->city,
                    'state' => $user->address->first()->state,
                    'zip' => $user->address->first()->pincode,
                    'country' => 'AED'
                ),
                'items' => array(
                    'name' => 'Demo item',
                    'sku' => 'sku-demo',
                    'unitprice' => $amount,
                    'quantity' => 1,
                    'linetotal' => 100
                )
            );

            $ch = curl_init($this->getCheckoutUrl() . '/web');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'X-PointCheckout-Api-Key:' . $this->API_KEY,
                    'X-PointCheckout-Api-Secret:' . $this->API_SECRET_KEY
                )
            );

            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            if ($result->success == true) {
                return $this->successResponse($result->result->redirectUrl, ['status' => $result->result->status]);
            } else {
                return $this->errorResponse($result->error, 400);
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function paylinkReturn(Request $request, $domain = '')
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getCheckoutUrl() . '/' . $request->checkout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-PointCheckout-Api-Key:' . $this->API_KEY,
                'X-PointCheckout-Api-Secret:' . $this->API_SECRET_KEY,
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        //  dd($response);
        $transactionId = $request->checkout;

        if ($response->result->status == 'PAID') {
            if($request->payment_form == 'cart'){
                $order_number = $request->order;
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
                        Cart::where('id', $request->cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                        CartAddon::where('cart_id', $request->cart_id)->delete();
                        CartCoupon::where('cart_id', $request->cart_id)->delete();
                        CartProduct::where('cart_id', $request->cart_id)->delete();
                        CartProductPrescription::where('cart_id', $request->cart_id)->delete();

                        // send success smm
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
                    $returnUrlParams = ''; //'?gateway=paylink&order=' . $order->id;
                    $returnUrl = route('order.return.success');
                    return Redirect::to(url($returnUrl . $returnUrlParams));

                    // Send Email
                    //   $this->successMail();
                }
            } elseif($request->payment_form == 'wallet'){
                $request->request->add(['wallet_amount' => $request->amount, 'transaction_id' => $transactionId]);
                $walletController = new WalletController();
                $walletController->creditWallet($request);
                $returnUrl = route('user.wallet');
                return Redirect::to(url($returnUrl));
            }
            elseif($request->payment_form == 'tip'){
                $request->request->add(['order_number' => $request->order, 'tip_amount' => $request->amount, 'transaction_id' => $transactionId]);
                $orderController = new OrderController();
                $orderController->tipAfterOrder($request);
                $returnUrl = route('user.orders');
                return Redirect::to(url($returnUrl));
            }
            elseif($request->payment_form == 'subscription'){
                $request->request->add(['payment_option_id' => 9, 'transaction_id' => $transactionId]);
                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription);
                $returnUrl = route('user.subscription.plans');
                return Redirect::to(url($returnUrl));
            }
            return Redirect::to(route('order.return.success'));
        } 
        else {
            if($request->payment_form == 'cart'){
                $order_number = $request->order;
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
                return Redirect::to(route('showCart'));
            }
            elseif($request->payment_form == 'wallet'){
                return Redirect::to(route('user.wallet'));
            }
            elseif($request->payment_form == 'tip'){
                return Redirect::to(route('user.orders'));
            }
            elseif($request->payment_form == 'subscription'){
                return Redirect::to(route('user.subscription.plans'));
            }
            return Redirect::to(route('order.return.success'));
        }
    }

    public function paylinkReturnApp(Request $request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getCheckoutUrl() . '/' . $request->checkout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-PointCheckout-Api-Key:' . $this->API_KEY,
                'X-PointCheckout-Api-Secret:' . $this->API_SECRET_KEY,
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        $user = User::where('auth_token', $request->auth_token)->first();
        Auth::login($user);
        $transactionId = $request->checkout;
        $returnUrl = url('payment/gateway/returnResponse');

        if ($response->result->status == 'PAID') {
            $returnUrlParams = '?status=200&gateway=paylink&action=' . $request->payment_form . '&transaction_id=' . $transactionId;
            if($request->payment_form == 'cart'){
                $order_number = $request->order;
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
                        CaregoryKycDoc::where('cart_id',$request->cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                        Cart::where('id', $request->cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                        CartAddon::where('cart_id', $request->cart_id)->delete();
                        CartCoupon::where('cart_id', $request->cart_id)->delete();
                        CartProduct::where('cart_id', $request->cart_id)->delete();
                        CartProductPrescription::where('cart_id', $request->cart_id)->delete();
                        
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

                    // Send Email
                    //   $this->successMail();
                    $returnUrlParams = $returnUrlParams . '&order=' . $order_number;
                }
            } elseif($request->payment_form == 'wallet'){
                $request->request->add(['wallet_amount' => $request->amount, 'transaction_id' => $transactionId]);
                $walletController = new WalletController();
                $walletController->creditWallet($request);
            }
            
            return Redirect::to(url($returnUrl . $returnUrlParams));
        } 
        else {
            $returnUrlParams = '?status=0&gateway=paylink&action=' .$request->payment_form;
            if($request->payment_form == 'cart'){
                $order_number = $request->order;
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
                $returnUrlParams = $returnUrlParams . '&order=' . $order_number;
            }
            return Redirect::to(url($returnUrl . $returnUrlParams));
        }
    }

    private function getCheckoutUrl(){
        if ($this->test_mode == true){
            return 'https://api.test.pointcheckout.com/mer/v2.0/checkout';
        }elseif($this->test_mode == false){
            return 'https://api.pointcheckout.com/mer/v2.0/checkout';
        }
        return 'https://api.staging.pointcheckout.com/mer/v2.0/checkout';
    }

    public function paylinkNotify(Request $request)
    {
       // Log::info($request->all());
    }
}
