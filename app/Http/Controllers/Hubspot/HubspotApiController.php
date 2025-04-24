<?php 

namespace App\Http\Controllers\Hubspot;

use Illuminate\Http\Request;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\ValidatorTrait;
use App\Http\Traits\ThirdPartyTrait;
use App\Models\{Client as ClientData, ClientPreferenceAdditional,User};

class HubspotApiController extends FrontController{
use ThirdPartyTrait;
use ValidatorTrait;
/**
 * create batch records for hubspot api
 *
 * @param  mixed $r
 * @return void
 */
public function create(Request $r)
    {
        
        try {
            $hub_key = @getAdditionalPreference(['hubspot_access_token','is_hubspot_enable','hubspot_last_update']);
            $ClientPreference = ClientPreferenceAdditional::getQuery();
            $ClientData =  ClientData::getQuery();  
            $User = User::getQuery();
            $post_data = [
                'hubspot_last_update' =>$hub_key['hubspot_last_update'],
                'is_hubspot_enable' =>$hub_key['is_hubspot_enable'],
                'hub_key' =>$hub_key['hubspot_access_token'],
                'ClientPreferenceAdditional'=>$ClientPreference,
                'ClientData'=>$ClientData,
                'User'=>$User
            ];
            if($post_data['is_hubspot_enable'] == '1' && $post_data['hub_key'] != '') {
                return $this->hubSpotSync($post_data);
            }
            
            } catch (\Throwable $th) {
                return response()->json(['status' => false, 'notiFY' => [] , 'message' => __('Something went wrong !!!')]);
            }
     
    }
}