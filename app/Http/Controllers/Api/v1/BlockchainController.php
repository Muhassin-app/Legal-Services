<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Traits\ApiResponser;
use App\Models\ClientPreferenceAdditional;
use Illuminate\Http\Request;

class BlockchainController extends BaseController
{
    use ApiResponser;

    public function getBlockchainAddress(Request $request)
    {
        try {
            $request->header();
            $preference = ClientPreferenceAdditional::where('key_name', 'blockchain_route_formation')->first();
            $data = [];
            if (isset($preference) && ($preference->key_value == 1)) {

                $address_id = ClientPreferenceAdditional::where('key_name', 'blockchain_address_id')->first();
                $api_domain = ClientPreferenceAdditional::where('key_name', 'blockchain_api_domain')->first();

                $data['blockchain_api_domain'] = $address_id->key_value ?? '';
                $data['blockchain_address_id'] = $api_domain->key_value ?? '';
            }

            return $this->successResponse($data, null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode(), null);
        }
    }




}
