<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Auth;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\Mpesa;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserVendor;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Redirect;
use Log;
use Illuminate\Support\Str;
use App\Http\Traits\OrderTrait;
use App\Models\Client;
use App\Models\OrderVendor;
use App\Models\UserDevice;
use App\Models\ClientPreference;
use App\Models\NotificationTemplate;

class MpesaSafariController extends Controller
{
    use ApiResponser,OrderTrait, Mpesa;

    //Initiate STK Push

    public function orderNumber($request)
    {
        $time = time();
        $user_id = auth()->id();
        $amount = $request->amt??$request->amount;
        if ($request->action == 'cart') {
            $time = $request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amount,
                'type' => 'cart',
                'date' => date('Y-m-d'),
                'user_id' => $user_id,
                'payment_from'=>$request->come_from??'web'
            ]);
        } elseif ($request->action == 'wallet') {
            $time = $request->transaction_id ?? time();
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amount,
                'type' => 'wallet',
                'date' => date('Y-m-d'),
                'user_id' => $user_id,
                'payment_from'=>$request->come_from??'web'

            ]);
        } elseif ($request->action == 'tip') {
            $time = $request->order_number. time();
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amount,
                'type' => 'tip',
                'date' => date('Y-m-d'),
                'user_id' => $user_id,
                'payment_from'=>$request->come_from??'web'

            ]);
        } elseif ($request->action == 'subscription') {
            $time = $request->subscription_id?$request->subscription_id: time();
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amount,
                'type' => 'subscription',
                'date' => date('Y-m-d'),
                'user_id' => $user_id,
                'payment_from'=>$request->come_from??'web'

            ]);
        } else if ($request->action == 'pickup_delivery') {
            $time = $request->order_id??$request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amount,
                'type' => 'pickup_delivery',
                'date' => date('Y-m-d'),
                'user_id' => $user_id,
                'payment_from'=>$request->come_from??'web'

            ]);
        }
        return $time;
    }

    public function createPayment(Request $request)
    {
        $amount = $request->amt??$request->amount;
        $accountReference=$this->orderNumber($request);
     /*   if($request->come_from == 'app'){
            $code = $request->header('code');
            $client = Client::where('code',$code)->first();
            $domain = '';
            if(!empty($client->custom_domain)){
                $domain = $client->custom_domain;
            }else{
                $domain = $client->sub_domain.env('SUBMAINDOMAIN');
            }
            $this->lnmocallback =  'https://'.$domain.'/webhook/mpesa';
        }*/

        $phone = $this->formatPhone(auth()->user()->phone_number);
        $response = $this->express($amount,$phone,$accountReference,'Payment');
        $response = json_decode($response);
        if(isset($response->ResponseCode)){
            if($response->ResponseCode == 0){
               $payment = Payment::where('transaction_id',$accountReference)->first();
               $payment->viva_order_id = $response->CheckoutRequestID;
               $payment->save();
               if($payment->type == 'cart'){
                    \Session::flash('success', 'Order placed successfully.');
                    if($payment->payment_from == 'web')
                        $route = route('order.success',['order_id' => $request->order_id]);
                } elseif($payment->type == 'wallet'){
                    \Session::flash('success', 'Wallet amount updated soon.');
                    if($payment->payment_from == 'web')
                        $route = route('user.wallet');
                } elseif($payment->type == 'tip'){
                    \Session::flash('success', 'Tip amount updated soon.');
                    if($payment->payment_from == 'web')
                        $route = route('user.orders');
                } elseif($payment->type == 'subscription'){
                    \Session::flash('success', 'Subscription updated soon.');
                    if($payment->payment_from == 'web')
                        $route = route('user.subscription.plans');
                } elseif ($payment->type == 'pickup_delivery') {
                    \Session::flash('success', 'Subscription updated soon.');
                    if($payment->payment_from == 'web')
                        $route = route('front.booking.details',$request->order_number);
                }
                $resp['status']         = 'Success';
                $resp['message']            = $response->ResponseDescription;
                $resp['payment_from']   = $request->action;
                $resp['route']          = $route??'';
                return $resp;
            }else{
                return $this->errorResponse($response->ResponseDescription, 400);
            }
        }
        if(isset($response->errorCode)){
            return  $this->errorResponse($response->errorMessage, 400);
        }
        return $this->errorResponse('Server Error', 400);
    }

    public function formatPhone($phone)
    {
        $phone = 'hfhsgdgs' . $phone;
        $phone = str_replace('hfhsgdgs0', '', $phone);
        $phone = str_replace('hfhsgdgs', '', $phone);
        $phone = str_replace('+', '', $phone);
        if (strlen($phone) == 9) {
            $phone = '254' . $phone;
        }
        return $phone;
    }

    public function successPage(Request $request)
    {
        \Log::info("webhook success");
        \Log::info($request->all());
         if (isset($request->Body) && isset($request->Body['stkCallback']) && isset($request->Body['stkCallback']['ResultCode'])) {
             if($request->Body['stkCallback']['ResultCode'] == 0){
                $payment = Payment::where('viva_order_id', $request->Body['stkCallback']['CheckoutRequestID'])->first();
                if ($payment->type == 'cart') {
                    $this->completeOrderCart($request, $payment);
                } elseif ($payment->type == 'wallet') {
                    $this->completeOrderWallet($request, $payment, $request->amount);
                } elseif ($payment->type == 'tip') {
                    $this->completeOrderTip($request, $payment);
                } elseif ($payment->type == 'subscription') {
                    $this->completeOrderSubs($request, $payment, $request);
                } elseif ($payment->type == 'pickup_delivery') {
                    $this->completePickupDelivery($request, $payment, $request);
                }
             }else{
                 $payment = Payment::where('viva_order_id', $request->Body['stkCallback']['CheckoutRequestID'])->first();
                 if (in_array($payment->type,['cart','pickup_delivery'])) {
                     $order = Order::where('order_number', $payment->transaction_id)->first();
                     if (!empty($order))
                     {
                         $currentOrderStatus = OrderVendor::where(['order_id' => $order->id])->first();
                         if(!empty($currentOrderStatus)){
                             $currentOrderStatus->order_status_option_id = 3;
                             $currentOrderStatus->save();
                             $this->sendStatusChangePushNotificationCustomer([$currentOrderStatus->user_id], $order,$currentOrderStatus->order_status_option_id);
                         }
                     }
                 }
             }
         }else{
             \Log::info("webhook error");
             \Log::info($request->all());
         }
    }

    public function completeOrderCart($request, $payment)
    {
        $order = Order::where('order_number', $payment->transaction_id)->first();
        if (!empty($order))
        {
            $order->payment_status = '1';
            $order->save();
            $this->orderSuccessCartDetail($order);
          /*  if($payment->payment_from != 'app'){
                $returnUrl = route('order.success',[$order->id]);
                return redirect($returnUrl);
            }else{
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=200&order='.$order->order_number;
                return redirect($returnUrl);
            }*/
        }
    }


    public function completeOrderWallet($request, $payment)
    {
        //if (isset($request) && ($request->get('statusId') == '2')){
            $user = User::findOrFail($payment->user_id);
            Auth::login($user);
            $wallet = $user->wallet;
            $wallet->depositFloat($payment->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $payment->transaction_id . '</b>']);
           /* if ($payment->payment_from == 'app') {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=200&transaction_id='.$request->order_id.'&action=wallet';
                return redirect($returnUrl);
            }else{
                return redirect(route('user.wallet'))->with('success', 'Wallet amount added successfully.');
            }*/
//         }else{
//             if ($payment->payment_from == 'app') {
//                 $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=200&transaction_id='.$request->order_id.'&action=wallet';
//                 return redirect($returnUrl);
//             }else{
//                 return redirect(route('user.wallet'))->with('error', 'Amount Failed.');
//             }
//         }
    }

    public function completeOrderTip($request, $payment)
    {
       // if (isset($request) && ($request->get('statusId') == '2')){
            $data['tip_amount'] = $request->amount;
            $data['order_number'] = $request->order_number;
            $data['transaction_id'] = $payment->transaction_id;
            $request = new \Illuminate\Http\Request($data);
            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
         /*   if ($payment->payment_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=200&order='.$request->order_id.'&action=tip';
                return redirect($returnUrl);
            }else{
                return redirect(route('user.orders'))->with('success', 'Tip given successfully.');
            }*/
//         }else{
//             if ($payment->payment_from == 'app') {
//                 $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=200&transaction_id='.$request->order_id.'&action=tip';
//                 return redirect($returnUrl);
//             }else{
//                 return redirect(route('user.orders'))->with('error', 'Failed.');
//             }
//         }
    }


    public function completeOrderSubs(Request $request,$payment)
    {
        //if (isset($request) && ($request->get('statusId') == '2')){
            $subscription = explode('_',$payment->transaction_id);
            $request->request->add(['user_id' => $payment->user_id, 'payment_option_id' => 62, 'amount' => $payment->balance_transaction, 'transaction_id' => $request->transId]);
            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription[0]);
           /* if(isset($payment->payment_from) && $payment->payment_from=='app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=200&transaction_id='.$request->transId.'&action=subscription';
                return Redirect::to($returnUrl);
            }else{
                return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
            }*/
//         }else{
//             $payment->delete();
//             if(isset($payment->payment_from) && $payment->payment_from=='app')
//             {
//                 $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=00&transaction_id='.$request->transId.'&action=subscription';
//                 return Redirect::to($returnUrl);
//             }else{
//                 return Redirect::to(route('user.subscription.plans'))->with('error',$request->message);
//             }
//         }
    }

    public function completePickupDelivery($request, $payment)
    {
        $order = Order::where('order_number', $payment->transaction_id)->first();
       // if (isset($request) && ($request->get('statusId') == '2')){
            $request->request->add(['order_number'=> $order->order_number, 'payment_option_id' => 62, 'amount' => $order->payable_amount, 'transaction_id' => $request->TransID]);
            $plaseOrderForPickup = new PickupDeliveryController();
            $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);

          /*  if($payment->payment_from=='app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=mpesasafari'.'&status=200&order='.$payment->transaction_id;
                return Redirect::to($returnUrl);
            }else{
                return Redirect::to(route('front.booking.details',$order->order_number));
            }*/
//         }else{
//             $data = Payment::where('transaction_id',$payment->transaction_id)->first();
//             $data->delete();
//             return Redirect::to(route('front.booking.details'))->with('error',$request->message);
//         }
    }

    public function sendStatusChangePushNotificationCustomer($user_ids, $orderData, $order_status_id)
    {
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            if ($order_status_id == 2) {
                $notification_content = NotificationTemplate::where('id', 5)->first();
            } elseif ($order_status_id == 3) {
                $notification_content = NotificationTemplate::where('id', 6)->first();
            } elseif ($order_status_id == 4) {
                $notification_content = NotificationTemplate::where('id', 7)->first();
            } elseif ($order_status_id == 5) {
                //Check for order is takeaway
                if(@$orderData->luxury_option_id == 3)
                {
                    $notification_content = NotificationTemplate::where('slug', 'order-out-for-takeaway-delivery')->first();
                }else{
                    $notification_content = NotificationTemplate::where('id', 8)->first();
                }
            } elseif ($order_status_id == 6) {
                $notification_content = NotificationTemplate::where('id', 9)->first();
            }
            if ($notification_content) {
                $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
                $redirect_URL['type'] = 4;
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "default",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => '',
                        "android_channel_id" => "default-channel-id",
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        "type" => "order_status_change",
                        "order_id" =>$orderData->id,
                        "vendor_id" =>$orderData->ordervendor->vendor_id,
                        "order_status" =>$order_status_id,
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "priority" => "high"
                ];
                sendFcmCurlRequest($data);
            }
        }
    }
  }
