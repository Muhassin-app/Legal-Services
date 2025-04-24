<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Traits\MtnMomoPaymentManager;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class MtnMomoController extends Controller
{
    use MtnMomoPaymentManager;

    public function createToken(Request $request)
    {
        $transactionId = self::orderNumber($request);
        self::__init(false);

        if (!self::$_isConfigurationSet) {
            return self::response(500, 'Sorry for inconvinence. Please try again later');
        }

        $data = [];
        $data['environment'] = 'app';
        $data['transaction_id'] = $transactionId;
        switch ($request->from) {
            case 'cart':
                $data['amt'] = $request->amount;
                $data['order_number'] = $request->order_no;
                $data['from'] = $request->from;
                break;
            case 'pickup_delivery':
                $data['amt'] = $request->amount;
                $data['from'] = $request->from;
                $data['order_number'] = $request->order_no;
                $data['reload_route'] = $request->reload_route;
                break;
            case 'wallet':
                $data['amt'] = $request->amount;
                $data['from'] = $request->from;
                break;
            case 'subscription':
                $data['amt'] = $request->amount;
                $data['from'] = $request->from;
                $data['subsid'] = $request->subscription_id;
                break;
            case 'tip':
                $data['amt'] = $request->amount;
                $data['from'] = $request->from;
                $data['order_number'] = $request->order_no;
                break;
        }
        // generate AccessToken
        self::GenerateAccressToken();

        if (empty(self::$_accessToken)) {
            return response()->json([
                'message' => 'Could not process payment at the moment. Please try again later'
            ], 500);
        }
        // request to pay 
        $response = self::RequestToPay(self::$_accessToken, $data);

        if ($response['status'] == 202) {
            if (!self::$_isSandbox || 1) {
                return response()->json([
                    'transaction_id' => $transactionId,
                    'status' => 'Success',
                    'message' => 'Payment request has been sent successfully',
                    'responseUrl' => url('api/v1/mtn/response') . '?transaction_id=' . $transactionId, //route('payment.response.mtn', ['id' => $transactionId], true),
                    'wait' => true
                ], 200);
            }
            //For Sandbox only
            return self::getSandboxResponse(self::$_referenceId, $request, $data);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Payment Failed',
            'response' => $response
        ], 500);
    }

    public function getResponse(Request $request){
        return self::paymentResponse($request);
    }
}
