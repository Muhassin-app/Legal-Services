<?php

namespace App\Http\Controllers\Front;


use Log;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
//use Khalti\Api\Api;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\{FrontController, OrderController, PickupDeliveryController};
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, CartDeliveryFee, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax};

class KhaltiGatewayController extends FrontController
{
    use ApiResponser;
    public $API_KEY;
    public $API_SECRET_KEY;
    public $test_mode;
    public $api;

    public function __construct()
    {
        $khalti_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'khalti')->where('status', 1)->first();
        $creds_arr = isset($khalti_creds->credentials) ? json_decode($khalti_creds->credentials) : null;
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $api_secret_key = (isset($creds_arr->api_secret_key)) ? $creds_arr->api_secret_key : '';
        $this->test_mode = (isset($khalti_creds->test_mode) && ($khalti_creds->test_mode == '1')) ? true : false;

        $this->API_KEY = $api_key;
        $this->API_SECRET_KEY = $api_secret_key;
        //$this->api = new Api($api_key, $api_secret_key);
    }

    //Intial Transaction
    public function khaltiVerification(Request $request)
    {
        try {
            $user = Auth::user();
            // $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            // $amount = $this->getDollarCompareAmount($request->amount);
            // $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_INT);

            // $order_number = $request->input('order_id');
            // if (!isset($order_number)) {
            //     $order_number = 0;
            // }
            $data = [
                // 'user_id' 	=> $request->input('user_id'),
                // 'mobile' 	=> $request->input('mobile'),
                'amount' 	=> $request->input('amount')/100,
                'pre_token' => $request->input('token')
                // 'order_id' => $request->input('order_number')
            ];

            $output = $this->verification($data);
            if($output) {

                $data = $request->all();
                $data['order_id'] = $request->input('order_id');
                $data['amount'] = $request->input('amount')/100;
                $data['currency'] = 'NPR';
                $data['payment_from'] = $request->input('payment_form');

                return $this->successResponse($data, 'Payment Verification in Process');

            }
        } catch (\Exception $ex) {
            die($ex->getMessage());
            return $this->errorResponse($ex->getMessage(), 400);
        }

    }

    // Verification after trannsaction
    public function verification($khalti)
    {
        $args = http_build_query(array(
            'token' => $khalti['pre_token'],
            'amount'  => $khalti['amount']*100
        ));
        $url = "https://khalti.com/api/v2/payment/verify/";

        # Make the call using API.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$args);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headers = ['Authorization: Key '.$this->API_SECRET_KEY.' '];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // Response
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $token = json_decode($response, TRUE);
        if (isset($token['idx'])&& $status_code == 200)
        {
            return true;
        }
        return false;
    }

    public function khaltiCompletePurchase(Request $request)
    {
        try {
            $user = Auth::user();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($request['data']['amount']);
            $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_INT);
            $amount = $amount/100;
            $returnUrl = route('order.return.success');
            if ($request['data']['payment_from'] == 'wallet') {
                $returnUrl = route('user.wallet');
            }
            $orderData = [
                'amount'          => $amount/100,
                'currency'        => 'NPR'
            ];
            if ($request['status'] == 'Success') {
                $response = $this->khaltiNotify($request);
                if($response) {
                    return $this->successResponse($response, 'Payment Transaction');
                }
            } else {
                $response = $this->khaltiNotify_fail($request);
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function khaltiNotify($request)
    {
        $response = [];
        $transactionId = $request['data']['payment_id'];
        $amount = $request['data']['amount'];
        if($request['data']['payment_from'] == 'cart')
        {
            $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $request['data']['order_id'])->first();
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

                    // Remove cart
                    $user = Auth::user();
                    $cart = Cart::where('user_id',$user->id)->select('id')->first();
                    Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                    CartAddon::where('cart_id', $cart->id)->delete();
                    CartCoupon::where('cart_id', $cart->id)->delete();
                    CartProduct::where('cart_id', $cart->id)->delete();
                    CartProductPrescription::where('cart_id', $cart->id)->delete();
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
                    //   $this->successMail();
                }
                $returnUrlParams = '?gateway=khalti&order=' . $order->id;
                $returnUrl = route('order.return.success');
                // dd($returnUrl . $returnUrlParams);
                $cartReturnUrl = $returnUrl.$returnUrlParams;
                $response['status'] = 'Success';
                $response['payment_from'] = 'cart';
                $response['route'] = $cartReturnUrl;
            } else {
                $returnUrlParams = '?gateway=khalti&amount=' . $amount . '&checkout=' . $payment['id'];
                $returnUrl = route('user.wallet');
                return $returnUrl.$returnUrlParams;
            }
        }elseif($request['data']['payment_from'] == 'wallet'){
            $request->request->add(['wallet_amount' => $amount, 'transaction_id' => $transactionId]);
            $walletController = new WalletController();
            if($walletController->creditWallet($request)) {
                $returnUrl = route('user.wallet');
                $response['status'] = 'Success';
                $response['payment_from'] = 'wallet';
                $response['route'] = $returnUrl;
            }
        }elseif($request['data']['payment_from'] == 'tip'){
            $request->request->add(['order_number' => $request['data']['order_id'], 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
            $orderController = new OrderController();
            if($orderController->tipAfterOrder($request)) {
                $returnUrl = route('user.orders');
                $response['status'] = 'Success';
                $response['payment_from'] = 'tip';
                $response['route'] = $returnUrl;
            }
        }elseif($request['data']['payment_from'] == 'subscription'){
            $request->request->add(['payment_option_id' => 47, 'transaction_id' => $transactionId, 'amount' => $amount]);
            $subscriptionController = new UserSubscriptionController();
            if($subscriptionController->purchaseSubscriptionPlan($request, '', $request['data']['subscription_id'])) {
                $returnUrl = route('user.subscription.plans');
                $response['status'] = 'Success';
                $response['payment_from'] = 'subscription';
                $response['route'] = $returnUrl;
            }
        }elseif($request['data']['payment_from'] == 'pickup_delivery'){
            $request->request->add(['payment_option_id' => 47, 'amount' => $amount,'order_number' => $request['data']['order_id'], 'transaction_id' => $transactionId]);
            $plaseOrderForPickup = new PickupDeliveryController();
            $pickupDeliveryResponse = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
            $responseData = $this->successResponse($pickupDeliveryResponse, '', 200)->getData();
            //\Log::info(json_encode($responseData));
            $returnUrl = route('front.booking.details', $request['data']['order_id']);
            $response['status'] = 'Success';
            $response['payment_from'] = 'pickup_delivery';
            $response['route'] = $returnUrl;
        }
        return $response;
        //return route('order.return.success');
    }

    public function khaltiNotify_fail($request)
    {
        if($request->payment_from == 'cart'){
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $request->order_number)->first();
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
                $returnUrl = route('viewcart');
                return $returnUrl;
            }
            elseif($request->payment_from == 'wallet'){
                return route('user.wallet');
            }
            elseif($request->payment_from == 'tip'){
                return route('user.orders');
            }
            elseif($request->payment_from == 'subscription'){
                return route('user.subscription.plans');
            }
            return route('order.return.success');
    }

    public function webView(Request $request, $domain='')
    {
        // try{
            $auth_token = $request->auth_token;
            $user = User::where('auth_token', $auth_token)->first();
            Auth::login($user);
            $payment_form = $request->action;
            // $returnParams = 'amount='. $request->amount . '&payment_form=' . $payment_form;
            // if($payment_form == 'cart'){
            //     $returnParams .= '&order='.$request->order;
            // }
            // elseif($payment_form == 'tip'){
            //     $returnParams .= '&order='.$request->order;
            // }
            
            $request->request->add(['payment_form' => $payment_form]);
            $data = $request->all();
            return view('frontend.payment_gatway.khalti_view')->with(['data' => $data]);
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('errors', $ex->getMessage());
        // }
    }

    public function khaltiCompletePurchaseApp(Request $request)
    {
        try {
            $user = Auth::user();
            if ($request['status'] == 'Success') {
                $response = [];
                $transactionId = $request['data']['payment_id'];
                $amount = $request['data']['amount'];
                if($request['data']['payment_from'] == 'cart')
                {
                    $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $request['data']['order_id'])->first();
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

                            // Remove cart
                            $user = Auth::user();
                            $cart = Cart::where('user_id',$user->id)->select('id')->first();
                            Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            CartAddon::where('cart_id', $cart->id)->delete();
                            CartCoupon::where('cart_id', $cart->id)->delete();
                            CartProduct::where('cart_id', $cart->id)->delete();
                            CartProductPrescription::where('cart_id', $cart->id)->delete();
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
                            //   $this->successMail();
                        }
                    }
                }elseif($request['data']['payment_from'] == 'wallet'){
                    $request->request->add(['wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $walletController->creditWallet($request);
                }elseif($request['data']['payment_from'] == 'tip'){
                    $request->request->add(['order_number' => $request['data']['order_id'], 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                }elseif($request['data']['payment_from'] == 'subscription'){
                    $request->request->add(['payment_option_id' => 47, 'transaction_id' => $transactionId, 'amount' => $amount]);
                    $subscriptionController = new UserSubscriptionController();
                    $subscriptionController->purchaseSubscriptionPlan($request, '', $request['data']['subscription_id']);
                }elseif($request['data']['payment_from'] == 'pickup_delivery'){
                    $request->request->add(['payment_option_id' => 47, 'amount' => $amount,'order_number' => $request['data']['order_id'], 'transaction_id' => $transactionId]);
                    $plaseOrderForPickup = new PickupDeliveryController();
                    $pickupDeliveryResponse = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                    $response = $this->successResponse($pickupDeliveryResponse, '', 200)->getData();
                    //\Log::info(json_encode($response));
                }
                return $this->successResponse($transactionId, __('Order placed successfully'));
            } else {
                if($request->payment_from == 'cart'){
                    $order = Order::where('order_number', $request['data']['order_id'])->first();
                    if($order){
                        $wallet_amount_used = $order->wallet_amount_used;
                        if($wallet_amount_used > 0){
                            $transaction = Transaction::where('type', 'deposit')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                            if(!$transaction){
                                $wallet = $user->wallet;
                                $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order <b>'. $order->order_number. '</b>']);
                                $this->sendWalletNotification($user->id, $order->order_number);
                            }
                        }
                    }
                }
                return $this->errorResponse(__('Payment Failed'), 400);
            }
        } catch (\Exception $ex) {
          Log::info($e->getMessage());
            return $this->errorResponse(__('Server Error'), 400);
        }
    }
}
