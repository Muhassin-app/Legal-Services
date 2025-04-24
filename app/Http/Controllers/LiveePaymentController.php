<?php

namespace App\Http\Controllers;

// use Core\Authentication\Auth;

use Algolia\AlgoliaSearch\Http\GuzzleHttpClient;
use App\Http\Controllers\Api\v1\PickupDeliveryController;
use App\Http\Controllers\Api\v1\UserSubscriptionController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Traits\ApiResponser;
use App\Models\CaregoryKycDoc;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\ClientCurrency;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentOption;
use App\Models\User;
use App\Models\UserVendor;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use JWT\Token;
use OrderTrait;
use Predis\Protocol\Text\Handler\ErrorResponse;

class LiveePaymentController extends Controller
{
    use ApiResponser;
    public $currency;
    private $trade_key;
    private $resource_key;
    private $apiUrl;
    // const trade_key = 'sa4b4km6c0l9eq7y6od88cnjp62efvr6ix59u5taz2ghw0193';
    // const TOKEN_API           = "";
    // const resource_key   = "bj65bih1kzo740snwbru2q9px3v5503fetfdaaegmc64yle58";


    public function __construct()
    {
        $payOption = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'livee')->where('status', 1)->first();

        if(@$payOption && !empty($payOption->credentials))
        {
            $credentials = json_decode($payOption->credentials);
            $this->trade_key = $credentials->livee_merchant_key;
            $this->resource_key = $credentials->livee_resource_key;
            $this->apiUrl = "https://www.livees.net/Checkout/api4";
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
        }
    }


    public function index(Request $request, $amt)
    {
        try {
            $orderNumber = $this->orderNumber($request);
            $users = $this->createUserToken();
            $user = Auth::user();
            $urlParams = '';
            $amount = $request->amt;
            $nameString = "name";
            $name = strtok(auth()->user()->name, " ");
            $lastname = substr(strstr(auth()->user()->name, " "), 1);
            $email = auth()->user()->email;
            $phone = auth()->user()->phone_number;
            $users = $this->createUserToken();
            if ($request->payment_from == 'cart') {
                $urlParams   = "transactionid=$orderNumber&paymentfrom=cart&success=true";
            } elseif ($request->payment_from == 'wallet') {
                $urlParams   = "transactionid=$orderNumber&paymentfrom=wallet&success=true";
            } elseif ($request->payment_from == 'subscription') {
                $urlParams   = "transactionid=$orderNumber&subscription_id=$request->subscription_id&amount=$request->amt&success=true";
            } elseif ($request->payment_from == 'pickup_delivery') {
                $urlParams   = "transactionid=$orderNumber&paymentfrom=pickup_delivery&reload_route=$request->reload_route&amount=$request->amt&success=true";
            } elseif ($request->payment_from == 'tip') {

                $urlParams   = "transactionid=$orderNumber&order_number=$request->order_number&paymentfrom=tip&amount=$request->amt&success=true";
            }
            $postURL = url('/livee/success' . '?' . $urlParams);

            return view('backend.payment.liveePay', compact('amount', 'postURL', 'user'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function token()
    {
        try {
            $apiUrl = $this->apiUrl;
            $input = json_encode([
                "trade_key" => $this->trade_key,
                "resource_key" => $this->resource_key
            ]);

            $header = [
                'Content-Type' => 'application/json'
            ];
            $response = Http::withBody($input, 'application/json')->withHeaders($header)->post($apiUrl);

            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function afterPayment(Request $request)
    {
        try {
            $transactionId = $request->transactionid;
            $payment = Payment::where('transaction_id', $transactionId)->first();
            if ($payment) {

                $payment->viva_order_id = $transactionId;
                $payment->payment_option_id = 59;
                $payment->save();
            }
            if ($request->paymentfrom == 'cart') {
                $order = Order::where('order_number', $transactionId)->first();
                if ($order) {
                    $order->payment_status = '1';
                    $order->save();
                    $this->orderSuccessCartDetail($order);
                    if ($payment->payment_from == 'web') {
                        return redirect()->route('order.success', $order->id);
                    } else {

                        $returnUrl = route('payment.gateway.return.response') . '/?gateway=livees' . '&status=200&order=' . $order->order_number;
                        return redirect($returnUrl);
                    }
                }
            } elseif ($request->paymentfrom == 'wallet') {
                if ($payment->payment_from == 'app') {
                    $user = User::findOrFail($payment->user_id);
                    Auth::login($user);
                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=livees' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=wallet';
                } else {
                    $user      = auth()->user();
                    $returnUrl = route('user.wallet');
                }
                $wallet  = $user->wallet;
                $wallet->depositFloat($payment->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $payment->transaction_id . '</b>']);
                return redirect($returnUrl);
            } elseif (isset($request->subscription_id)) {

                $data['transaction_id'] = $payment->transaction_id;
                $data['payment_option_id'] = 59;
                $data['subsid'] = $request->subscription_id;
                $data['subscription_id'] = $request->subscription_id;
                $data['amount'] = $request->amount;
                $request = new \Illuminate\Http\Request($data);

                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, $request->subscription_id);

                if ($payment->payment_from == 'web') {

                    return redirect()->route('user.subscription.plans');
                } else {
                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=livee' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=subscription';
                    return redirect($returnUrl);
                }
            } elseif ($request->paymentfrom == 'pickup_delivery') {
                $data['payment_option_id'] = 59;
                $data['transaction_id'] = $transactionId;
                $data['amount'] = $request->amount;
                $data['order_number'] = $transactionId;
                $data['reload_route'] = $request->reload_route;
                $request = new \Illuminate\Http\Request($data);
                $plaseOrderForPickup = new PickupDeliveryController();
                $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                if ($payment->payment_from == 'web') {
                    return redirect()->route('front.booking.details', $transactionId);
                } else {
                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=livee' . '&status=200&order=' . $transactionId;
                    return redirect($returnUrl);
                }
            } elseif ($request->paymentfrom == 'tip') {

                $data['tip_amount'] = $request->amt;
                $data['order_number'] = $request->order_number;
                $data['transaction_id'] = $transactionId;
                $request = new \Illuminate\Http\Request($data);
                $orderController = new OrderController();
                $orderController->tipAfterOrder($request);
                if ($payment->payment_from == 'web') {
                    return redirect()->route('user.orders');
                } else {
                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=livee' . '&status=200&order=' . $transactionId . '&action=tip';
                    return redirect($returnUrl);
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function payFormWeb(Request $request)
    {

        try {
            if (isset($request->user_id)) {
                $user = User::where('id', $request->user_id)->first();
                Auth::login($user);
            } else {
                $user = Auth::loginUsingId(1);
            }
            $request->request->add(['payment_from' => isset($request->paymentfrom) ? $request->paymentfrom : $request->payment_from]);
            $request->request->add(['amount' => isset($request->amt) ? $request->amt : $request->amount]);

            $orderNumber = $this->orderNumber($request);
            $urlParams = '';
            $amount = $request->amt;
            $nameString = "name";

            $name = strtok(auth()->user()->name, " ");
            $email = auth()->user()->email;
            $phone = auth()->user()->phone_number;


            if ($request->payment_from == 'cart') {
                $urlParams   = "transactionid=$orderNumber&paymentfrom=cart&success=true";
            } elseif ($request->payment_from == 'wallet') {
                $urlParams   = "transactionid=$orderNumber&paymentfrom=wallet&success=true&come_from=app&amount=$request->amount&success=true";
            } elseif ($request->payment_from == 'subscription') {
                $urlParams   = "transactionid=$orderNumber&subscription_id=$request->subscription_id&amount=$request->amount&success=true";
            } elseif ($request->payment_from == 'pickup_delivery') {
                $urlParams   = "transactionid=$orderNumber&paymentfrom=pickup_delivery&reload_route=$request->reload_route&amount=$request->amount&success=true";
            } elseif ($request->payment_from == 'tip') {
                $urlParams   = "transactionid=$orderNumber&order_number=$request->order_number&paymentfrom=tip&amount=$request->amount&success=true";
            }
            $postURL = url('/livee/success' . '?' . $urlParams);
            return view('backend.payment.liveePay', compact('amount', 'postURL', 'user'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function orderNumber($request)
    {

        try {
            $time    = isset($request->transaction_id) ? $request->transaction_id : time();
            $user_id = auth()->id();
            $amount  = $request->amt;
            if ($request->payment_from == 'cart') {
                $time = $request->order_number;
                Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'cart',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            } elseif ($request->payment_from == 'wallet') {
                Payment::create([
                    'amount' => $amount,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'wallet',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            } elseif ($request->payment_from == 'subscription') {
                Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => round($amount, 2),
                    'type' => 'subscription',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            } elseif ($request->payment_from == 'tip') {
                $time = time();
                Payment::create([
                    'amount' => 0,
                    'transaction_id' =>  $time,
                    'balance_transaction' => $request->amt,
                    'type' => 'tip',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            } else if ($request->payment_from == 'pickup_delivery') {
                $time = $request->order_number;
                Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => $request->amt,
                    'type' => 'pickup_delivery',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->come_from ?? 'web',
                ]);
            }
            return $time;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }




    public function orderSuccessCartDetail($order)
    {
        try {
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id', $order->user_id)->select('id')->first();
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

            // send sms
            $this->sendOrderSuccessSMS($order);
        } catch (\Exception $e) {
            return true;
        }
        return true;
    }

    public function mobilePay(Request $request, $domain = '')
    {

        $message = '';
        $amount = $request->amount;
        $message = '';
        $amount = $request->amount;
        $user = auth()->user();
        $action = isset($request->action) ? $request->action : '';
        $params = '?amt=' . $amount . '&paymentfrom=' . $action . "&come_from=app&user_id=" . $user->id;
        if ($action == 'cart') {
            $params = $params . '&order_number=' . $request->order_number . '&app=1';
        } elseif ($action == 'wallet') {
            $params = $params . '&app=2&transaction_id=' . time();
        } elseif ($action == 'subscription') {
            $params = $params . '&app=3&subscription_id=' . $request->subscription_id;
        } elseif ($action == 'tip') {
            $params = $params . '&app=3&order_number=' . $request->order_number;
        }elseif ($action == 'pickup_delivery') {
            $params = $params . '&app=3&order_number=' . $request->order_number;
        }

        $url = url('payment/livees/api/' . $params);

        return $this->successResponse(($url));
    }

    public function createUserToken()
    {
        $user = auth()->user();
        $token1 = new Token();
        $token = $token1->make([
            'key' => 'royoorders-jwt',
            'issuer' => 'royoorders.com',
            'expiry' => strtotime('+1 month'),
            'issuedAt' => time(),
            'algorithm' => 'HS256',
        ])->get();
        $token1->setClaim('user_id', $user->id);
        $this->token = $token;
        $user->auth_token = $token;
        $user->save();
        return $user;
    }

    public function livee()
    {
        return view('backend.payment.liveePay');
    }
}
