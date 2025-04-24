<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\{BaseController, OrderController};
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser};
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Log, Auth;

class PagarmeGatewayController extends BaseController
{
	use \App\Http\Traits\ApiResponser;

    public function __construct()
    {
       //
    } 
    public function pagarmePurchase(Request $request)
    {
        $user = Auth::user();
        $amount = $request->amount;
        $action = isset($request->action) ? $request->action : ''; 
        $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&payment_from='.$action;
        if(($action == 'cart') || ($action == 'tip')){
            $params = $params . '&order_number=' . $request->order_number;
        }
        // return $this->successResponse(url('payment/pagarme/page'.$params)); 
        return $this->successResponse(url($request->serverUrl.'payment/pagarme/page'.$params)); 
    }
}
