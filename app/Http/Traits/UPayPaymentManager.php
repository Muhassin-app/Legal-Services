<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

use Log;
trait UPayPaymentManager{

  public function createPaymentRequest($data)
  {
    $formData = [
      'Amt' => $data['amount'],
      'Email' => $data['user_email'],
      'Mobile' => $data['user_phone'],
      'Redir' => $data['redirect_url']??'http://192.168.1.3:8060',
      'References' => $data['references']??[]
    ];
    $info = json_encode($formData);
    return $info;
  }
}
