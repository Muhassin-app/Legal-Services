<?php

namespace App\Http\Controllers\Api\v1;
use Log;
use Auth;
use App\Helpers\Easebuzz;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use App\Models\{ClientCurrency};
use App\Http\Controllers\Api\v1\{BaseController};
class UseRedePaymentController  extends BaseController
{
    use ApiResponser;
    public function beforePayment(Request $request) 
    {   
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $currency =  (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
        if($currency != "BRL"  ){
            return response()->json([
                'status' => 'error',
                'message' =>__('Something went wrong!Please try again.')
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
      
        //return $this->successResponse(url('http://192.168.99.124:8000/payment/userede/page'.$params));
        return $this->successResponse(url($request->serverUrl.'payment/userede/page'.$params)); 
    }
}