<?php

namespace App\Http\Controllers\Api\v1;

//use Log;
use WebhookCall;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yoco\YocoClient;
use Yoco\Exceptions\ApiKeyException;
use Yoco\Exceptions\DeclinedException;
use Yoco\Exceptions\InternalException;
use Illuminate\Support\Facades\Redirect;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Models\{User, UserVendor, ClientCurrency, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax};

class YocoGatewayController extends BaseController
{
    use ApiResponser;
    public $SECRET_KEY;
    public $PUBLIC_KEY;
    public $test_mode;
    public $currency;

    public function __construct()
    {
        $yoco_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'yoco')->where('status', 1)->first();
        $creds_arr = json_decode($yoco_creds->credentials);
        $secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $public_key = (isset($creds_arr->public_key)) ? $creds_arr->public_key : '';
        $this->test_mode = (isset($yoco_creds->test_mode) && ($yoco_creds->test_mode == '1')) ? true : false;
        $this->SECRET_KEY = $secret_key;
        $this->PUBLIC_KEY = $public_key;

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
    }

    public function yocoWebview(Request $request){
        $user = Auth::user();
        $amount = $request->amount;
        $amount = $this->getDollarCompareAmount($amount);
        $action = isset($request->action) ? $request->action : '';
        $params = '?amount=' . $amount . '&public_key_yoco=' . $this->PUBLIC_KEY.'&auth_token='.$user->auth_token.'&action='.$action;
        if(($action == 'cart') || ($action == 'tip')){
            $params = $params . '&order=' . $request->order_number;
        }
        return $this->successResponse(url($request->serverUrl.'payment/yoco-webview'.$params));
    }

    // public function yocoFunctionality(Request $request)
    // {
    //     try {
    //         $user = Auth::user();
    //         $token = $request->token;
    //         $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
    //         $amount = $this->getDollarCompareAmount($request->amount);
    //         $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_INT);


    //         // $returnUrlParams = '?gateway=yoco&order=' . $request->order_number;

    //         // $returnUrl = route('order.return.success');
    //         // if ($request->payment_form == 'wallet') {
    //         //     $returnUrl = route('user.wallet');
    //         // }

    //         $checkout_data = array(
    //             'token' => $token,
    //             'amountInCents' => $amount,
    //             'currency' => 'ZAR',
    //             'description' => 'Order Checkout',

    //             'reference' => $request->order_number,

    //             'redirect' => false,
    //             'test' => $this->test_mode, // True, testing, false, production

    //             'customer' => array(
    //                 'email' => $user->email,
    //                 'name' => $user->name,
    //                 // 'identification' => '12123123',
    //                 'cart_id' => $cart->id
    //             )
    //         );


    //         $ch = curl_init();

    //         curl_setopt($ch, CURLOPT_URL, "https://online.yoco.com/v1/charges/");
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_USERPWD, $this->SECRET_KEY . ":");
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($checkout_data));

    //         // send to yoco
    //         $result = curl_exec($ch);
    //        // Log::info($result);
    //         // return $result;
    //         $result = json_decode($result);


    //         if ($result->status == 'successful') {
    //             $this->yocoSuccess($request, $result);
    //             // $response = $this->mb->mobbex_checkout($checkout_data);
    //             return $this->successResponse(url( $request->serverUrl . 'payment/gateway/returnResponse?status=200&gateway=yoco&order=' . $request->order_number));
    //         } else {
    //             $this->yocoFail($request);
    //             return $this->errorResponse($result->status, 400);
    //         }
    //     } catch (\Exception $ex) {
    //         return $this->errorResponse($ex->getMessage(), 400);
    //     }
    // }

}
