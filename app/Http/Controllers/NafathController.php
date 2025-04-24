<?php

namespace App\Http\Controllers;

use App\Models\ClientPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NafathController extends Controller
{

    protected $nafath_client_id;
    protected $nafath_client_secret;

    public function sendMfaRequest(Request $request)
    {
        $ClientPreference = ClientPreference::first();
        $this->nafath_client_id = $ClientPreference->nafath_client_id;
        $this->nafath_client_secret = $ClientPreference->nafath_client_secret;

        $response = Http::withHeaders([
            'APP-ID' => $this->nafath_client_id,
        ])->post('https://nafath.api.elm.sa/stg/api/v1/mfa/request', [
            'nationalId' => $request->input('nationalId'),
            'service' => $this->nafath_client_secret,
        ]);

        if ($response->successful()) {
            return response()->json([
                'message' => 'MFA Request sent successfully.',
                'transId' => $response['transId'],
                'random' => $response['random'],
            ]);
        } else {
            return response()->json([
                'error' => $response->json(),
            ], $response->status());
        }
    }

    public function checkMfaStatus(Request $request)
    {
        $ClientPreference = ClientPreference::first();
        $this->nafath_client_id = $ClientPreference->nafath_client_id;

        $response = Http::withHeaders([
            'APP-ID' => $this->nafath_client_id ,
        ])->post('https://nafath.api.elm.sa/stg/api/v1/mfa/request/status', [
            'nationalId' => $request->input('nationalId'),
            'transId' => $request->input('transId'),
            'random' => $request->input('random'),
        ]);

        if ($response->successful()) {
            $status = $response['status'];

            if ($status === 'COMPLETED') {
                // User has successfully logged in via Nafath
                return response()->json(['message' => 'User authenticated successfully.']);
            } elseif ($status === 'WAITING') {
                return response()->json(['message' => 'Awaiting user confirmation.']);
            } else {
                return response()->json(['message' => 'Authentication failed.', 'status' => $status]);
            }
        } else {
            return response()->json([
                'error' => $response->json(),
            ], $response->status());
        }
    }

}
