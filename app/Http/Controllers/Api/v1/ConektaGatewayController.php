<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\{BaseController};
use Log, Auth;

class ConektaGatewayController extends BaseController
{
    use \App\Http\Traits\ApiResponser;

    public function conektaPurchase(Request $request)
    {
        $user = Auth::user();
        $amount = $request->amount;
        $action = isset($request->action) ? $request->action : ''; 
        $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&payment_from='.$action;
        if(($action == 'cart') || ($action == 'tip')){
            $params = $params . '&order_number=' . $request->order_number;
        }
        // return $this->successResponse(url('payment/conekta/page'.$params)); 
        return $this->successResponse(url($request->serverUrl.'payment/conekta/page'.$params)); 
    }
}
