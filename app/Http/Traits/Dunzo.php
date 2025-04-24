<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\ShippingOption;

trait Dunzo{

  private $api_key;
  private $app_url;
  private $base_price;
  private $distance;
  private $amount_per_km;
  public $status;

/**
 * Dunzo integrations Details
 * Req Api Key
 * Req App URl
 */

  public function __construct()
  {
    $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'dunzo')->where('status', 1)->first();
    if($simp_creds){
        $this->status = $simp_creds->status??'0';
        $creds_arr = json_decode($simp_creds->credentials);
        $this->api_key = $creds_arr->api_key??'';
        $this->app_url = (($simp_creds->test_mode=='1')?'https://dev.adloggs.com/aa':'https://app.adloggs.com/aa'); //Live url - https://app.adloggs.com/aa
        $this->base_price = $creds_arr->base_price ?? ''; 
        $this->distance = $creds_arr->distance ?? ''; 
        $this->amount_per_km = $creds_arr->amount_per_km ?? '';
    }else{
        return 0;
    }
  }

  public function configDetails()
  {
    $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'dunzo')->where('status', 1)->first();
    if($simp_creds){
        $this->status = $simp_creds->status??'0';
        $creds_arr = json_decode($simp_creds->credentials);
        $this->api_key = $creds_arr->api_key??'';
        $this->app_url = (($simp_creds->test_mode=='1')?'https://dev.adloggs.com/aa':'https://app.adloggs.com/aa'); //Live url - https://app.adloggs.com/aa
        $this->base_price = $creds_arr->base_price ?? ''; 
        $this->distance = $creds_arr->distance ?? ''; 
        $this->amount_per_km = $creds_arr->amount_per_km ?? '';
    }else{
        return 0;
    }
  }


  //Quotation Function Api
  public function getfees($data)
  {
    $this->configDetails();
    $curl = curl_init();
    //dd($data);

    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->app_url."/oporder/service/availability",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "apikey: {$this->api_key}"
    ),
    ));

    $response = curl_exec($curl);
    //dd($response);
    //\Log::info(json_encode($response));
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
    return  $err;
    } else {
    return json_decode($response);
    }


  //"status": true,
  // "code": 200,
  // "message": "Success",
  // "data": {
  //     "distance": 15,
  //     "estimated_price": 204
  // }

}


public function createOrder($data)
{
    $this->configDetails();
    $curl = curl_init();
    //dd($data);
    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->app_url."/oporder/create",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "apikey: {$this->api_key}"
    ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    //\Log::info($response);
    curl_close($curl);
    if ($err) {
    return  $err;
    } else {
    return json_decode($response);
    }

} 


  public function orderDetail($id)
  {
    $data = array('order_uuid'=>$id);
    $this->configDetails();
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->app_url."/aa/oporder/getbyid",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "apikey: {$this->api_key}"
    ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {
    echo $response;
    }

  }


  public function checkAvilabilty($data)
  {
        $this->configDetails();
        $curl = curl_init();
     
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->app_url."/oporder/dscheck",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "apikey: ".$this->api_key
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return (object)json_decode($response,true);
        }

        // "status": true,
        // "code": 200,
        // "message": "Riders available",
        // "data": {}

  }


  public function cancelOrder($data)
  {
    $this->configDetails();
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->app_url."/oporder/update",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "apikey: {$this->api_key}",
    ),
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return $err;
    } else {
        return $response;
    }

  }






}