<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\PaymentOption;
use Log;
trait GCashpaymentManager{

  private $public_key;
  public function __construct()
  {
    $gcash_creds = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'gcash')->where('status', 1)->first();
    if(@$gcash_creds->status){
        $creds_arr = json_decode($gcash_creds->credentials);
        $this->public_key = $creds_arr->public_key??'';
        $this->api_url = "https://g.payx.ph";
    }
  }

  // public function createPaymentRequest($data)
  // {
  //   // Set your X-API-KEY with the API key from the Customer Area.
  //   $client = new \Adyen\Client();
  //   $client->setXApiKey($this->public_key);
  //   $service = new \Adyen\Service\Checkout($client);
     
  //   $params = array(
  //     "amount" => array(
  //       "currency" => "PHP",
  //       "value" => 1000
  //     ),
  //     "reference" => "YOUR_ORDER_NUMBER",
  //     "paymentMethod" => array(
  //       "type" => "gcash"
  //     ),
  //     "returnUrl" => "https://sales.alerthire.com",
  //     "merchantAccount" => "YOUR_MERCHANT_ACCOUNT"
  //   );
  //   $result = $service->payments($params);
  // }



  public function createPaymentRequest($data)
  {
    $post_data = array(
      'x-public-key' => $this->public_key,
      'amount' => '100',
      'description' => 'Payment for services rendered',
      'customername' => Auth::user()->name??'',
      'customermobile' => Auth::user()->phone_number,
      'customeremail' => Auth::user()->email??'',
      'merchantname' => getClientDetail()->company_name,
      'merchantlogourl' => getClientDetail()->logo_image_url,
      // 'redirectsuccessurl' => asset($data['returnSuccessUrl']),
      // 'redirectfailurl' => asset($data['returnFailureUrl'])
      'redirectsuccessurl' => 'https://sales.alerthire.com',
      'redirectfailurl' => 'https://sales.alerthire.com',
      'returnUrl' => 'https://sales.alerthire.com'
      );
   // Log::info($post_data);
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://g.payx.ph/payment_request',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $post_data));


    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response);
  }
}
