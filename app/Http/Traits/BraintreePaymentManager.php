<?php
namespace App\Http\Traits;
use App\Models\PaymentOption;
use Auth, Log, Config;
trait BraintreePaymentManager{

  public function init(){
    $gateway = new \Braintree\Gateway([
      'environment' => 'sandbox',
      'merchantId' => $this->merchant_id,
      'publicKey' => $this->public_key,
      'privateKey' => $this->private_key
    ]);
    return $gateway;
  }
  public function createToken($user){
    $gateway = $this->init();
    $customer = $this->createCustomer($gateway,$user);
    if($customer->success)
    {
      $aCustomerId = $customer->customer->id;
      $clientToken = $gateway->clientToken()->generate([
        "customerId" => $aCustomerId
      ]);
      return $clientToken;
    }
    return null;
  }
  public function createCustomer($gateway,$user)
  {
    $result = $gateway->customer()->create([
      'firstName' => 'Mike',
      'lastName' => 'Jones',
      'company' => 'Jones Co.',
      'email' => 'mike.jones@example.com',
      'phone' => '281.330.8004',
      'fax' => '419.555.1235',
      'website' => 'http://example.com'
    ]);
    return $result;
  }
  public function createTransaction($data)
  {
    $gateway = $this->init();
    $result = $gateway->transaction()->sale([
      'amount' => '10.00',
      'paymentMethodNonce' => $data['paymentMethodNonce'],
      'deviceData' => $data['deviceData'],
      'options' => [
        'submitForSettlement' => True
      ]
    ]);
    if($result->success){
      return $result->transaction->id;
    }
    return null;
  }


}
