<?php
namespace App\Http\Traits;

use DB;
use HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\{Order,ProductVariant,OrderVendor,VendorOrderCancelReturnPayment,UserDevice,ClientPreference,Product};
use Auth;
use GuzzleHttp\Client as GClient;
trait DispatcherSlot{

    public function getSlotFeeDispatcher($data)
    {
      
        try {

                $postdata =  [ 
                                "latitude"  => $data['latitude'], 
                                "longitude" => $data['longitude'], 
                                "tags"      => $data['tags'], 
                                "schedule_date" => $data['schedule_date'] ,
                                "service_time" => $data['service_time'] ?? "30",
                                "slot_start_time" => $data['slot_start_time'] ?? "30",
                                "team_email" => $data['team_email']
                            ];
                
                $client = new GClient([
                    'headers' => [
                        'personaltoken' => $data['service_key'],
                        'shortcode'     => $data['service_key_code'],
                        'content-type'  => 'application/json'
                    ]
                ]);

              
                $url = $data['service_key_url'];
                $res = $client->post(
                    $url . '/api/agent/check_slot',
                    ['form_params' => ($postdata)]
                );
                $response = json_decode($res->getBody(), true);
               
                if ($response && $response['message'] == 'success') {
                    $agets =count($response['data']['agents']) > 0 ? $response['data']['agents'] : [];
                    return $response['data'];
                }
               
        } catch (\Exception $e) {
            //print($e->getMessage())
           // Log::info($e->getMessage());
            return [];
        }
    }
    public function getAgentDetailFromDispatcher($data)
    {
      
        try {
                $client = new GClient([
                    'headers' => [
                        'personaltoken' => $data['service_key'],
                        'shortcode'     => $data['service_key_code'],
                        'content-type'  => 'application/json'
                    ]
                ]);
              
                $url = $data['service_key_url']. '/api/get/agent_detail/'.$data['driver_id'];
                $res = $client->get($url );
                $response = json_decode($res->getBody(), true);
              //  pr( $response['status']);
                if ($response && $response['status'] === 200) {
                    return $response['data'];
                }
                return [];
               
        } catch (\Exception $e) {
           // Log::info($e->getMessage());
            return [];
        }
    }
    public function productDetail($product_id){
        return Product::with(['vendor'=> function ($q1)  {
            $q1->select('id', 'latitude','longitude');
        },'productcategory'=> function ($q1)  {
            $q1->select('id', 'type_id');
        }])->find($product_id);
    }
     # get prefereance if appointment on in config
     public function getDispatchAppointmentDomain()
     {
         $preference = ClientPreference::select('need_appointment_service','appointment_service_key','appointment_service_key_url','appointment_service_key_code')->first();
         if ($preference->need_appointment_service == 1 && !empty($preference->appointment_service_key) && !empty($preference->appointment_service_key_url) && !empty($preference->appointment_service_key_code)) {
             return $preference;
         } else {
             return false;
         }
     }
}
