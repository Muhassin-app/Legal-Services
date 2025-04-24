<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Traits\{ApiResponser, OrderTrait};
use App\Models\{Client, ClientCurrency, Order, Payment, PaymentOption, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Http};

class OboPaymentController extends Controller
{
    use ApiResponser, OrderTrait;

    public $currency;
    private $obo_business_name;
    private $obo_client_id;
    private $obo_key_id;
    private $obo_market_place_id;
    private $obo_company_reference;
    private $testMode;
    const TEST_MODE_TOKEN_API = 'https://www.obo-pay.co.rw/test/payments/v1/token';
    const TOKEN_API           = "https://www.obo-pay.co.rw/application/payments/v1/token";
    const TEST_MODE_URL_API   = "https://www.obo-pay.co.rw/test/payments/v1/payment";
    const URL_API             = "https://www.obo-pay.co.rw/application/payments/v1/payment";

    public function __construct()
    {
        $payOption = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'obo')->where('status', 1)->first();
        $credentials = json_decode($payOption->credentials);
        $this->obo_business_name     = $credentials->obo_business_name;
        $this->obo_client_id         = $credentials->obo_client_id;
        $this->obo_key_id            = $credentials->obo_key_id;
        $this->obo_market_place_id   = $credentials->obo_market_place_id;
        $this->obo_company_reference = $credentials->obo_company_reference;
        $this->testMode              = $payOption->test_mode;

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }



    public function beforePayment(Request $request, $domain = '', $app = '')
    {
        try {
            $client = Client::where('id', '>', 0)->select([
                'phone_number',
                'company_name'
            ])->first();

            $tokenData =  $this->token();
            if (isset($tokenData['httpStatus']) &&  $tokenData['httpStatus'] == "OK") {
                $token = $tokenData['token'];
                if (isset($token)) {
                    // user details
                    $user = auth()->user();
                    $userEmail       = $user->email;
                    $userPhone       = $user->dial_code . $user->phone_number;
                    $userFirstName   = strtok($user->name, " ");
                    $userLastName    = substr(strstr($user->name, " "), 1);

                    $orderNumber = $this->orderNumber($request);
                    if ($request->payment_from == 'cart') {
                        $urlParams   = "transactionid=$orderNumber&paymentfrom=cart&success=true";
                    } elseif ($request->payment_from == 'wallet') {
                        $urlParams   = "transactionid=$orderNumber&paymentfrom=wallet&payment_from=wallet&success=true";
                    } elseif ($request->payment_from == 'subscription') {
                        $urlParams   = "transactionid=$orderNumber&subscription_id=$request->subscription_id&amount=$request->amount&success=true";
                    } elseif ($request->payment_from == 'pickup_delivery') {
                        $urlParams   = "transactionid=$orderNumber&paymentfrom=pickup_delivery&reload_route=$request->reload_route&amount=$request->amount&success=true";
                    } elseif ($request->payment_from == 'tip') {
                        $urlParams   = "transactionid=$orderNumber&order_number=$request->order_number&paymentfrom=tip&amount=$request->amount&success=true";
                    }
                    if ($this->testMode == 1) {
                        $apiUrl = SELF::TEST_MODE_URL_API;
                    } else {
                        $apiUrl = SELF::URL_API;
                    }

                    $header = compact('token');
                    $input = json_encode([
                        //
                        "amount"           => $request->amount,
                        "currency"         => $this->currency,
                        "email"            => $userEmail,
                        "phone"            => $userPhone,
                        "reference_id"     => $orderNumber,
                        "first_name"       => $userFirstName,
                        "last_name"        => $userLastName,
                        "merchant"         => $this->obo_business_name,
                        "cancel_url"       => url(($request->cancelUrl) ?? ('after-payment/obo' . '?success=false')),
                        "return_url"       => url('after-payment/obo' . '?' . $urlParams),
                        "timeout_url"      => url($request->cancelUrl),
                        "custom_pg_id"     => $this->obo_market_place_id,
                        "companyname"      => $client->company_name,
                        "companycontact"   => $client->phone_number,
                        "companyreference" => $this->obo_company_reference,
                    ], JSON_UNESCAPED_SLASHES);
                    $responce = Http::withBody($input, 'application/json')->withHeaders($header)->post($apiUrl);
                    $responceData = json_decode($responce->body(), true);
                    if (isset($responceData['status']) && $responceData['status'] === "200") {
                        $redirectUrl =  $responceData['data']['url'];
                        return response()->json([
                            'status' => 'Success',
                            'data'   => $redirectUrl
                        ], 200);
                    } else {
                        return $this->errorResponse("Url Is Not Generated", 400);
                    }
                }
            } else {
                return $this->errorResponse('Token Error', 400);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function afterPayment(Request $request)
    {
        try {
            if ($request->has('success') && $request->success === "true") {
                $transactionId = $request->transactionid;
                $payment = Payment::where('transaction_id', $transactionId)->first();
                if ($payment) {
                    $payment->viva_order_id = $transactionId;
                    $payment->payment_option_id = 56;
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

                            $returnUrl = route('payment.gateway.return.response') . '/?gateway=obo' . '&status=200&order=' . $order->order_number;
                            return redirect($returnUrl);
                        }
                    }
                } elseif ($request->paymentfrom == 'wallet') {
                    if ($payment->payment_from == 'app') {
                        $user = User::findOrFail($payment->user_id);
                        Auth::login($user);
                        $returnUrl = route('payment.gateway.return.response') . '/?gateway=obo' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=wallet';
                    } else {
                        $user      = auth()->user();
                        $returnUrl = route('user.wallet');
                    }
                    $wallet  = $user->wallet;
                    $wallet->depositFloat($payment->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $payment->transaction_id . '</b>']);
                    return redirect($returnUrl);
                } elseif (isset($request->subscription_id)) {
                    $data['transaction_id'] = $payment->transaction_id;
                    $data['payment_option_id'] = 56;
                    $data['subsid'] = $request->subscription_id;
                    $data['subscription_id'] = $request->subscription_id;
                    $data['amount'] = $request->amount;
                    $request = new \Illuminate\Http\Request($data);
                    $subscriptionController = new UserSubscriptionController();
                    $subscriptionController->purchaseSubscriptionPlan($request, '', $request->subscription_id);
                    if ($payment->payment_from == 'web') {
                        return redirect()->route('user.subscription.plans');
                    } else {
                        $returnUrl = route('payment.gateway.return.response') . '/?gateway=obo' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=subscription';
                        return redirect($returnUrl);
                    }
                } elseif ($request->paymentfrom == 'pickup_delivery') {
                    $data['payment_option_id'] = 56;
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
                        $returnUrl = route('payment.gateway.return.response') . '/?gateway=obo' . '&status=200&order=' . $transactionId;
                        return redirect($returnUrl);
                    }
                } elseif ($request->paymentfrom == 'tip') {
                    $data['tip_amount'] = $request->amount;
                    $data['order_number'] = $request->order_number;
                    $data['transaction_id'] = $transactionId;
                    $request = new \Illuminate\Http\Request($data);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                    if ($payment->payment_from == 'web') {
                        return redirect()->route('user.orders');
                    } else {
                        $returnUrl = route('payment.gateway.return.response') . '/?gateway=ono' . '&status=200&order=' . $transactionId . '&action=tip';
                        return redirect($returnUrl);
                    }
                }
            } else {
                return "error";
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function token()
    {
        try {
            if ($this->testMode == 1) {
                $apiUrl = SELF::TEST_MODE_TOKEN_API;
            } else {
                $apiUrl = SELF::TOKEN_API;
            }
            $input = json_encode([
                "id" => $this->obo_client_id,
                "key" => $this->obo_key_id
            ]);
            $header = [
                'Content-Type' => 'application/json'
            ];
            $responce = Http::withBody($input, 'application/json')->withHeaders($header)->post($apiUrl);
            $data = json_decode($responce->body(),  true);
            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
            \Log::error($e->getMessage());
            \Log::error($e->getLine());
        }
    }

    public function orderNumber($request)
    {
        try {
            $time    = time();
            $user_id = auth()->id();
            $amount  = $request->amount;
            if ($request->payment_from == 'cart') {
                $time = $request->order_number;
                Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'cart',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->user_from ?? 'web'
                ]);
            } elseif ($request->payment_from == 'wallet') {
                Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'wallet',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->user_from ?? 'web'
                ]);
            } elseif ($request->payment_from == 'subscription') {
                Payment::create([
                    'amount' => 0,
                    'transaction_id' => $time,
                    'balance_transaction' => $amount,
                    'type' => 'subscription',
                    'date' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'payment_from' => $request->user_from ?? 'web'
                ]);
            }
            return $time;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function mobilePay(Request $request, $domain = '')
    {
        try {
            $request->request->add(['payment_from' => $request->action, 'from' => $request->action, 'amt' => $request->amount, 'subsid' => $request->subscription_id ?? '', 'user_from' => 'app']);
            $data =  $this->beforePayment($request, $domain, 'app');
            if (isset($data) && !empty($data)) {
                return $data;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
