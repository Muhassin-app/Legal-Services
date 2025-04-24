<?php
namespace App\Http\Traits;

use DB;
use HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\{Order,ProductVariant,OrderVendor,VendorOrderCancelReturnPayment,UserDevice,ClientPreference};
use Auth;
use GuzzleHttp\Client as GClient;
trait Dispatcher{

    public function getRatingQuestingDispatcher($traking_url)
    {
        try {
            $new_dispatch_traking_url = str_replace('/tracking/', '/driver_additional_rating/', $traking_url);
    
            $Httpresponse = Http::get($new_dispatch_traking_url);
            $response = json_decode($Httpresponse->getBody(), true);
            $data =  json_decode($response['data'], true);
            return  $data;
        } catch (\Exception $e) {
           // Log::info($e->getMessage());
            return [];
        }
    }
    # set Driver rating at dispatch panel
    public function setDriverRatingDispatcher($postdata , $traking_url)
    {
        try {
            $new_dispatch_traking_url = str_replace('/tracking/', '/submit_driver_additional_rating/', $traking_url);
            $client = new GCLIENT();
    
          
            $res = $client->post($new_dispatch_traking_url,
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
         return $response ;
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    
}
