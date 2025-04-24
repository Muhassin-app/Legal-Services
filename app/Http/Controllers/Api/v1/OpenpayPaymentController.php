<?php

namespace App\Http\Controllers\Api\v1;
use Log;
use Auth;
use App\Helpers\Easebuzz;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use App\Models\{Country,Client};
use App\Http\Controllers\Api\v1\{BaseController};
use Openpay\Data\Openpay as Openpay;
class OpenpayPaymentController  extends BaseController
{
    use ApiResponser;
    public function beforePayment(Request $request) 
    {   
        $cl = Client::first();
        $getAdminCurrentCountry = Country::where('id', '=', $cl->country_id)->get()->first();
        //pr($request->all());
        if(!empty($getAdminCurrentCountry)){
            $countryCode = $getAdminCurrentCountry->code;
        }else{
        $countryCode = '';
        }
        if($countryCode != "MX" && $countryCode !="CO" && $countryCode !="PE" ){
            return response()->json([
                'status' => 'error',
                'message' =>__('Country code must be Mexico,Colombia,Peru.')
            ]);
        }
        $user = Auth::user();
        $amount = $this->getDollarCompareAmount($request->amount);
        $action = isset($request->action) ? $request->action : ''; 
        $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&payment_from='.$action.'&view_from=app';
        if(($action == 'cart') || ($action == 'tip' || $action == 'pickup_delivery')){
            $params = $params . '&order_number=' . $request->order_number;
        }
        if($action == 'subscription'){
            $params = $params . '&subscription_id=' . $request->subscription_id;
        }
        return $this->successResponse(url($request->serverUrl.'payment/opnepay/page'.$params)); 
    }
}