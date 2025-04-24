<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Traits\OrderTrait;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Traits\PesapalPaymentTrait;
use App\Models\CaregoryKycDoc;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
 
class PesapalPaymentController extends Controller
{
    use PesapalPaymentTrait, OrderTrait;
    const PaymentId = 57;
    public function payByPesapal(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();
        $data['come_from'] = 'app';

        $order = Order::where(['order_number' => $request->order_number])->first();

        if ($request->isMethod('post')) {
                $data['come_from'] = 'web';
            if (isset($request->auth_token) && ! empty($request->auth_token)) {
                $user = User::where('auth_token', $request->auth_token)->first();
                Auth::login($user);
            } else {
                $user = auth()->user();
            }         
        }

        $response = $this->PesapalPayment($request);

        if($response['status'] == 201){
            return $response;
        }

        if($request->payment_from == 'cart')
        {
            $data = [
                'amount' => $request->total_amount,
                'payment_option_id' => $this::PaymentId,
                'transaction_id' => $response['order_tracking_id'],
                'balance_transaction' => $request->total_amount,
                'order_id' => $order->id ?? '',
                'type' => $request->payment_from,
                'date' => date('Y-m-d'),
                'user_id' => $user->id
            ];
        }elseif($request->payment_from == 'subscription')
        {
            $slug = explode('-',$request->order_number);
            $data = [
                'amount' => $request->total_amount,
                'payment_option_id' => $this::PaymentId,
                'transaction_id' => $response['order_tracking_id'],
                'balance_transaction' => $request->total_amount,
                'viva_order_id' => $slug[0] ?? '',
                'type' => $request->payment_from,
                'date' => date('Y-m-d'),
                'user_id' => $user->id,
            ];
        }
        else{
            $data = [
                'amount' => $request->total_amount,
                'payment_option_id' => $this::PaymentId,
                'transaction_id' => $response['order_tracking_id'],
                'balance_transaction' => $request->total_amount,
                'viva_order_id' => $request->order_number ?? '',
                'type' => $request->payment_from,
                'date' => date('Y-m-d'),
                'user_id' => $user->id
            ];
        }
        Payment::create($data);
        return $response;
    }
    
    public function cancelPage(Request $request)
    {
        if(isset($request->come_from) && $request->come_from == 'app')
        {
            $response['status']         = 'Error';
            $response['msg']            = 'Payment Cancel';
            return response()->json($response);
        }
        return redirect()->back();
    }

    public function successPage(Request $request)
    {
        $payment = Payment::where('transaction_id', $request->get('OrderTrackingId'))->first();

        if ($payment->type == 'cart') {
            return $this->completeOrderCart($request, $payment);
        } elseif ($payment->type == 'wallet') {
            return $this->completeOrderWallet($request, $payment);
        } elseif ($payment->type == 'tip') {
            return $this->completeOrderTip($request, $payment);
        } elseif ($payment->type == 'subscription') {
            return $this->completeOrderSubs($request, $payment);
        } elseif ($payment->type == 'pickup_delivery') {
            return $this->completeOrderPickup($request, $payment);
        }   
    }

    public function completeOrderCart(Request $request, $payment)
    {
        $order = Order::where('id', $payment->order_id)->first();
        if( (isset($request->id)) && (!empty($request->id)) ){
            $user = User::find($request->id);
        }elseif( (isset($request->auth_token)) && (!empty($request->auth_token)) ){
            $user = User::whereHas('device',function  ($qu) use ($request){
                $qu->where('access_token', $request->auth_token);
            })->first();
        }else{
            $user = Auth::user();
        }

        if (! empty($order)) {
            $order->payment_status = '1';
            $order->save();

            $this->orderSuccessCartDetail($order);

            if(isset($request->come_from) && $request->come_from == 'app')
            {
                $response['status']         = 200;
                $response['msg']            = 'Success Order.';
                $response['payment_from']   = 'cart';
                $response['order_id']       = $order->id;
                $returnUrl = route('payment.gateway.return.response').'/?gateway=pesapal'.'&status=200&transaction_id='.$payment->transaction_id.'&order_id='.$order->id;
                return Redirect::to($returnUrl);
            }
            return redirect()->route('order.success',['order_id' => $order->id]);

        } else {
            $user = auth()->user();
            $wallet = $user->wallet;
            if (isset($order->wallet_amount_used)) {
                $wallet->depositFloat($order->wallet_amount_used, [
                    'Wallet has been <b>refunded</b> for cancellation of order #' . $order->order_number
                ]);
            }

            if(isset($request->come_from) && $request->come_from == 'app')
            {
                $response['status']         = 200;
                $response['msg']            = 'Success Added wallet.';
                $response['payment_from']   = 'Wallet has been <b>refunded</b> for cancellation of order #' . $order->order_number;
                return response()->json($response);
            }

            return redirect()->route('user.wallet');
        }
    }

    public function completeOrderWallet(Request $request,$payment)
    {
        $data['amount'] =  $payment->amount;
        $data['transaction_id'] =  $payment->transaction_id;
        $data['payment_option_id'] =  $this::PaymentId;
        $data['come_from'] = $request['come_from'];
        $data['user_id'] = $request['id'];

        $request = new \Illuminate\Http\Request($data);
        $this->creditMyWallet($request);

        if(isset($request->come_from) && $request->come_from == 'app')
        {
            $response['status']         = 200;
            $response['msg']            = 'Success Added wallet.';
            $response['payment_from']   = 'wallet';
            $response['data']   = $data;
            $returnUrl = route('payment.gateway.return.response').'/?gateway=pesapal'.'&status=200&transaction_id='.$payment->transaction_id;
            return Redirect::to($returnUrl);
            return response()->json($response);
        }
        return redirect()->route('user.wallet');

    }

    public function creditMyWallet(Request $request, $domain = '')
    {
        if( (isset($request->user_id)) && (!empty($request->user_id)) ){
            $user = User::find($request->user_id);
        }elseif( (isset($request->auth_token)) && (!empty($request->auth_token)) ){
            $user = User::whereHas('device',function  ($qu) use ($request){
                $qu->where('access_token', $request->auth_token);
            })->first();

        }else{
            $user = Auth::user();
        }
        if($user){
            $credit_amount = $request->amount;
            $wallet = $user->wallet;
            if ($credit_amount > 0) {
                $saved_transaction = Transaction::where('meta', 'like', '%'.$request->transaction_id.'%')->first();
                if($saved_transaction){
                    return $this->errorResponse('Transaction has already been done', 400);
                }

                $wallet->depositFloat($credit_amount, [__("Wallet has been").' <b>Credited</b> by transaction reference <b>'.$request->transaction_id.'</b>']);

                $payment = new Payment();
                $payment->date = date('Y-m-d');
                $payment->user_id = $user->id;
                $payment->transaction_id = $request->transaction_id;
                $payment->payment_option_id = $request->payment_option_id ?? null;
                $payment->balance_transaction = $credit_amount;
                $payment->type = 'wallet';
                $payment->save();

                $transactions = Transaction::where('payable_id', $user->id)->get();
                $response['wallet_balance'] = $wallet->balanceFloat;
                $response['transactions'] = $transactions;
                $message = 'Wallet has been credited successfully';
                Session::put('success', $message);
                return $this->successResponse($response, $message, 200);
            }
            else{
                return $this->errorResponse('Amount is not sufficient', 400);
            }
        }
        else{
            return $this->errorResponse('Invalid User', 400);
        }
    }

    public function completeOrderSubs(Request $request, $payment)
    {
        $data['transaction_id'] = $payment->transaction_id;
        $data['payment_option_id'] = $this::PaymentId;
        $data['subsid'] = $request['subscription_id'];
        $data['subscription_id'] = $request['subscription_id'];
        $data['amount'] = $request['amount'];
        $data['come_from'] = $request['come_from'];
        $data['user_id'] = $request['id'];

        $request = new \Illuminate\Http\Request($data);

        $subscriptionController = new UserSubscriptionController();        
        $subscriptionController->purchaseSubscriptionPlan($request,'', $payment->viva_order_id);

        if (isset($request->come_from) && $request->come_from == 'app') {
            $response['status'] = 200;
            $response['msg'] = 'Success Added Subscription.';
            $response['payment_from'] = 'subscription';
            $response['transaction_id'] = $payment->transaction_id;
            $returnUrl = route('payment.gateway.return.response').'/?gateway=pesapal'.'&status=200&transaction_id='.$payment->transaction_id;
            return Redirect::to($returnUrl);
        }
        return redirect()->route('user.subscription.plans');
        
    }

    public function completeOrderPickup(Request $request,$payment)
    {
        $order = Order::where('order_number',$payment->viva_order_id)->first();
        if(isset($request->OrderMerchantReference))
        {
            if ($order) {
                $order->payment_status = 1;
                $order->save();
                $payment_exists = Payment::where('transaction_id', $request->OrderMerchantReference)->first();
                if (!$payment_exists) {
                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->type = 'pickup_delivery';
                    $payment->order_id = $order->id ?? '';
                    $payment->payment_option_id = $this::PaymentId;
                    $payment->user_id = $order->user_id ?? '';
                    $payment->transaction_id = $request->OrderMerchantReference;
                    $payment->balance_transaction = $order->payable_amount ?? '';
                    $payment->save();
                }
                
                $request->request->add(['order_number'=> $order->order_number, 'amount' => $order->payable_amount, 'transaction_id' => $request->OrderMerchantReference]);
                $plaseOrderForPickup = new PickupDeliveryController();
                $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                
                if (isset($request->come_from) && $request->come_from == 'app') {
                    $response['status'] = 200;
                    $response['msg'] = 'Success Added Pickup.';
                    $response['payment_from'] = 'pickup_delivery';
                    $response['order'] = $order;
                    return response()->json($response);
                }

                return Redirect::to(route('front.booking.details',$order->order_number));
            }
        }else{
            //Failed transaction case
            $data = Payment::where('transaction_id',$request->OrderMerchantReference)->first();
            $data->delete();

            if (isset($request->come_from) && $request->come_from == 'app') {
                $response['status'] = 200;
                $response['msg'] = 'Success Added Pickup.';
                $response['payment_from'] = 'pickup_delivery';
                return response()->json($response);
            }

            return Redirect::to(route('user.wallet'))->with('error',$request->message);
        }
    }

    public function completeOrderTip($request, $payment)
    {
        $data['tip_amount'] = $payment->amount;
        $data['order_number'] = explode('-', $payment->viva_order_id)[0];
        $data['transaction_id'] = $payment->transaction_id;
        $data['user_id'] = $request['id'];

        $request = new \Illuminate\Http\Request($data);

        $orderController = new OrderController();
        $orderController->tipAfterOrder($request);

        if (isset($request->come_from) && $request->come_from == 'app') {
            $response['status'] = 200;
            $response['msg'] = 'Success Added Tip.';
            $response['payment_from'] = 'tip';
            return response()->json($response);
        }
        return redirect()->route('user.orders');
        
    }

}
