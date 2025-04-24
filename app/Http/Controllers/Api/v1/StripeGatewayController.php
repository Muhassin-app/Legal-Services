<?php

namespace App\Http\Controllers\Api\v1;

use Auth;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\WalletController;
use App\Http\Controllers\Api\v1\PickupDeliveryController;
use App\Http\Traits\StripeTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, CartDeliveryFee, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser, UserAddress};

class StripeGatewayController extends BaseController
{

    use ApiResponser, StripeTrait;
    public $gateway;
    public $currency;

    public function config()
    {
        $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $testmode = (isset($stripe_creds->test_mode) && ($stripe_creds->test_mode == '1')) ? true : false;
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey($api_key);
        $this->gateway->setTestMode($testmode); //set it to 'false' when go live

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function stripePurchase(request $request)
    {
        $this->config();
        // try {
            $user = Auth::user();
            $address = UserAddress::where('user_id', $user->id);
            $amount = $this->getDollarCompareAmount($request->amount);
            $token = $request->input('stripe_token');

            // $saved_payment_method = $this->getSavedUserPaymentMethod($request);
           
            // if (!$saved_payment_method) {
                $customerResponse = $this->gateway->createCustomer(array(
                    'description' => 'Creating Customer',
                    'name' => $user->name,
                    'email' => $user->email,
                    'source' => $token,
                    'metadata' => [
                        'user_id' => $user->id,
                        'phone_number' => $user->phone_number
                    ]
                ))->send();

                // Find the card ID
                $customer_id = $customerResponse->getCustomerReference();
                if ($customer_id) {
                    $request->request->set('customerReference', $customer_id);
                    $save_payment_method_response = $this->saveUserPaymentMethod($request);
                }
            // }else {
            //     $customer_id = $saved_payment_method->customerReference;
            // }
            $postdata = [
                'currency' => $this->currency,
                // 'token' => $token,
                'amount' => $amount,
                'metadata' => [
                    'user_id' => $user->id,
                    'name'=> $user->name,
                    'email'=> $user->email,
                    'phone_number'=> $user->phone_number
                ],
                'customerReference' => $customer_id
            ];
            if($request->action == 'cart'){
                $address_id = $request->address_id;
                $user_address = UserAddress::where('id', $address_id)->first();
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $order_number = $request->order_number;

                $postdata['description'] = 'Order Checkout';
                $postdata['metadata']['cart_id'] = $cart->id;
                $postdata['metadata']['order_number'] = $order_number;
            }
            elseif($request->action == 'pickup_delivery'){
                $order_number = $request->order_number;
                $postdata['description'] = 'Pickup Delivery ';
                $postdata['metadata']['order_number'] = $order_number;
               
            } 
            // $authorizeResponse = $this->gateway->authorize($postdata)->send();
            // if ($authorizeResponse->isSuccessful()) {
                $response = $this->gateway->purchase($postdata)->send();
                // $response = $this->gateway->purchase([
                //     'currency' => $this->currency,
                //     'token' => $token,
                //     'amount' => $amount,
                //     'metadata' => ['cart_id' => ($request->cart_id) ? $request->cart_id : 0],
                //     'description' => 'This is a test purchase transaction.',
                // //     'name'=>Auth::user()->name,
                // //     'address' => [
                // //        'line1'       => '510 Townsend St',
                // //        'postal_code' => '98140',
                // //        'city'        => 'San Francisco',
                // //        'state'       => 'CA',
                // //        'country'     => 'US',
                // //    ],
                //     // 'name' => Auth::user()->name,
                //     // 'address' => $address->address . ', ' . $address->state . ', ' . $address->country . ', ' . $address->pincode,
                // ])->send();
                if ($response->isSuccessful()) {
                // $this->successMail();
                    $transactionId = $response->getTransactionReference();
                    $request->request->add(['transaction_id' => $transactionId]);
                    if($request->action == 'cart'){
                        // // $orderController = new OrderController();
                        // // $orderResponse = $orderController->postPlaceOrder($request);
                        // return $orderResponse;

                        $order_number = $request->order_number;
                        $cart_id = $cart ? $cart->id : 0 ;
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
        
                                // Remove cart
                                Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                                CartAddon::where('cart_id', $cart_id)->delete();
                                CartCoupon::where('cart_id', $cart_id)->delete();
                                CartProduct::where('cart_id', $cart_id)->delete();
                                CartProductPrescription::where('cart_id', $cart_id)->delete();
                                CartDeliveryFee::where('cart_id', $cart_id)->delete();
        
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

                                $request->request->add(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);
                                //Send Email to customer
                                $orderController->sendSuccessEmail($request, $order);
                                //Send Email to Vendor
                                foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                                    $orderController->sendSuccessEmail($request, $order, $vendor_id);
                                }
                            }
                            // Send Email
                            //   $this->successMail();
                        }
                    }
                    else if($request->action == 'wallet'){
                        $walletController = new WalletController();
                        $walletController->creditMyWallet($request);
                    }
                    else if($request->action == 'pickup_delivery'){
                        $request->request->add(['payment_option_id' => 4, 'amount' => $amount]);
                        $PickupDeliveryController = new PickupDeliveryController();
                        $delivery_response =  $PickupDeliveryController->orderUpdateAfterPaymentPickupDelivery($request);
                        $responseData=$delivery_response;
                    }
                    $responseData['transaction_id']=$response->getTransactionReference();
                    return $this->successResponse($responseData);
                }
                else {
                    // $this->failMail();
                    return $this->errorResponse($response->getMessage(), 400);
                }
            // }else {
            //     return $this->errorResponse($authorizeResponse->getMessage(), 400);
            // }
        // } catch (\Exception $ex) {
        //     // $this->failMail();
        //     return $this->errorResponse($ex->getMessage(), 400);
        // }
    }


    public function createPaymentIntent(Request $request){
        $domain = $request->getHost();
        $response = $this->paymentInit($request, $domain);
    }


    public function subscriptionPaymentViaStripe(request $request)
    {
        try {
            $this->config();
            $user = Auth::user();
            $address = UserAddress::where('user_id', $user->id);
            $token = $request->stripe_token;
            $plan = SubscriptionPlansUser::where('slug', $request->subscription_id)->firstOrFail();
            // $saved_payment_method = $this->getSavedUserPaymentMethod($request);
            // if (!$saved_payment_method) {
                $customerResponse = $this->gateway->createCustomer(array(
                    'description' => 'Creating Customer for subscription',
                    'email' => $request->email,
                    'source' => $token
                ))->send();
                // Find the card ID
                $customer_id = $customerResponse->getCustomerReference();
                if ($customer_id) {
                    $request->request->set('customerReference', $customer_id);
                    $save_payment_method_response = $this->saveUserPaymentMethod($request);
                }
            // } else {
            //     $customer_id = $saved_payment_method->customerReference;
            // }

            // $subscriptionResponse = $this->gateway->createSubscription(array(
            //     "customerReference" => $customer_id,
            //     'plan' => 'Basic Plan',
            // ))->send();

            $amount = $this->getDollarCompareAmount($request->amount);
            $authorizeResponse = $this->gateway->authorize([
                'amount' => $amount,
                'currency' => $this->currency,
                'description' => 'This is a subscription purchase transaction.',
                'customerReference' => $customer_id
            ])->send();
            if ($authorizeResponse->isSuccessful()) {
                $purchaseResponse = $this->gateway->purchase([
                    'currency' => $this->currency,
                    'amount' => $amount,
                    'metadata' => ['user_id' => $user->id, 'plan_id' => $plan->id],
                    'description' => 'This is a subscription purchase transaction.',
                    'customerReference' => $customer_id
                ])->send();
                if ($purchaseResponse->isSuccessful()) {
                  //  $this->successMail();
                    return $this->successResponse($purchaseResponse->getData());
                } else {
                    $this->failMail();
                    return $this->errorResponse($purchaseResponse->getMessage(), 400);
                }
            } else {
                $this->failMail();
                return $this->errorResponse($authorizeResponse->getMessage(), 400);
            }
        } catch (\Exception $ex) {
            $this->failMail();
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }


    ///// Stripe FPX Payment /////

    public function createStripeFPXPaymentIntent(Request $request)
    {
        try{
            ////// Create webhook Endpoint ///////
            $secret_key = stripeFPXPaymentCredentials()->secret_key;
            $stripe = new \Stripe\StripeClient($secret_key);
            
            $webhook_url = 'https://'.$domain.'/payment/webhook/stripe_fpx';
            $webhook_exists = false;

            // $stripe->webhookEndpoints->delete(
            //     'we_1KQXhFA3MquWN79FKLUy0Zzp',
            //     []
            // );
            // $stripe->webhookEndpoints->delete(
            //     'we_1KQXc3A3MquWN79FjGGWHT66',
            //     []
            // );
            // $stripe->webhookEndpoints->delete(
            //     'we_1KQX8gA3MquWN79FmZFGhD9G',
            //     []
            // );
            $endpoints = $stripe->webhookEndpoints->all();

            foreach($endpoints->data as $obj){
                if($obj->url == $webhook_url){
                    $webhook_exists = true;
                    break;
                }
            }
            
            if(!$webhook_exists){
                $res = $stripe->webhookEndpoints->create([
                    'url' => $webhook_url,
                    'enabled_events' => [
                        'payment_intent.succeeded',
                        'payment_intent.payment_failed'
                    ]
                ]);
            }
            // return $webhook_exists;

            $user = Auth::user();

            // $saved_payment_method = $this->getSavedUserPaymentMethod($request);
           
            // if (!$saved_payment_method) {
                $customerResponse = $stripe->customers->create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'description' => 'Creating Customer',
                    'metadata' => [
                        'user_id' => $user->id
                    ]
                ]);

                // Find the card ID
                $customer_id = $customerResponse->id;
                if ($customer_id) {
                    $request->request->set('customerReference', $customer_id);
                    $save_payment_method_response = $this->saveUserPaymentMethod($request);
                }
            // }else {
            //     $customer_id = $saved_payment_method->customerReference;
            // }

            $description = '';
            $payment_form = $request->payment_form;
            $amount = $this->getDollarCompareAmount($request->amount);

            $postdata = [
                'payment_method_types' => ['fpx'],
                'amount' => $amount * 100,
                'currency' => 'myr', //$this->currency
                // 'customer' => '',
                'receipt_email' => $user->email ?? '',
                'metadata' => [
                    'user_id' => $user->id,
                    'payment_form' => $payment_form
                ]
            ];

            if(isset($customer_id) && !empty($customer_id)){
                $postdata['customer'] = $customer_id;
            }

            if($payment_form == 'cart'){
                $address_id = $request->address_id;
                $user_address = UserAddress::where('id', $address_id)->first();
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $order_number = $request->order_number;

                $postdata['description'] = 'Order Checkout';
                $postdata['metadata']['cart_id'] = $cart->id;
                $postdata['metadata']['order_number'] = $order_number;
                $postdata['shipping']['name'] = $user->name;
                $postdata['shipping']['phone'] = $user->dial_code . $user->phone_number;
                $postdata['shipping']['address']['line1'] = $user_address->street;
                $postdata['shipping']['address']['city'] = $user_address->city;
                $postdata['shipping']['address']['state'] = $user_address->state;
                $postdata['shipping']['address']['country'] = $user_address->country;
                $postdata['shipping']['address']['postal_code'] = $user_address->pincode;
            }
            elseif($payment_form == 'wallet'){
                $postdata['description'] = 'Wallet Checkout';
            }
            if($payment_form == 'tip'){
                $postdata['description'] = 'Tip Checkout';
                $order_number = $request->order_number;
                $postdata['metadata']['order_number'] = $order_number;
            }
            elseif($request->payment_form == 'subscription'){
                $postdata['description'] = 'Subscription Checkout';
                $postdata['metadata']['subscription_id'] = $request->subscription_id;
            }
            
            $payment_intent = $stripe = $stripe->paymentIntents->create($postdata);
            
            return $this->successResponse($payment_intent->client_secret);
        }
        catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    public function paymentWebViewStripeFPX(Request $request, $domain='')
    {
        $user = Auth::user();
        $payment_form = $request->action;
        $returnParams = '?amount='. $request->amount .'&auth_token='.$user->auth_token. '&payment_form=' . $payment_form;
        if($payment_form == 'cart'){
            $returnParams .= '&order_number='.$request->order_number;
            if($request->has('address_id')){
                $returnParams .= '&address_id='.$request->address_id;
            }
        }
        elseif($payment_form == 'tip'){
            $returnParams .= '&order_number='.$request->order_number;
        }
        elseif($payment_form == 'subscription'){
            $returnParams .= '&subscription_id='.$request->subscription_id;
        }
        return $this->successResponse(url($request->serverUrl.'payment/webview/stripe_fpx'.$returnParams)); 
    }

    public function paymentWebViewStripeIdeal(Request $request, $domain='')
    {
        $user = Auth::user();
        $payment_form = $request->action;
        $returnParams = '?amount='. $request->amount .'&auth_token='.$user->auth_token. '&payment_form=' . $payment_form;
        if($payment_form == 'cart'){
            $returnParams .= '&order_number='.$request->order_number;
            if($request->has('address_id')){
                $returnParams .= '&address_id='.$request->address_id;
            }
        }
        elseif($payment_form == 'tip'){
            $returnParams .= '&order_number='.$request->order_number;
        }
        elseif($payment_form == 'subscription'){
            $returnParams .= '&subscription_id='.$request->subscription_id;
        }
        return $this->successResponse(url($request->serverUrl.'payment/webview/stripe_ideal'.$returnParams)); 
    }

    public function paymentWebViewStripeOXXO(Request $request, $domain='')
    {
        $user = Auth::user();
        $payment_form = $request->action;
        $returnParams = '?amount='. $request->amount .'&auth_token='.$user->auth_token. '&payment_form=' . $payment_form;
        if($payment_form == 'cart'){
            $returnParams .= '&order_number='.$request->order_number;
            if($request->has('address_id')){
                $returnParams .= '&address_id='.$request->address_id;
            }
        }
        elseif($payment_form == 'tip'){
            $returnParams .= '&order_number='.$request->order_number;
        }
        elseif($payment_form == 'subscription'){
            $returnParams .= '&subscription_id='.$request->subscription_id;
        }
        return $this->successResponse(url($request->serverUrl.'payment/webview/stripe_oxxo'.$returnParams)); 
    }
}
