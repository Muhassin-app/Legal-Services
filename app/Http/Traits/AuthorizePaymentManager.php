<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\PaymentOption;
use Omnipay\Omnipay;
use Log;
trait AuthorizePaymentManager{

  public function init()
  {
    $gateway = Omnipay::create('AuthorizeNetApi_Api');
    $gateway->setAuthName($this->login_id);
    $gateway->setTransactionKey($this->transaction_key);
    $gateway->setTestMode($this->test_mode?true:false);
    return $gateway;
  }

  public function create_payment($data)
  {
    $gateway = $this->init();
    $transactionId = rand(100000000, 999999999);
    try {
      $response = $gateway->authorize([
        'amount' => $data['amount'],
        'currency' => 'USD',
        'transactionId' => $transactionId,
        'opaqueDataDescriptor' => $data['opaqueDataDescriptor'],
        'opaqueDataValue' => $data['opaqueDataValue'],
      ])->send();

      if($response->isSuccessful()) {
        // Captured from the authorization response.
        $transactionReference = $response->getTransactionReference();
        $response = $gateway->capture([
          'amount' => $data['amount'],
          'currency' => 'USD',
          'transactionReference' => $transactionReference,
        ])->send();
        $transaction_id = $response->getTransactionReference();
        return $transaction_id;
      }else{
       // Log::info('Exception Handling Error');
       // Log::info($response->getMessage());
        return null;
      }
    }catch(Exception $e) {
        return [
            'auth_message' => $e->getMessage()
        ];
    }
  }

}
