<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\{BaseController, OrderController};
use App\Models\{User, UserVendor, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser};
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Log, Auth;


class SquareGatewayController extends BaseController
{
    use \App\Http\Traits\SquarePaymentManager;
	use \App\Http\Traits\ApiResponser;

	private $application_id;
	private $access_token;
	public function __construct()
  	{
		$this->square_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'square')->where('status', 1)->first();
	    $this->creds_arr = json_decode($this->square_creds->credentials);
	    $this->application_id = $this->creds_arr->application_id??'';
	    $this->access_token = $this->creds_arr->api_access_token??'';
	    $this->location_id = $this->creds_arr->location_id??'';
	    $this->square_url = $this->square_creds->test_mode ? "https://sandbox.web.squarecdn.com/v1/square.js" : "https://web.squarecdn.com/v1/square.js";
	}
	public function squarePurchase(Request $request)
    {
        $user = Auth::user();
        $amount = $request->amount;
        $action = isset($request->action) ? $request->action : ''; 
        $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&payment_from='.$action;
        if(($action == 'cart') || ($action == 'tip')){
            $params = $params . '&order_number=' . $request->order_number;
        }
        return $this->successResponse(url($request->serverUrl.'payment/square/page'.$params));
        // return $this->successResponse(url('payment/square/page'.$params)); 
    }
}
