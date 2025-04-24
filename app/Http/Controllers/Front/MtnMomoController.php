<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\FrontController;
use Auth;
use App\Models\User;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;

use Illuminate\Http\Request;
use App\Models\PaymentOption;
use App\Models\CaregoryKycDoc;
use Illuminate\Support\Carbon;
use App\Http\Traits\ApiResponser;
use App\Models\CartProductPrescription;
use App\Models\ClientCurrency;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\UrlGenerator;
use Log;
use App\Http\Traits\MtnMomoPaymentManager;

class MtnMomoController extends FrontController
{
    use ApiResponser;
    use MtnMomoPaymentManager;

    public function createToken(Request $request, UrlGenerator $url = null)
    {
        $transactionId = self::orderNumber($request);
        self::__init(false);

        if (!self::$_isConfigurationSet) {
            return self::response(500, 'Sorry for inconvinence. Please try again later');
        }

        $data = [];
        $data['environment'] = 'web';
        $data['transaction_id'] = $transactionId;
        switch ($request->from) {
            case 'cart':
                $data['amt'] = $request->amt;
                $data['order_number'] = $request->order_number;
                $data['from'] = $request->from;
                break;
            case 'pickup_delivery':
                $data['amt'] = $request->amt;
                $data['from'] = $request->from;
                $data['order_number'] = $request->order_number;
                $data['reload_route'] = $request->reload_route;
                break;
            case 'wallet':
                $data['amt'] = $request->amt;
                $data['from'] = $request->from;
                $data['order_number'] = 'wallet';
                break;
            case 'subscription':
                $data['amt'] = $request->amt;
                $data['from'] = $request->from;
                $data['subsid'] = $request->subsid;
                $data['order_number'] = 'subscription';
                break;
            case 'tip':
                $data['amt'] = $request->amt;
                $data['from'] = $request->from;
                $data['order_number'] = $request->order_number;
                break;
        }
        self::GenerateAccressToken();

        if (empty(self::$_accessToken)) {
            return self::response(500, 'Sorry for inconvinence. Please try again later');
        }
        // request to pay 
        $response = self::RequestToPay(self::$_accessToken, $data);
        if ($response['status'] == 202) {
            if (!self::$_isSandbox || 1) {
                return response()->json([
                    'transaction_id' => $transactionId,
                    'status' => 'Success',
                    'message' => 'Payment request has been sent successfully',
                    'responseUrl' => url('mtn/response') . '?transaction_id=' . $transactionId, //route('payment.response.mtn', ['id' => $transactionId], true),
                    'wait' => true
                ], 200);
            }

            //For Sandbox only
            $response = self::getSandboxResponse(self::$_referenceId, $request, $data);
            return $response;
        }
        return response()->json([
            'status' => 'PAYMENT FAILED',
            'message' => 'Payment Failed',
            'response' => !empty($response['response']) ? json_decode($response['response']->getBody()->getContents(), true) : ''
        ], 500);
    }

    public function mtnCallback(Request $request,$domain = '')
    {
        $request = [];
        $mtnPayload = file_get_contents('php://input');
        if (empty($mtnPayload))
            return false;
        $payload = json_decode($mtnPayload, true);
        $payment = Payment::where('transaction_id', $payload['externalId']);
        if (!$payment)
            return false;
        $payment->update(['payment_detail' => $mtnPayload, 'payment_option_id' => 48]);
        $user = $payment->first()->user;
        if (!empty($payload['status']) && $payload['status'] == 'SUCCESSFUL') {
            $details = explode(',', $payload['payeeNote']);
            $env = $details[0] ?? '';
            $from = $details[1] ?? '';
            $order_number = $details[2] ?? '';
            $route = $details[3] ?? '';
            $subsId = $details[4] ?? '';
            if (!empty($user)) {
                Auth::login($user);
                $request['amt'] = $payload['amount'];
                $request['from'] = $from;
                $request['order_number'] = $order_number;
                $request['environment'] = $env;
                $request['subsid'] = $subsId;
                $request['reload_route'] = $route;
                $response = self::sucessPayment($request, $payload['externalId']);
                return response()->json([
                    'response' => $response
                ], 200);
            }
        }else{
            return response()->json([
                'message' => 'Payment Failed'
            ], 500);
        }
    }

    public function getResponse(Request $request)
    {
        return self::paymentResponse($request);
    }
}
