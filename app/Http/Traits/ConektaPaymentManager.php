<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Log;

trait ConektaPaymentManager{

  protected function createToken()
  {
    $token = base64_encode($this->private_key);
    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', 'https://api.conekta.io/tokens', [
      'body' => '{"checkout":{"returns_control_on":"Token"}}',
      'headers' => [
        'Accept' => 'application/vnd.conekta-v2.0.0+json',
        'Authorization' => 'Basic '.$token,
        'Content-Type' => 'application/json',
      ],
    ]);
  }
  protected function init()
  {
    \Conekta\Conekta::setApiKey($this->private_key);
    \Conekta\Conekta::setApiVersion("2.0.0");
    \Conekta\Conekta::setLocale('en');
  }
  protected function createCustomer($data)
  {
    $validCustomer = [
      'name' => $data['customer_name'],
      'email' => $data['customer_email'],
      'phone' => $data['customer_phone']
    ];
    $customer = \Conekta\Customer::create($validCustomer);
    return $customer;
  }
  protected function createCheckout($data)
  {
    $this->init();
    $customer = $this->createCustomer($data);
    $after_url = $data['payment_from']."/".$data['come_from'].'/'.$data['amount']."/".($data["order_number"]??0);

   $success_url = $this->url.'/payment/conekta/status?q=success&payment_from='.$data['payment_from'].'&come_from='.$data['come_from'].'&amount='.$data['amount'].'&order_number='.($data["order_number"]??0);
   $failure_url = $this->url.'/payment/conekta/status?q=failure&payment_from='.$data['payment_from'].'&come_from='.$data['come_from'].'&amount='.$data['amount'].'&order_number='.($data["order_number"]??0);
    $validOrderWithCheckout = array(
      'line_items'=> $data['line_items'],
      'checkout' => array(
        'allowed_payment_methods' => array("card", "bank_transfer"),
        'type' => 'HostedPayment',
        'success_url' =>$success_url,
        'failure_url' => $failure_url,
        'monthly_installments_enabled' => true,
        'monthly_installments_options' => array(3, 6, 9, 12),
        "redirection_time" => 4, //Tiempo de Redirección al Success/Failure URL, umbrales de 4 a 20 seg.
        'is_redirect_on_failure'       => true
      ),
      'customer_info' => array(
        'customer_id'   =>  $customer->id
      ),
      'currency'    => 'mxn',
      'metadata'    => array('test' => 'extra info')
    );
    $order = \Conekta\Order::create($validOrderWithCheckout);
    if(!is_null($order))
    {
      return $order->checkout->url;
    }
    return null;
  }
  protected function createPaymentRequest($data){
    $token = base64_encode($this->private_key);
    $data = [
      "name" => "Online Shopping",
      "type" => "HostedPayment",
      "recurrent" => false,
      "needs_shipping_contact" => false,
      "expires_at" => strtotime('+7 day',strtotime(date('Y-m-d'))),
      "allowed_payment_methods" => ["cash", "card", "bank_transfer"],
      "order_template" => [
        "line_items" => [
          [
            "name" => "Red Wine",
            "unit_price" => 1000,
            "quantity" => 10
          ]
        ],
        "metadata" => [
           "mycustomkey" => "12345",
          "othercustomkey" => "abcd"
        ],
        "currency" => "MXN",
        'customer_info' => [
          'name' => "Juan Perez",
          'email' => "juan.perez@conekta.com",
          'phone' => "5566982090"
        ]
      ],
    ];

    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', 'https://api.conekta.io/checkout', [
      'body' => json_encode($data),
      'headers' => [
        'Accept' => 'application/vnd.conekta-v2.0.0+json',
         'Authorization' => 'Basic '.$token,
        'Content-Type' => 'application/json',
        'accept' => 'application/vnd.conekta-v2.0.0+json',
        'content-type' => 'application/json',
      ],
    ]);
    if($response->getStatusCode() == 200)
    {
      return json_decode((string) $response->getBody());
    }
  }
}
