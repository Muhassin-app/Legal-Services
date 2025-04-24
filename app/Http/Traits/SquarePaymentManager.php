<?php
namespace App\Http\Traits;
use Square\SquareClient;
use Square\Environment;
use Square\LocationsApi;
use Square\Exceptions\ApiException;
use Square\Http\ApiResponse;
use Square\Models\ListLocationsResponse;
use Square\Models\CreateCustomerRequest;
use Square\Models\CreatePaymentRequest;
use Square\Models\Payment;
use Square\Models\Money;

use App\Models\PaymentOption;
use Ramsey\Uuid\Uuid;
use Auth, Log;
trait SquarePaymentManager{

  private $application_id;
  private $access_token;
  private $location_id;
  public function __construct()
  {
    $square_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'square')->where('status', 1)->first();
    $creds_arr = json_decode($square_creds->credentials);
    $this->application_id = $creds_arr->application_id??''; 
    $this->access_token = $creds_arr->api_access_token??'';
    $this->location_id = $creds_arr->location_id??'';
  }

  public function init()
  {
    $square_creds = PaymentOption::select('test_mode')->where('code', 'square')->where('status', 1)->first();
    return new SquareClient([
        'accessToken' => $this->access_token,
        'environment' => $square_creds->test_mode ? Environment::SANDBOX : Environment::PRODUCTION,
      ]);
  }

  public function getLocation()
  {
    try{
      $client = $this->init();
      $location = $client->getLocationsApi()->retrieveLocation($this->location_id)->getResult()->getLocation();
      return $location;
    } catch (ApiException $e) {
      dd("Recieved error while calling Square: " . $e->getMessage());
    } 
  }
  public function createSquarePayment($data)
  {
    $client = $this->init();
    $amount_money = new \Square\Models\Money();
    $amount_money->setAmount($data['amount']);
    $amount_money->setCurrency($data['currency']);

    // $app_fee_money = new \Square\Models\Money();
    // $app_fee_money->setAmount(0);
    // $app_fee_money->setCurrency('USD');

    $body = new \Square\Models\CreatePaymentRequest(
        $data['source_id'],
        Uuid::uuid4(),
        $amount_money
    );
    // $body->setAppFeeMoney($app_fee_money);
    $body->setReferenceId($data['reference']);
    $body->setLocationId($data['location_id']);
    $body->setAutocomplete(true);
    $body->setNote($data['description']);

    $api_response = $client->getPaymentsApi()->createPayment($body);
    $payment_id = null;
    if ($api_response->isSuccess()) {
        $result = $api_response->getResult();
        if($result->getPayment()->getStatus() == "COMPLETED")
        {
          $payment_id = $result->getPayment()->getId();
          return $payment_id;
        }
    } else {
        $errors = $api_response->getErrors();
    }
   
    return $payment_id;
  }
}
