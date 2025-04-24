<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\PaymentOption;
use Log;
trait PagarmePaymentManager{ 

  private $api_key;
  private $secret_key;
  private $multiplier;
  private $har;
  public function __construct()
  {
    $pagarme_creds = PaymentOption::getCredentials('pagarme');
    $creds_arr = json_decode($pagarme_creds->credentials);
    $this->api_key = $creds_arr->api_key??'';
    $this->secret_key = $creds_arr->secret_key??'';
    $this->multiplier = $creds_arr->multiplier??1;
  }

  public function init()
  {
    $pagarme = new \PagarMe\Client($this->api_key);
    return $pagarme;
  }
  public function create_card($data)
  {
    $pagarme = $this->init();
    $card  =  $pagarme ->cards()->create([
      'holder_name' => $data['holder_name'] ,
      'number' => $data['card_number'] ,
      'expiration_date' => $data['expMonth'].$data['expYear'],
      'cvv' => $data['cvc']
    ]);
    return $card;
  }
  public function create_transaction_via_creditCard($data)
  {
    try{
      $pagarme = $this->init();
      $response = $pagarme->transactions()->create([
        'amount' => $data['amount'],
        'card_id' => $data['card_id'],
        'payment_method' => 'credit_card',
        'postback_url' => 'http://requestb.in/pkt7pgpk',
        'async' => false,
        'customer' => [
          'external_id' => 'EXTERNALID'.$data['customer']->id,
          'name' => $data['customer']->name, 
          'email' => $data['customer']->email,
          'type' => 'individual',
            'country' => 'br',
            'documents' => [
              [
                'type' => 'cpf',
                'number' => '67415765095'
              ]
            ],
            'phone_numbers' => [ $data['phone'] ]
        ],
        'billing' => [
            'name' => $data['customer']->name,
            'address' => [
              'country' => 'br',
              'street' => 'Avenida Brigadeiro Faria Lima',
              // 'street_number' => '1811',
              'state' => 'sp',
              'city' => 'Sao Paulo',
              // 'neighborhood' => 'Jardim Paulistano',
              'zipcode' => '01451001'
            ]
        ],
        'items' => $data['items'],
      ]);
      // dd($transaction);
      return $response;
    }catch(\Exception $ex){
      return null;
    }
  }
  public function create_transaction_via_pix($data)
  { 
    try{
      $pagarme = $this->init();
      $response = $pagarme->transactions()->create([
        // 'amount' => $data['amount'],
        // 'card_id' => $data['card_id'],
        // 'payment_method' => 'credit_card',
        // 'postback_url' => 'http://requestb.in/pkt7pgpk',
        // 'async' => false,
        'customer' => [
          // 'external_id' => 'EXTERNALID'.$data['customer']->id,
          'name' => $data['customer']->name, 
          'email' => $data['customer']->email,
          'type' => 'individual',
          "document" => "01234567890",
          "phones" => [
            "home_phone" => [
              "country_code" => "55",
              "number" => "22180513",
              "area_code" => "21"
            ]
          ]
        ],
        // 'billing' => [
        //     'name' => $data['customer']->name,
        //     'address' => [
        //       'country' => 'br',
        //       'street' => 'Avenida Brigadeiro Faria Lima',
        //       // 'street_number' => '1811',
        //       'state' => 'sp',
        //       'city' => 'Sao Paulo',
        //       // 'neighborhood' => 'Jardim Paulistano',
        //       'zipcode' => '01451001'
        //     ]
        // ],
        'items' => $data['items'],
        'payments' => [
          [
            "payment_method" =>  "pix",
            "pix" => [
                "expires_in" => "52134613",
                "additional_information" => [
                  [
                    "name" => "Quantidade",
                    "value" => "2"
                  ]
                ]
            ]
          ]
        ]
      ]);
    }catch(\Exception $ex){
      Log::info($ex);
      return null;
    }
  }

  public function create_payment_link($data)
  {
    $pagarme = $this->init();
    $paymentLink = $pagarme->paymentLinks()->create([
    'amount' => 10000,
    'postback_url' => 'http://requestb.in/pkt7pgpk',
    'async' => false,
    'items' => [
      [
        'id' => '1',
        'title' => "Fighter's Sword",
        'unit_price' => 4000,
        'quantity' => 1,
        'tangible' => true,
        'category' => 'weapon',
        'venue' => 'A Link To The Past',
        'date' => '1991-11-21'
      ],
      [
        'id' => '2',
        'title' => 'Kokiri Sword',
        'unit_price' => 6000,
        'quantity' => 1,
        'tangible' => true,
        'category' => 'weapon',
        'venue' => "Majora's Mask",
        'date' => '2000-04-27'
      ],
    ],
    'payment_config' => [
      'boleto' => [
        'enabled' => true,
        'expires_in' => 20
      ],
      'credit_card' => [
        'enabled' => true,
        'free_installments' => 4,
        'interest_rate' => 25,
        'max_installments' => 12
      ],
      'default_payment_method' => 'boleto'
    ],
    'max_orders' => 1,
    'expires_in' => 60
  ]);
    dd($paymentLink);
  }
  

}
