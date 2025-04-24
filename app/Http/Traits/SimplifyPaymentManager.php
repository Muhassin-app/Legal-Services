<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Rak\Simplify\Simplify;
use App\Models\PaymentOption;
use Log;
trait SimplifyPaymentManager{

  private $public_key;
  private $private_key;
  public function __construct()
  {
    $simp_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'simplify')->where('status', 1)->first();
    $creds_arr = json_decode($simp_creds->credentials);
    $this->public_key = $creds_arr->public_key??'';
    $this->private_key = $creds_arr->private_key??'';
  }

  public function init()
  {
    Simplify::$publicKey = $this->public_key;
    Simplify::$privateKey = $this->private_key;
  }

  public function create_payment($data)
  {
    $this->init();
    try {
      // dd($data);
      $requestData = [
        'reference' => $data['reference'], // Order reference
        'amount' => (int)$data['amount'], // 10 AED multiplied by 100
        'description' => $data['description'],
        'currency' => 'QAR',
        'token' => $data['simplifyToken'], // Card token you received
        'order' => [
          'source' => 'WEB',
          'customerEmail' => $data['email'],
          'customerName' => $data['username']
        ]
      ];

      $rakAuthCheck = Simplify::createPayment($requestData); 
      if($rakAuthCheck){
            //Success call
        return $rakAuthCheck;
      }
    }catch(Exception $e) {
        // Failed call
        return [
            'auth_message' => $e->getMessage()
        ];
    }
  }

}
