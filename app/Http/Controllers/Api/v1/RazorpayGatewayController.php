<?php

namespace App\Http\Controllers\Api\v1;


use Log;
use Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Razorpay\Api\Api;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Api\v1\BaseController;

use App\Http\Controllers\Api\v1\OrderController;
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax};
use Illuminate\Support\Facades\Auth as FacadesAuth;

class RazorpayGatewayController extends BaseController
{
    use ApiResponser;
    public $API_KEY;
    public $API_SECRET_KEY;
    public $test_mode;
    public $api;

    public function __construct()
    {
        $razorpay_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'razorpay')->where('status', 1)->first();
        $creds_arr = json_decode($razorpay_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $api_secret_key = (isset($creds_arr->api_secret_key)) ? $creds_arr->api_secret_key : '';
        $this->test_mode = (isset($razorpay_creds->test_mode) && ($razorpay_creds->test_mode == '1')) ? true : false;

        $this->API_KEY = $api_key;
        $this->API_SECRET_KEY = $api_secret_key;
        $this->api = new Api($api_key, $api_secret_key);
    }

    public function razorpayPurchase(Request $request)
    {
        try {
            $user = Auth::user();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($request->amount);
            $amount = (int)($amount * 100);
            $order_number = $request->order_number;
            if (!isset($order_number)) {
                $order_number = 0;
            }
            $api_key = $this->API_KEY;
            return $this->successResponse(url('/payment/razorpay/view?amount=' . $amount . '&order=' . $order_number . '&api_key=' . $api_key));
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function razorpayCompletePurchase(Request $request, $domain, $amount, $order = null)
    {

        try {
            $user = Auth::user();
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            $amount = $this->getDollarCompareAmount($amount);

            // $returnUrlParams = '?gateway=razorpay&order=' . $request->order_number;

            $returnUrl = route('order.return.success');
            if ($request->payment_form == 'wallet') {
                $returnUrl = route('user.wallet');
            }

            $orderData = [

                'amount'          => (int)$amount,

                'currency'        => 'INR'
            ];

            // $payment = $this->api->payment->fetch($request->razorpay_payment_id)->capture($orderData);
            $payment = $this->api->payment->fetch($request->razorpay_payment_id);


            if ($payment['status'] == 'captured') {
                return $this->razorpayNotify($payment, $amount, $order, $orderData);
            } else {
                return $this->razorpayNotify_fail($payment, $amount, $order, $orderData);
            }
        } catch (\Exception $ex) {
            \Log::info('error response'.$ex->getMessage().'---'.$ex->getLine());

            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function razorpayNotify($payment, $amount, $order, $orderData)
    {
        $transactionId = $payment['id'];
        $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order)->first();
        if ($order) {
            $order->payment_status = 1;
            $order->save();
            $payment_exists = Payment::where('transaction_id', $transactionId)->first();
            if (!$payment_exists) {
                $user = Auth::user();

                Payment::insert([
                    'date' => date('Y-m-d'),
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'balance_transaction' => $amount,
                    'user_id' => $user->id
                ]);

                // Auto accept order
                $orderController = new OrderController();
                $orderController->autoAcceptOrderIfOn($order->id);

                // Remove cart
                Cart::where('id', $user['cart_id'])->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                CartAddon::where('cart_id', $user['cart_id'])->delete();
                CartCoupon::where('cart_id', $user['cart_id'])->delete();
                CartProduct::where('cart_id', $user['cart_id'])->delete();
                CartProductPrescription::where('cart_id', $user['cart_id'])->delete();

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
                $request = new Request();
                $request->request->add(['user_id'=>$order->user_id,'address_id'=>$order->address_id]);
                //Send Email to customer
                 $orderController->sendSuccessEmail($request, $order);
                 //Send Email to Vendor
                 foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                     $orderController->sendSuccessEmail($request, $order, $vendor_id);
                 }
            }
            $returnUrlParams = '?gateway=razorpay&order=' . $order->id;
            $returnUrl = route('order.return.success');

            return redirect()->to($returnUrl . $returnUrlParams);
        } else {
            $returnUrlParams = '?gateway=razorpay&amount=' . $amount . '&checkout=' . $payment['id'];
            $returnUrl = route('user.wallet');
            return Redirect::to(url($returnUrl . $returnUrlParams));
        }
    }

    public function razorpayNotify_fail($payment, $amount, $order, $orderData)
    {
        $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
        foreach ($order_products as $order_prod) {
            OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
        }
        OrderProduct::where('order_id', $order->id)->delete();
        OrderProductPrescription::where('order_id', $order->id)->delete();
        VendorOrderStatus::where('order_id', $order->id)->delete();
        OrderVendor::where('order_id', $order->id)->delete();
        OrderTax::where('order_id', $order->id)->delete();
        Order::where('id', $order->id)->delete();
        return Redirect::to('viewcart');
    }
}
