<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log, Auth;

class UPayGatewayController extends Controller
{
    public function upayPurchase(Request $request)
    {
        $user = Auth::user();
        $amount = $request->amount;
        $action = isset($request->action) ? $request->action : ''; 
        $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&payment_from='.$action;
        if(($action == 'cart') || ($action == 'tip')){
            $params = $params . '&order_number=' . $request->order_number;
        }
        // return $this->successResponse(url('payment/upay/page'.$params)); 
        return $this->successResponse(url($request->serverUrl.'payment/upay/page'.$params)); 
    }
}
