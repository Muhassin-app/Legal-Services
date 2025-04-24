<?php

namespace App\Http\Traits;

use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\UserSubscriptionController;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartDeliveryFee;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\ClientCurrency;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentOption;
use App\Models\SavedCards;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Omnipay\Omnipay;
use Stripe\PaymentMethod;

trait StripeTrait
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

    public function paymentInit(Request $request, $domain = '')
    {
        $user = Auth::user();
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';

        \Stripe\Stripe::setApiKey($this->api_key_new);
        // header('Content-Type: application/json');
        // $json_str = file_get_contents('php://input');
        $json_obj = $request; //json_decode($json_str);
        
        $intent = null;

        $total_amount = $this->getDollarCompareAmount($json_obj->amount);

        $parameters = [
            'total_amount'      => $total_amount,
            'payment_option_id' => $json_obj->payment_option_id,
            'payment_form'      => $json_obj->action
        ];
        try {

            $secret_key = stripePaymentCredentials()->secret_key;
            $stripe = new \Stripe\StripeClient($secret_key);

            $webhook_url = 'https://'.$domain.'/payment/webhook/stripe';

            $webhook_exists = false;
            $endpoints = $stripe->webhookEndpoints->all();
            foreach ($endpoints->data as $obj) {
                if ($obj->url == $webhook_url) {
                    $webhook_exists = true;
                    break;
                }
            }
            if (!$webhook_exists) {
                $res = $stripe->webhookEndpoints->create([
                    'url' => $webhook_url,
                    'enabled_events' => [
                        'payment_intent.succeeded',
                        'payment_intent.payment_failed',
                        'payment_intent.amount_capturable_updated'
                    ]
                ]);
            }
            
            if (!isset($json_obj->payment_intent_id)) {
                #  Create the Customer if not exists

                $savedPaymentMethod = SavedCards::where('user_id', $user->id)->where('card_id', $request->payment_method_id)->orderBy('id', 'DESC')->first();
                if (!$savedPaymentMethod) { //not exists
                    $user = Auth::user();
                    $address = UserAddress::where('user_id', $user->id);
                    $customerResponse = $stripe->customers->create(array(
                        'description' => 'Creating Customer',
                        'name' => $user->name,
                        'email' => $user->email,
                        'metadata' => [
                            'user_id' => $user->id,
                            'phone_number' => $user->phone_number
                        ]
                    ));
                    $customer_id = $customerResponse['id'];
                    $card = $stripe->customers->createSource(
                        $customer_id,
                        ['source' => $request->token]
                    );
                    if ($customer_id) {
                        $saved_card = new SavedCards();
                        $saved_card->user_id = $user->id;
                        $saved_card->token = $request->token;
                        $saved_card->card_holder_name = $request->card_holder_name ?? null;
                        $saved_card->bank_name = $request->bank_name ?? null;
                        $saved_card->customer_id = $customer_id;
                        $saved_card->card_id = $card->id;
                        $saved_card->save();
                    }
                } else { //exsits
                    $customer_id = $savedPaymentMethod->customer_id;
                }

                $postdata = [
                    'payment_method'       => $savedPaymentMethod->card_id,
                    'amount'               => $total_amount * 100,
                    'currency'             => $this->currency,
                    'confirm'              => true,
                    'customer'             => $customer_id,
                    'metadata' => [
                        'user_id' => $user->id,
                        'payment_form' => $json_obj->action
                    ],
                    'capture_method' => 'manual',
                    'payment_method_types' => ['card'],
                    'payment_method_options' => [
                        'card_present' => ['request_extended_authorization' => true],
                    ]
                ];


                switch ($json_obj->action) {
                    case 'cart':
                        $parameters['order_number'] = $json_obj->order_number;
                        $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                        $order_number = $json_obj->order_number;
                        $postdata['description'] = 'Order Checkout';
                        $postdata['metadata']['cart_id'] = ($cart) ? $cart->id : 'N/A';
                        $postdata['metadata']['order_number'] = $order_number;
                        break;
                    case 'wallet':
                        $postdata['description'] = 'Wallet Checkout';
                        break;
                    case 'tip':
                        $parameters['order_number'] = $json_obj->order_number;
                        $postdata['description'] = 'Tip Checkout';
                        $order_number = $json_obj->order_number;
                        $postdata['metadata']['order_number'] = $order_number;
                        break;
                    case 'subscription':
                        $parameters['subscription_id'] = $json_obj->subscription_id;
                        $postdata['description'] = 'Subscription Checkout';
                        $postdata['metadata']['subscription_id'] = $json_obj->subscription_id;
                        break;
                    case 'giftCard':
                        $parameters['gift_card_id'] = $json_obj->gift_card_id;
                        $postdata['description'] = 'giftCard Checkout';
                        $parameters['gift_card_id'] = $json_obj->gift_card_id;
                        $postdata['metadata']['gift_card_id'] = $json_obj->gift_card_id;
                        $sendor = [];
                        if (!empty($json_obj->send_card_to_name)) {
                            $sendor['send_card_to_name'] =  $json_obj->send_card_to_name;
                        }
                        if (!empty($json_obj->send_card_to_mobile)) {
                            $sendor['send_card_to_mobile'] = $json_obj->send_card_to_mobile;
                        }
                        if (!empty($json_obj->send_card_to_email)) {
                            $sendor['send_card_to_email'] = $json_obj->send_card_to_email;
                        }
                        if (!empty($json_obj->send_card_to_address)) {
                            $sendor['send_card_to_address'] = $json_obj->send_card_to_address;
                        }

                        $sendor['send_card_is_delivery'] = $json_obj->send_card_is_delivery ?? 0;

                        $postdata['metadata']['senderData'] = !empty($sendor) ? json_encode($sendor) : '';
                        $parameters['senderData'] = !empty($sendor) ? json_encode($sendor) : '';
                        break;
                    case 'pending_amount_form':
                        $parameters['order_number'] = $json_obj->order_number;
                        $postdata['description'] = 'Pending amount';
                        $order_number = $json_obj->order_number;
                        $postdata['metadata']['order_number'] = $order_number;
                        break;
                }

                $intent = $stripe->paymentIntents->create($postdata);
                $order = Order::where('order_number', $json_obj->order_number)->first();
                $order->payment_intent_id = $intent->id;
                $order->save();
            }
            if (isset($json_obj->payment_intent_id)) {
                $intent = $stripe->paymentIntents->retrieve(
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
        // $this->config();
        if (($intent->status == 'requires_action') && isset($intent->next_action->type) && ($intent->next_action->type == 'use_stripe_sdk')) {
            # Tell the client to handle the action
            echo json_encode([
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret
            ]);
        } else if ($intent->status == 'succeeded' || $intent->status == 'requires_capture') {
            # The payment didn’t need any additional actions and completed!
            # Handle post-payment fulfillment
            // $result = $this->checkStripeReturnDataFrom3DAuth($intent, $parameters);

            echo json_encode([
                "success" => true,
                'payment_intent_client_secret' => $intent->client_secret
                // 'result' => $result
            ]);
        } else {
            # Invalid status
            http_response_code(500);
            echo json_encode(['error' => 'Invalid PaymentIntent status']);
        }
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
            if ($payment_form == 'cart') {
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
                        if (!empty($cart)) {
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

                        $request = new Request(['user_id' => $order->user_id, 'address_id' => $order->address_id]);

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
            } elseif ($payment_form == 'wallet') {
                // $request = new Request(['wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                // $walletController = new WalletController();
                // $walletController->creditWallet($request);
                $message = 'Wallet has been credited successfully';
                $returnUrl = route('user.wallet');
            } elseif ($payment_form == 'tip') {
                // $order_number = $parameters['order_number'];
                // $request = new Request(['order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                // $orderController = new OrderController();
                // $orderController->tipAfterOrder($request);
                $message = 'Tip has been submitted successfully';
                $returnUrl = route('user.orders');
            } elseif ($payment_form == 'subscription') {
                $subscription = $parameters['subscription_id'];
                $request = new Request(['payment_option_id' => 4, 'amount' => $amount, 'transaction_id' => $transactionId]);
                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription);
                $message = __('Your subscription has been activated successfully.');
                $returnUrl = route('user.subscription.plans');
            } elseif ($payment_form == 'giftCard') {
                // $gift_card_id = $parameters['gift_card_id'];
                // $senderData = $parameters['senderData'];

                // $request = new Request(['payment_option_id' => 4, 'user_id' => $user->id,  'amount' => $amount, 'transaction_id' => $transactionId,'senderData'=>$senderData]);

                // $subscriptionController = new GiftcardController();
                /// $subscriptionController->purchaseGiftCard($request, '', $gift_card_id);
                $message = __('Your giftCard has been activated successfully.');
                $returnUrl = route('giftCard.index');
            } elseif ($payment_form == 'pending_amount_form') {

                $order_number = $parameters['order_number'];

                $order = Order::select('id')->where('order_number', $order_number)->first();
                Order::where('id', $order->id)->update(['advance_amount' => null]);

                $message = 'Pending has been submitted successfully';
                $returnUrl = route('user.orders');
            }
            \Session::put('success', $message);
            // return redirect($returnUrl);
            return $returnUrl;
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function capture($payment_intent_id){
        $stripe = $this->getStripeClient();
        $intent = $stripe->paymentIntents->retrieve(
            $payment_intent_id
        );
        $intent->capture();
    }

    public function cancel($payment_intent_id){
        $stripe = $this->getStripeClient();
        $intent = $stripe->paymentIntents->retrieve(
            $payment_intent_id
        );
        $intent->cancel();
    }

    public function getStripeClient(){
        $secret_key = stripePaymentCredentials()->secret_key;
        return new \Stripe\StripeClient($secret_key);
    }
}
