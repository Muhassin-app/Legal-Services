<?php
namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use HttpRequest;

trait fedexManager{

    private $api_key;
    private $secret_key;
    private $api_url;
    private $grant_type;
    public function __construct()
    {
        $this->api_key = 'l73667587cb58b4ab3ba4af09c93b19932';
        $this->secret_key = '194505a8543b4da19477727db61a8f1a';
        $this->api_url = 'https://apis-sandbox.fedex.com';
        $this->grant_type = 'client_credentials';
    }
   
    public function getFedexAuthToken():object{
      $endpoint='/oauth/token';
      $data = "grant_type=".$this->grant_type."&client_id=".$this->api_key."&client_secret=".$this->secret_key;
      $response=$this->postCurl($endpoint,$data);
      return $response;
    }

    public function validateAddress($token)
    {
      $endpoint='/address/v1/addresses/resolve';
      $data = array (
        'addressesToValidate' => 
        array (
          0 => 
          array (
            'address' => 
            array (
              'streetLines' => 
              array (
                0 => '7372 PARKRIDGE BLVD',
                1 => 'APT 286',
              ),
              'city' => 'IRVING',
              'stateOrProvinceCode' => 'TX',
              'postalCode' => '75063-8659',
              'countryCode' => 'US',
            ),
          ),
        ),
      );
      $response=$this->postCurl($endpoint,$data,$token);
      return $response;
    }

    public function getRate($token)
    {
      $endpoint='/rate/v1/rates/quotes';
      $data = array (
        'accountNumber' => 
        array (
          'value' => '510087240',
        ),
        'requestedShipment' => 
        array (
          'shipper' => 
          array (
            'address' => 
            array (
              'postalCode' => 65247,
              'countryCode' => 'US',
            ),
          ),
          'recipient' => 
          array (
            'address' => 
            array (
              'postalCode' => 75063,
              'countryCode' => 'US',
            ),
          ),
          'pickupType' => 'DROPOFF_AT_FEDEX_LOCATION',
          'rateRequestType' => 
          array (
            0 => 'ACCOUNT',
            1 => 'LIST',
          ),
          'requestedPackageLineItems' => 
          array (
            0 => 
            array (
              'weight' => 
              array (
                'units' => 'LB',
                'value' => 10,
              ),
            ),
          ),
        ),
      );
      $response=$this->postCurl($endpoint,$data,$token);
      return $response;
    }
    public function createShipment($token)
    {
      $endpoint='/ship/v1/shipments';
      $data = array (
        'labelResponseOptions' => 'URL_ONLY',
        'requestedShipment' => 
        array (
          'shipper' => 
          array (
            'contact' => 
            array (
              'personName' => 'SHIPPER NAME',
              'phoneNumber' => 1234567890,
              'companyName' => 'Shipper Company Name',
            ),
            'address' => 
            array (
              'streetLines' => 
              array (
                0 => 'SHIPPER STREET LINE 1',
              ),
              'city' => 'HARRISON',
              'stateOrProvinceCode' => 'AR',
              'postalCode' => 72601,
              'countryCode' => 'US',
            ),
          ),
          'recipients' => 
          array (
            0 => 
            array (
              'contact' => 
              array (
                'personName' => 'RECIPIENT NAME',
                'phoneNumber' => 1234567890,
                'companyName' => 'Recipient Company Name',
              ),
              'address' => 
              array (
                'streetLines' => 
                array (
                  0 => 'RECIPIENT STREET LINE 1',
                  1 => 'RECIPIENT STREET LINE 2',
                ),
                'city' => 'Collierville',
                'stateOrProvinceCode' => 'TN',
                'postalCode' => 38017,
                'countryCode' => 'US',
              ),
            ),
          ),
          'shipDatestamp' => '2021-12-08',
          'serviceType' => 'STANDARD_OVERNIGHT',
          'packagingType' => 'FEDEX_PAK',
          'pickupType' => 'USE_SCHEDULED_PICKUP',
          'blockInsightVisibility' => false,
          'shippingChargesPayment' => 
          array (
            'paymentType' => 'SENDER',
          ),
          'labelSpecification' => 
          array (
            'imageType' => 'PDF',
            'labelStockType' => 'PAPER_85X11_TOP_HALF_LABEL',
          ),
          'requestedPackageLineItems' => 
          array (
            0 => 
            array (
              'groupPackageCount' => 1,
              'weight' => 
              array (
                'value' => 10,
                'units' => 'LB',
              ),
            ),
            1 => 
            array (
              'groupPackageCount' => 2,
              'weight' => 
              array (
                'value' => 5,
                'units' => 'LB',
              ),
            ),
          ),
        ),
        'accountNumber' => 
        array (
          'value' => '740561073',
        ),
      );
      $response=$this->postCurl($endpoint,$data,$token);
      return $response;
    }

    public function validateShipment($token)
    {
      $endpoint = "/ship/v1/shipments/packages/validate";
      $data = array (
        'labelResponseOptions' => 'URL_ONLY',
        'requestedShipment' => 
        array (
          'shipper' => 
          array (
            'contact' => 
            array (
              'personName' => 'SHIPPER NAME',
              'phoneNumber' => 1234567890,
              'companyName' => 'Shipper Company Name',
            ),
            'address' => 
            array (
              'streetLines' => 
              array (
                0 => 'SHIPPER STREET LINE 1',
              ),
              'city' => 'HARRISON',
              'stateOrProvinceCode' => 'AR',
              'postalCode' => 72601,
              'countryCode' => 'US',
            ),
          ),
          'recipients' => 
          array (
            0 => 
            array (
              'contact' => 
              array (
                'personName' => 'RECIPIENT NAME',
                'phoneNumber' => 1234567890,
                'companyName' => 'Recipient Company Name',
              ),
              'address' => 
              array (
                'streetLines' => 
                array (
                  0 => 'RECIPIENT STREET LINE 1',
                  1 => 'RECIPIENT STREET LINE 2',
                ),
                'city' => 'Collierville',
                'stateOrProvinceCode' => 'TN',
                'postalCode' => 38017,
                'countryCode' => 'US',
              ),
            ),
          ),
          'shipDatestamp' => '2021-12-08',
          'serviceType' => 'STANDARD_OVERNIGHT',
          'packagingType' => 'FEDEX_PAK',
          'pickupType' => 'USE_SCHEDULED_PICKUP',
          'blockInsightVisibility' => false,
          'shippingChargesPayment' => 
          array (
            'paymentType' => 'SENDER',
          ),
          'labelSpecification' => 
          array (
            'imageType' => 'PDF',
            'labelStockType' => 'PAPER_85X11_TOP_HALF_LABEL',
          ),
          'requestedPackageLineItems' => 
          array (
            0 => 
            array (
              'groupPackageCount' => 1,
              'weight' => 
              array (
                'value' => 10,
                'units' => 'LB',
              ),
            ),
            1 => 
            array (
              'groupPackageCount' => 2,
              'weight' => 
              array (
                'value' => 5,
                'units' => 'LB',
              ),
            ),
          ),
        ),
        'accountNumber' => 
        array (
          'value' => '740561073',
        ),
      );
      $response=$this->postCurl($endpoint,$data,$token);
      return $response;
    }

    private function postCurl($endpoint,$data,$token=null):object{
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->api_url.''.$endpoint);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1);
      if(!is_null($token))
      {
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
      }else{
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
      }
      $headers = array();
      // $headers[] = 'Accept: */*';
      if(!is_null($token)){
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'x-locale: en_US';
        $headers[] = "authorization: Bearer ${token}";
      }else{
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
      }
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $result = curl_exec($ch);
      if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
      }
      curl_close($ch);
      if(is_null(json_decode($result)))
      {
        dd($result,$endpoint,$data,$token);
      }
      return json_decode($result); 
    }

}