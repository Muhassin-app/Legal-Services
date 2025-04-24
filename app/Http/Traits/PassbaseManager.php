<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Passbase\Configuration;
use Passbase\api\IdentityApi;
use GuzzleHttp\Client;
use App\Models\VerificationOption;
use Log;
trait PassbaseManager{ 

  public function init()
  {
    $passbase_creds = VerificationOption::select('credentials','test_mode')->where('code','passbase')->where('status',1)->first();
    $creds_arr = json_decode($passbase_creds->credentials);
    $config = Configuration::getDefaultConfiguration()->setApiKey('X-API-KEY', $creds_arr->secret_key ?? '');
    $apiInstance = new IdentityApi(new Client(),$config);
    return $apiInstance;
  }
  public function getIdentity($id)
  {
    try{
      $apiInstance = $this->init();
      $result = $apiInstance->getIdentityById($id);
      return $result;
    } catch (Exception $e) {
      dd($e->getMessage());
      echo 'Exception when calling IdentityApi->getIdentityById: ', $e->getMessage(), PHP_EOL;
    }
  }

  

}
