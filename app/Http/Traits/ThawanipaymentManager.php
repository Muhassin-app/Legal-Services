<?php

namespace App\Http\Traits;
use App\Http\Controllers\Api\v1\PickupDeliveryController as V1PickupDeliveryController;
use App\Http\Controllers\Api\v1\UserSubscriptionController;
use App\Models\PaymentOption;
use Auth, Log, Config, Session;
use GuzzleHttp\Client;
use App\Models\ClientCurrency;
use App\Models\CartAddon;
use App\Models\UserVendor;
use App\Models\CartCoupon;
use App\Models\UserAddress;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Models\CaregoryKycDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Routing\UrlGenerator;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\PickupDeliveryController;
use App\Http\Controllers\Front\WalletController;
use App\Models\Cart;

trait ThawanipaymentManager
{
    public function getDetails(){

        $payOption           = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'thawani')->where('status', 1)->first();
       \Log::info( $payOption);
        $credentials         = json_decode($payOption->credentials);
        $thawani_Apikey      = $credentials->thawani_Apikey;
        $thawani_publishKey  = $credentials->thawani_publishKey;
        $primaryCurrency     = ClientCurrency::where('is_primary', '=', 1)->first();
        $currency            = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'OMR';

        if(json_decode($payOption->test_mode) == 1){
          $checkoutUrl = "https://uatcheckout.thawani.om/api/v1/checkout/session";
          $payurl ="https://uatcheckout.thawani.om";
        }else{
            $checkoutUrl = "https://checkout.thawani.om/api/v1/checkout/session";
            $payurl ="https://checkout.thawani.om";
        }
        return [
            'checkout_url' =>$checkoutUrl,
            'payurl' =>$payurl,
            'thawani_Apikey' => $thawani_Apikey,
            'thawani_publishKey' => $thawani_publishKey,
            'currency' => $currency,
        ];
    }
    public static function orderNumber($request)
    {
        try{
                $time    = isset($request->transaction_id) ? $request->transaction_id : time();
                $user_id = auth()->id();
                $amount  = $request->amount?$request->amount:$request->amt;

                if(isset($request->action)){
                    $request->request->add(['payment_from' => $request->action,'come_from'=>'app']);
                }
                if ($request->payment_from == 'cart') {
                    $time = $request->order_number;
                    Payment::create([
                        'amount'              => $amount,
                        'transaction_id'      => $time,
                        'balance_transaction' => $amount,
                        'type'                => 'cart',
                        'date'                => date('Y-m-d'),
                        'user_id'             => $user_id,
                        'payment_from'        => $request->come_from ?? 'web',
                    ]);
                } elseif ($request->payment_from == 'wallet') {
                    Payment::create([
                        'amount'              => $amount,
                        'transaction_id'      => $time,
                        'balance_transaction' => $amount,
                        'type'                => 'wallet',
                        'date'                => date('Y-m-d'),
                        'user_id'             => $user_id,
                        'payment_from'        => $request->come_from ?? 'web',
                    ]);
                } elseif ($request->payment_from == 'subscription') {
                    $time   = ($request->subscription_id ?$request->subscription_id : time()).'_'.time();
                    $payment = Payment::create([
                        'amount'               => $request->amount?$request->amount:$request->subscription_amount,
                        'transaction_id'       => $time,
                        'balance_transaction'  => round($amount, 2),
                        'type'                 => 'subscription',
                        'date'                 => date('Y-m-d'),
                        'user_id'              => $user_id,
                        'payment_from'         => $request->come_from ?? 'web',
                    ]);
                } elseif ($request->payment_from == 'tip') {
                    $time = time();
                    $res  =  Payment::create([
                        'amount'              => 0,
                        'transaction_id'      =>  $time,
                        'balance_transaction' => $request->amount,
                        'type'                => 'tip',
                        'date'                => date('Y-m-d'),
                        'user_id'             => $user_id,
                        'payment_from'        => $request->come_from ?? 'web',
                    ]);
                } else if ($request->payment_from == 'pickup_delivery') {
                    $time = $request->order_number;
                    Payment::create([
                        'amount'              => 0,
                        'transaction_id'      => $time,
                        'balance_transaction' => $request->amt,
                        'type'                => 'pickup_delivery',
                        'date'                => date('Y-m-d'),
                        'user_id'             => $user_id,
                        'payment_from'        => $request->come_from ?? 'web',
                    ]);
                }
                return $time;
           }
            catch (\Exception $e) {
            return $e->getMessage();
           }
    }
    private static function response($code, $message, $response = null)
    {
        return [
            'status' => $code,
            'message' => $message,
            'response' => $response
        ];
    }
    public function orderSuccessCartDetail($order)
    {
        try {
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);
            $cart = Cart::select('id')->where('status', '0')
                ->where('user_id', $user->id)
                ->first();

            // Remove cart
            CaregoryKycDoc::where('cart_id', $cart->id)->update([
                'ordre_id' => $order->id,
                'cart_id' => ''
            ]);
            Cart::where('id', $cart->id)->update([
                'schedule_type' => null,
                'scheduled_date_time' => null
            ]);
            CartAddon::where('cart_id', $cart->id)->delete();
            CartCoupon::where('cart_id', $cart->id)->delete();
            CartProduct::where('cart_id', $cart->id)->delete();
            CartProductPrescription::where('cart_id', $cart->id)->delete();
            $orderController->sendSuccessSMS($$order);
            if (!empty($order->vendors)) {
                foreach ($order->vendors as $vendor_value) {
                    $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                    $user_vendors = UserVendor::where([
                        'vendor_id' => $vendor_value->vendor_id
                    ])->pluck('user_id');
                    $orderController->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
                }
            }

            $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id);
            $super_admin = User::where('is_superadmin', 1)->pluck('id');
            $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);
            if ($request->action == 'app') {
                $returnUrl = url('payment/gateway/returnResponse') . '/?gateway=thawani' . '&status=200&transaction_id=' . $transactionId . '&order=' . $order_number;
            } else {
                $returnUrl = route('order.return.success');
            }

            // send sms
            $this->sendOrderSuccessSMS($order);
        } catch (\Exception $e) {
            return true;
        }
        return true;
    }
}
