<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Checkout;
use CoinbaseCommerce\Resources\Charge;

use Log;
trait CoinbasePaymentManager{

  public function init()
  {
    ApiClient::init('iRM97WT0cpgnKAEl0TQDzaXLaJ5UY6F2BrrOPMFgwRj8uIS3aumvLrjHEPHy/6G4U8loVhuDByLSauxMO8cyYA==');
  }
  public function createCheckout($data,$user)
  {
    $this->init();
    $checkoutData = [
      'name' => 'The Sovereign Individual',
      'description' => 'Mastering the Transition to the Information Age',
      'pricing_type' => 'fixed_price',
      'local_price' => [
          'amount' => '100.00',
          'currency' => 'USD'
      ],
      'requested_info' => [],
    ];
    $newCheckoutObj = Checkout::create($checkoutData);
    return $newCheckoutObj;
  }
  public function createCharge()
  {
    $this->init();
    $chargeData = [
      'name' => 'The Sovereign Individual',
      'description' => 'Mastering the Transition to the Information Age',
      'local_price' => [
          'amount' => '100.00',
          'currency' => 'USD'
      ],
      'pricing_type' => 'fixed_price'
    ];
    $newChargeObj = Charge::create($chargeData);
    dd($newChargeObj);
  }
  public function createCharge1()
  {
    $endpoint='/charges';
    $data=[
       "name" =>  "The Sovereign Individual",
       "description" => "Mastering the Transition to the Information Age",
       "local_price" =>  [
        "amount" => "100.00",
        "currency" => "USD"
       ],
       "pricing_type" => "fixed_price",
       "metadata" => [
         "customer_id" => "id_1005",
         "customer_name" => "Satoshi Nakamoto"
       ],
       "redirect_url" => "https://charge/completed/page",
       "cancel_url" => "https://charge/canceled/page"
    ];
    $response=$this->postCurl($endpoint,$data);
    dd($response);
    return $response;
  }
  public function createCheckout1($data,$user)
  {
    $endpoint='/checkouts';
    $checkoutData = [
      'name' => 'The Sovereign Individual',
      'description' => 'Mastering the Transition to the Information Age',
      'pricing_type' => 'fixed_price',
      'local_price' => [
          'amount' => '100.00',
          'currency' => 'USD'
      ],
      'requested_info' => ['email'],
    ];
    $response=$this->postCurl($endpoint,$data);
  
    return $response;
  }
  private function postCurl($data,$token=null):object{
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://api-public.sandbox.exchange.coinbase.com');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
      $headers = array();
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'X-CC-Api-Key: m/qQJP1aUY4iZE59yHLs2BjoAsCF+Z8hmNCOIE4f/YyRrRL2m5o8G410dm0cOfCzHBvwZkSMybdhhBbuhPUJFg==';
      $headers[] = 'X-CC-Version:  2022-05-01';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $result = curl_exec($ch);
      if (curl_errno($ch)) {
          echo 'Error:' . curl_error($ch);
      }
      curl_close($ch);
      return json_decode($result);
  }
  private function getCurl($endpoint):object{
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $endpoint);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
      $headers = array();
      $headers[] = 'Accept: */*';
      $headers[] = 'Content-Type: application/json';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $result = curl_exec($ch);
      if (curl_errno($ch)) {
          echo 'Error:' . curl_error($ch);
      }
      curl_close($ch);
      return $result;
      return json_decode($result);

      $curl = curl_init();

  }


}
