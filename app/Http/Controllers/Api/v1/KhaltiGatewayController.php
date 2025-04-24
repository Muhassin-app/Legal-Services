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
use App\Http\Controllers\Api\v1\{BaseController, OrderController, WalletController};
use App\Models\Client as CP;
use App\Models\{PaymentOption, Client, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, UserDevice, UserVendor};

class KhaltiGatewayController extends BaseController
{
    use ApiResponser;
    public $API_KEY;
    public $API_SECRET_KEY;
    public $test_mode;
    public $api;

    public function __construct()
    {
        $khalti_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'khalti')->where('status', 1)->first();
        $creds_arr = json_decode($khalti_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $api_secret_key = (isset($creds_arr->api_secret_key)) ? $creds_arr->api_secret_key : '';
        $this->test_mode = (isset($khalti_creds->test_mode) && ($khalti_creds->test_mode == '1')) ? true : false;

        $this->API_KEY = $api_key;
        $this->API_SECRET_KEY = $api_secret_key;
        //$this->api = new Api($api_key, $api_secret_key);
    }

    public function khaltiPurchase(Request $request){
        $user = Auth::user();
        $amount = $request->amount;
        $action = isset($request->action) ? $request->action : '';
        $params = '?amount=' . $amount . '&auth_token='.$user->auth_token.'&action='.$action;
        if(($action == 'cart') || ($action == 'tip') || ($action == 'pickup_delivery')){
            $params = $params . '&order=' . $request->order_number;
        }
        elseif($action == 'subscription'){
            $params = $params . '&subscription_id=' . $request->subscription_id;
        }
        return $this->successResponse(url($request->serverUrl.'payment/webview/khalti'.$params));
    }

}
