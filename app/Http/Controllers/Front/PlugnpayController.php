<?php
namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Front\{FrontController, OrderController, PickupDeliveryController};
use App\Http\Traits\ApiResponser;
use App\Http\Traits\PlugnpaypaymentManager;
use App\Models\CaregoryKycDoc;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\Order;
use App\Models\User;
use App\Models\UserVendor;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class PlugnpayController extends FrontController
{
    use PlugnpaypaymentManager;

    public function orderNumber($request)
   {
        $time = time();


        $user_id = auth()->user()->id;

        if($request->from == 'cart')
        {
            $time = $request->order_number;
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'cart','date'=>date('Y-m-d'),'user_id'=>$user_id]);

        }elseif($request->from == 'wallet')
        {
            $time = $request->transaction_id??time();
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'wallet','date'=>date('Y-m-d'),'user_id'=>$user_id]);

        }elseif($request->from == 'tip')
        {
             $time = time();
             Payment::create(['amount'=>0,'transaction_id'=>$request->order_number.'_'.$time,'balance_transaction'=>$request->amt,'type'=>'tip','date'=>date('Y-m-d'),'user_id'=>$user_id]);

        }elseif($request->from == 'subscription')
        {
            $time = time();
            Payment::create(['amount'=>0,'transaction_id'=>$request->subsid.'_'.$time,'balance_transaction'=>$request->amt,'type'=>'subscription','date'=>date('Y-m-d'),'user_id'=>$user_id]);

        }else if($request->from == 'pickup_delivery')
        {
            $time = $request->order_number;
            Payment::create(['amount'=>0,'transaction_id'=>$time,'balance_transaction'=>$request->amt,'type'=>'pickup_delivery','date'=>date('Y-m-d'),'user_id'=>$user_id]);

        }
        return $time;
   }

    public function beforePayment(Request $request)
    {


      $response = [];

      $number =  $this->orderNumber($request);
      if($request->from=='wallet'){
        $number =  $this->orderNumber($request);
        $request->request->add(['order_number' => $number,'amount'=>$request->amount]);
      }
      if($request->from=='subscription'){
        $number =  $this->orderNumber($request);
        $request->request->add(['order_number' => $number,'amount'=>$request->amount]);
      }

      ////\Log::info(json_encode($request->all()));
    	$responsePay = $this->createPaymentRequest($request->all());
        ////\Log::info(json_encode($responsePay));
        $dataResponse = json_decode($responsePay);
        if($dataResponse->FinalStatus == 'badcard'){
            $response['status']         = 'Fail';
            $response['msg']            = $dataResponse->MErrMsg;
            $response['payment_from']   = $request->from;
            $response['route']          = '';
            return $response;
        }
        ////\Log::info($dataResponse->FinalStatus);

        if(isset($dataResponse->FinalStatus))
        {
        ////\Log::info('Done');


        if($request->from=='tip'){
            $payment = Payment::where('transaction_id',$dataResponse->address2.'_'.$number)->first();
        }else if($request->from=='subscription'){
            $payment = Payment::where('transaction_id',$request->subsid.'_'.$number)->first();
        }
        else{
            $payment = Payment::where('transaction_id',$dataResponse->address2)->first();
        }

       ////\Log::info(json_encode($request->all()));

            if($payment->type=='cart'){
            return $this->completeOrderCart($dataResponse,$payment);
            }elseif($payment->type=='wallet'){
                return $this->completeOrderWallet($dataResponse,$payment,$request->amount);
            }elseif($payment->type=='tip'){
                return $this->completeOrderTip($dataResponse,$payment,$request->amount);
            }elseif($payment->type=='subscription'){
                return $this->completeOrderSubs($dataResponse,$payment,$request);
            }

            elseif($payment->type=='pickup_delivery'){
                return $this->completePickupDelivery($dataResponse,$payment,$request);
            }

        }else{
            ////\Log::info('fail--'.$dataResponse->FinalStatus.'--');
            $returnUrl = route('order.return.success');
            $response['status'] = 'Fail';
            $response['msg'] = 'Failed.';
            $response['payment_from'] = 'cart';
            $response['route'] = $returnUrl;

            return $response;
            // return Redirect::to(route('showCart'))->with('error',$request->FinalStatus. ', Somthing went wrong.');
        }


    }


    public function completeOrderCart($request,$payment)
    {



      $order = Order::where('order_number',$payment->transaction_id)->first();
          if(isset($request->FinalStatus) && $request->FinalStatus == 'success')
          {
            $order->payment_status = '1';
            $order->save();

            // Auto accept order
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id',auth()->id())->select('id')->first();
            $cartid = $cart->id;
            Cart::where('id', $cartid)->update([
              'schedule_type' => null, 'scheduled_date_time' => null,
              'comment_for_pickup_driver' => null, 'comment_for_dropoff_driver' => null, 'comment_for_vendor' => null, 'schedule_pickup' => null, 'schedule_dropoff' => null, 'specific_instructions' => null
          ]);
            CaregoryKycDoc::where('cart_id',$cartid)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();

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

          if(isset($request->auth_token) && !empty($request->auth_token))
          {
            $returnUrl = route('order.return.success');
            $response['status'] = 'Success';
            $response['msg'] = 'Success Order.';
            $response['payment_from'] = 'cart';
            $response['route'] = $returnUrl;

            return $response;
          }else{
            $returnUrl = route('order.return.success');
            $response['status'] = 'Success';
            $response['msg'] = 'Success Order.';
            $response['payment_from'] = 'cart';
            $response['route'] = $returnUrl;

            return $response;
          }

          }else{
            $user = auth()->user();
            $wallet = $user->wallet;
            if(isset($order->wallet_amount_used)){
              $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order #'. $order->order_number]);
              $this->sendWalletNotification($order->user_id, $order->order_number);
            }
            if(isset($request->auth_token) && !empty($request->auth_token))
            {
              $returnUrl = route('order.return.success');
              $response['status'] = 'Fail';
              $response['msg'] = 'Failed Order.';
              $response['payment_from'] = 'cart';
              $response['route'] = $returnUrl;

              return $response;

            }else{

              $returnUrl = route('order.return.success');
              $response['status'] = 'Fail';
              $response['msg'] = 'Failed Order.';
              $response['payment_from'] = 'cart';
              $response['route'] = $returnUrl;

              return $response;

            }

          }

    }

    public function completeOrderWallet($request,$payment,$amount){

        if(isset($request->FinalStatus) && $request->FinalStatus == 'success')
        {

            $data['wallet_amount'] =  $amount;
            $data['transaction_id'] =  $payment->transaction_id;
            $request = new \Illuminate\Http\Request($data);
            $walletController = new WalletController();
            $walletController->creditWallet($request);

            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=plugnpay'.'&status=200&transaction_id='.$payment->transaction_id;
                $response['route'] = $returnUrl;
            }else{
                $returnUrl = route('user.wallet');
                $response['route'] = $returnUrl;
            }
            return $response;
        }else{
            //dd($request->FinalStatus);
        }

    }

    public function completeOrderTip($request,$payment,$amount){
        if(isset($request->FinalStatus) && $request->FinalStatus == 'success')
        {
            $data['tip_amount']     =  $amount;
            $data['order_number']   =  $request->address2;
            $data['transaction_id'] =  $payment->transaction_id;

            $request = new \Illuminate\Http\Request($data);

            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
            if($request['from'] == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=plugnpay'.'&status=200&transaction_id='. $payment->transaction_id;
                $response['route'] = $returnUrl;
            }else{
                $returnUrl = route('user.orders');
                $response['route'] = $returnUrl;
            }
            return $response;
        }
    }

    public function completeOrderSubs($request,$payment,$requestdata){
        if(isset($request->FinalStatus) && $request->FinalStatus == 'success')
        {

            $data['transaction_id']      = $payment->transaction_id;
            $data['payment_option_id']   = 49;
            $data['subsid']              = $requestdata['subsid'];
            $data['subscription_id']     = $requestdata['subsid'];
            $data['amount']              = $requestdata['amt'];

            $request = new \Illuminate\Http\Request($data);

            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, '', $requestdata->subsid);
            if($request['from'] == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=plugnpay'.'&status=200&transaction_id='.$payment->transaction_id;
                $response['route'] = $returnUrl;
            }else{
                $returnUrl = route('user.subscription.plans');
                $response['route'] = $returnUrl;
            }
            return $response;
        }
    }

    public function completePickupDelivery($request,$payment,$requestdata){
        if(isset($request->FinalStatus) && $request->FinalStatus == 'success')
        {

            $data['payment_option_id']   = 49;
            $data['transaction_id']      = $payment->transaction_id;
            $data['amount']              = $requestdata['amt'];
            $data['order_number']        = $requestdata['order_number'];
            $data['reload_route']        = $requestdata['reload_route'];
            $request = new \Illuminate\Http\Request($data);
            $plaseOrderForPickup = new PickupDeliveryController();
            $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
            $returnUrl = $request->reload_route;
            $response['route'] = $returnUrl;
            if($request->come_from == 'app')
            {
                $returnUrl = route('payment.gateway.return.response').'/?gateway=plugnpay'.'&status=200&transaction_id='.$payment->transaction_id;
                $response['route'] = $returnUrl;
            }

            return $response;
        }

    }



}
