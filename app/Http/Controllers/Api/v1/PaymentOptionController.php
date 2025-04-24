<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Front\HitpayController;
use DB;
use Auth;
use Carbon\Carbon;
use GuzzleHttp\Client as GCLIENT;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use App\Models\PaymentOption;
use Omnipay\Common\CreditCard;
use App\Http\Traits\{ApiResponser, PaymentTrait};
use App\Http\Controllers\Api\v1\{BaseController, VnpayController, StripeGatewayController, PaystackGatewayController, PayfastGatewayController, MobbexGatewayController, YocoGatewayController, RazorpayGatewayController, SimplifyGatewayController, SquareGatewayController, PagarmeGatewayController, CheckoutGatewayController, EasebuzzController, MyCashGatewayController, OpenpayPaymentController, UseRedePaymentController, UPayGatewayController, ConektaGatewayController, TelrGatewayController, KhaltiGatewayController, PlugnpayGatewayController};
use App\Http\Controllers\Front\DpoController;
use App\Http\Controllers\Front\CcavenueController;
use App\Http\Controllers\Front\KongapayController;
use App\Http\Controllers\Front\{CyberSourcePaymentController, MastercardPaymentController, MpesaController, OrangePaymentController, TotalpayController};
use App\Http\Controllers\Front\MvodafoneController;
use App\Http\Controllers\Front\NmiPaymentController;
use App\Http\Controllers\Front\OboPaymentController;
use App\Http\Controllers\Front\{PayphoneController, ThawaniPaymentController};
use App\Http\Controllers\Front\PowerTransPaymentController;
use App\Http\Controllers\Front\PesapalPaymentController;
use App\Http\Controllers\Front\SkipCashController;
use App\Http\Controllers\Front\ToyyibPayController;
use App\Http\Controllers\Front\VivawalletController;
use App\Http\Controllers\Front\WindcaveController;
use App\Http\Controllers\LiveePaymentController;
use App\Http\Requests\OrderStoreRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\{Order, OrderProduct, Cart, CartAddon, CartProduct, Product, OrderProductAddon, Client, ClientPreference, ClientCurrency, OrderVendor, UserAddress, CartCoupon, CartDeliveryFee, CartProductPrescription, VendorOrderStatus, OrderStatusOption, Vendor, LoyaltyCard, OrderProductPrescription, OrderTax, User, Payment, Transaction, UserVendor};
use App\Http\Controllers\Front\MpesaSafariController;
use Exception;

class PaymentOptionController extends BaseController
{
    use ApiResponser, PaymentTrait;
    public $gateway;

    public function getPaymentOptions(Request $request, $page = '')
    {
        $code = $this->paymentOptionArray($page);
        $getAdditionalPreference = getAdditionalPreference(['advance_booking_amount', 'advance_booking_amount_percentage']);
        if ($request->service_type == 'takeaway' && !empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101)) {
            $payment_options = PaymentOption::whereIn('code', $code)->where('status', 1)->where('id', '!=', 1)->get(['id', 'code', 'credentials', 'title', 'off_site']);
        } else {
            //Till here
            $payment_options = PaymentOption::whereIn('code', $code)->where('status', 1)->get(['id', 'code', 'credentials', 'title', 'off_site']);
        }
        foreach ($payment_options as $option) {
            if ($option->code == 'stripe') {
                $option->title = __('Credit/Debit Card (Stripe)');
            } elseif ($option->code == 'kongapay') {
                $option->title = 'Pay Now';
            } elseif ($option->code == 'mvodafone') {
                $option->title = 'Vodafone M-PAiSA';
            } elseif ($option->code == 'mobbex') {
                $option->title = __('Mobbex');
            } elseif ($option->code == 'offline_manual') {
                $json = json_decode($option->credentials);
                $option->title = $json->manule_payment_title;
            } elseif ($option->code == 'mycash') {
                $option->title = __('Digicel MyCash');
            } elseif ($option->code == 'windcave') {
                $option->title = __('Windcave (Debit/Credit card)');
            } elseif ($option->code == 'stripe_ideal') {
                $option->title = __('iDEAL');
            } elseif ($option->code == 'authorize_net') {
                $option->title = __('Credit/Debit Card');
            } elseif ($option->code == 'obo') {
                $option->title = __("O'Pay");
            } elseif ($option->code == 'livee') {
                $option->title = __("livees");
            } elseif ($option->code == 'totalpay') {
                $option->title = __('Total Pay');
            } elseif ($option->code == 'thawani') {
                $option->title = __('Thawani Payment');
            } else if ($option->code == 'hitpay') {
                $option->title = __('Hitpay');
            }
            $option->title = __($option->title);
        }
        return $this->successResponse($payment_options, '', 201);
    }

    public function postPayment(Request $request, $gateway = '')
    {
        if (!empty($gateway)) {
            $code = $request->header('code');
            $client = Client::where('code', $code)->first();
            $domain = '';
            if (!empty($client->custom_domain)) {
                $domain = $client->custom_domain;
            } else {
                $domain = $client->sub_domain . env('SUBMAINDOMAIN');
            }


            $function = 'postPaymentVia_' . $gateway;


            if (method_exists($this, $function)) {
                if (!empty($request->action)) {
                    $response = $this->$function($request); // call related gateway for payment processing
                    return $response;
                }
            } else {
                return $this->errorResponse("Invalid Gateway Request", 400);
            }
        } else {
            return $this->errorResponse("Invalid Gateway Request", 400);
        }
    }

    public function postPaymentVia_cyber_source(Request $request)
    {
        $gateway = new CyberSourcePaymentController();
        return $gateway->cyberSourcePurchase($request);
    }
    public function postPaymentVia_orange_pay(Request $request)
    {
        $gateway = new OrangePaymentController();
        return $gateway->orangePayPurchase($request);
    }

    public function postPaymentVia_mastercard(Request $request) {
        $gateway = new MastercardPaymentController($request);
        $request->request->add([
            'payment_from' => $request->action,
            'come_from'    => 'app',
        ]);
        return $gateway->createSession($request);
    }

    public function postPaymentVia_livee(Request $request)
    {

        $gateway = new LiveePaymentController();
        return $gateway->mobilePay($request);
    }
    public function postPaymentVia_obo(Request $request)
    {
        $gateway = new OboPaymentController();
        return $gateway->mobilePay($request);
    }



    public function postPaymentVia_skip_cash(Request $request)
    {
        $gateway = new SkipCashController();
        return $gateway->mobilePay($request);
    }

    public function postPaymentVia_nmi(Request $request)
    {
        $gateway = new NmiPaymentController();
        return $gateway->mobilePay($request);
    }

    public function postPaymentVia_azul(Request $request)
    {
        $gateway = new AzulPaymentController();
        return $gateway->beforePayment($request);
    }

    public function postPaymentVia_dpo(Request $request)
    {
        $gateway = new DpoController();
        return $gateway->createAppTocken($request);
    }

    public function postPaymentVia_mycash(Request $request)
    {
        $gateway = new MyCashGatewayController();
        return $gateway->purchase($request);
    }
    public function postPaymentVia_totalpay(Request $request)
    {
        $gateway = new TotalpayController();
        return $gateway->makePayment($request);
    }
    public function postPaymentVia_mpesasafari(Request $request)
    {
        $gateway = new MpesaSafariController();
        return $gateway->createPayment($request);
    }

    public function postPaymentVia_ccavenue(Request $request)
    {
        $gateway = new CcavenueController();
        return $gateway->CcavenuePurchase($request);
    }

    public function postPaymentVia_kongapay(Request $request)
    {
        $gateway = new KongapayController();
        return $gateway->kongapayPurchase($request);
    }

    public function postPaymentVia_stripe(Request $request)
    {
        $gateway = new StripeGatewayController();
        return $gateway->stripePurchase($request);
    }

    public function postPaymentVia_stripe_fpx(Request $request)
    {
        $gateway = new StripeGatewayController();
        return $gateway->paymentWebViewStripeFPX($request);
    }

    public function postPaymentVia_stripe_ideal(Request $request)
    {
        $gateway = new StripeGatewayController();
        return $gateway->paymentWebViewStripeIdeal($request);
    }

    public function postPaymentVia_stripe_oxxo(Request $request)
    {
        $gateway = new StripeGatewayController();
        return $gateway->paymentWebViewStripeOXXO($request);
    }

    public function postPaymentVia_paystack(Request $request)
    {
        $gateway = new PaystackGatewayController();
        return $gateway->paystackPurchase($request);
    }

    public function postPaymentVia_payfast(Request $request)
    {
        $gateway = new PayfastGatewayController();
        return $gateway->payfastPurchase($request);
    }

    public function postPaymentVia_mobbex(Request $request)
    {
        $gateway = new MobbexGatewayController();
        return $gateway->mobbexPurchase($request);
    }

    public function postPaymentVia_yoco(Request $request)
    {
        $gateway = new YocoGatewayController();
        return $gateway->yocoWebview($request);
    }

    public function postPaymentVia_paylink(Request $request)
    {
        $gateway = new PaylinkGatewayController();
        return $gateway->paylinkPurchase($request);
    }

    public function postPaymentVia_razorpay(Request $request)
    {
        $gateway = new RazorpayGatewayController();
        return $gateway->razorpayPurchase($request);
    }

    public function postPaymentVia_simplify(Request $request)
    {
        $gateway = new SimplifyGatewayController();
        return $gateway->simplifyPurchase($request);
    }
    public function postPaymentVia_square(Request $request)
    {
        $gateway = new SquareGatewayController();
        return $gateway->squarePurchase($request);
    }
    public function postPaymentVia_pagarme(Request $request)
    {
        $gateway = new PagarmeGatewayController();
        return $gateway->pagarmePurchase($request);
    }
    public function postPaymentVia_upay(Request $request)
    {
        $gateway = new UPayGatewayController();
        return $gateway->upayPurchase($request);
    }
    public function postPaymentVia_conekta(Request $request)
    {
        $gateway = new ConektaGatewayController();
        return $gateway->conektaPurchase($request);
    }
    public function postPaymentVia_telr(Request $request)
    {
        $gateway = new TelrGatewayController();
        return $gateway->telrPurchase($request);
    }

    public function postPaymentVia_checkout(Request $request)
    {
        $gateway = new CheckoutGatewayController();
        return $gateway->checkoutPurchase($request);
    }
    public function postPaymentVia_authorize_net(Request $request)
    {
        $gateway = new AuthorizeGatewayController();
        return $gateway->authorizePurchase($request);
    }

    public function postPaymentVia_cashfree(Request $request)
    {
        $gateway = new CashfreeGatewayController();
        return $gateway->createOrder($request);
    }
    public function postPaymentVia_easebuzz(Request $request)
    {
        $gateway = new EasebuzzController();
        return $gateway->order($request);
    }

    public function postPaymentVia_windcave(Request $request)
    {
        $gateway = new WindcaveController();
        return $gateway->createHashApp($request);
    }

    public function postPaymentVia_viva_wallet(Request $request)
    {
        $gateway = new VivawalletController();
        return $gateway->createPayLinkApp($request);
    }

    public function postPaymentVia_payphone(Request $request)
    {
        $gateway = new PayphoneController();
        return $gateway->createHashApp($request);
    }

    public function postPaymentVia_mvodafone(Request $request)
    {
        $gateway = new MvodafoneController();
        return $gateway->createPayLinkApp($request);
    }

    public function postPaymentVia_toyyibpay(Request $request)
    {

        //for getting server main url from header
        $code = $request->header('code');
        $client = Client::where('code', $code)->first();
        $domain = '';
        if (!empty($client->custom_domain)) {
            $domain = $client->custom_domain;
        } else {
            $domain = $client->sub_domain . env('SUBMAINDOMAIN');
        }
        $server_url = "https://" . $domain . "/";
        $request['serverUrl'] = $server_url;
        $request['currencyId'] = $request->header('currency');
        $request['auth_token'] = $request->header('authorization') ?? "";

        $gateway = new ToyyibPayController();
        return $gateway->orderForApp($request);
    }

    public function postPaymentVia_vnpay(Request $request)
    {
        $gateway = new VnpayController();
        return $gateway->order($request);
    }

    public function postPaymentVia_openpay(Request $request)
    {
        $gateway = new OpenpayPaymentController();
        return $gateway->beforePayment($request);
    }
    public function postPaymentVia_userede(Request $request)
    {
        $gateway = new UseRedePaymentController();
        return $gateway->beforePayment($request);
    }

    public function postPaymentVia_khalti(Request $request)
    {
        $gateway = new KhaltiGatewayController();
        return $gateway->khaltiPurchase($request);
    }

    public function postPaymentVia_powertrans(Request $request)
    {
        $gateway = new PowerTransPaymentController();
        return $gateway->payByPowerTrans($request);
    }

    public function postPaymentVia_plugnpay(Request $request)
    {

        $gateway = new PlugnpayGatewayController();

        return $gateway->PlugPayPurchase($request);
    }
    public function postPaymentVia_thawani(Request $request)
    {

        $gateway = new ThawaniPaymentController();

        return $gateway->paybythawanipg($request);
    }

    public function postPaymentVia_hitpay(Request $request)
    {
        $gateway = new HitpayController();
        return $gateway->makePayment($request);
    }

    public function postPaymentVia_paypal(Request $request)
    {
        try {
            $user = Auth::user();
            $paypal_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'paypal')->where('status', 1)->first();
            $creds_arr = json_decode($paypal_creds->credentials);
            $username = (isset($creds_arr->username)) ? $creds_arr->username : '';
            $password = (isset($creds_arr->password)) ? $creds_arr->password : '';
            $signature = (isset($creds_arr->signature)) ? $creds_arr->signature : '';
            $testmode = (isset($paypal_creds->test_mode) && ($paypal_creds->test_mode == '1')) ? true : false;
            $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            $currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
            $this->gateway = Omnipay::create('PayPal_Express');
            $this->gateway->setUsername($username);
            $this->gateway->setPassword($password);
            $this->gateway->setSignature($signature);
            $this->gateway->setTestMode($testmode); //set it to 'false' when go live

            $response = $this->gateway->purchase([
                'currency' => $currency, //'USD',
                'amount' => $this->getDollarCompareAmount($request->amount),
                'cancelUrl' => url($request->serverUrl . $request->cancelUrl),
                'returnUrl' => url('/payment/paypal/CompletePurchase?amount=' . $request->amount . '&order_number=' . $request->order_number . '&action=' . $request->action . '&come_from=' . $request->come_from)
            ])->send();

            if ($response->isSuccessful()) {
                return $this->successResponse($response->getData());
            } elseif ($response->isRedirect()) {
                $token = $response->getData();
                if(isset($token['TOKEN']) && $request->action=="pickup_delivery"){
                    $getOrder = Order::where('order_number',$request->order_number)->first();
                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->user_id = $user->id ?? null;
                    $payment->transaction_id = $token['TOKEN'];
                    $payment->payment_option_id = 3;
                    $payment->order_id = isset($getOrder)?$getOrder->id:$request->order_number;
                    $payment->balance_transaction = $request->amount?? '';
                    $payment->type = $request->action;
                    $payment->save();
                }
                return $this->successResponse($response->getRedirectUrl());
            } else {
                return $this->errorResponse($response->getMessage(), 400);
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function creditMyWallet(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $credit_amount = $request->amount;
            $wallet = $user->wallet;
            if ($credit_amount > 0) {
                $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> by transaction reference <b>' . $request->transaction_id . '</b>']);
                $transactions = Transaction::where('payable_id', $user->id)->get();
                $response['wallet_balance'] = $wallet->balanceFloat;
                $response['transactions'] = $transactions;
                $message = 'Wallet has been credited successfully';
                return $this->successResponse($response, $message, 201);
            } else {
                return $this->errorResponse('Amount is not sufficient', 402);
            }
        } else {
            return $this->errorResponse('Invalid User', 402);
        }
    }

    public function getDeliveryFeeDispatcher($vendor_id, $user_id)
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $customer = User::find($user_id);
                $cus_address = UserAddress::where('user_id', $user_id)->orderBy('is_primary', 'desc')->first();
                if ($cus_address) {
                    $tasks = array();
                    $vendor_details = Vendor::find($vendor_id);
                    $location[] = array(
                        'latitude' => $vendor_details->latitude ?? 30.71728880,
                        'longitude' => $vendor_details->longitude ?? 76.80350870
                    );
                    $location[] = array(
                        'latitude' => $cus_address->latitude ?? 30.717288800000,
                        'longitude' => $cus_address->longitude ?? 76.803508700000
                    );
                    $postdata =  ['locations' => $location];
                    $client = new GClient([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->delivery_service_key,
                            'shortcode' => $dispatch_domain->delivery_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                    $url = $dispatch_domain->delivery_service_key_url;
                    $res = $client->post(
                        $url . '/api/get-delivery-fee',
                        ['form_params' => ($postdata)]
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['message'] == 'success') {
                        return $response['total'];
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
    # check if last mile delivery on
    public function checkIfLastMileOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }
    public function postPlaceOrder(Request $request)
    {
        try {
            $total_amount = 0;
            $total_discount = 0;
            $taxable_amount = 0;
            $payable_amount = 0;
            $user = Auth::user();
            if ($user) {
                DB::beginTransaction();
                $loyalty_amount_saved = 0;
                $redeem_points_per_primary_currency = '';
                $loyalty_card = LoyaltyCard::where('status', '0')->first();
                if ($loyalty_card) {
                    $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
                }
                $client_preference = ClientPreference::first();
                if ($client_preference->verify_email == 1) {
                    if ($user->is_email_verified == 0) {
                        return response()->json(['error' => 'Your account is not verified.'], 404);
                    }
                }
                if ($client_preference->verify_phone == 1) {
                    if ($user->is_phone_verified == 0) {
                        return response()->json(['error' => 'Your phone is not verified.'], 404);
                    }
                }
                $user_address = UserAddress::where('id', $request->address_id)->first();
                if (!$user_address) {
                    return response()->json(['error' => 'Invalid address id.'], 404);
                }
                $cart = Cart::where('user_id', $user->id)->first();
                if ($cart) {
                    $loyalty_points_used;
                    $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
                    if ($order_loyalty_points_earned_detail) {
                        $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                        if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                            $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                        }
                    }
                    $order = new Order;
                    $order->user_id = $user->id;
                    $order->order_number = generateOrderNo();
                    $order->address_id = $request->address_id;
                    $order->payment_option_id = $request->payment_option_id;
                    $order->save();
                    $clientCurrency = ClientCurrency::where('currency_id', $request->currencyId)->first();
                    $cart_products = CartProduct::with('product.pimage', 'product.variants', 'product.taxCategory.taxRate', 'coupon', 'product.addon')->where('cart_id', $cart->id)->where('status', [0, 1])->where('cart_id', $cart->id)->orderBy('created_at', 'asc')->get();
                    $total_delivery_fee = 0;
                    foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                        $delivery_fee = 0;
                        $vendor_payable_amount = 0;
                        $vendor_discount_amount = 0;
                        $order_vendor = new OrderVendor;
                        $order_vendor->status = 0;
                        $order_vendor->user_id = $user->id;
                        $order_vendor->order_id = $order->id;
                        $order_vendor->vendor_id = $vendor_id;
                        $order_vendor->save();
                        foreach ($vendor_cart_products as $vendor_cart_product) {
                            $variant = $vendor_cart_product->product->variants->where('id', $vendor_cart_product->variant_id)->first();
                            $quantity_price = 0;
                            $divider = (empty($vendor_cart_product->doller_compare) || $vendor_cart_product->doller_compare < 0) ? 1 : $vendor_cart_product->doller_compare;
                            $price_in_currency = $variant->price / $divider;
                            $price_in_dollar_compare = $price_in_currency * $clientCurrency->doller_compare;
                            $quantity_price = $price_in_dollar_compare * $vendor_cart_product->quantity;
                            $payable_amount = $payable_amount + $quantity_price;
                            $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
                            $product_taxable_amount = 0;
                            $product_payable_amount = 0;
                            $vendor_taxable_amount = 0;
                            if ($vendor_cart_product->product['taxCategory']) {
                                foreach ($vendor_cart_product->product['taxCategory']['taxRate'] as $tax_rate_detail) {
                                    $rate = round($tax_rate_detail->tax_rate);
                                    $tax_amount = ($price_in_dollar_compare * $rate) / 100;
                                    $product_tax = $quantity_price * $rate / 100;
                                    $taxable_amount = $taxable_amount + $product_tax;
                                    $payable_amount = $payable_amount + $product_tax;
                                    $vendor_payable_amount = $vendor_payable_amount;
                                }
                            }
                            if (!empty($vendor_cart_product->product->Requires_last_mile) && $vendor_cart_product->product->Requires_last_mile == 1) {
                                $delivery_fee = $this->getDeliveryFeeDispatcher($vendor_cart_product->vendor_id, $user->id);
                            }
                            $vendor_taxable_amount += $taxable_amount;
                            $total_amount += $variant->price;
                            $order_product = new OrderProduct;
                            $order_product->order_vendor_id = $order_vendor->id;
                            $order_product->order_id = $order->id;
                            $order_product->price = $variant->price;
                            $order_product->quantity = $vendor_cart_product->quantity;
                            $order_product->vendor_id = $vendor_cart_product->vendor_id;
                            $order_product->product_id = $vendor_cart_product->product_id;
                            $order_product->created_by = $vendor_cart_product->created_by;
                            $order_product->variant_id = $vendor_cart_product->variant_id;
                            $order_product->product_name = $vendor_cart_product->product->sku;
                            if ($vendor_cart_product->product->pimage) {
                                $order_product->image = $vendor_cart_product->product->pimage->first() ? $vendor_cart_product->product->pimage->first()->path : '';
                            }
                            $order_product->save();
                            if (!empty($vendor_cart_product->addon)) {
                                foreach ($vendor_cart_product->addon as $ck => $addon) {
                                    $opt_quantity_price = 0;
                                    $opt_price_in_currency = $addon->option->price;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                    $opt_quantity_price = $opt_price_in_doller_compare * $order_product->quantity;
                                    $total_amount = $total_amount + $opt_quantity_price;
                                    $payable_amount = $payable_amount + $opt_quantity_price;
                                    $vendor_payable_amount = $vendor_payable_amount + $opt_quantity_price;
                                }
                            }
                            $cart_addons = CartAddon::where('cart_product_id', $vendor_cart_product->id)->get();
                            if ($cart_addons) {
                                foreach ($cart_addons as $cart_addon) {
                                    $orderAddon = new OrderProductAddon;
                                    $orderAddon->addon_id = $cart_addon->addon_id;
                                    $orderAddon->option_id = $cart_addon->option_id;
                                    $orderAddon->order_product_id = $order_product->id;
                                    $orderAddon->save();
                                }
                                CartAddon::where('cart_product_id', $vendor_cart_product->id)->delete();
                            }
                        }
                        $coupon_id = null;
                        $coupon_name = null;
                        $actual_amount = $vendor_payable_amount;
                        if ($vendor_cart_product->coupon) {
                            $coupon_id = $vendor_cart_product->coupon->promo->id;
                            $coupon_name = $vendor_cart_product->coupon->promo->name;
                            if ($vendor_cart_product->coupon->promo->promo_type_id == 2) {
                                $coupon_discount_amount = $vendor_cart_product->coupon->promo->amount;
                                $total_discount += $coupon_discount_amount;
                                $vendor_payable_amount -= $coupon_discount_amount;
                                $vendor_discount_amount += $coupon_discount_amount;
                            } else {
                                $coupon_discount_amount = ($quantity_price * $vendor_cart_product->coupon->promo->amount / 100);
                                $final_coupon_discount_amount = $coupon_discount_amount * $clientCurrency->doller_compare;
                                $total_discount += $final_coupon_discount_amount;
                                $vendor_payable_amount -= $final_coupon_discount_amount;
                                $vendor_discount_amount += $final_coupon_discount_amount;
                            }
                        }

                        $order_vendor->coupon_id = $coupon_id;
                        $order_vendor->coupon_code = $coupon_name;
                        $order_vendor->order_status_option_id = 1;
                        $order_vendor->subtotal_amount = $actual_amount;
                        $order_vendor->payable_amount = $vendor_payable_amount;
                        $order_vendor->taxable_amount = $vendor_taxable_amount;
                        $order_vendor->discount_amount = $vendor_discount_amount;
                        $order_vendor->payment_option_id = $request->payment_option_id;
                        $vendor_info = Vendor::where('id', $vendor_id)->first();
                        if ($vendor_info) {
                            if (($vendor_info->commission_percent) != null && $vendor_payable_amount > 0) {
                                $order_vendor->admin_commission_percentage_amount = round($vendor_info->commission_percent * ($vendor_payable_amount / 100), 2);
                            }
                            if (($vendor_info->commission_fixed_per_order) != null && $vendor_payable_amount > 0) {
                                $order_vendor->admin_commission_fixed_amount = $vendor_info->commission_fixed_per_order;
                            }
                        }
                        $order_vendor->save();
                        $order_status = new VendorOrderStatus();
                        $order_status->order_id = $order->id;
                        $order_status->vendor_id = $vendor_id;
                        $order_status->order_status_option_id = 1;
                        $order_status->order_vendor_id = $order_vendor->id;
                        $order_status->save();
                    }
                    $loyalty_points_earned = LoyaltyCard::getLoyaltyPoint($loyalty_points_used, $payable_amount);
                    $order->total_amount = $total_amount;
                    $order->total_discount = $total_discount;
                    $order->taxable_amount = $taxable_amount;
                    if ($loyalty_amount_saved > 0) {
                        if ($payable_amount < $loyalty_amount_saved) {
                            $loyalty_amount_saved =  $payable_amount;
                            $loyalty_points_used = $payable_amount * $redeem_points_per_primary_currency;
                        }
                    }
                    $order->total_delivery_fee = $total_delivery_fee;
                    $order->loyalty_points_used = $loyalty_points_used;
                    $order->loyalty_amount_saved = $loyalty_amount_saved;
                    $order->payable_amount = $delivery_fee + $payable_amount - $total_discount - $loyalty_amount_saved;
                    $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'];
                    $order->loyalty_membership_id = $loyalty_points_earned['loyalty_card_id'];
                    $order->save();
                    CartCoupon::where('cart_id', $cart->id)->delete();
                    CartProduct::where('cart_id', $cart->id)->delete();
                    if (($request->payment_option_id != 1) && ($request->payment_option_id != 2)) {
                        Payment::insert([
                            'date' => date('Y-m-d'),
                            'order_id' => $order->id,
                            'transaction_id' => $request->transaction_id,
                            'balance_transaction' => $order->payable_amount,
                            'type' => 'cart'
                        ]);
                    }


                    DB::commit();
                    return $this->successResponse($order, __('Order placed successfully.'), 201);
                }
            } else {
                return $this->errorResponse(['error' => __('Empty cart.')], 404);
            }
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function sdkResponsePayment(Request $request, $gateway = '')
    {
        if (!empty($gateway)) {
            $function = 'sdkPaymentVia_' . $gateway;
            if (method_exists($this, $function)) {
                if (!empty($request->action)) {
                    $response = $this->$function($request); // call related gateway for payment processing
                    return $response;
                }
            } else {
                return $this->errorResponse("Invalid Gateway Request", 400);
            }
        } else {
            return $this->errorResponse("Invalid Gateway Request", 400);
        }
    }


    public function sdkPaymentVia_flutterwave(Request $request)
    {
        try {
            $user = Auth::user();
            $transaction_id = $request->transaction_id;
            $request->amount = $request->amount;
            if ($request->action == 'cart') {
                $order_number = $request->order_number;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    $order->payment_status = 1;
                    $order->save();
                    $payment_exists = Payment::where('transaction_id', $transaction_id)->first();
                    if (!$payment_exists) {
                        $this->savePaymentCartDetails($request, $order, $user);
                    }
                }
            } elseif ($request->action == 'pickup_delivery') {
                $order_number = $request->order_number;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    $order->payment_status = 1;
                    $order->save();
                    $payment_exists = Payment::where('transaction_id', $transaction_id)->first();
                    if (!$payment_exists) {
                        $this->csavePaymentOrderPickup($request, $order);
                        $url = OrderVendor::where('order_id', $order->id)->select('dispatch_traking_url')->first();
                    }
                }
            } elseif ($request->action == 'wallet') {
                $this->savePaymentWalletDetails($request);
            } elseif ($request->action == 'tip') {
                $this->savePaymentTipDetails($request);
            } elseif ($request->action == 'subscription') {
                $request->request->add(['payment_option_id' => '30']);
                $this->savePaymentSubscriptionDetails($request);
            }
            return $this->successResponse(['dispatch_traking_url' => $url->dispatch_traking_url ?? ''], __('Payment completed successfully'), 200);
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function sdkFailedPayment(Request $request)
    {
        try {
            $user = Auth::user();
            if ($request->action == 'cart') {
                $order_number = $request->order_number;
                $order = Order::where('order_number', $order_number)->first();
                if ($order) {
                    $wallet_amount_used = $order->wallet_amount_used;
                    if ($wallet_amount_used > 0) {
                        $transaction = Transaction::where('type', 'deposit')->where('meta', 'LIKE', '%' . $order->order_number . '%')->first();
                        if (!$transaction) {
                            $wallet = $user->wallet;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>refunded</b> for cancellation of order <b>' . $order->order_number . '</b>']);
                        }
                    }
                }
            }
            return $this->errorResponse(__('Payment failed'), 400);
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    function savePaymentWalletDetails(Request $request)
    {
        $transaction_id = $request->transaction_id;
        $request->request->add(['wallet_amount' => $request->amount, 'transaction_id' => $transaction_id]);
        $walletController = new WalletController();
        $walletController->creditMyWallet($request);
        return true;
    }

    function savePaymentTipDetails(Request $request)
    {
        $transaction_id = $request->transaction_id;
        $request->request->add(['order_number' => $request->order_number, 'tip_amount' => $request->amount, 'transaction_id' => $transaction_id]);
        $orderController = new OrderController();
        $orderController->tipAfterOrder($request);
        return true;
    }

    function savePaymentSubscriptionDetails(Request $request)
    {
        $transaction_id = $request->transaction_id;
        $request->request->add(['payment_option_id' => $request->payment_option_id, 'transaction_id' => $transaction_id]);
        $subscriptionController = new UserSubscriptionController();
        $subscriptionController->purchaseSubscriptionPlan($request, $request->subscription_id);
        return true;
    }

    public function csavePaymentOrderPickup(Request $request, $order)
    {
        // $request->request->add(['order_number'=> $order->order_number, 'payment_option_id' => 30, 'amount' => $order->payable_amount, 'transaction_id' => $request->TransID]);
        $orderDeatils = (object) array('order_number' => $order->order_number, 'payment_option_id' => 30, 'amount' => $order->payable_amount, 'transaction_id' => $request->transaction_id);

        $plaseOrderForPickup = new PickupDeliveryController();
        $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($orderDeatils);
        return true;
    }

    function savePaymentCartDetails(Request $request, $order, $user)
    {
        $transaction_id = $request->transaction_id;
        $payment = new Payment();
        $payment->date = date('Y-m-d');
        $payment->order_id = $order->id;
        $payment->transaction_id = $transaction_id;
        $payment->balance_transaction = $request->amount;
        $payment->type = 'cart';
        $payment->save();

        // Auto accept order
        $orderController = new OrderController();
        $orderController->autoAcceptOrderIfOn($order->id);

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

        $request->request->add(['user_id' => $order->user_id, 'address_id' => $order->address_id]);
        //Send Email to customer
        $orderController->sendSuccessEmail($request, $order);
        //Send Email to Vendor
        foreach ($order->vendors->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
            $orderController->sendSuccessEmail($request, $order, $vendor_id);
        }
        //Send SMS to customer
        $orderController->sendSuccessSMS($request, $order);

        return true;
    }



    public function postPaymentVia_pesapal(Request $request)
    {
        $gateway = new PesapalPaymentController();
        $request->action ? $request->request->add([
            'payment_from' => $request->action,
        ]) : '';
        return $gateway->payByPesapal($request);
    }

    public function paystackCancelPurchase(Request $request)
    {

        try{
            if($request->action == 'cart'){
                $order_number = $request->order_number;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if(empty($order)){
                    return $this->errorResponse('Order Not Found', 404);
                }
                // If the transaction has been failed, we need to delete the order.
                $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                foreach ($order_products as $order_prod) {
                    $order_prod->delete();
                }
                $user = User::find($order->user_id);
                if($user){
                    if($order->wallet_amount_used > 0){
                        $wallet = $user->wallet;
                        $wallet->depositFloat($order->wallet_amount_used, ['Wallet has been <b>refunded</b> for payment failed of order #'. $order->order_number]);
                    }
                }
                OrderProduct::where('order_id', $order->id)->delete();
                OrderProductPrescription::where('order_id', $order->id)->delete();
                VendorOrderStatus::where('order_id', $order->id)->delete();
                OrderVendor::where('order_id', $order->id)->delete();
                OrderTax::where('order_id', $order->id)->delete();
                Order::where('id', $order->id)->delete();
            }
            return $this->errorResponse('Payment Failed', 500);
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
