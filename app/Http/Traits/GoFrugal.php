<?php
namespace App\Http\Traits;

use App\Models\Client;
use App\Models\ClientPreferenceAdditional;
use GuzzleHttp\Client as GClient;

trait GoFrugal
{
    private $_client = null;

    private $_transport = null;

    private $_clientPreference =  null;

    public function __construct()
    {
        if($this->_transport == null){
            $this->_transport = new GClient();
        }
        if($this->_client == null){
            $this->_client = Client::first();
        }
        if($this->_clientPreference == null){
            $clientPreferenceAdditional = ClientPreferenceAdditional::where('key_name','gofrugal_credentials')->where('client_code' ,$this->_client->code)->first();
            if(empty($clientPreferenceAdditional)){
                throw new \Exception('Configure API Key for GoFrugal POS Integration');
            }
            $this->_clientPreference = json_decode($clientPreferenceAdditional['key_value'], true);
        }
    }

    private function createRequest(string $endpoint, string $method, array $headers = [], array $data = []){
        try{
            $headers['X-Auth-Token'] = $this->_clientPreference['api_key'];
            
            $url = $this->_clientPreference['domain_url']. $endpoint;

            $response = $this->_transport->request($method, $url, [
                'headers' => $headers
            ]);
            
            return [
                'status' =>  ($response->getStatusCode() == 200),
                'message' => 'success',
                'data' => json_decode($response->getBody()->getContents())
            ];
        }catch(\Exception $e){
            \Log::info($e->getMessage());
            switch($e->getCode()){
                case 404:
                    $message = "Given Url is not Found";
                    break;
                case 500:
                    $message = "Intenal Server Error";
                    break;
                default:
                    $message = "Unknown Error";
                    break;
            }
            return [
                'status' => false,
                'message' => $message
            ];
        }
    }

    protected function getCategory(){
        $endpoint = 'categories';
        return $this->createRequest($endpoint, 'GET');
    }

    protected function getVendors(){
        $endpoint = 'supplierMaster';
        return $this->createRequest($endpoint, 'GET');
    }

    protected function getCustomers(){
        $endpoint = 'eCustomers';
        return $this->createRequest($endpoint, 'GET');
    }

    protected function getProducts(){
        $endpoint = 'items?selectAll=true';
        return $this->createRequest($endpoint, 'GET');
    }
}