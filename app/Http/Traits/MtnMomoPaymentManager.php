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
use PhpParser\Node\Expr\Cast\Array_;

trait MtnMomoPaymentManager
{

    protected static $_apiUrl;

    protected static $_referenceId;

    protected static $_apiKey;

    protected static $_subscriptionKey;

    protected static $_paymentOption;

    protected static $_client;

    protected static $_environment;

    protected static $_isSandbox;

    protected static $_header;

    protected static $_domain_name;

    protected static $_accessToken;

    protected static $_isConfigurationSet = false;

    protected static $_currency = 'EUR';

    public function __init($creatingApiKey = true)
    {
        if (self::$_paymentOption == null) {
            self::$_paymentOption = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'mtn_momo')
                ->where('status', 1)
                ->first();
        }
        if (self::$_paymentOption == null || empty(self::$_paymentOption))
            return false;

        if (self::$_client == null) {
            self::$_client = new Client();
        }

        if ((!empty(self::$_paymentOption) && self::$_paymentOption->test_mode == '1') || self::$_isSandbox == 'true') {
            self::$_apiUrl = 'https://sandbox.momodeveloper.mtn.com/v1_0/';
            self::$_environment = 'sandbox';
            self::$_isSandbox = true;
        } else {
            self::$_apiUrl = 'https://proxy.momoapi.mtn.com/collection/';
            self::$_environment = 'mtnuganda';
            self::$_isSandbox = false;
        }

        $credentials = json_decode(self::$_paymentOption->credentials);
        if (!empty($credentials) && !$creatingApiKey) {
            self::$_subscriptionKey = (isset($credentials->subscription_key)) ? $credentials->subscription_key : '';
            self::$_referenceId = (isset($credentials->reference_id)) ? $credentials->reference_id : '';
            self::$_apiKey = (isset($credentials->api_key)) ? $credentials->api_key : '';
            self::$_header = [
                'Authorization' => 'Basic ' . base64_encode(self::$_referenceId . ':' . self::$_apiKey),
                'Ocp-Apim-Subscription-Key' => self::$_subscriptionKey
            ];
            if (!self::$_isSandbox) {
                $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
                self::$_currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'EUR';
            }
            self::$_isConfigurationSet = true;
        }

        /* Set the callback URL */
        $url = url('/');
        self::$_domain_name = self::getDomainName($url);
    }

    private static function createRequest($method, $url, $acceptedStatus, $options)
    {
        try {
            $response = self::$_client->request($method, $url, $options);

            if (empty($response)) {
                return self::response(404, 'Resouce Not Found');
            }

            $code = $response->getStatusCode();
            switch ($code) {
                case $acceptedStatus:
                    return self::response($code, 'Success', $response);
                    break;
                case 400:
                    return self::response($code, 'There is a Problem with submitted data.', $response);
                    break;
                case 500:
                    return self::response($code, 'Internal Server Error', $response);
                    break;
                case 409:
                    return self::response($code, 'Something wrong with the keys', $response);
                    break;
                case 401:
                    return self::response($code, 'Unauthorized', $response);
                    break;
                default:
                    return self::response($code, json_decode($response->getBody()->getContents(), true), $response);
                    break;
            }
        } catch (\Exception $e) {
            return self::response($e->getCode(), $e->getMessage());
        }
    }


    public static function createApiUser()
    {
        /* Check if payment option avaiable for MOMO API */
        if (empty(self::$_paymentOption)) {
            self::response(404, 'Creadentials for Momo API not found.');
        }

        self::$_header = [
            'X-Reference-Id' => self::$_referenceId,
            'Ocp-Apim-Subscription-Key' => self::$_subscriptionKey,
            'Content-Type' => 'application/json'
        ];

        $params = [
            'providerCallbackHost' => self::$_domain_name
        ];

        $response = self::createRequest('POST', self::$_apiUrl . 'apiuser', 201, [
            'headers' => self::$_header,
            'body' => json_encode($params)
        ]);
        return $response;
    }

    public static function createApiKey()
    {
        $response = self::createRequest('POST', self::$_apiUrl . 'apiuser/' . self::$_referenceId . '/apikey', 201, [
            'headers' => self::$_header
        ]);

        if ($response['status'] == 201) {
            $response = json_decode($response['response']->getBody()->getContents(), true);
            return [
                'status' => 201,
                'apiKey' => $response['apiKey']
            ];
        } else {
            return $response;
        }
    }

    public static function GenerateAccressToken()
    {
        if (self::$_isSandbox) {
            $url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';
        } else {
            $url = 'https://proxy.momoapi.mtn.com/collection/token/';
        }
        $response = self::createRequest('POST', $url, 200, [
            'headers' => self::$_header
        ]);
        if ($response['status'] == 200) {
            $response = json_decode($response['response']->getBody()->getContents(), true);
            self::$_accessToken = $response['access_token'];
        }
        return true;
    }

    public function RequestToPay($token, $data)
    {
        $subsid = '';
        $reloadRoute = '';
        switch ($data['from']) {
            case 'cart':
                $amount = $data['amt'];
                $from = $data['from'];
                $order_number = $data['order_number'];
                if ($data['environment'] != 'app') {
                    $reloadRoute = route('order.return.success');
                }
                break;
            case 'pickup_delivery':
                $amount = $data['amt'];
                $from = $data['from'];
                $order_number = $data['order_number'];
                $reloadRoute = $data['reload_route'];
                break;
            case 'wallet':
                $amount = $data['amt'];
                $from = $data['from'];
                $order_number = 'wallet';
                if ($data['environment'] != 'app') {
                    $reloadRoute = route('user.wallet');
                }
                break;
            case 'subscription':
                $amount = $data['amt'];
                $from = $data['from'];
                $subsid = $data['subsid'];
                $order_number = 'subscription';
                if ($data['environment'] != 'app') {
                    $reloadRoute =  route('user.subscription.plans');
                }
                break;
            case 'tip':
                $amount = $data['amt'];
                $from = $data['from'];
                $order_number = $data['order_number'];
                if ($data['environment'] != 'app') {
                    $reloadRoute = route('user.orders');
                }
                break;
        }
        $user = Auth::user();

        $partyId = $user->dial_code . $user->phone_number;

        /* Generate new reference ID for each new request to pay api call */
        $response = self::$_client->get('https://www.uuidgenerator.net/api/version4');

        if (empty($response)) {
            self::response(404, 'Resouce Not Found');
        }

        self::$_referenceId = $response->getBody()->getContents();

        if (self::$_isSandbox) {
            $url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';
        } else {
            $url = 'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay';
        }

        $headers = [
            'X-Reference-Id' => self::$_referenceId,
            'X-Target-Environment' => self::$_environment,
            'Ocp-Apim-Subscription-Key' => self::$_subscriptionKey,
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'X-Callback-Url' => (self::$_isSandbox) ? route('payment.webhook.mtn', []) : url('/payment/webhook/mtn')
            // 'X-Callback-Url' => 'https://webhook.site/8d253f63-1db2-4795-a41c-4dc24902a989'
        ];

        $env = $data['environment'];
        $params = [
            'amount' => $amount,
            'currency' => self::$_currency,
            'externalId' => $data['transaction_id'],
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $partyId
            ],
            'payerMessage' => "Paying for Driver tester code",
            'payeeNote' => "$env,$from,$order_number,$reloadRoute,$subsid"
        ];

        // Request to Pay 
        $response = self::createRequest('POST', $url, 202, [
            'headers' => $headers,
            'body' => json_encode($params)
        ]);

        return $response;
    }

    public static function getTransactionStatus($referenceId)
    {
        $headers = [
            'X-Target-Environment' => self::$_environment,
            'Ocp-Apim-Subscription-Key' => self::$_subscriptionKey,
            'Authorization' => 'Bearer ' . self::$_accessToken,
            'Content-Type' => 'application/json'
        ];

        if (self::$_isSandbox) {
            $url = "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/$referenceId";
        } else {
            $url = "https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay/$referenceId";
        }


        // Get Transaction Status
        $response = self::createRequest('GET', $url, 200, [
            'headers' => $headers
        ]);

        $result = json_decode($response['response']->getBody()->getContents(), true);
        return $result;
    }

    private static function getDomainName($url)
    {
        $disallowed = array(
            'http://',
            'https://'
        );
        foreach ($disallowed as $d) {
            if (strpos($url, $d) === 0) {
                return str_replace($d, '', $url);
            }
        }
        return $url;
    }

    private static function response($code, $message, $response = null)
    {
        return [
            'status' => $code,
            'message' => $message,
            'response' => $response
        ];
    }

    public static function sucessPayment($request, $transactionId)
    {
        $user = Auth::user();

        if ($request['from'] == 'cart') {
            $order_number = $request['order_number'];
            $order = Order::with([
                'paymentOption',
                'user_vendor',
                'vendors:id,order_id,vendor_id'
            ])->where('order_number', $order_number)->first();
            if ($order) {
                $order->payment_status = 1;
                $order->save();

                Payment::where('transaction_id', $transactionId)->update([
                    'date' => date('Y-m-d'),
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'balance_transaction' => $request['amt'],
                    'type' => 'cart',
                ]);

                // Auto accept order
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
                // send success sms
                $orderController->sendSuccessSMS($request, $order);
                // Send Notification
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

                if ($request['environment'] == 'app') {
                    $returnUrl = url('payment/gateway/returnResponse') . '/?gateway=mtn_momo' . '&status=200&transaction_id=' . $transactionId . '&order=' . $order_number;
                } else {
                    $returnUrl = route('order.return.success');
                }

                return $returnUrl;
            }
        } elseif ($request['from'] == 'wallet') {
            $request['wallet_amount'] = $request['amt'];
            $request['transaction_id'] = $transactionId;

            $newRequest = new \Illuminate\Http\Request($request);

            $walletController = new WalletController();
            $walletController->creditWallet($newRequest);
            if ($request['environment'] == 'app') {
                return url('payment/gateway/returnResponse') . '/?gateway=mtn_momo' . '&status=200&transaction_id=' . $transactionId;
            } else {
                return route('user.wallet');
            }
        } elseif ($request['from'] == 'tip') {

            $request['tip_amount'] = $request['amt'];
            $request['order_number'] = $request['order_number'];
            $request['transaction_id'] = $transactionId;
            $newRequest = new \Illuminate\Http\Request($request);

            $orderController = new OrderController();
            $orderController->tipAfterOrder($newRequest);
            if ($request['environment'] == 'app') {
                $returnUrl = url('payment/gateway/returnResponse') . '/?gateway=mtn_momo' . '&status=200&transaction_id=' . $transactionId;
            } else {
                $returnUrl = route('user.orders');
            }
            return $returnUrl;
        } elseif ($request['from'] == 'subscription') {
            $request['transaction_id'] = $transactionId;
            $request['payment_option_id'] = 48;
            // $request['subsid'] = $request['subsid'];
            $request['subscription_id'] = $request['subsid'];
            $request['amount'] = $request['amt'];

            $newRequest = new \Illuminate\Http\Request($request);

            $subscriptionController = new UserSubscriptionController();
            $subscriptionController->purchaseSubscriptionPlan($newRequest, $newRequest->subscription_id);
            if ($request['environment'] == 'app') {
                $returnUrl = url('payment/gateway/returnResponse') . '/?gateway=mtn_momo' . '&status=200&transaction_id=' . $transactionId;
            } else {
                $returnUrl = route('user.subscription.plans');
            }
            return $returnUrl;
        } elseif ($request['from'] == 'pickup_delivery') {
            $data['payment_option_id'] = 48;
            $data['transaction_id'] = $transactionId;
            $data['amount'] = $request['amt'];
            $data['order_number'] = $request['order_number'];
            $data['reload_route'] = $request['reload_route'];
            $newRequest = new \Illuminate\Http\Request($data);
            if ($request['environment'] == 'app') {
                $plaseOrderForPickup = new V1PickupDeliveryController();
                $response = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($newRequest);
                return $response;
                // $returnUrl = url('payment/gateway/returnResponse') . '/?gateway=mtn_momo' . '&status=200&transaction_id=' . $transactionId;
            } else {
                $plaseOrderForPickup = new PickupDeliveryController();
                $response = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($newRequest);
                $returnUrl = $request['reload_route'];
            }
            return $returnUrl;
        }
        return route('order.return.success');
    }

    public static function orderNumber($request)
    {
        $time = '';
        $amt = $request->amt ?? $request->amount;
        if (isset($request->auth_token) && !empty($request->auth_token)) {
            $user = User::where('auth_token', $request->auth_token)->first();
            FacadesAuth::login($user);
        } else {
            $user = auth()->user();
        }
        $name = explode(' ', $user->name);
        $returnUrl = '';
        if ($request->from == 'cart') {
            $request->amt = $amt;
            $time = 'C_' . time() . $request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amt,
                'type' => 'cart',
                'user_id' => auth()->id(),
                'date' => date('Y-m-d')
            ]);
        } elseif ($request->from == 'pickup_delivery') {
            $request->amt = $amt;
            $time = 'P_' . time() . $request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amt,
                'type' => 'pickup_delivery',
                'date' => date('Y-m-d'),
                'user_id' => auth()->id(),
                'payment_from' => $request->device ?? 'web'
            ]);
        } elseif ($request->from == 'wallet') {
            $time = ($request->transaction_id) ?? 'W_' . time();
            // Save transaction before payment success for get information only
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amt,
                'type' => 'wallet',
                'user_id' => auth()->id(),
                'date' => date('Y-m-d')
            ]);
            $request->amt = $amt;
        } elseif ($request->from == 'tip') {
            $time = 'T_' . time() . '_' . $request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amt,
                'type' => 'tip',
                'user_id' => auth()->id(),
                'date' => date('Y-m-d')
            ]);

            $request->amt = $amt;
        } elseif ($request->from == 'subscription') {
            $time = 'S_' . time() . '_' . (!empty($request->subsid) ? $request->subsid : $request->subscription_id);
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amt,
                'type' => 'subscription',
                'user_id' => auth()->id(),
                'date' => date('Y-m-d')
            ]);
            $request->amt = $amt;
        }
        $request->request->add([
            'amt' => number_format($amt, 2)
        ]);
        return $time;
    }

    private static function getSandboxResponse($referenceId, $request, $data)
    {
        $response = self::getTransactionStatus($referenceId);
        if (!empty($response) && !empty($response['status']) && ($response['status'] == 'SUCCESSFUL')) {
            $response =  self::sucessPayment($data, $data['transaction_id']);
            if ($response) {
                if ($data['environment'] == 'app' && $data['from'] == 'pickup_delivery') {
                    return response()->json([
                        'status' => 'SUCCESSFUL',
                        'message' => 'Payment Successful',
                        'data' => $response
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'SUCCESSFUL',
                        'message' => 'Payment Successful',
                        'url' => $response
                    ], 200);
                }
            }
        } else {
            $message = 'Payment Failed';
            if (!empty($response['reason'])) {
                if (is_array($response['reason'])) {
                    $message = $response['reason']['message'];
                } else {
                    $message = $response['reason'];
                    $currency = self::$_currency;
                    $amount = $request->amt ?? $request->amount;
                    switch ($message) {
                        case 'APPROVAL_REJECTED';
                            $message = "Payment request of $currency $amount has been Rejected";
                            break;
                        case 'INTERNAL_PROCESSING_ERROR':
                            $message = "Payment request of $currency $amount has been Failed.";
                            break;
                        case 'EXPIRED':
                            $message = "Payment request of $currency $amount has been Expired.";
                            break;
                        default:
                            $message = "Payment Request of $currency $amount has been Failed.";
                            break;
                    }
                }
            }
            return response()->json([
                'status' => 'PAYMENT FAILED',
                'message' => $message,
                'response' => $response
            ], 500);
        }
    }
    //For Production
    public static function paymentResponse(Request $request)
    {
        $payment = Payment::where('transaction_id', $request->transaction_id)->first();
        if ($payment && !empty($payment->payment_detail)) {
            $response = json_decode($payment->payment_detail, true);
            $details = explode(',', $response['payeeNote']);
            $env = $details[0] ?? '';
            $from = $details[1] ?? '';
            $order_number = $details[2] ?? '';
            $route = $details[3] ?? '';
            $subsId = $details[4] ?? '';
            if (!empty($response) && !empty($response['status']) && ($response['status'] == 'SUCCESSFUL')) {
                $order = Order::where('order_number', $order_number)->first();
                switch ($from) {
                    case 'cart':
                        Session::put('success', 'Order placed successfully');
                        break;
                    case 'pickup_delivery':
                        Session::put('success', 'Payment has been processed successfully');
                        break;
                    case 'wallet':
                        Session::put('success', 'Wallet has been credited successfully');
                        break;
                    case 'subscription':
                        Session::put('success', 'Wallet has been buyed successfully');
                        break;
                    case 'tip':
                        Session::put('success', 'Tip has been added successfully');
                        break;
                }
                return response()->json([
                    'status' => 'SUCCESSFUL',
                    'message' => 'Payment Successful',
                    'url' => $route,
                    'order_number' => $order_number,
                    'order_id' => $order->id ?? ''
                ], 200);
            } else {
                $message = 'Payment Failed';
                if (!empty($response['reason'])) {
                    if (is_array($response['reason'])) {
                        $message = $response['reason']['message'];
                    } else {
                        $message = $response['reason'];
                        $currency = self::$_currency;
                        $amount = $request->amt ?? $request->amount;
                        switch ($message) {
                            case 'APPROVAL_REJECTED';
                                $message = "Payment request of $currency $amount has been Rejected";
                                break;
                            case 'INTERNAL_PROCESSING_ERROR':
                                $message = "Payment request of $currency $amount has been Failed.";
                                break;
                            case 'EXPIRED':
                                $message = "Payment request of $currency $amount has been Expired.";
                                break;
                            case 'COULD_NOT_PERFORM_TRANSACTION':
                                $message = "Payment request of $currency $amount has been Expired or wasn't performed.";
                                break;
                            default:
                                $message = "Payment Request of $currency $amount has been Failed.";
                                break;
                        }
                    }
                }
                return response()->json([
                    'status' => 'PAYMENT FAILED',
                    'message' => $message,
                    'response' => $response
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'PAYMENT PENDING',
                'message' => 'Payment is Pending',
            ], 200);
        }
    }

    // public function createPaymentpage($data,$user,$address = null)
    // {
    // if(is_null($address))
    // {
    // $address = (object)[];
    // }
    // $order_number = isset($data['order_number']) ? $data['order_number'] : "";
    // $pay = paypage::sendPaymentCode('all')
    // ->sendTransaction('Auth')
    // ->sendCart(mt_rand(10000000,99999999),(int)$data['amount'],'test1')
    // // ->sendCustomerDetails('Walaa Elsaeed', 'w.elsaeed@paytabs.com', '0101111111', 'test', 'Nasr City', 'Cairo', 'EG', '1234','100.279.20.10')
    // ->sendCustomerDetails($user->name??'', $user->email??'', '0101111111', $address->address??'', $address->city??'', $address->state??'', $address->country_code??'', $address->pincode??'','100.279.20.11')
    // ->sendShippingDetails('same as billing')
    // ->sendURLs(route('payment.paytab.return',['amount' => (int)$data['amount'], 'payment_from' => $data['payment_from'], 'come_from' => $data['come_from'], 'order_number' => $order_number,'auth_token'=>$user->auth_token]), route('payment.paytab.callback'))
    // // ->sendURLs('https://619a-112-196-88-218.ngrok.io/payment/paytab/return?amount='.(int)$data['amount'].'&payment_from='.$data['payment_from'].'&come_from='.$data['come_from'].'&order_number='.$order_number.'&auth_token='.$user->auth_token, 'https://619a-112-196-88-218.ngrok.io/payment/paytab/callback')
    // ->sendLanguage('en')
    // ->create_pay_page();
    // return $pay;
    // }
    // public function capturePayment($data)
    // {
    // return Paypage::capture($data['tranRef'],$data['cartId'],(int)$data['amount'],$data['description']);
    // }
}
