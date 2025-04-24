<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\{ShippingOption,User,Vendor, UserAddress, Order, OrderVendor};
use Illuminate\Http\Request;
use App\Http\Traits\Borzoe;
class BorzoeDeliveryController extends Controller
{
    use Borzoe;
    // public function orderListToBorzoApi(){

    //     $url = 'https://robotapitest-in.borzodelivery.com/api/business/1.4/orders?status=available';
    //     $shippingOption = ShippingOption::where('code', 'borzo')->first();
    //     $cred = json_decode($shippingOption->credentials);
    //     $apiKey = $cred->api_key;

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-DV-Auth-Token: '.$apiKey.'']);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     return $response;
    // }

    public function borzoeWebhook(Request $request){
       return $this->Webhook($request);
    }

}
