<?php

namespace App\Http\Controllers\Api\v1; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\{BaseController, OrderController};
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser};
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Log, Auth;

class SimplifyGatewayController extends BaseController
{
    use \App\Http\Traits\SimplifyPaymentManager;
	use \App\Http\Traits\ApiResponser;
    private $public_key;
	private $private_key;

    public function __construct()
    {
        $simp_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'simplify')->where('status', 1)->first();
	    $creds_arr = json_decode($simp_creds->credentials);
	    $this->public_key = $creds_arr->public_key??'';
	    $this->private_key = $creds_arr->private_key??'';
    } 
    public function simplifyPurchase(Request $request)
    {
        $user = Auth::user();
        $amount = $request->amount;
        $action = isset($request->action) ? $request->action : ''; 
        $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&payment_from='.$action;
        if(($action == 'cart') || ($action == 'tip')){
            $params = $params . '&order_number=' . $request->order_number;
        }
        // return $this->successResponse(url('payment/simplify/page'.$params)); 
        return $this->successResponse(url($request->serverUrl.'payment/simplify/page'.$params)); 
    }
}
