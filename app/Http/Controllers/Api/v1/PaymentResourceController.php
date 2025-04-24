<?php

namespace App\Http\Controllers\Api\v1;

use Session;
use Exception;
use Stripe\Stripe;
use Omnipay\Omnipay;
use App\Models\Payment;
use Slim\Http\Response;
use App\Models\Transaction;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use App\Models\PaymentOption;
use App\Models\ClientCurrency;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\v1\OrderController;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSavedPaymentMethods;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\WalletController;
use App\Http\Controllers\Front\UserSubscriptionController;
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, CartDeliveryFee, Client, ClientPreference, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SavedCards, SubscriptionPlansUser};

class PaymentResourceController extends BaseController
{
    use ApiResponser;
    public $currency;
    // For Stripe - To get Payment Intent
    public function createPaymentIntent(Request $request)
    {
        $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $testmode = (isset($stripe_creds->test_mode) && ($stripe_creds->test_mode == '1')) ? true : false;

        $primaryCurrency = ClientCurrency::where('is_primary', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';

        $secret_key = stripePaymentCredentials()->secret_key;
        $stripe = new \Stripe\StripeClient($secret_key);
        
        $code = $request->header('code');
        $client = Client::where('code',$code)->first();
        $domain = '';
        if(!empty($client->custom_domain)){
            $domain = $client->custom_domain;
        }else{
            $domain = $client->sub_domain.env('SUBMAINDOMAIN');
        }

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

        $user = Auth::user();
        \Stripe\Stripe::setApiKey($api_key);
        $customer_id = $user->stripe_customer_id;
        $payment_form = $request->action;
        if(empty($customer_id)){ //if customer is not created and no saved card is being used to pay
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
            $user->stripe_customer_id = $customer_id;
            User::find($user->id)->update(['stripe_customer_id' => $customer_id]);
        }
            #  Create the Customer if not exists
            if(isset($request->card_id)){
                $savedPaymentMethod = SavedCards::where('user_id', $user->id)->where('card_id',$request->card_id)->orderBy('id', 'DESC')->first();
            }
            $customer_id = $savedPaymentMethod->customer_id ?? $customer_id;
            $postdata = array(
                'payment_method'       => $savedPaymentMethod->card_id ?? $request->payment_method_id,
                'amount'               => $request->amount * 100,
                'currency'             => $this->currency,
                // 'confirmation_method'  => 'automatic',
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
        // $saved_payment_method = UserSavedPaymentMethods::where('user_id', $user->id)->where('payment_option_id', $request->payment_option_id)->first();
        // if (!$saved_payment_method) {
            // $customerResponse = \Stripe\Customer::create(array(  
            //     'description' => 'Creating Customer',
            //     'name' => $user->name,
            //     'email' => $user->email,
            //     'metadata' => [
            //         'user_id' => $user->id,
            //         'phone_number' => $user->phone_number
            //     ]
            // ));  
        //     $customer_id = $customerResponse['id'];
        //     User::find($user->id)->update(['stripe_customer_id' => $customer_id]);
        //     if ($customer_id) {
        //         $payment_method = new UserSavedPaymentMethods;
        //         $payment_method->user_id = Auth::user()->id;
        //         $payment_method->payment_option_id = $request->payment_option_id;
        //         $payment_method->customerReference = $customer_id;
        //         $payment_method->save();
        //     }
        // // }else {
        // //     $customer_id = $saved_payment_method->customerReference;
        // // }

        // $postdata = array(
        //     'payment_method'       => $request->payment_method_id,
        //     'amount'               => $request->amount * 100,
        //     'currency'             => $this->currency,
        //     'confirmation_method'  => 'automatic',
        //     'confirm'              => true,
        //     'customer'             => $customer_id,
        //     'metadata' => [
        //         'user_id' => $user->id,
        //         'payment_form' => $payment_form
        //     ]
        // );

        if($payment_form == 'cart'){
            $address_id = $request->address_id;
            $user_address = UserAddress::where('id', $address_id)->first();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $order_number = $request->order_number;

            $postdata['description'] = 'Order Checkout';
            $postdata['metadata']['cart_id'] = ($cart) ? $cart->id : 'N/A';
            $postdata['metadata']['order_number'] = $order_number;
            $postdata['shipping']['name'] = ($user->name) ? $user->name : 'N/A';
            $postdata['shipping']['phone'] = $user->dial_code . $user->phone_number;
            $postdata['shipping']['address']['line1'] = ($user_address) ? ($user_address->street != "") ? $user_address->street : 'N/A' : 'N/A';
            $postdata['shipping']['address']['city'] = ($user_address) ? ($user_address->city != "") ? $user_address->city : 'N/A' : 'N/A';
            $postdata['shipping']['address']['state'] = ($user_address) ? ($user_address->state != "") ? $user_address->state : 'N/A' : 'N/A';
            $postdata['shipping']['address']['country'] = ($user_address) ? ($user_address->country != "") ? $user_address->country : 'N/A' : 'N/A';
            $postdata['shipping']['address']['postal_code'] = ($user_address) ? ($user_address->pincode != "") ? $user_address->pincode : 'N/A' : 'N/A';
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
            $postdata['metadata']['subscription_id'] = $request->subscription_slug;
        }

        $intent = \Stripe\PaymentIntent::create($postdata);

        return $intent;
    }

    // Confirm Payment Intent For Stripe
    public function confirmPaymentIntent(Request $request)
    {
        $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
         \Stripe\Stripe::setApiKey($api_key);
         
         $intent = \Stripe\PaymentIntent::retrieve(
           $request->payment_intent_id
        );

        if($intent->status == 'succeeded'){
            // PAyment intent is already confirmed by SDK in FROntEnd
            $amount            = $request->amount;
            $address_id        = ($request->has('address_id')) ? $request->address_id : "";
            $order_number      = ($request->has('order_number')) ? $request->order_number : "";
            $payment_form      = ($request->has('action')) ? $request->action : "";
            $payment_option_id = ($request->has('payment_option_id')) ? $request->payment_option_id : "4";
            $subscription_slug = ($request->has('subscription_slug')) ? $request->subscription_slug : "";
            $tip_amount        = ($request->has('tip_amount')) ? $request->tip_amount : "";
            

            $parameters = [
                'transaction_id'    => $intent->id,
                'total_amount'      => $request->amount,
                'payment_option_id' => $payment_option_id,
                'address_id'        => $address_id,
                'order_number'      => $order_number,
                'payment_form'      => $payment_form,
                'subscription_slug' => $subscription_slug,
                'tip_amount'        => $tip_amount
            ];

            $result = $this->checkStripeReturnDataFrom3DAuth($request, $parameters);
            return $result;

        }else{
            return response()->json('error', __('Sorry, We cannot procees your payment.'));
        }
      
    }

    public function checkStripeReturnDataFrom3DAuth($request, $parameters)
    {
        try {
            $user    = Auth::user();

            $address = UserAddress::where('user_id', $user->id);
            $amount = $parameters['total_amount'];
            $payment_form = $parameters['payment_form'];
            $order_number = $parameters['order_number'];
            $transactionId = $parameters['transaction_id'];
            $subscription_slug = $parameters['subscription_slug'];
            $tip_amount = $parameters['tip_amount'];

            $returnUrl = '';

            if($payment_form == 'cart'){

                // $orderController = new OrderController();
                // $result = $orderController->postPlaceOrder($request);
                // $returnUrl = $result;
                $cart = Cart::select('id','user_id')->where('status', '0')->where('user_id', $user->id)->first();
                $cart_id = $cart ? $cart->id : 0 ;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    if ($user->balanceFloat > 0 && $order->wallet_amount_used > 0) {
                            $user->wallet->withdrawFloat($order->wallet_amount_used, ['Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>']);
                    }
                    $order->payment_status = 1;
                    $order->save();
                    $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                    if (!$payment_exists) {

                        $payment = new Payment();
                        $payment->date = date('Y-m-d');
                        $payment->order_id = $order->id;
                        $payment->user_id = $user->id;
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
                }
                return $this->successResponse($order, __('Order placed successfully.'), 200);
                
            } elseif($payment_form == 'wallet'){
               // $walletController = new WalletController();
                // $result  =  $this->creditMyWallet($parameters);
                // $returnUrl = $result;
                return $this->successResponse('', __('Wallet has been credited successfully'), 200);
            }
            elseif($payment_form == 'tip'){
                // $request->request->add(['order_number' => $order_number, 'tip_amount' => $tip_amount, 'transaction_id' => $transactionId]);
                // $orderController = new OrderController();
                // $result = $orderController->tipAfterOrder($request);
                // return $result;
                return $this->successResponse('', __('Tip has been submitted successfully'), 200);
            }
            elseif($payment_form == 'subscription'){
                // $request->request->add(['payment_option_id' => $parameters['payment_option_id'], 'amount' => $amount, 'transaction_id' => $transactionId]);
                // $subscriptionController = new UserSubscriptionController();
                // $result = $subscriptionController->purchaseSubscriptionPlan($request, '' ,$subscription_slug);
                // return $result;
                return $this->successResponse('', __('Your subscription has been activated successfully.'), 200);
            }
            return $returnUrl;
         
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    // Credit My Wallet
    // public function creditMyWallet($parameters)
    // {   
    //     $transactionId = $parameters['transaction_id'];

    //     $user = Auth::user();
    //     if($user){
    //         $credit_amount = $parameters['total_amount'];
    //         $wallet = $user->wallet;
    //         if ($credit_amount > 0) {
    //             $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> by transaction reference <b>'.$transactionId.'</b>']);

    //             $payment = new Payment();
    //             $payment->date = date('Y-m-d');
    //             $payment->user_id = $user->id;
    //             $payment->transaction_id = $parameters['transaction_id'];
    //             $payment->payment_option_id =  $parameters['payment_option_id'];
    //             $payment->balance_transaction = $credit_amount;
    //             $payment->type = 'wallet_topup';
    //             $payment->save();

    //             $transactions = Transaction::where('payable_id', $user->id)->get();
    //             $response['wallet_balance'] = $wallet->balanceFloat;
    //             $response['transactions'] = $transactions;
    //             $message = 'Wallet has been credited successfully';
    //             return $this->successResponse($response, $message, 201);
    //         }
    //         else{
    //             return $this->errorResponse('Amount is not sufficient', 402);
    //         }
    //     }
    //     else{
    //         return $this->errorResponse('Invalid User', 402);
    //     }
    // }


}
