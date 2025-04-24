<?php

namespace App\Http\Controllers\Api\v1; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log, Auth;

class TelrController extends Controller
{
    public function telrPurchase(Request $request)
    {
        $user = Auth::user();
        $amount = $request->amount;
        $action = isset($request->action) ? $request->action : ''; 
        $params = '?amount=' . $amount.'&auth_token='.$user->auth_token.'&payment_from='.$action;
        if(($action == 'cart') || ($action == 'tip') || $action == 'pickup_delivery'){
            $params = $params . '&order_number=' . $request->order_number;
        }
        // return $this->successResponse(url('payment/telr/page'.$params)); 
        return $this->successResponse(url($request->serverUrl.'payment/telr/page'.$params)); 
    }
}
