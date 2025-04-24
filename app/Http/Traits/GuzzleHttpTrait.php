<?php
namespace App\Http\Traits;
use GuzzleHttp\Client as Guzzle;

trait GuzzleHttpTrait{


    public function guzzlePost($endPoints,$dispatch_domain,$postdata)
    {

        try {
            $client = new Guzzle(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,'content-type' => 'application/json']]);
            $url = $dispatch_domain->pickup_delivery_service_key_url;
            $res = $client->post($url.$endPoints,['form_params' => ($postdata),'timeout'=>'70']);
            $response = json_decode($res->getBody(), true);

            // You can process the response further based on the $statusCode and $responseBody
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $response = [];
                $response['status'] = 400;
                $response['message'] =  $e->getMessage().'- line-'.$e->getLine();
            // Handle request-related errors (e.g., connection issues, timeouts)
            // You can access the error message using $e->getMessage()
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Handle other Guzzle exceptions here
                $response = [];
                $response['status'] = 400;
                $response['message'] =  $e->getMessage().'- line-'.$e->getLine();
            }

            return $response;
       
    }

    public function guzzleGet($endPoints,$dispatch_domain)
    {
        try {
            $client = new Guzzle(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,'content-type' => 'application/json']]);
            $url = $dispatch_domain->pickup_delivery_service_key_url;
            
            $res = $client->get($url.$endPoints,['timeout'=>'50']);
            $response = json_decode($res->getBody(), true);

            // You can process the response further based on the $statusCode and $responseBody
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $response = [];
                $response['status'] = 400;
                $response['message'] =  $e->getMessage().'- line-'.$e->getLine();

                // Handle request-related errors (e.g., connection issues, timeouts)
            // You can access the error message using $e->getMessage()
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Handle other Guzzle exceptions here
                $response = [];
                $response['status'] = 400;
                $response['message'] =  $e->getMessage().'- line-'.$e->getLine();
            }
            return $response;
       
    }
   

}