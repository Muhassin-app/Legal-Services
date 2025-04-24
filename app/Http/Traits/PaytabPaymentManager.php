<?php
namespace App\Http\Traits;
use App\Models\PaymentOption;
use Paytabscom\Laravel_paytabs\Facades\paypage; 
use Auth, Log, Config;
trait PaytabPaymentManager{
  public function __construct()
    {
      $this->paytab_creds = PaymentOption::select('credentials')->where('code', 'paytab')->where('status', 1)->first();
      if(@$this->paytab_creds && !empty($this->paytab_creds->credentials)){
      $this->creds_arr = json_decode($this->paytab_creds->credentials);
      $this->profile_id = $this->creds_arr->profile_id ?? '';
      $this->client_key = $this->creds_arr->client_key ?? '';
      $this->server_key = $this->creds_arr->server_key ?? '';
        Config::set('Paytabs.profile_id', $this->profile_id);
        Config::set('Paytabs.server_key', $this->server_key); 
      }
  }

  public function createPaymentpage($data,$user,$address = null)
  {
    if(is_null($address))
    {
      $address = (object)[];
    }
    $order_number = isset($data['order_number']) ? $data['order_number'] : "";
    $pay = paypage::sendPaymentCode('all')
        ->sendTransaction('Auth')
        ->sendCart(mt_rand(10000000,99999999),(int)$data['amount'],'test1')
        // ->sendCustomerDetails('Walaa Elsaeed', 'w.elsaeed@paytabs.com', '0101111111', 'test', 'Nasr City', 'Cairo', 'EG', '1234','100.279.20.10')
        ->sendCustomerDetails($user->name??'', $user->email??'', '0101111111', $address->address??'', $address->city??'', $address->state??'', $address->country_code??'', $address->pincode??'','100.279.20.11')
        ->sendShippingDetails('same as billing')
        ->sendURLs(route('payment.paytab.return',['amount' => (int)$data['amount'], 'payment_from' => $data['payment_from'], 'come_from' => $data['come_from'], 'order_number' => $order_number,'auth_token'=>$user->auth_token]), route('payment.paytab.callback')) 
        // ->sendURLs('https://619a-112-196-88-218.ngrok.io/payment/paytab/return?amount='.(int)$data['amount'].'&payment_from='.$data['payment_from'].'&come_from='.$data['come_from'].'&order_number='.$order_number.'&auth_token='.$user->auth_token, 'https://619a-112-196-88-218.ngrok.io/payment/paytab/callback') 
        ->sendLanguage('en')
        ->create_pay_page();
    return $pay;
  }
  public function capturePayment($data)
  {
    return  Paypage::capture($data['tranRef'],$data['cartId'],(int)$data['amount'],$data['description']); 
  }
}
