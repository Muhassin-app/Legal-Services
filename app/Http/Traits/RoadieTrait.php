<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\ShippingOption;
use App\Models\Webhook;
use Illuminate\Support\Facades\Http;


trait RoadieTrait{
  private $api_access_token;
  private $api_base_url;
  
  public function configDetails()
  {
    $simp_creds = ShippingOption::select('credentials', 'test_mode')->where('code', 'roadie')->where('status', 1)->first();
    $creds_arr = json_decode($simp_creds->credentials);
    $this->api_access_token = $creds_arr->api_access_token??'';
    $this->api_base_url = $creds_arr->api_base_url ?? '';
  }
  //Quotation Function Api
  public function getQuotations($data){
    $this->configDetails();
    $path = '/v1/estimates';
    $response = Http::withHeaders([
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$this->api_access_token
    ])->post($this->api_base_url.$path, $data);
    $statusCode = $response->getStatusCode();
    // pr(json_decode($response));
    if ($statusCode == 200) {
      $price = json_decode($response)->price;
      return array('code'=>$statusCode,'price'=>$price);       
    }
  }

  public function createShipmentRoadie($data){
    $this->configDetails();
    $path = '/v1/shipments';
    $response = [];
    $response = Http::withHeaders([
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$this->api_access_token
    ])->post($this->api_base_url.$path, $data);
    $statusCode = $response->getStatusCode();
    if($statusCode = 200){
      $data = json_decode($response);
      return $data;
    }else{
      return false;
    }
  }
}