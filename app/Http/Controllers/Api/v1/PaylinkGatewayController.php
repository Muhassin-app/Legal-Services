<?php

namespace App\Http\Controllers\Api\v1;

use Log;

//use WebhookCall;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Models\{CaregoryKycDoc, User, UserVendor, Cart, CartAddon, CartBookingOption, CartCoupon, CartDeliveryFee, CartProduct, CartProductPrescription, CartRentalProtection, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, ClientDocument, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaylinkGatewayController extends BaseController
{
    use ApiResponser;
    public $API_KEY;
    public $API_SECRET_KEY;
    public $test_mode;
    public $currency;

    public function __construct()
    {
        // $paylink_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'paylink')->where('status', 1)->first();
        // $creds_arr = json_decode($paylink_creds->credentials);
        $api_key ='APP_ID_1123453311';
        $api_secret_key = '0662abb5-13c7-38ab-cd12-236e58f43766';
        $this->test_mode = true;
        $this->API_KEY = $api_key;
        $this->API_SECRET_KEY = $api_secret_key;

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }
    public function updatePaymentStatus(Request $request){
        try {
            DB::beginTransaction();
            $payment= Payment::where('transaction_id', trim($request->transactionNumber))->first();
            $order= Order::where('order_number',$request->ordernumber)->first();
            $payment->update([
                'payment_detail' => $request->orderStatus,
                'order_id' => $order->id
            ]);
            if($request->orderStatus == 'Paid'){
                //delete cart and releted data
                $cart = Cart::where('user_id',$order->user_id)->first();
                Cart::where('id', $cart->id)->update([
                'schedule_type' => null,
                'scheduled_date_time' => null,
                'comment_for_pickup_driver' => null,
                'comment_for_dropoff_driver' => null,
                'comment_for_vendor' => null,
                'schedule_pickup' => null,
                'schedule_dropoff' => null,
                'specific_instructions' => null,
                'order_id' => NULL
                ]);
            
                CaregoryKycDoc::where('cart_id', $cart->id)->update([
                    'ordre_id' => $order->id,
                    'cart_id' => ''
                ]);
            
                CartAddon::where('cart_id', $cart->id)->delete();
                CartCoupon::where('cart_id', $cart->id)->delete();
                CartProduct::where('cart_id', $cart->id)->delete();
                CartProductPrescription::where('cart_id', $cart->id)->delete();
                CartDeliveryFee::where('cart_id', $cart->id)->delete();
                CartRentalProtection::where('cart_id', $cart->id)->delete();
                CartBookingOption::where('cart_id', $cart->id)->delete();
                Cart::where('id', $cart->id)->delete();
                $order->update(['payment_status' => 1,'payment_method' => 3]);
                // //for notification 
                // $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$order->lawyer_id])->where('is_vendor_app', 1)->pluck('device_token')->toArray();
                // Log::info(['devices11' => $devices]);
                // // push notification
                // $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from')->first();
                // if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                //     $notification_content = NotificationTemplate::where('id', 25)->first();
                //     if ($notification_content) {
                //         $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                //         $redirect_URL['type'] = 4;
                //         $data = [
                //             "registration_ids" => $devices,
                //             "notification" => [
                //                 'title' => $notification_content->subject,
                //                 'body'  => $body_content,
                //                 'sound' => "default",
                //                 "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                //                 'click_action' => '',
                //                 "android_channel_id" => "default-channel-id",
                //                 "redirect_type" => $redirect_URL['type']
                //             ],
                //             "data" => [
                //                 'title' => $notification_content->subject,
                //                 'body'  => $body_content,
                //                 "type" => "order_status_change",
                //                 "order_id" => $order->order_number,
                //                 "vendor_id" => $vendorId ?? '',
                //                 "order_status" => 3,
                //                 "redirect_type" => $redirect_URL['type']
                //             ],
                //             "priority" => "high"
                //         ];
                //         Log::info(['data11' => $data]);
                //         sendFcmCurlRequest($data, '', 1);
                //     }
                    //add order id in client document
                    ClientDocument::where('client_id', $order->user_id)
                    ->whereNull('order_id')
                    ->update(['order_id' => $order->id]);
                    DB::commit();
                return response()->json(['status' => 200,'msg' => 'Payment Updated Successfully','order_id' => $order->id,'order_number' => $order->order_number]);
            }else{
                DB::commit();
                return response()->json(['status' => 0,'msg' => 'Payment rejected Successfully']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 500, 'msg' => 'An error occurred. Please try again.']);
        }
        
    }
    public function paylinkPurchase(Request $request)
    {
        try {
            $user = Auth::user();
            $amount = $this->getDollarCompareAmount($request->amount);

            $request->request->add(['payment_form' => $request->action]);
            $uniqid = uniqid();
            $customer_data = array(
                'firstName' => $user->name,
                'lastName' => '-',
                'email' => $user->email,
                'phone' => $user->phone_number
                // 'identification' => '12123123'
            );
            $reference_number = $description = '';
            $returnUrlParams = '?gateway=paylink&amount=' . $request->amount . '&payment_form=' . $request->payment_form . '&auth_token=' . $user->auth_token;

            if($request->payment_form == 'cart'){
                $description = 'Order Checkout';
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
                $request->request->add(['cart_id' => $cart->id]);
                $customer_data['cart_id'] = $cart->id;
                $reference_number = $request->order_number;
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
                }
            }

            $data = array(
                'requestId' => 'CHK-' . $uniqid,
                'orderNumber' => $reference_number,
                'amount' => $amount,
                'currency' => $this->currency, //'AED'
                'description' => $description,
                'reference' => $reference_number,
                'callBackUrl' => url('payment/gateway/returnResponse') . '?status=200&action=appointment',
                'cancelUrl' => url('payment/gateway/returnResponse') . '?status=0&action=appointment',
                'redirect' => false,
                'test' => $this->test_mode, // True, testing, false, production
                'customer' => $customer_data,
               'billingAddress' => array(
                    'name' => optional($user->address->first())->address ?? null,
                    'address1' => optional($user->address->first())->address ?? null,
                    'address2' => optional($user->address->first())->address ?? null,
                    'street' => optional($user->address->first())->street ?? null,
                    'city' => optional($user->address->first())->city ?? null,
                    'state' => optional($user->address->first())->state ?? null,
                    'zip' => optional($user->address->first())->pincode ?? null,
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

            // $ch = curl_init($this->getCheckoutUrl() . '/web');
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            // curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            // curl_setopt(
            //     $ch,
            //     CURLOPT_HTTPHEADER,
            //     array(
            //         'Content-Type: application/json',
            //         'X-PointCheckout-Api-Key:' . $this->API_KEY,
            //         'X-PointCheckout-Api-Secret:' . $this->API_SECRET_KEY
            //     )
            // );

            // $result = curl_exec($ch);
            // curl_close($ch);
            // $result = json_decode($result);
            // if ($result->success == true) {
            //     return $this->successResponse($result->result->redirectUrl, ['status' => $result->result->status]);
            // } else {
            //     return $this->errorResponse($result->error, 400);
            // }
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://restpilot.paylink.sa/api/auth",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode([
                    'apiId' => 'APP_ID_1123453311',  // Replace with actual API ID
                    'persistToken' => true,
                    'secretKey' => '0662abb5-13c7-38ab-cd12-236e58f43766'  // Replace with actual Secret Key
                ]),
                CURLOPT_HTTPHEADER => [
                    "accept: application/json",  // Expect JSON response
                    "content-type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $response = json_decode($response);
            if ($err) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $err,
                ]);
            } else {
                $result  = $this->paylinkReturn($data,$response->id_token);
                // Log::info([
                //     'amount' => $result->gatewayOrderRequest->amount,
                //     'order_reference' => $result->gatewayOrderRequest->orderNumber,
                //     'transaction_id' => $result->transactionNo,
                //     'balance_transaction' => $amount,
                //     'type' => 'cart',
                //     'date' => date('Y-m-d'),
                //     'user_id' => Auth::user()->id,
                //     'cart_id' => Cart::where('user_id',Auth::user()->id)->first()->id,
                //     'payment_from' => 'app',
                // ]);
                $order = Order::where('order_number',$result->gatewayOrderRequest->orderNumber)->first();
                Payment::create([
                    'amount' => $result->gatewayOrderRequest->amount,
                    'order_reference' => $result->gatewayOrderRequest->orderNumber,
                    'transaction_id' => $result->transactionNo,
                    'balance_transaction' => $amount,
                    'type' => 'cart',
                    'date' => date('Y-m-d'),
                    'user_id' => Auth::user()->id,
                    'cart_id' => Cart::where('user_id', Auth::user()->id)->first()->id,
                    'payment_from' => 'app',
                    'viva_order_id' => $response->id_token, 
                    'order_id' => $order->id
                ]);
                return $result;
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    } 
    public function paylinkReturn($data, $token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_URL => "https://restpilot.paylink.sa/api/addInvoice",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'amount' => $data['items']['unitprice'],
            'callBackUrl' => $data['callBackUrl'],
            'cancelUrl' => $data['cancelUrl'],
            'clientEmail' => $data['customer']['email'],
            'clientMobile' => $data['customer']['phone'],
            'clientName' => $data['customer']['firstName'],
            'currency' => $data['currency'],
            'note' => $data['description'],
            'orderNumber' => $data['orderNumber'],
            'products' => [
                [
                        'description' =>  $data['items']['sku'],
                        'isDigital' => false,
                        'price' => $data['items']['unitprice'],
                        'productCost' => $data['items']['unitprice'] * $data['items']['quantity'] ,
                        'qty' => $data['items']['quantity'],
                        'specificVat' => 0,
                        'title' =>  $data['items']['name'],
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "Authorization: $token",
            "accept: application/json",
            "content-type: application/json"
        ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $data = json_decode($response);
        $data->token = $token;
        if ($err) {
            return response()->json([
                'status' => 'Error',
                'message' => $err,
            ]);
        } else {
            return $data;
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
}
