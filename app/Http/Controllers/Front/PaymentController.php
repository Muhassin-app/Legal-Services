<?php

namespace App\Http\Controllers\Front;

use Auth;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use App\Models\{CaregoryKycDoc, Order, User, Cart, CartAddon, CartBookingOption, CartCoupon, CartDeliveryFee, ClientCurrency, CartProduct, CartProductPrescription, CartRentalProtection, Payment};
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Front\{FrontController, CashfreeGatewayController,EasebuzzController,VnpayController, PayUGatewayController, MyCashGatewayController,UseRedePaymentController,OpenpayPaymentController};
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends FrontController{

    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $user = Auth::user();
        $vendor_min_amount_errors = [];
        $cart = Cart::where('user_id', $user->id)->first();
        if($cart){
            $currency_id = Session::get('customerCurrency');
            $currency_symbol = Session::get('currencySymbol');
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $cart_products = CartProduct::with('vendor','product.pimage', 'product.variants', 'product.taxCategory.taxRate','coupon', 'product.addon')->where('cart_id', $cart->id)->where('status', [0,1])->where('cart_id', $cart->id)->orderBy('created_at', 'asc')->get();
            $dollar_compare = $clientCurrency ? $clientCurrency->doller_compare : 1;
            foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                $vendor_detail = [];
                $vendor_payable_amount = 0;
                $vendor_discount_amount = 0;
                foreach ($vendor_cart_products as $vendor_cart_product) {
                    $vendor_detail = $vendor_cart_product->vendor;
                    $variant = $vendor_cart_product->product->variants->where('id', $vendor_cart_product->variant_id)->first();
                    $quantity_price = 0;
                    $divider = $vendor_cart_product->doller_compare ? $vendor_cart_product->doller_compare : 1;
                    $price_in_currency = $variant->price / $divider;
                    $price_in_dollar_compare = $price_in_currency * $dollar_compare;
                    $quantity_price = $price_in_dollar_compare * $vendor_cart_product->quantity;
                    $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
                    $product_taxable_amount = 0;
                    $product_payable_amount = 0;
                    if(!empty($vendor_cart_product->addon)){
                        foreach ($vendor_cart_product->addon as $ck => $addon) {
                            $opt_quantity_price = 0;
                            $opt_price_in_currency = $addon->option->price??0;
                            $opt_price_in_doller_compare = $opt_price_in_currency * $dollar_compare;
                            $opt_quantity_price = $opt_price_in_doller_compare * $vendor_cart_product->quantity;
                            $vendor_payable_amount = $vendor_payable_amount + $opt_quantity_price;
                        }
                    }
                }
                // if($vendor_detail){
                //     if($vendor_detail->order_min_amount > 0){
                //         if($vendor_payable_amount < $vendor_detail->order_min_amount){
                //             $vendor_min_amount_errors[]= array(
                //                 'vendor_id' => $vendor_detail->id,
                //                 'message' => "Minimum order should be more than  $currency_symbol $vendor_detail->order_min_amount",
                //             );
                //         }
                //     }
                // }
            }
            // if(count($vendor_min_amount_errors) > 0){
            //     return $this->errorResponse($vendor_min_amount_errors, 402);
            // }
        }
        $checkCod = '';
        $codMinAmount = PaymentOption::select('credentials')->where('code','cod')->value('credentials');
        $cod = json_decode($codMinAmount);
        if(isset($cod->cod_min_amount) && ($cod->cod_min_amount>0 && $cart->payable_amount < $cod->cod_min_amount))
        {
            $checkCod = 'cod';
        }
        $ex_codes = ['cod'];
        //mohit sir branch code added by sohail
        $serviceType =  Session::get('vendorType');
        $getAdditionalPreference = getAdditionalPreference(['advance_booking_amount', 'advance_booking_amount_percentage','is_cod_payment','is_prepaid_payment']);
        if($serviceType == 'takeaway' && !empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101) ){

            $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->where('status', 1)->where('id', '!=', 1)->get();
        }else{

            $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->where('status', 1)->get();
        }

        if(@$getAdditionalPreference['is_cod_payment']==1 && @$getAdditionalPreference['is_prepaid_payment']==1){
            $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->where('status', 1)->get();
        }elseif(@$getAdditionalPreference['is_prepaid_payment']==1){
            $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->where('status', 1)->where('id', '!=', 1)->get();
        }elseif(@$getAdditionalPreference['is_cod_payment']==1){
            $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->where('status', 1)->where('id', '=', 1)->get();
        }
        //till here
        foreach ($payment_options as $k => $payment_option) {
            if(((in_array($payment_option->code, $ex_codes)) || (!empty($payment_option->credentials)))){
                $payment_option->slug = strtolower(str_replace(' ', '_', $payment_option->title));
                if($payment_option->code == 'stripe'){
                    $payment_option->title = 'Credit/Debit Card (Stripe)';
                }elseif($payment_option->code == 'kongapay'){
                    $payment_option->title = 'Pay Now';
                }elseif($payment_option->code == 'mvodafone'){
                    $payment_option->title = 'Vodafone M-PAiSA';
                }elseif($payment_option->code == 'mobbex'){
                    $payment_option->title = __('Mobbex');
                }
                elseif($payment_option->code == 'offline_manual'){
                    $json = json_decode($payment_option->credentials);
                    $payment_option->title = $json->manule_payment_title;
                }elseif($payment_option->code == 'mycash'){
                    $payment_option->title = __('Digicel MyCash');
                }elseif($payment_option->code == 'windcave'){
                    $payment_option->title = __('Windcave (Debit/Credit card)');
                }elseif($payment_option->code == 'stripe_ideal'){
                    $payment_option->title = __('iDEAL');
                }elseif($payment_option->code == 'authorize_net'){
                    $payment_option->title = __('Credit/Debit Card');
                }elseif($payment_option->code == 'obo'){
                    $payment_option->title = __("MoMo, Airtel Money by O'Pay");
                }elseif($payment_option->code == 'livee'){
                    $payment_option->title = __("Livees");
                }
                $payment_option->title = __($payment_option->title);
                unset($payment_option->credentials);
            }
            else{
                unset($payment_options[$k]);
            }
        }

        return $this->successResponse($payment_options);
    }

    public function paypalCompleteCheckout(Request $request, $domain = '', $token = '', $action = '', $address_id ='')
    {
        return view('frontend.account.complete-checkout')->with(['auth_token' => $token, 'action' => $action, 'address_id' => $address_id]);
    }

    public function paylinkCompleteCheckout(Request $request, $domain = '', $token = '', $action = '', $address_id ='')
    {
        return view('frontend.account.complete-checkout')->with(['auth_token' => $token, 'action' => $action, 'address_id' => $address_id]);
    }

    public function getCheckoutSuccess(Request $request, $domain = '', $id = '')
    {
        return view('frontend.account.checkout-success');
    }

    public function getGatewayReturnResponse(Request $request)
    { 

        return view('frontend.account.gatewayReturnResponse');
      
    }

    public function verifyPaymentOtp(Request $request, $domain='', $gateway)
    {
        if($gateway == 'mycash'){
            $data = $request->all();
            return view('frontend.payment_gatway.mycash_otp_verify', compact('data'));
        }
    }

    public function verifyPaymentOtpApp(Request $request, $domain='', $gateway)
    {
        if($gateway == 'mycash'){
            $data = $request->all();
            return view('frontend.payment_gatway.mycash_otp_verify', compact('data'));
        }
    }

    public function sendPaymentOtp(Request $request, $domain='', $gateway)
    {
        if(!empty($gateway)){
            $function = 'sendPaymentOtpVia_'.$gateway;
            if(method_exists($this, $function)) {
                if(!empty($request->payment_form)){
                    $response = $this->$function($request); // call related gateway for payment processing
                    return $response;
                }
            }
            else{
                return $this->errorResponse("Invalid Gateway Request", 400);
            }
        }else{
            return $this->errorResponse("Invalid Gateway Request", 400);
        }
    }

    public function sendPaymentOtpVia_mycash(Request $request){
        $gateway = new MyCashGatewayController();
        return $gateway->sendOtp($request);
    }

    public function verifyPaymentOtpSubmit(Request $request, $domain='', $gateway)
    {
        if(!empty($gateway)){
            $function = 'verifyPaymentOtpVia_'.$gateway;
            if(method_exists($this, $function)) {
                if(!empty($request->payment_form)){
                    $response = $this->$function($request); // call related gateway for payment processing
                    return $response;
                }
            }
            else{
                return $this->errorResponse("Invalid Gateway Request", 400);
            }
        }else{
            return $this->errorResponse("Invalid Gateway Request", 400);
        }
    }

    public function verifyPaymentOtpVia_mycash(Request $request){
        $gateway = new MyCashGatewayController();
        return $gateway->verifyOtp($request);
    }

    public function postPayment(Request $request, $domain='', $gateway = ''){
        if(!empty($gateway)){
            $function = 'postPaymentVia_'.$gateway;
            if(method_exists($this, $function)) {
                if(!empty($request->payment_form)){
                    $response = $this->$function($request); // call related gateway for payment processing
                    return $response;
                }
            }
            else{
                return $this->errorResponse("Invalid Gateway Request", 400);
            }
        }else{
            return $this->errorResponse("Invalid Gateway Request", 400);
        }
    }

    public function postPaymentVia_cashfree(Request $request){
        $gateway = new CashfreeGatewayController();
        return $gateway->createOrder($request);
    }
    public function postPaymentVia_easebuzz(Request $request){
        $gateway = new EasebuzzController();
        return $gateway->order($request);
    }

    public function postPaymentVia_vnpay(Request $request){
        $gateway = new VnpayController();
        return $gateway->order($request);
    }

    public function postPaymentVia_payu(Request $request){
        $gateway = new PayUGatewayController();
        return $gateway->purchase($request);
    }

    public function postPaymentVia_mycash(Request $request){
        $gateway = new MyCashGatewayController();
        return $gateway->purchase($request);
    }
    public function postPaymentVia_userede(Request $request){
        $gateway = new UseRedePaymentController();
        return $gateway->beforePayment($request);
    }
    public function postPaymentVia_openpay(Request $request){
        $gateway = new OpenpayPaymentController();
        return $gateway->beforePayment($request);
    }
}
