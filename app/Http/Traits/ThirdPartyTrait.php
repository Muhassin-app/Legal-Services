<?php
namespace App\Http\Traits;
use SevenShores\Hubspot\Http\Client;
use SevenShores\Hubspot\Resources\Contacts;
use SevenShores\Hubspot\Factory;
use DB;
use App\Models\Client as ClientData;
use App\Models\User;
use App\Models\ClientPreferenceAdditional;
use Auth;
use Carbon\Carbon;
trait ThirdPartyTrait{
    
    /**
     * hubSpotSync
     *
     * @param  mixed $post_data
     * @return void
     */
    public function hubSpotSync($post_data = array(['hub_key'=>'0','hubspot_last_update'=>'','is_hubspot_enable'=>'','db_name'=>'','ClientPreferenceAdditional'=>'','ClientData'=>'','User'=>'']))
    {
        $lastUpdate='1998-01-14';
        try {
            $hubkey = $post_data['hub_key'];
            $hubspot = Factory::createWithOAuth2Token($hubkey);
            $endpoint = 'https://api.hubapi.com/contacts/v1/contact/batch';
            $hubclient = $hubspot->getClient();
            if((isset($post_data['hubspot_last_update'])) && (!empty($post_data['hubspot_last_update']))){
                $lastUpdate =  Carbon::parse($post_data['hubspot_last_update'])->format('Y-m-d h:i:s');
            } else {
                $lastUpdate =  Carbon::parse($lastUpdate)->format('Y-m-d h:i:s');
            }
            if($post_data['User']){
                $users = $post_data['User']->where('created_at','>',$lastUpdate)->get();
            }
            $data = [];
            if(sizeof($users)){
                foreach($users as $key => $user){
                    $data[] =  [  "email"=> $user->email, 
                                        'properties'=>  
                                            [
                                                [ 'property' => 'firstname', 'value'  => $user->name ],
                                                [ 'property' => 'lastname', 'value'  => $user->name ],
                                                [ 'property' => 'phone', 'value'  => $user->phone_number ],
                                                // [ 'property' => 'address', 'value'  => $user->email ],
                                                // [ 'property' => 'city', 'value'  => $user->email ],
                                                // [ 'property' => 'state', 'value'  => $user->email ],
                                                // [ 'property' => 'zip', 'value'  => $user->email ],
                                            ]
                                ];
                                
                }
                if(count($data)>0){
                    $rp = $hubclient->request(
                        'post',
                        $endpoint,
                        ['json' => $data]
                    );
                    $now = Carbon::now()->format('Y-m-d h:i:s');

                        if($post_data['ClientPreferenceAdditional'] && $post_data['ClientData']){
                            $client = $post_data['ClientData']->first();
                            $post_data['ClientPreferenceAdditional']->updateOrInsert(
                                ['key_name' => 'hubspot_last_update', 'client_code' => $client->code],
                                ['key_name' => 'hubspot_last_update', 'key_value' => $now,'client_code' => $client->code,'client_id'=> $client->id]);
                        }
                       
                    return response()->json(['status' => true, 'notiFY' => [] , 'message' => __('Sucessfully migrated!!')]);
                
                
                }
                return response()->json(['status' => true, 'notiFY' => [] , 'message' => __('No New Data found to sync!!')]);
            
            }
            return response()->json(['status' => true, 'notiFY' => [] , 'message' => __('No New Data found to sync!!')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'notiFY' => [] , 'message' => __('Something went wrong ,or key must be wrong !!!')]);
        }
        
    }
}
