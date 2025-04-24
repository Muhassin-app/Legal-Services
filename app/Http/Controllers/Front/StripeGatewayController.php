<?php

namespace App\Http\Controllers\Front;

use Log;
use Auth;
use Session;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\WalletController;
use App\Http\Controllers\Front\UserSubscriptionController;
use App\Http\Controllers\Front\giftCard\GiftcardController;
use App\Http\Controllers\Front\PickupDeliveryController;
use App\Models\{User, UserVendor, CaregoryKycDoc,Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, CartDeliveryFee, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser, Transaction, UserAddress, UserSavedPaymentMethods, Webhook};
use Carbon\Carbon;

use function App\Notifications\via;

class StripeGatewayController extends FrontController
{

    use ApiResponser;
    public $gateway;
    public $API_KEY;
    public $currency;
    public $api_key_new;
    public $testmodenew;

    public function config()
    {
        $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $this->api_key_new = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $testmode = (isset($stripe_creds->test_mode) && ($stripe_creds->test_mode == '1')) ? true : false;
        $this->testmodenew = (isset($stripe_creds->test_mode) && ($stripe_creds->test_mode == '1')) ? true : false;
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey($api_key);
        $this->gateway->setTestMode($testmode); //set it to 'false' when go live
        $this->API_KEY = $api_key;
    }

    public function paymentInit(Request $request, $domain='')
    {
        if($request->payment_form == 'subscription'){
            $this->subscriptionPaymentViaStripe( $request, $domain);
            exit();
        }
        $this->config();
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';

        \Stripe\Stripe::setApiKey($this->api_key_new);
        // header('Content-Type: application/json');
        // $json_str = file_get_contents('php://input');
        $json_obj = $request; //json_decode($json_str);

        $intent = null;

        $total_amount = $this->getDollarCompareAmount($json_obj->total_amount);

        $parameters = [
            'total_amount'      => $total_amount,
            'payment_option_id' => $json_obj->payment_option_id,
            'payment_form'      => $json_obj->payment_form
        ];
        try {

            $secret_key = stripePaymentCredentials()->secret_key;
            $stripe = new \Stripe\StripeClient($secret_key);

            $webhook_url = 'https://'.$domain.'/payment/webhook/stripe';
            $webhook_exists = false;
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

            $payment_form = $json_obj->payment_form;
            if($payment_form == 'cart'){
                $parameters['address_id'] = $json_obj->address_id;
                $parameters['order_number'] = $json_obj->order_number;
            }
            elseif($payment_form == 'wallet'){

            }
            if($payment_form == 'tip'){
                $parameters['order_number'] = $json_obj->order_number;
            }
            elseif($payment_form == 'subscription'){
                $parameters['subscription_id'] = $json_obj->subscription_id;
            }
            elseif($payment_form == 'giftCard'){
                $parameters['gift_card_id'] = $json_obj->gift_card_id;
            }elseif($payment_form == 'pending_amount_form'){
                $parameters['order_number'] = $json_obj->order_number;

            }

            if (isset($json_obj->payment_method_id) && !isset($json_obj->payment_intent_id)) {

                #  Create the Customer

                // $saved_payment_method = UserSavedPaymentMethods::where('user_id', Auth::user()->id)->where('payment_option_id', $json_obj->payment_option_id)->first();
                // if (!$saved_payment_method) {
                    $user = Auth::user();
                    $address = UserAddress::where('user_id', $user->id);
                    $customerResponse = \Stripe\Customer::create(array(
                        'description' => 'Creating Customer',
                        'name' => $user->name,
                        'email' => $user->email,
                        'metadata' => [
                            'user_id' => $user->id,
                            'phone_number' => $user->phone_number
                        ]
                    ));
                    $customer_id = $customerResponse['id'];
                    if ($customer_id) {
                        $payment_method = new UserSavedPaymentMethods;
                        $payment_method->user_id = Auth::user()->id;
                        $payment_method->payment_option_id = $json_obj->payment_option_id;
                        $payment_method->customerReference = $customer_id;
                        $payment_method->save();
                    }
                // }else {
                //     $customer_id = $saved_payment_method->customerReference;
                // }

                # Create the PaymentIntent
                // $intent = \Stripe\PaymentIntent::create([
                //     'payment_method'       => $json_obj->payment_method_id,
                //     'amount'               => $total_amount * 100,
                //     'currency'             => $this->currency,
                //     'confirmation_method'  => 'manual',
                //     'confirm'              => true,
                //     'customer'             => $customer_id
                // ]);

                $postdata = array(
                    'payment_method'       => $json_obj->payment_method_id,
                    'amount'               => $total_amount * 100,
                    'currency'             => $this->currency,
                    // 'confirmation_method'  => 'manual',
                    'confirm'              => true,
                    'customer'             => $customer_id,
                    'metadata' => [
                        'user_id' => $user->id,
                        'payment_form' => $payment_form
                    ],
                    'automatic_payment_methods' => array(
                        'enabled'         => true,
                        'allow_redirects' => 'never'
                    )
                );

                $user_address = UserAddress::where('is_primary', 1)->first();

                if($payment_form == 'cart'){
                    $address_id = $json_obj->address_id;
                    $user_address = UserAddress::where('id', $address_id)->first();
                    $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                    $order_number = $json_obj->order_number;

                    $postdata['description'] = 'Order Checkout';
                    $postdata['metadata']['cart_id'] = ($cart) ? $cart->id : 'N/A';
                    $postdata['metadata']['order_number'] = $order_number;
                }
                elseif($payment_form == 'wallet'){
                    $postdata['description'] = 'Wallet Checkout';
                }
                if($payment_form == 'tip'){
                    $postdata['description'] = 'Tip Checkout';
                    $order_number = $json_obj->order_number;
                    $postdata['metadata']['order_number'] = $order_number;
                }
                elseif($payment_form == 'subscription'){
                    $postdata['description'] = 'Subscription Checkout';
                    $postdata['metadata']['subscription_id'] = $json_obj->subscription_id;
                }
                elseif($payment_form == 'giftCard'){
                    $postdata['description'] = 'giftCard Checkout';
                    $parameters['gift_card_id'] = $json_obj->gift_card_id;
                    $postdata['metadata']['gift_card_id'] = $json_obj->gift_card_id;
                    $sendor = [];
                    //pr($json_obj->all());
                    if(!empty($json_obj->send_card_to_name)){
                        $sendor['send_card_to_name'] =  $json_obj->send_card_to_name;
                    }
                    if(!empty($json_obj->send_card_to_mobile)){
                        $sendor['send_card_to_mobile'] = $json_obj->send_card_to_mobile;
                    }
                    if(!empty($json_obj->send_card_to_email)){
                        $sendor['send_card_to_email'] = $json_obj->send_card_to_email;
                    }
                    if(!empty($json_obj->send_card_to_address)){
                        $sendor['send_card_to_address'] = $json_obj->send_card_to_address;
                    }

                    $sendor['send_card_is_delivery'] = $json_obj->send_card_is_delivery ??0;

                    $postdata['metadata']['senderData'] =!empty($sendor) ? json_encode($sendor) : '';
                    $parameters['senderData'] = !empty($sendor) ? json_encode($sendor) : '';
                }elseif($payment_form =="pending_amount_form"){
                    $postdata['description'] = 'Pending amount';
                    $order_number = $json_obj->order_number;
                    $postdata['metadata']['order_number'] = $order_number;
                }
                 $intent = \Stripe\PaymentIntent::create($postdata);
            }


            if (isset($json_obj->payment_intent_id)) {
                $intent = \Stripe\PaymentIntent::retrieve(
                    $json_obj->payment_intent_id
                );
                $intent->confirm();
            }

            $this->generateResponse($intent, $parameters);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            # Display error on client
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        }
    }

    function generateResponse($intent, $parameters)
    {
        $this->config();
        if (($intent->status == 'requires_action') && isset($intent->next_action->type) && ($intent->next_action->type == 'use_stripe_sdk')) {
            # Tell the client to handle the action
            echo json_encode([
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret,
            ]);
        } else if ($intent->status == 'succeeded') {
            # The payment didn’t need any additional actions and completed!
            # Handle post-payment fulfillment
            $result = $this->checkStripeReturnDataFrom3DAuth($intent, $parameters);

            echo json_encode([
                "success" => true,
                'result' => $result
            ]);

        } else {
            # Invalid status
            http_response_code(500);
            echo json_encode(['error' => 'Invalid PaymentIntent status']);
        }
    }

    public function checkStripeSecurity(Request $request)
    {
        $this->config();
        $token        = $request->input('stripe_token');
        $total_amount = $this->getDollarCompareAmount($request->input('total_amount'));
        $address_id   = $request->input('address_id');
        $order_number = $request->input('order_number');

        $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr    = json_decode($stripe_creds->credentials);
        $api_key      = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $testmode     = (isset($stripe_creds->test_mode) && ($stripe_creds->test_mode == '1')) ? true : false;

        \Stripe\Stripe::setApiKey($api_key);
        $source = \Stripe\Source::create([
            'amount' => $total_amount * 100,
            'currency' => $this->currency,
            'type' => 'three_d_secure',
            'three_d_secure' => [
              'card' => $token,
            ],
            'redirect' => [
              'return_url' => route('check_stripe_return_data').'?releezer_token='.$token.'&releezer_amount='.$total_amount.'&releezer_type=three_d_secure&releezer_payment_form=cart&releezer_address_id='.$address_id.'&releezer_order_number='.$order_number.'&releezer_subscription_id=1',
            ],
          ]);

          return response()->json(['url'=> $source->redirect->url]);
    }

    public function checkStripeReturnDataFrom3DAuth($intent, $parameters)
    {
        try {
            $this->config();
            $user = Auth::user();
            $address = UserAddress::where('user_id', $user->id);
            $amount = $parameters['total_amount'];
            $payment_form = $parameters['payment_form'];

            $transactionId = $intent->id;
            $returnUrl = '';
            if($payment_form == 'cart'){
                $message = 'successfully';
                $order_number = $parameters['order_number'];
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    $order->payment_status = 1;
                    $order->save();
                    $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                    if (!$payment_exists) {

                        $payment = new Payment();
                        $payment->date = date('Y-m-d');
                        $payment->user_id = $user->id;
                        $payment->order_id = $order->id;
                        $payment->transaction_id = $transactionId;
                        $payment->balance_transaction = $amount;
                        $payment->payment_option_id = 4;
                        $payment->type = 'cart';
                        $payment->save();


                        // Auto accept order
                        $orderController = new OrderController();
                        $orderplaced = $orderController->autoAcceptOrderIfOn($order->id);

                        // Remove cart
                        $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                        if(!empty($cart))
                        {
                            Cart::where('id', $cart->id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            CartAddon::where('cart_id', $cart->id)->delete();
                            CartCoupon::where('cart_id', $cart->id)->delete();
                            CartProduct::where('cart_id', $cart->id)->delete();
                            CartProductPrescription::where('cart_id', $cart->id)->delete();
                            CartDeliveryFee::where('cart_id', $cart->id)->delete();
                        }

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

                        $request = new Request(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);

                        //Send Email to customer
                        $orderController->sendSuccessEmail($request, $order);
                        //Send Email to Vendor
                        foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                            $orderController->sendSuccessEmail($request, $order, $vendor_id);
                        }
                        // send sms
                        $orderController->sendSuccessSMS($request, $order);
                    }
                }
                $returnUrl = route('order.success', $order->id);

            } elseif($payment_form == 'wallet'){
                // $request = new Request(['wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                // $walletController = new WalletController();
                // $walletController->creditWallet($request);
                $message = 'Wallet has been credited successfully';
                $returnUrl = route('user.wallet');
            }
            elseif($payment_form == 'tip'){
                // $order_number = $parameters['order_number'];
                // $request = new Request(['order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                // $orderController = new OrderController();
                // $orderController->tipAfterOrder($request);
                $message = 'Tip has been submitted successfully';
                $returnUrl = route('user.orders');
            }
            elseif($payment_form == 'subscription'){
                $subscription = $parameters['subscription_id'];
                $request = new Request(['payment_option_id' => 4, 'amount' => $amount, 'transaction_id' => $transactionId]);
                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);
                $message = __('Your subscription has been activated successfully.');
                $returnUrl = route('user.subscription.plans');
            }
            elseif($payment_form == 'giftCard'){
                // $gift_card_id = $parameters['gift_card_id'];
                // $senderData = $parameters['senderData'];

                // $request = new Request(['payment_option_id' => 4, 'user_id' => $user->id,  'amount' => $amount, 'transaction_id' => $transactionId,'senderData'=>$senderData]);

                // $subscriptionController = new GiftcardController();
                /// $subscriptionController->purchaseGiftCard($request, '', $gift_card_id);
                $message = __('Your giftCard has been activated successfully.');
                $returnUrl = route('giftCard.index');
            }elseif($payment_form == 'pending_amount_form'){

                $order_number = $parameters['order_number'];

                $order = Order::select('id')->where('order_number', $order_number)->first();
                Order::where('id', $order->id)->update(['advance_amount' => null]);

                $message = 'Pending has been submitted successfully';
                $returnUrl = route('user.orders');
            }
            Session::put('success', $message);
            // return redirect($returnUrl);
            return $returnUrl;

        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function postPaymentViaStripe(request $request)
    {
        try {
            $this->config();
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';

            $user = Auth::user();
            $address = UserAddress::where('user_id', $user->id);
            $amount = $this->getDollarCompareAmount($request->amount);
            $token = $request->input('stripe_token');

            $payment_form = $request->payment_form;

            // $saved_payment_method = $this->getSavedUserPaymentMethod($request);

            // if (!$saved_payment_method) {
                $customerResponse = $this->gateway->createCustomer(array(
                    'description' => 'Creating Customer',
                    'name' => $user->name,
                    'email' => $user->email,
                    // 'source' => $token,
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
            //     // \Stripe\Stripe::setApiKey($this->API_KEY);
            //     // $retrieve_customer = \Stripe\Customer::retrieve(
            //     //     $customer_id,
            //     //     []
            //     // );
            // }


            $postdata = [
                // 'token' => $token,
                'source'   => $token,
                'currency' => $this->currency,
                'amount'   => $amount,
                'customer' => $customer_id,

                'metadata' => [
                    'user_id'      => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'phone_number' => $user->phone_number
                ],

                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ];

            if($payment_form == 'cart'){
                $address_id = $request->address_id;
                $user_address = UserAddress::where('id', $address_id)->first();
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $order_number = $request->order_number;

                $postdata['description'] = 'Order Checkout';
                $postdata['metadata']['cart_id'] = $cart->id;
                $postdata['metadata']['order_number'] = $order_number;
            }
            elseif($payment_form == 'wallet'){
                $postdata['description'] = 'Wallet Checkout';
            }
            if($payment_form == 'tip'){
                $postdata['description'] = 'Tip Checkout';
                $order_number = $request->order_number;
                $postdata['metadata']['order_number'] = $order_number;
            }
            elseif($payment_form == 'subscription'){
                $postdata['description'] = 'Subscription Checkout';
                $postdata['metadata']['subscription_id'] = $request->subscription_id;
            }
            elseif($payment_form == 'pickup_delivery'){
                $order_number = $request->order_number;
                $postdata['description'] = 'Pickup Delivery ';
                $postdata['metadata']['order_number'] = $order_number;

            }

            // $authorizeResponse = $this->gateway->authorize($postdata)->send();

            // // dd($authorizeResponse->isSuccessful());
            // if ($authorizeResponse->isSuccessful()) {
                $response = $this->gateway->purchase($postdata)->send();

                if ($response->isSuccessful()) {
                // $this->successMail();
                // return $this->successResponse($response->getData());
                    $transactionId = $response->getTransactionReference();

                    $returnUrl = '';

                    if($payment_form == 'cart'){
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
                                CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                                Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                                CartAddon::where('cart_id', $cart_id)->delete();
                                CartCoupon::where('cart_id', $cart_id)->delete();
                                CartProduct::where('cart_id', $cart_id)->delete();
                                CartProductPrescription::where('cart_id', $cart_id)->delete();
                                CartDeliveryFee::where('cart_id', $cart_id)->delete();

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
                            $returnUrl = route('order.success', $order->id); // route('order.return.success');
                            // Send Email
                            //   $this->successMail();
                        }
                    } elseif($payment_form == 'wallet'){
                        $request->request->add(['wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                        $walletController = new WalletController();
                        $walletController->creditWallet($request);
                        $returnUrl = route('user.wallet');
                    }
                    elseif($payment_form == 'tip'){
                        $order_number = $request->order_number;
                        $request->request->add(['order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                        $orderController = new OrderController();
                        $orderController->tipAfterOrder($request);
                        $returnUrl = route('user.orders');
                    }
                    elseif($payment_form == 'subscription'){
                        $subscription = $request->subscription_id;
                        $request->request->add(['payment_option_id' => 4, 'amount' => $amount, 'transaction_id' => $transactionId]);
                        $subscriptionController = new UserSubscriptionController();
                        $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);
                        $returnUrl = route('user.subscription.plans');
                    }
                    elseif($payment_form == 'pickup_delivery'){

                        $request->request->add(['payment_option_id' => 4, 'amount' => $amount, 'transaction_id' => $transactionId]);

                        $plaseOrderForPickup = new PickupDeliveryController();
                        $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);


                        $returnUrl = $request->reload_route;
                    }
                    return $this->successResponse($returnUrl, __('Payment has been completed successfully'), 200);
                }
                else {
                    return $this->errorResponse($response->getMessage(), 400);
                }
            // }else {
            //     return $this->errorResponse($authorizeResponse->getMessage(), 400);
            // }
        } catch (\Exception $ex) {
          Log::error($ex);
            return $this->errorResponse('Server Error', $ex->getCode());
        }
    }

    // public function subscriptionPaymentViaStripe(request $request)
    // {
    //     try {
    //         $this->config();
    //         $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
    //         $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    //         $user = Auth::user();
    //         $address = UserAddress::where('user_id', $user->id);
    //         $token = $request->stripe_token;
    //         $plan = SubscriptionPlansUser::where('slug', $request->subscription_id)->firstOrFail();
    //         // $saved_payment_method = $this->getSavedUserPaymentMethod($request);
    //         // if (!$saved_payment_method) {
    //             $customerResponse = $this->gateway->createCustomer(array(
    //                 'description' => 'Creating Customer for subscription',
    //                 'email' => $request->email,
    //                 'source' => $token
    //             ))->send();
    //             // Find the card ID
    //             $customer_id = $customerResponse->getCustomerReference();
    //             if ($customer_id) {
    //                 $request->request->set('customerReference', $customer_id);
    //                 $save_payment_method_response = $this->saveUserPaymentMethod($request);
    //             }
    //         // } else {
    //         //     $customer_id = $saved_payment_method->customerReference;
    //         // }

    //         // $subscriptionResponse = $this->gateway->createSubscription(array(
    //         //     "customerReference" => $customer_id,
    //         //     'plan' => 'Basic Plan',
    //         // ))->send();

    //         $amount = $this->getDollarCompareAmount($request->amount);
    //         // $authorizeResponse = $this->gateway->authorize([
    //         //     'amount' => $amount,
    //         //     'currency' => $this->currency,
    //         //     'description' => 'This is a subscription purchase transaction.',
    //         //     'customerReference' => $customer_id
    //         // ])->send();
    //         // if ($authorizeResponse->isSuccessful()) {
    //             $purchaseResponse = $this->gateway->purchase([
    //                 'currency' => $this->currency,
    //                 'amount' => $amount,
    //                 'metadata' => ['user_id' => $user->id, 'plan_id' => $plan->id],
    //                 'description' => 'This is a subscription purchase transaction.',
    //                 'customerReference' => $customer_id
    //             ])->send();
    //             if ($purchaseResponse->isSuccessful()) {
    //               //  $this->successMail();
    //                 return $this->successResponse($purchaseResponse->getData());
    //             } else {
    //                 $this->failMail();
    //                 return $this->errorResponse($purchaseResponse->getMessage(), 400);
    //             }
    //         // } else {
    //         //     $this->failMail();
    //         //     return $this->errorResponse($authorizeResponse->getMessage(), 400);
    //         // }
    //     } catch (\Exception $ex) {
    //         $this->failMail();
    //         return $this->errorResponse($ex->getMessage(), 400);
    //     }
    // }

    public function subscriptionPaymentViaStripe(request $request,$domain='')
    {
        try{
            $this->config();
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
            $user = Auth::user();
            $token = $request->stripe_token;
            $plan = SubscriptionPlansUser::where('slug', $request->subscription_id)->firstOrFail();
            $secret_key = stripePaymentCredentials()->secret_key;

            \Stripe\Stripe::setApiKey($this->api_key_new);
            $stripe = new \Stripe\StripeClient($secret_key);

            $webhook_url = 'https://'.$domain.'/payment/webhook/stripe';


            $webhook_exists = false;
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
                        'customer.subscription.created',
                        'customer.subscription.deleted',
                        'customer.subscription.updated',
                        'invoice.payment_failed',
                        'invoice.paid'
                    ]
                ]);
            }


            $stripPlanId =    $plan->strip_plan_id;

            $saved_payment_method = UserSavedPaymentMethods::where('user_id', Auth::user()->id)->where('payment_option_id', $request->payment_option_id)->first();

            if (empty($saved_payment_method)) {
                $user = Auth::user();
                $address = UserAddress::where('user_id', $user->id);
                $user = Auth::user();
                $add = [];
                $address = UserAddress::where('user_id', $user->id)->first();
                if($address) {
                    $add = ["city" => $address->city, "country" => $address->country_code, "line1" => $address->street, "line2" => "", "postal_code" => $address->pincode, "state" => $address->state];
                } else {
                    $add = ["city" => 'New york', "country" => 'US', "line1" => 'sector 28', "line2" => "", "postal_code" => '10001', "state" => 'New york'];

                }
                $customerResponse = \Stripe\Customer::create(array(
                    'description' => 'Creating Customer',
                    'name' => $user->name,
                    'email' => $user->email,
                    'address'=>$add,
                    'metadata' => [
                        'user_id' => $user->id,
                        'phone_number' => $user->phone_number
                    ],
                ));
                $customer_id = $customerResponse['id'];

                if ($customer_id) {

                    $payment_method = new UserSavedPaymentMethods;
                    $payment_method->user_id = Auth::user()->id;
                    $payment_method->payment_option_id = $request->payment_option_id;
                    $payment_method->customerReference = $customer_id;
                    $payment_method->save();
                }
            }else {
                $customer_id = $saved_payment_method->customerReference;
            }
            //payment method will be attached to the customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve( $request->payment_method_id);
            $paymentMethod->attach(['customer' => $customer_id]);
            \Stripe\Customer::update(
                $customer_id,
                [
                    'invoice_settings' => [
                        'default_payment_method' => $request->payment_method_id,
                    ],
                ]
            );


            // Get Customer from Stripe
            $customer = \Stripe\Customer::retrieve($customer_id);
            // Get the default payment method ID
            $defaultPaymentMethodId = $customer->invoice_settings->default_payment_method;
           // Get the default payment method ID
           // $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);

            // Attach the payment method to the customer
            //$paymentMethod->attach(['customer' => $customer_id]);

            //Carbon::now()->setTimezone($client->timezone);
            $current_date_time = strtotime(Carbon::now()->addMinute()->toDateTimeString());

                $amount = $plan->price;
                // $postdata = array(
                //     'payment_method'       => $defaultPaymentMethodId,
                //     'amount'               => $amount * 100,
                //     'currency'             => $this->currency,
                //     'confirmation_method'  => 'manual',
                //     'confirm'              => true,
                //     'description'         =>'Subscription Checkout',
                //     'customer'             => $customer_id,
                //     'metadata' => [
                //         'user_id' => $user->id,
                //         'payment_form' => 'subscription',
                //         'subscription_id' => $request->subscription_id,
                //         'type_id' => $request->type_id
                //     ]
                // );

            $subscribedPlans = \Stripe\Subscription::all([
                'customer' => $customer->id,
                'status' => 'active',
                'limit' => 100,
            ]); // retrieve the customer's active subscriptions

            $existingPlanIds = array(); // initialize an array to store the IDs of the customer's active subscriptions

            foreach ($subscribedPlans->data as $pl) {
                $existingPlanIds[] = $pl->plan->id; // add each subscription ID to the array
            }

            $newPlanId = $stripPlanId; // replace with the ID of the new plan the customer wants to subscribe to

            if (in_array($newPlanId, $existingPlanIds)) { // check if the customer is already subscribed to the new plan
                // handle the scenario where the customer is already subscribed to the new plan
                $message = __('Your are already subscribed.');
                $success = false;
            } else {
                // subscribe the customer to the new plan
                // $intent = \Stripe\PaymentIntent::create($postdata);
                // if (isset($intent->id)) {
                //     $intent = \Stripe\PaymentIntent::retrieve(
                //         $intent->id
                //     );
                //     $intent->confirm();
                // }
                // $price = \Stripe\Price::create([
                //     'unit_amount' => $postdata['amount'],
                //     'currency' => $postdata['currency'],
                //     'recurring' => ['interval' => $plan->frequency],
                //     'product_data' => ['name' => $plan->title],
                // ]);

                $subscriptionData = [
                    'customer' => $customer_id,
                    'items' => [
                        [
                            'plan' => $stripPlanId,
                        ],
                    ],
                    //'default_payment_method' =>  $defaultPaymentMethodId,
                    //'billing_cycle_anchor' =>  $current_date_time,
                    //'proration_behavior' => 'default_incomplete',
                    //'trial_end' =>  'now',
                    'metadata' => [
                        'description' => 'subscription for premium content',
                        'user_id' => $user->id,
                        'subscription_id' => $request->subscription_id,
                        'amount' => $plan->price,
                        'type_id' => $request->type_id

                    ],
                    'expand' => ['latest_invoice.payment_intent'],
                    'collection_method' => 'charge_automatically', // Set to 'charge_automatically' for automatic payments
                    'payment_behavior' => 'allow_incomplete'
                ];

                if($request->type_id == SubscriptionPlansUser::SUBSCRIPTION_MEAL){
                    $subscriptionData['metadata']['mealSubscriptionForm'] = $request->mealSubscriptionForm;
                    $subscriptionData['metadata']['days'] = $request->days;
                }

                $subscription = \Stripe\Subscription::create($subscriptionData);
                $intent = $subscription->latest_invoice->payment_intent;
                if (($intent->status == 'requires_action') && isset($intent->next_action->type) && ($intent->next_action->type == 'use_stripe_sdk')) {
                    # Tell the client to handle the action

                    $returnUrl = route('user.subscription.plans');
                    echo json_encode([
                        'requires_action' => true,
                        'payment_intent_client_secret' => $intent->client_secret,
                        'type' => 'subscription',
                        'result' => $returnUrl
                    ]);
                } else if ($intent->status == 'succeeded') {
                    # The payment didn’t need any additional actions and completed!
                    # Handle post-payment fulfillment
                    $message = __('Your subscription has been activated successfully.');
                    $success = true;
                    $returnUrl = route('user.subscription.plans');
                    \Session::put('success', $message);
                    echo  json_encode([
                        "success" => $success,
                        'result' => $returnUrl,
                        "message"=>$message
                    ]);

                } else {
                    # Invalid status
                    http_response_code(500);
                    echo json_encode(['error' => 'Invalid PaymentIntent status']);
                }
            }
            } catch(\Stripe\Exception\CardException $e) {
                exit("A payment error occurred: {$e->getError()->message}");
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                throw $e;
                exit("Invalid request: {$e->getError()->message}");
            } catch (\Exception $e) {
               throw $e;
            }
    }

    ///// Stripe FPX Payment /////

    public function createStripeFPXPaymentIntent(Request $request, $domain='')
    {
        try{
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
            if($this->currency != 'MYR'){
                return $this->errorResponse($this->currency. ' ' . __('currency not supported'), 400);
            }
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
                    $request->request->add(['customerReference' => $customer_id, 'payment_option_id' => 19]);
                    $save_payment_method_response = $this->saveUserPaymentMethod($request);
                }
            // }else {
            //     $customer_id = $saved_payment_method->customerReference;
            //     // \Stripe\Stripe::setApiKey($this->API_KEY);
            //     // $retrieve_customer = \Stripe\Customer::retrieve(
            //     //     $customer_id,
            //     //     []
            //     // );
            // }

            $description = '';
            $payment_form = $request->payment_form;
            $amount = $this->getDollarCompareAmount($request->amount);

            $postdata = [
                'payment_method_types' => ['fpx'],
                'amount' => $amount * 100,
                'currency' => $this->currency, //'myr'
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
                $user_address = '';
                if($request->has('address_id')){
                    $address_id = $request->address_id;
                    $user_address = UserAddress::where('id', $address_id)->first();
                }
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $order_number = $request->order_number;

                $postdata['description'] = 'Order Checkout';
                $postdata['metadata']['cart_id'] = $cart->id;
                $postdata['metadata']['order_number'] = $order_number;
                $postdata['shipping']['name'] = $user->name;
                $postdata['shipping']['phone'] = $user->dial_code . $user->phone_number;
                if(!empty($user_address)){
                    $postdata['shipping']['address']['line1'] = $user_address->street;
                    $postdata['shipping']['address']['city'] = $user_address->city;
                    $postdata['shipping']['address']['state'] = $user_address->state;
                    $postdata['shipping']['address']['country'] = $user_address->country;
                    $postdata['shipping']['address']['postal_code'] = $user_address->pincode;
                }
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
          Log::info($e->getMessage());
            return $this->errorResponse('Server Error', $ex->getCode());
        }
    }

    public function createStripeOXXOPaymentIntent(Request $request, $domain='')
    {
        try{
            ////// Create webhook Endpoint ///////
            $secret_key = stripeOXXOPaymentCredentials()->secret_key;
            $stripe = new \Stripe\StripeClient($secret_key);
            $webhook_url = 'https://'.$domain.'/payment/webhook/stripe_oxxo';
            $webhook_exists = false;

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

            $user = Auth::user();

                // $customerResponse = $stripe->customers->create([
                //     'name' => $user->name,
                //     'email' => $user->email,
                //     'phone' => $user->phone_number,
                //     'description' => 'Creating Customer',
                //     'metadata' => [
                //         'user_id' => $user->id
                //     ]
                // ]);

                // // Find the card ID
                // $customer_id = $customerResponse->id;
                // if ($customer_id) {
                //     $request->request->add(['customerReference' => $customer_id, 'payment_option_id' => 19]);
                //     $save_payment_method_response = $this->saveUserPaymentMethod($request);
                // }


            $description = '';
            $payment_form = $request->payment_form;
            $amount = $this->getDollarCompareAmount($request->amount);

            $postdata = [
                'payment_method_types' => ['oxxo'],
                'amount' => $amount * 100,
                'currency' => 'mxn', //$this->currency
                'receipt_email' => $user->email ?? '',
                'metadata' => [
                    'user_id' => $user->id,
                    'payment_form' => $payment_form
                ]
            ];

            // if(isset($customer_id) && !empty($customer_id)){
            //     $postdata['customer'] = $customer_id;
            // }

            $user_address = '';
            if($request->has('address_id') && isset($request->address_id)){
                $address_id = $request->address_id;
                $user_address = UserAddress::where('id', $address_id)->first();
            }else{
                $user_address = UserAddress::where(['user_id'=>auth()->id(),'is_primary'=>'1'])->first();
            }
            if($payment_form == 'cart'){

                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $order_number = $request->order_number;

                $postdata['description'] = 'Order Checkout';
                $postdata['metadata']['cart_id'] = $cart->id;
                $postdata['metadata']['order_number'] = $order_number;
                $postdata['shipping']['name'] = $user->name;
                $postdata['shipping']['phone'] = $user->dial_code . $user->phone_number;

            }

            elseif($payment_form == 'wallet'){
                $postdata['description'] = 'Wallet Checkout';
                $postdata['shipping']['name'] = $user->name;
            }
            if($payment_form == 'tip'){
                $postdata['description'] = 'Tip Checkout';
                $order_number = $request->order_number;
                $postdata['metadata']['order_number'] = $order_number;
                $postdata['shipping']['name'] = $user->name;

            }
            elseif($request->payment_form == 'subscription'){
                $postdata['description'] = 'Subscription Checkout';
                $postdata['metadata']['subscription_id'] = $request->subscription_id;
                $postdata['shipping']['name'] = $user->name;
            }

            if(!empty($user_address)){
                $postdata['shipping']['address']['line1'] = $user_address->street;
                $postdata['shipping']['address']['city'] = $user_address->city;
                $postdata['shipping']['address']['state'] = $user_address->state;
                $postdata['shipping']['address']['country'] = $user_address->country;
                $postdata['shipping']['address']['postal_code'] = $user_address->pincode;
            }
            $payment_intent = $stripe = $stripe->paymentIntents->create($postdata);

            if($request->payment_form == 'cart'){
                \Session::flash('success', 'Order updated soon.');
            } elseif($request->payment_form == 'wallet'){
                \Session::flash('success', 'Wallet amount updated soon.');
            } elseif($request->payment_form == 'tip'){
                \Session::flash('success', 'Tip amount updated soon.');
            } elseif($request->payment_form == 'subscription'){
                \Session::flash('success', 'Subscription updated soon.');
            }

            return $this->successResponse($payment_intent);
        }
        catch (\Exception $ex) {
            return $this->errorResponse('Server Error', $ex->getCode());
        }
    }

    public function createStripeIdealPaymentIntent(Request $request, $domain='')
    {
        try{
            ////// Create webhook Endpoint ///////
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
            if($this->currency != 'EUR'){
                return $this->errorResponse($this->currency. ' ' . __('currency not supported'), 400);
            }
            ////// Create webhook Endpoint ///////
            $secret_key = stripeDynamicPaymentCredentials('stripe_ideal')->secret_key;
            $stripe = new \Stripe\StripeClient($secret_key);

            $webhook_url = 'https://'.$domain.'/payment/webhook/stripe_ideal';

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
                    $request->request->add(['customerReference' => $customer_id, 'payment_option_id' => 39]);
                    $save_payment_method_response = $this->saveUserPaymentMethod($request);
                }
            // }else {
            //     $customer_id = $saved_payment_method->customerReference;
            //     // \Stripe\Stripe::setApiKey($this->API_KEY);
            //     // $retrieve_customer = \Stripe\Customer::retrieve(
            //     //     $customer_id,
            //     //     []
            //     // );
            // }

            $description = '';
            $payment_form = $request->payment_form;
            $amount = $this->getDollarCompareAmount($request->amount);

            $postdata = [
                'payment_method_types' => ['ideal'],
                'amount' => $amount * 100,
                'currency' => $this->currency, //'eur'
                // 'customer' => '',
                'receipt_email' => $user->email ?? '',
                'metadata' => [
                    'user_id' => $user->id,
                    'payment_form' => $payment_form
                ]
            ];

            // if(isset($customer_id) && !empty($customer_id)){
            //     $postdata['customer'] = $customer_id;
            // }

            $user_address = '';
            if($request->has('address_id')){
                $address_id = $request->address_id;
                $user_address = UserAddress::where('id', $address_id)->first();
            }else{
                $user_address = UserAddress::where(['user_id'=>auth()->id(),'is_primary'=>'1'])->first();
            }

            $postdata['shipping']['name'] = $user->name;
            if($payment_form == 'cart'){

                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $order_number = $request->order_number;

                $postdata['description'] = 'Order Checkout';
                $postdata['metadata']['cart_id'] = $cart->id;
                $postdata['metadata']['order_number'] = $order_number;
                $postdata['shipping']['phone'] = $user->dial_code . $user->phone_number;
                if(!empty($user_address)){
                    $postdata['shipping']['address']['line1'] = $user_address->street;
                    $postdata['shipping']['address']['city'] = $user_address->city;
                    $postdata['shipping']['address']['state'] = $user_address->state;
                    $postdata['shipping']['address']['country'] = $user_address->country;
                    $postdata['shipping']['address']['postal_code'] = $user_address->pincode;
                }
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

            if(!empty($user_address)){
                $postdata['shipping']['address']['line1'] = $user_address->street;
                $postdata['shipping']['address']['city'] = $user_address->city;
                $postdata['shipping']['address']['state'] = $user_address->state;
                $postdata['shipping']['address']['country'] = $user_address->country;
                $postdata['shipping']['address']['postal_code'] = $user_address->pincode;
            }

            $payment_intent = $stripe = $stripe->paymentIntents->create($postdata);

            return $this->successResponse($payment_intent);
        }
        catch (\Exception $ex) {
            Log::info($ex->getMessage());
            return $this->errorResponse('Server Error', $ex->getCode());
        }
    }

    public function retrieveStripeFPXPaymentIntent(Request $request)
    {
        if($request->has('payment_intent')){
            if($request->has('redirect_status') && ($request->redirect_status == 'succeeded')){

                if($request->payment_form == 'cart'){
                    $order_number = $request->order;
                    $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                    if ($order) {
                        $returnUrlParams = ''; //'?gateway=paylink&order=' . $order->id;
                        $returnUrl = route('order.success', $order->id); // route('order.return.success');
                        return Redirect::to(url($returnUrl . $returnUrlParams));

                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($request->payment_form == 'wallet'){
                    $returnUrl = route('user.wallet');
                    return Redirect::to(url($returnUrl));
                }
                elseif($request->payment_form == 'tip'){
                    $returnUrl = route('user.orders');
                    return Redirect::to(url($returnUrl));
                }
                elseif($request->payment_form == 'subscription'){
                    $returnUrl = route('user.subscription.plans');
                    return Redirect::to(url($returnUrl));
                }
            }
            elseif($request->has('redirect_status') && ($request->redirect_status == 'failed')){
                if($request->payment_form == 'cart'){
                    return Redirect::to(route('showCart'))->with('error', 'Your order has been cancelled');
                } elseif($request->payment_form == 'wallet'){
                    return Redirect::to(route('user.wallet'))->with('error', 'Transaction has been cancelled');
                } elseif($request->payment_form == 'tip'){
                    return Redirect::to(route('user.orders'))->with('error', 'Transaction has been cancelled');
                } elseif($request->payment_form == 'subscription'){
                    return Redirect::to(route('user.subscription.plans'))->with('error', 'Transaction has been cancelled');
                }
            }
        }
    }


    public function retrieveStripeIdealPaymentIntent(Request $request)
    {
        if($request->has('payment_intent')){
            if($request->has('redirect_status') && ($request->redirect_status == 'succeeded')){

                if($request->payment_form == 'cart'){
                    $order_number = $request->order;
                    $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                    if ($order) {
                        $returnUrlParams = ''; //'?gateway=paylink&order=' . $order->id;
                        $returnUrl = route('order.success', $order->id); // route('order.return.success');
                        return Redirect::to(url($returnUrl . $returnUrlParams));

                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($request->payment_form == 'wallet'){
                    $returnUrl = route('user.wallet');
                    return Redirect::to(url($returnUrl));
                }
                elseif($request->payment_form == 'tip'){
                    $returnUrl = route('user.orders');
                    return Redirect::to(url($returnUrl));
                }
                elseif($request->payment_form == 'subscription'){
                    $returnUrl = route('user.subscription.plans');
                    return Redirect::to(url($returnUrl));
                }
            }
            elseif($request->has('redirect_status') && ($request->redirect_status == 'failed')){
                if($request->payment_form == 'cart'){
                    return Redirect::to(route('showCart'))->with('error', 'Your order has been cancelled');
                } elseif($request->payment_form == 'wallet'){
                    return Redirect::to(route('user.wallet'))->with('error', 'Transaction has been cancelled');
                } elseif($request->payment_form == 'tip'){
                    return Redirect::to(route('user.orders'))->with('error', 'Transaction has been cancelled');
                } elseif($request->payment_form == 'subscription'){
                    return Redirect::to(route('user.subscription.plans'))->with('error', 'Transaction has been cancelled');
                }
            }
        }
    }

    public function stripeWebhook(Request $request)
    {
        $secret_key = stripePaymentCredentials()->secret_key;
        \Stripe\Stripe::setApiKey($secret_key);

        $payload = @file_get_contents('php://input');
        $event = null;
        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
                );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }

        Webhook::create(['tracking_order_id'=>'','response'=>$request->getContent() ?? json_encode($payload)]);
        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $transactionId = $user_id = $cart_id = $payment_form = $order_number = '';
                $payment_intent_id = $paymentIntent->id;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                if(!empty($intent->charges) && !empty($intent->charges->data)){
                    $charges = $intent->charges->data[0];
                    $transactionId = @$charges->balance_transaction;
                }else{
                    $charges = $intent;
                    $transactionId = @$charges->id;
                }
                $amount = 0;
                if(@$charges){
                    $payment_form = @$charges->metadata->payment_form;
                    $amount = @$charges->amount / 100;
                    $user_id = @$charges->metadata->user_id;
                }
                if($payment_form == 'cart'){
                    $order_number = @$charges->metadata->order_number;
                    $cart_id = @$charges->metadata->cart_id ?? '';
                    $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                    if ($order) {
                        $order->payment_status = 1;
                        $order->save();
                        $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                        if (!$payment_exists) {
                            $payment = new Payment();
                            $payment->date = date('Y-m-d');
                            $payment->user_id = $user_id;
                            $payment->order_id = $order->id;
                            $payment->transaction_id = $transactionId;
                            $payment->balance_transaction = $amount;
                            $payment->payment_option_id = 4;
                            $payment->type = 'cart';
                            $payment->save();

                            // Deduct wallet amount if payable amount is successfully done on gateway
                            if ( $order->wallet_amount_used > 0 ) {
                                $user = User::find($user_id);
                                $wallet = $user->wallet;
                                $transaction_exists = Transaction::where('type', 'withdraw')->where('meta', 'LIKE', '%order_number%')->where('meta', 'LIKE', '%'.$order->order_number.'%')->first();
                                if(!$transaction_exists){
                                    $wallet->withdrawFloat($order->wallet_amount_used, [
                                        'description' => 'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>',
                                        'order_number' => $order->order_number,
                                        'transaction_id' => $transactionId,
                                        'payment_option' => 'Stripe'
                                    ]);
                                }
                            }

                            // Auto accept order
                            $orderController = new OrderController();
                            $orderController->autoAcceptOrderIfOn($order->id);

                            // Remove cart
                            CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
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

                            $request = new Request(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);

                            //Send Email to customer
                            $orderController->sendSuccessEmail($request, $order);
                            //Send Email to Vendor
                            foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                                $orderController->sendSuccessEmail($request, $order, $vendor_id);
                            }
                            // send sms
                            $this->sendSuccessSMS($request, $order);
                        }

                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($payment_form == 'wallet'){
                    $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $walletController->creditWallet($request);
                }
                elseif($payment_form == 'tip'){
                    $order_number = @$charges->metadata->order_number;
                    $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                }
                elseif($payment_form == 'subscription'){
                    // $subscription = @$charges->metadata->subscription_id;
                    // $request->request->add(['user_id' => $user_id, 'payment_option_id' => 19, 'amount' => $amount, 'transaction_id' => $transactionId]);
                    // $subscriptionController = new UserSubscriptionController();
                    // $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);
                }
                elseif($payment_form == 'giftCard'){
                    $gift_card_id = @$charges->metadata->gift_card_id;
                    $senderData =  @$charges->metadata->senderData;
                    $request->request->add([ 'payment_option_id' => 4, 'user_id' => $user_id,  'amount' => $amount, 'transaction_id' => $transactionId,'senderData'=>$senderData]);
                    $subscriptionController = new GiftcardController();
                    $subscriptionController->purchaseGiftCard($request, '', $gift_card_id);
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;

                $meta = $paymentIntent->metadata;

                $user_id = $payment_form = $order_number = '';
                // $amount = $paymentIntent->amount / 100;
                if($meta){
                    $payment_form = $meta->payment_form;
                    $user_id = $meta->user_id;
                }
                $user = User::find($user_id);

                if($payment_form == 'cart'){
                    $order_number = $meta->order_number;
                    $order = Order::where('order_number', $order_number)->first();
                    if($order){
                        // $wallet_amount_used = $order->wallet_amount_used;
                        // if($wallet_amount_used > 0){
                        //     $wallet = $user->wallet;
                        //     $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
                        // }

                        // $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                        // foreach($order_products as $order_prod){
                        //     OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
                        // }
                        // OrderProduct::where('order_id', $order->id)->delete();
                        // OrderProductPrescription::where('order_id', $order->id)->delete();
                        // VendorOrderStatus::where('order_id', $order->id)->delete();
                        // OrderVendor::where('order_id', $order->id)->delete();
                        // OrderTax::where('order_id', $order->id)->delete();
                        // $order->delete();
                    }
                }
            break;
            case 'customer.subscription.created':
                $paymentIntent = $event->data->object;
                $transactionId = $user_id = $cart_id = $payment_form = $order_number = '';
                $latestInvoice = \Stripe\Invoice::retrieve($paymentIntent->latest_invoice);
                $payment_intent_id = $latestInvoice->payment_intent;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                $meta = $paymentIntent->metadata;
                //$payment_intent_id = $meta->intent_id;

                if(!empty($intent->charges) && !empty($intent->charges->data)){
                    $charges = $intent->charges->data[0];
                    $transactionId = @$charges->balance_transaction;
                }else{
                    $charges = $intent;
                    $transactionId = @$charges->id;
                }
                $amount = 0;
                if(@$charges){
                    $payment_form = @$charges->metadata->payment_form;
                    $amount = @$charges->amount / 100;
                    $user_id = $meta->user_id;
                    $subscription = @$charges->metadata->subscription_id;
                    $type_id = $meta->type_id;
                    $mealSubscription = $meta->mealSubscriptionForm ?? '';
                    $days = $meta->days ?? '';
                }
                $subscriptionController = new UserSubscriptionController();
                $message = __('Your subscription has been activated successfully.');
                $request->merge(['payment_option_id' => 4, 'amount' => $amount, 'transaction_id' => $payment_intent_id,'strip_subscriber_id'=>$paymentIntent->id,'message'=>$message ,'user_id'=>$meta->user_id, 'type_id' => $type_id, 'mealSubscriptionForm' => $mealSubscription,'days' => $days]);
                $subscriptionController->purchaseSubscriptionPlan($request, '',  $meta->subscription_id);
                $returnUrl = route('user.subscription.plans');
            break;
            case 'customer.subscription.updated':
                $paymentIntent = $event->data->object;
                if($paymentIntent->status == 'past_due' || $paymentIntent->status == 'canceled' ) {
                  ////Log::info($event->data->object);

                  $meta = $paymentIntent->metadata;
                  //$meta = $paymentIntent->lines->data[0]->metadata;
                  ////Log::info($paymentIntent->lines->data[0]->metadata);
                  $payment_intent_id = $paymentIntent->payment_intent;

                  if( (isset($meta->user_id)) && (!empty($meta->user_id)) ){
                      $user = User::find($meta->user_id);
                      $subscriptionController = new UserSubscriptionController();
                      $message = __('Your subscription has been expired.');
                      if($paymentIntent->status == 'canceled'){
                        $request->merge(['strip_subscriber_id'=>$paymentIntent->id,'user_id'=>$meta->user_id,'status_id'=>'3' ]);
                      } else {
                        $request->merge(['strip_subscriber_id'=>$paymentIntent->id,'user_id'=>$meta->user_id,'status_id'=>'1' ]);
                      }
                      $subscriptionController->updateSubscriptionPlan($request, '',  $meta->subscription_id);
                  }
                }

            break;
            case 'customer.subscription.deleted':
                $paymentIntent = $event->data->object;
                //$meta = $paymentIntent->metadata;
                $meta = $paymentIntent->metadata;
                ////Log::info($paymentIntent->lines->data[0]->metadata);
                $payment_intent_id = $paymentIntent->payment_intent;
                $subscriptionController = new UserSubscriptionController();
                if( (isset($meta->user_id)) && (!empty($meta->user_id)) ){
                    $user = User::find($meta->user_id);
                    $request->merge(['strip_subscriber_id'=>$paymentIntent->id,'user_id'=>$meta->user_id,'status_id'=>'1' ]);
                    $subscriptionController->deleteSubscriptionPlan($request, '',  $meta->subscription_id);

                }

            break;
            case 'invoice.payment_failed':
                ////Log::info($event->data->object);
                $paymentIntent = $event->data->object;
                //$meta = $paymentIntent->metadata;
                $meta = $paymentIntent->lines->data[0]->metadata;
                ////Log::info($paymentIntent->lines->data[0]->metadata);
                $payment_intent_id = $paymentIntent->payment_intent;
                $subscriptionController = new UserSubscriptionController();
                if( (isset($meta->user_id)) && (!empty($meta->user_id)) ){
                    $user = User::find($meta->user_id);

                    $message = __('Your payment has been failed.');
                    $request->merge(['strip_subscriber_id'=>$paymentIntent->id,'user_id'=>$meta->user_id,'status_id'=>'1' ]);
                    $subscriptionController->updateSubscriptionPlan($request, '',  $meta->subscription_id);
                }

            break;
            case 'invoice.paid':
                $paymentIntent = $event->data->object;
                //$meta = $paymentIntent->metadata;
                $meta = $paymentIntent->lines->data[0]->metadata;
                $stripeSubscriptionId = $paymentIntent->lines->data[0]->subscription;
                $payment_intent_id = $paymentIntent->payment_intent;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                if(!empty($intent->charges) && !empty($intent->charges->data)){
                    $charges = $intent->charges->data[0];
                    $transactionId = @$charges->balance_transaction;
                }else{
                    $charges = $intent;
                    $transactionId = @$charges->id;
                }
                $amount = 0;
                if(@$charges){
                    $payment_form = @$charges->metadata->payment_form;
                    $amount = @$charges->amount / 100;
                    $user_id = @$charges->metadata->user_id;
                }

                if( (isset($meta->user_id)) && (!empty($meta->user_id)) ){
                    $subscriptionController = new UserSubscriptionController();
                    $user = User::find($meta->user_id);
                    $message = __('Your subscription has been activated successfully.');
                    $wallet = $user->wallet;
                    $wallet->depositFloat($amount, [$message]);
                    $request->merge(['strip_subscriber_id'=>$stripeSubscriptionId,'user_id'=>$meta->user_id,'status_id'=>'2' ]);
                    $subscriptionController->updateSubscriptionPlan($request, '',  $meta->subscription_id);
                    \Stripe\Subscription::update($stripeSubscriptionId, [
                        'payment_behavior' => 'default_incomplete',
                    ]);

                }

            break;

                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);
    }

    public function stripeFPXWebhook(Request $request)
    {
        $secret_key = stripeFPXPaymentCredentials()->secret_key;
        \Stripe\Stripe::setApiKey($secret_key);

        $payload = @file_get_contents('php://input');
        $event = null;
        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
                );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }

        Webhook::create(['tracking_order_id'=>'','response'=>$request->getContent() ?? json_encode($payload)]);

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                $transactionId = $user_id = $cart_id = $payment_form = $order_number = '';
                $payment_intent_id = $paymentIntent->id;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                if(!empty($intent->charges) && !empty($intent->charges->data)){
                    $charges = $intent->charges->data[0];
                    $transactionId = @$charges->balance_transaction;
                }else{
                    $charges = $intent;
                    $transactionId = @$charges->id;
                }
                $amount = 0;
                if(@$charges){
                    $payment_form = @$charges->metadata->payment_form;
                    $amount = @$charges->amount / 100;
                    $user_id = @$charges->metadata->user_id;
                }
                if($payment_form == 'cart'){
                    $order_number = @$charges->metadata->order_number;
                    $cart_id = @$charges->metadata->cart_id ?? '';
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
                            CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
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

                            $request = new Request(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);

                            //Send Email to customer
                            $orderController->sendSuccessEmail($request, $order);
                            //Send Email to Vendor
                            foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                                $orderController->sendSuccessEmail($request, $order, $vendor_id);
                            }
                            // send sms
                            $this->sendSuccessSMS($request, $order);
                        }

                        // Send Email
                        //   $this->successMail();
                    }
                } elseif($payment_form == 'wallet'){
                    $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $walletController->creditWallet($request);
                }
                elseif($payment_form == 'tip'){
                    $order_number = @$charges->metadata->order_number;
                    $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                }
                elseif($payment_form == 'subscription'){
                    $subscription = @$charges->metadata->subscription_id;
                    $request->request->add(['user_id' => $user_id, 'payment_option_id' => 19, 'amount' => $amount, 'transaction_id' => $transactionId]);
                    $subscriptionController = new UserSubscriptionController();
                    $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;

                $meta = $paymentIntent->metadata;

                $user_id = $payment_form = $order_number = '';
                // $amount = $paymentIntent->amount / 100;
                if($meta){
                    $payment_form = $meta->payment_form;
                    $user_id = $meta->user_id;
                }
                $user = User::find($user_id);

                if($payment_form == 'cart'){
                    $order_number = $meta->order_number;
                    $order = Order::where('order_number', $order_number)->first();
                    if($order){
                        $wallet_amount_used = $order->wallet_amount_used;
                        if($wallet_amount_used > 0){
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
                            $this->sendWalletNotification($order->user_id, $order->order_number);
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
                        // $order->delete();
                    }
                }
                break;

                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);
    }

    public function cartStripeOXXOClear(Request $request)
    {

            $order = Order::where('order_number',$request->no)->first();
            if(isset($request->payment_from) && $request->payment_from == 'cart')
            {
                $cart = Cart::where('user_id',auth()->id())->select('id')->first();
                $cart_id = $cart->id;
                $order = Order::where('order_number',$request->no)->first();

                // Remove cart
                CaregoryKycDoc::where('cart_id',$cart->id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                CartAddon::where('cart_id', $cart_id)->delete();
                CartCoupon::where('cart_id', $cart_id)->delete();
                CartProduct::where('cart_id', $cart_id)->delete();
                CartProductPrescription::where('cart_id', $cart_id)->delete();

                if(isset($request->come_from) && $request->come_from == 'app')
                {
                    $returnUrl = route('payment.gateway.return.response').'/?gateway=stripe_oxxo'.'&status=200&order='.$request->no;
                    return Redirect::to($returnUrl);
                }

            }else
            {
                if(isset($request->come_from) && $request->come_from == 'app')
                {
                    $returnUrl = route('payment.gateway.return.response').'/?gateway=stripe_oxxo'.'&status=200&transaction_id='.time().'&action='.$request->payment_from;
                    return Redirect::to($returnUrl);
                }
            }

            return Redirect::to(route('order.success',[$order->id]));

    }

    public function stripeOXXOWebhook(Request $request)
    {
        $secret_key = stripeOXXOPaymentCredentials()->secret_key;
        \Stripe\Stripe::setApiKey($secret_key);

        $payload = @file_get_contents('php://input');
        $event = null;
        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
                );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }
        Webhook::create(['tracking_order_id'=>'','response'=>$request->getContent()??json_encode($payload)]);
        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                $transactionId = $user_id = $cart_id = $payment_form = $order_number = '';
                $payment_intent_id = $paymentIntent->id;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                if(!empty($intent->charges) && !empty($intent->charges->data)){
                    $charges = $intent->charges->data[0];
                    $transactionId = @$charges->balance_transaction;
                }else{
                    $charges = $intent;
                    $transactionId = @$charges->id;
                }
                $amount = 0;
                if(@$charges){
                    $payment_form = @$charges->metadata->payment_form;
                    $amount = @$charges->amount / 100;
                    $user_id = @$charges->metadata->user_id;
                }
                if($payment_form == 'cart'){
                    $order_number = @$charges->metadata->order_number;
                    $cart_id = @$charges->metadata->cart_id ?? '';
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
                            // CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                            // Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            // CartAddon::where('cart_id', $cart_id)->delete();
                            // CartCoupon::where('cart_id', $cart_id)->delete();
                            // CartProduct::where('cart_id', $cart_id)->delete();
                            // CartProductPrescription::where('cart_id', $cart_id)->delete();

                            // send sms
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
                    }
                } elseif($payment_form == 'wallet'){
                    $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $walletController->creditWallet($request);
                }
                elseif($payment_form == 'tip'){
                    $order_number = @$charges->metadata->order_number;
                    $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                }
                elseif($payment_form == 'subscription'){
                    $subscription = @$charges->metadata->subscription_id;
                    $request->request->add(['user_id' => $user_id, 'payment_option_id' => 19, 'amount' => $amount, 'transaction_id' => $transactionId]);
                    $subscriptionController = new UserSubscriptionController();
                    $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;

                $meta = $paymentIntent->metadata;

                $user_id = $payment_form = $order_number = '';
                // $amount = $paymentIntent->amount / 100;
                if($meta){
                    $payment_form = $meta->payment_form;
                    $user_id = $meta->user_id;
                }
                $user = User::find($user_id);

                if($payment_form == 'cart'){
                    $order_number = $meta->order_number;
                    $order = Order::where('order_number', $order_number)->first();
                    if($order){
                        $wallet_amount_used = $order->wallet_amount_used;
                        if($wallet_amount_used > 0){
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
                            $this->sendWalletNotification($order->user_id, $order->order_number);
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
                        // $order->delete();
                    }
                }
                break;

                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);
    }


    public function stripeIdealWebhook(Request $request)
    {
        $secret_key = stripeDynamicPaymentCredentials('stripe_ideal')->secret_key;
        \Stripe\Stripe::setApiKey($secret_key);



        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
                );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }
        Webhook::create(['tracking_order_id'=>'','response'=>$request->getContent()??json_encode($payload)]);
        // Handle the event
        switch (@$event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $transactionId = $user_id = $cart_id = $payment_form = $order_number = '';
                $payment_intent_id = $paymentIntent->id;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                if(!empty($intent->charges) && !empty($intent->charges->data)){
                    $charges = $intent->charges->data[0];
                    $transactionId = @$charges->balance_transaction;
                }else{
                    $charges = $intent;
                    $transactionId = @$charges->id;
                }
                $amount = 0;
                if(@$charges){
                    $payment_form = @$charges->metadata->payment_form;
                    $amount = @$charges->amount / 100;
                    $user_id = @$charges->metadata->user_id;
                }
                if($payment_form == 'cart'){
                    $order_number = @$charges->metadata->order_number;
                    $cart_id = @$charges->metadata->cart_id ?? '';
                    $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                    if ($order) {
                        $order->payment_status = 1;
                        $order->save();
                        $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                        $orderController = new OrderController();
                        if (!$payment_exists) {
                            $payment = new Payment();
                            $payment->date = date('Y-m-d');
                            $payment->order_id = $order->id;
                            $payment->transaction_id = $transactionId;
                            $payment->balance_transaction = $amount;
                            $payment->type = 'cart';
                            $payment->save();

                            // Auto accept order

                            $orderController->autoAcceptOrderIfOn($order->id);
                            $orderController->sendSuccessEmail($request, $order);
                            $this->sendSuccessSMS($request, $order);

                            // Remove cart
                            CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                            Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                            CartAddon::where('cart_id', $cart_id)->delete();
                            CartCoupon::where('cart_id', $cart_id)->delete();
                            CartProduct::where('cart_id', $cart_id)->delete();
                            CartProductPrescription::where('cart_id', $cart_id)->delete();
                            CartDeliveryFee::where('cart_id', $cart_id)->delete();

                            // send sms


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
                            $request = new Request(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);
                            $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);

                        }
                        //Send Email to customer

                    }
                } elseif($payment_form == 'wallet'){
                    $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                    $walletController = new WalletController();
                    $walletController->creditWallet($request);
                }
                elseif($payment_form == 'tip'){
                    $order_number = @$charges->metadata->order_number;
                    $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                }
                elseif($payment_form == 'subscription'){
                    $subscription = @$charges->metadata->subscription_id;
                    $request->request->add(['user_id' => $user_id, 'payment_option_id' => 19, 'amount' => $amount, 'transaction_id' => $transactionId]);
                    $subscriptionController = new UserSubscriptionController();
                    $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;

                $meta = $paymentIntent->metadata;

                $user_id = $payment_form = $order_number = '';
                // $amount = $paymentIntent->amount / 100;
                if($meta){
                    $payment_form = $meta->payment_form;
                    $user_id = $meta->user_id;
                }
                $user = User::find($user_id);

                if($payment_form == 'cart'){
                    $order_number = $meta->order_number;
                    $order = Order::where('order_number', $order_number)->first();
                    if($order){
                        $wallet_amount_used = $order->wallet_amount_used;
                        if($wallet_amount_used > 0){
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
                            $this->sendWalletNotification($order->user_id, $order->order_number);
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
                        // $order->delete();
                    }
                }
                break;

                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);
    }
    public function paymentWebViewStripeFPX(Request $request, $domain='')
    {
        // try{
            $auth_token = $request->auth_token;
            $user = User::where('auth_token', $auth_token)->first();
            Auth::login($user);
            $payment_form = $request->payment_form;
            $returnParams = 'amount='. $request->amount . '&payment_form=' . $payment_form;
            if($payment_form == 'cart'){
                $returnParams .= '&order='.$request->order_number;
            }
            elseif($payment_form == 'tip'){
                $returnParams .= '&order='.$request->order_number;
            }
            $payment_retrive_stripe_fpx_url = url('payment/webview/response/stripe_fpx' .'/?'. $returnParams);

            $request->request->add(['come_from' => 'app', 'payment_form' => $payment_form]);
            $data = $request->all();
            return view('frontend.payment_gatway.stripe_fpx_view')->with(['data' => $data, 'payment_retrive_stripe_fpx_url'=>$payment_retrive_stripe_fpx_url]);
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('errors', $ex->getMessage());
        // }
    }

    public function webViewResponseStripeFPX(Request $request)
    {
        if($request->has('payment_intent')){
            $url = 'payment/gateway/returnResponse?status=0&gateway=stripe_fpx&action='.$request->payment_form;
            if($request->has('redirect_status') && ($request->redirect_status == 'succeeded')){
                $url = 'payment/gateway/returnResponse?status=200&gateway=stripe_fpx&action='.$request->payment_form;
                if($request->payment_form == 'cart'){
                    $url = $url.'&order='.$request->order;
                }
            }
            return Redirect::to($url);
        }
    }


    public function paymentWebViewStripeIdeal(Request $request, $domain='')
    {
        try{
            $auth_token = $request->auth_token;
            $user = User::where('auth_token', $auth_token)->first();
            Auth::login($user);
            $payment_form = $request->payment_form;
            $returnParams = 'amount='. $request->amount . '&payment_form=' . $payment_form;
            if($payment_form == 'cart'){
                $returnParams .= '&order='.$request->order_number;
            }
            elseif($payment_form == 'tip'){
                $returnParams .= '&order='.$request->order_number;
            }elseif($payment_form == 'subscription'){
                $returnParams .= '&subscription_id='.$request->subscription_id;
            }
            $payment_retrive_stripe_ideal_url = url('payment/webview/response/stripe_ideal' .'/?'. $returnParams);

            $request->request->add(['come_from' => 'app', 'payment_form' => $payment_form]);
            $data = $request->all();
            return view('frontend.payment_gatway.stripe_ideal_view')->with(['data' => $data, 'payment_retrive_stripe_ideal_url'=>$payment_retrive_stripe_ideal_url]);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('errors', $ex->getMessage());
        }
    }

    public function webViewResponseStripeIdeal(Request $request)
    {
        if($request->has('payment_intent')){
            $url = 'payment/gateway/returnResponse?status=0&gateway=stripe_ideal&action='.$request->payment_form;
            if($request->has('redirect_status') && ($request->redirect_status == 'succeeded')){
                $url = 'payment/gateway/returnResponse?status=200&gateway=stripe_ideal&action='.$request->payment_form;
                if($request->payment_form == 'cart'){
                    $url = $url.'&order='.$request->order;
                }elseif($request->payment_form == 'subscription'){
                    $url = $url.'&transaction_id='.$request->subscription_id;
                }
            }
            return Redirect::to($url);
        }
    }

    public function paymentWebViewStripeOXXO(Request $request, $domain='')
    {
        try{
            $secret_key = stripeOXXOPaymentCredentials()->secret_key;
            $auth_token = $request->auth_token;
            $user = User::where('auth_token', $auth_token)->first();
            Auth::login($user);
            $payment_form = $request->payment_form;
            $returnParams = 'amount='. $request->amount . '&payment_form=' . $payment_form;
            if($payment_form == 'cart'){
                $returnParams .= '&order='.$request->order_number;
            }
            elseif($payment_form == 'tip'){
                $returnParams .= '&order='.$request->order_number;
            }
            $payment_retrive_stripe_oxxo_url = url('payment/webview/response/stripe_oxxo' .'/?'. $returnParams);

            $request->request->add(['come_from' => 'app', 'payment_form' => $payment_form]);
            $data = $request->all();
            return view('frontend.payment_gatway.stripe_oxxo_view')->with(['data' => $data, 'payment_retrive_stripe_oxxo_url'=>$payment_retrive_stripe_oxxo_url]);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('errors', $ex->getMessage());
        }
    }

    public function webViewResponseStripeOXXO(Request $request)
    {
        if($request->has('payment_intent')){
            $url = 'payment/gateway/returnResponse?status=0&gateway=stripe_oxxo&action='.$request->payment_form;
            if($request->has('redirect_status') && ($request->redirect_status == 'succeeded')){
                $url = 'payment/gateway/returnResponse?status=200&gateway=stripe_oxxo&action='.$request->payment_form;
                if($request->payment_form == 'cart'){
                    $url = $url.'&order='.$request->order;
                }
            }
            return Redirect::to($url);
        }
    }
}
