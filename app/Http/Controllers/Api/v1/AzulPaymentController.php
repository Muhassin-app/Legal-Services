<?php
namespace App\Http\Controllers\Api\v1;

use DB;
use Log;
use Auth;
use Session;
use Redirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\ {
    BaseController,
    OrderController,
    WalletController
};
use App\Models\Client as CP;
use App\Models\ {
    PaymentOption,
    Client,
    ClientPreference,
    Order,
    OrderProduct,
    EmailTemplate,
    Cart,
    CartAddon,
    OrderProductPrescription,
    CartProduct,
    User,
    Product,
    OrderProductAddon,
    Payment,
    ClientCurrency,
    OrderVendor,
    UserAddress,
    Vendor,
    CartCoupon,
    CartProductPrescription,
    LoyaltyCard,
    NotificationTemplate,
    VendorOrderStatus,
    OrderTax,
    SubscriptionInvoicesUser,
    UserDevice,
    UserVendor,
    CaregoryKycDoc
};
use App\Http\Traits\AzulPaymentService;
use App\Models\UserDataVault;

class AzulPaymentController extends BaseController
{
    use AzulPaymentService;

    public function orderNumber($request)
    {
        $time = time();

        $user_id = auth()->user()->id;

        if ($request->action == 'cart') {
            $time = $request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $request->amount,
                'type' => 'cart',
                'date' => date('Y-m-d'),
                'user_id' => $user_id
            ]);
        } elseif ($request->action == 'wallet') {
            $time = $request->transaction_id ?? time();
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $request->amount,
                'type' => 'wallet',
                'date' => date('Y-m-d'),
                'user_id' => $user_id
            ]);
        } elseif ($request->action == 'tip') {
            $time = time();
            Payment::create([
                'amount' => 0,
                'transaction_id' => $request->order_number . '_' . $time,
                'balance_transaction' => $request->amount,
                'type' => 'tip',
                'date' => date('Y-m-d'),
                'user_id' => $user_id
            ]);
        } elseif ($request->action == 'subscription') {
            $time = time();
            Payment::create([
                'amount' => 0,
                'transaction_id' => $request->subscription_id . '_' . $time,
                'balance_transaction' => $request->amount,
                'type' => 'subscription',
                'date' => date('Y-m-d'),
                'user_id' => $user_id
            ]);
        } else if ($request->action == 'pickup_delivery') {
            $time = $request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $request->amount,
                'type' => 'pickup_delivery',
                'date' => date('Y-m-d'),
                'user_id' => $user_id
            ]);
        }
        return $time;
    }

    public function beforePayment(Request $request)
    {
        $response = [];
        $number = $this->orderNumber($request);
        if ($request->action == 'wallet') {
            $number = $this->orderNumber($request);
            $request->request->add([
                'order_number' => $number,
                'amount' => $request->amount
            ]);
        }
        if ($request->action == 'subscription') {
            $number = $this->orderNumber($request);
            $request->request->add([
                'order_number' => $number,
                'amount' => $request->amount
            ]);
        }

        if (! isset($request->come_from)) {
            $request->request->add([
                'come_from' => 'app'
            ]);
        }
        $dataResponse = $this->payWithCard($request->all());
        
        if ($dataResponse['ok'] === false && $this->mode) {
            $response['status'] = 'Fail';
            $response['msg'] = 'Invalid Card Details.';
            $response['payment_from'] = $request->action;
            return response()->json($response, 400);
        }

        if (isset($dataResponse['ok']) && $dataResponse['ok'] === $this->mode) {

            if ($request->action == 'tip') {
                $payment = Payment::where('transaction_id', $request->order_number . '_' . $number)->first();
            } else if ($request->action == 'subscription') {
                $payment = Payment::where('transaction_id', $request->subscription_id . '_' . $number)->first();
            } else {
                $payment = Payment::where('transaction_id', $request->order_number)->first();
            }
            
            if (! empty($payment)) {
                $payment->viva_order_id = $dataResponse['data']->AzulOrderId;
                $payment->save();
            }

            if ($payment->type == 'cart') {
                return $this->completeOrderCart($dataResponse, $payment);
            } elseif ($payment->type == 'wallet') {
                return $this->completeOrderWallet($dataResponse, $payment, $request->amount, $request->come_from);
            } elseif ($payment->type == 'tip') {
                return $this->completeOrderTip($dataResponse, $payment, $request, $request->amount, $request->come_from);
            } elseif ($payment->type == 'subscription') {
                return $this->completeOrderSubs($dataResponse, $payment, $request, $request->come_from);
            } elseif ($payment->type == 'pickup_delivery') {
                return $this->completePickupDelivery($dataResponse, $payment, $request, $request->come_from);
            }
        } else {
            
            $response['status'] = 'Fail';
            $response['msg'] = $dataResponse['message'];
            $response['payment_from'] = $request->action;
            return response()->json($response, 200);
        }
    }

    public function completeOrderCart($request, $payment)
    {
        $order = Order::where('order_number', $payment->transaction_id)->first();
        if (isset($request['ok']) && $request['ok'] == $this->mode) {
            $order->payment_status = '1';
            $order->save();

            // Auto accept order
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id', auth()->id())->select('id')->first();
            $cartid = $cart->id;
            Cart::where('id', $cartid)->update([
                'schedule_type' => null,
                'scheduled_date_time' => null,
                'comment_for_pickup_driver' => null,
                'comment_for_dropoff_driver' => null,
                'comment_for_vendor' => null,
                'schedule_pickup' => null,
                'schedule_dropoff' => null,
                'specific_instructions' => null
            ]);
            CaregoryKycDoc::where('cart_id', $cartid)->update([
                'ordre_id' => $order->id,
                'cart_id' => ''
            ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();

            // send sms
            $orderController->sendSuccessSMS($request, $order);

            // Send Notification
            if (! empty($order->vendors)) {
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

            if (isset($request->auth_token) && ! empty($request->auth_token)) {
                $response['status'] = 'Success';
                $response['msg'] = 'Success Order.';
                $response['payment_from'] = 'cart';
                return response()->json($response, 200);
            } else {
                $response['status'] = 'Success';
                $response['msg'] = 'Success Order.';
                $response['payment_from'] = 'cart';
                return response()->json($response, 200);
            }
        } else {
            $user = auth()->user();
            $wallet = $user->wallet;
            if (isset($order->wallet_amount_used)) {
                $wallet->depositFloat($order->wallet_amount_used, [
                    'Wallet has been <b>refunded</b> for cancellation of order #' . $order->order_number
                ]);
                $this->sendWalletNotification($user->id, $order->order_number);
            }
            if (isset($request->auth_token) && ! empty($request->auth_token)) {
                $response['status'] = 'Fail';
                $response['msg'] = 'Failed Order.';
                $response['payment_from'] = 'cart';
                return response()->json($response, 200);
            } else {
                $response['status'] = 'Fail';
                $response['msg'] = 'Failed Order.';
                $response['payment_from'] = 'cart';
                return response()->json($response, 200);
            }
        }
    }

    public function completeOrderWallet($request, $payment, $amount, $come_from)
    {
        if (isset($request['ok']) && $request['ok'] === $this->mode) {

            $data['amount'] = $amount;
            $data['transaction_id'] = $payment->transaction_id;
            $request = new \Illuminate\Http\Request($data);
            $walletController = new WalletController();
            $walletController->creditMyWallet($request);

            if ($come_from == 'app') {
                $response['status'] = 'Success';
                $response['msg'] = 'Success Added wallet.';
                $response['payment_from'] = 'wallet';
            }
            return response()->json($response, 200);
        }
    }

    public function completeOrderTip($request, $payment, $requestdata, $amount, $come_from)
    {
        if (isset($request['ok']) && $request['ok'] == $this->mode) {
            $data['tip_amount'] = $amount;
            $data['order_number'] = $requestdata->order_number;
            $data['transaction_id'] = $payment->transaction_id;

            $request = new \Illuminate\Http\Request($data);

            $orderController = new OrderController();
            $orderController->tipAfterOrder($request);
            if ($come_from == 'app') {
                $response['status'] = 'Success';
                $response['msg'] = 'Success Added Tip.';
                $response['payment_from'] = 'tip';
            }
            return response()->json($response, 200);
        }
    }

    public function completeOrderSubs($request, $payment, $requestdata, $come_from)
    {
        if (isset($request['ok']) && $request['ok'] == $this->mode) {

            $data['transaction_id'] = $payment->transaction_id;
            $data['payment_option_id'] = 50;
            $data['subsid'] = $requestdata['subscription_id'];
            $data['subscription_id'] = $requestdata['subscription_id'];
            $data['amount'] = $requestdata['amount'];

            $request = new \Illuminate\Http\Request($data);

            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($request, $requestdata->subscription_id);
            if ($come_from == 'app') {
                $response['status'] = 'Success';
                $response['msg'] = 'Success Added Subscription.';
                $response['payment_from'] = 'subscription';
            }
            return response()->json($response, 200);
        }
    }

    public function completePickupDelivery($request, $payment, $requestdata, $come_from)
    {
        if (isset($request['ok']) && $request['ok'] == $this->mode) {

            $data['payment_option_id'] = 50;
            $data['transaction_id'] = $payment->transaction_id;
            $data['amount'] = $requestdata['amount'];
            $data['order_number'] = $requestdata['order_number'];
            $data['reload_route'] = $requestdata['reload_route'];
            $request = new \Illuminate\Http\Request($data);
            $plaseOrderForPickup = new PickupDeliveryController();
            $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
            if ($come_from == 'app') {
                $response['status'] = 'Success';
                $response['msg'] = 'Success Added Pickup Delivery.';
                $response['payment_from'] = 'pickup_delivery';
                $response['data'] = $res;
            }

            return response()->json($response, 200);
        }
    }
}