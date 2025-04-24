<?php
namespace App\Http\Traits;

use App\Models\PaymentOption;
use Paytabscom\Laravel_paytabs\Facades\paypage;
use Auth, Log, Config;
use Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\UserDataVault;
use Illuminate\Support\Collection;

trait AzulPaymentService
{

    public function __construct()
    {
        $this->creds = PaymentOption::where('code', 'azul')->where('status', 1)->first();
        if(@$this->creds->status)
        {
            $this->creds_arr = json_decode($this->creds->credentials);
            $this->MAIN_URL = $this->creds_arr->azul_main_url;
            $this->ALTERNATE_URL = $this->creds_arr->azul_alternate_url;
            $this->TEST_URL = $this->creds_arr->azul_test_url;
            $this->ECOMMERCE_URL = $this->creds_arr->azul_ecommerce_url;
            $this->TEST_MODE = $this->creds->test_mode;
            $this->SAVE_TO_DATAVAULT = 1;
            $this->DONT_SAVE_TO_DATAVAULT = 2;
            $this->HOLD_TRANSACTION = 'Hold';
            $this->REFUND_TRANSACTION = 'Refund';
            $this->PAYMENT_CHANNEL = 'EC';
            $this->OK_RESPONSE_CODE = '00';
            $this->AZUL_OK_RESPONSE_CODE = 'ISO8583';
            $this->MERCHANT_ID = $this->creds_arr->azul_merchant_id;
            $this->POST_INPUT_MODE = 'E-Commerce';
            $this->AUTH_1_HEADER = $this->creds_arr->azul_auth_header_one;
            $this->AUTH_2_HEADER = $this->creds_arr->azul_auth_header_two;
            $this->SSL_CERTIFICATE = $this->creds->getPath($this->creds_arr->azul_ssl_certificate);
            $this->SSL_KEY = $this->creds->getPath($this->creds_arr->azul_ssl_key);
            $this->errors = [
                'INSUF FONDOS' => 'Tu tarjeta no tiene fondos suficientes para completar la transacción'
            ];
            $this->mode = true;
            if ($this->TEST_MODE) {
                $this->mode = false;
            }
        }
    }

    /**
     * Make a payment(on Hold) with a given card.
     *
     * @param App\Entities\CreditCardEntity $card
     *
     * @return array
     */
    public function payWithCard($card)
    {
        $phone_number = auth()->user()->phone_number;
        $user_id = auth()->user()->id;         
        $saveVault = 0;
        if(isset($card['card_id']) && !empty($card['card_id'])){
            $userCard = UserDataVault::where(['id' => $card['card_id']])->first();
            $request = [
                'Channel' => $this->PAYMENT_CHANNEL,
                'Store' => $this->MERCHANT_ID,
                'CardNumber' => '',
                'Expiration' => '',
                'PosInputMode' => $this->POST_INPUT_MODE,
                'TrxType' => 'Sale',
                'Amount' => $this->parseAmount($card['amount']),
                'Itbis' => '000',
                'CurrencyPosCode' => '$',
                'Payments' => '1',
                'Plan' => '0',
                'AcquirerRefData' => '1',
                "RRN" => '',
                'CustomerServicePhone' => $phone_number,
                'OrderNumber' => $card['order_number'],
                'ECommerceUrl' => $this->ECOMMERCE_URL,
                'CustomOrderId' => $card['order_number'],
                'DataVaultToken' => $userCard->token,
                'ForceNo3DS' => '0'
            ];
        }else{
            $forceNo3DS = 1;
            if (isset($card['come_from']) && $card['come_from'] == 'app') {
                $expiry = $card['dt'];
            }else{
                $exp = explode('/', $card['dt']);
                $expiry = $exp[1] . $exp[0];
            }
            if(isset($card['save_card']) && $card['save_card'] == 1){
                $saveVault = 1;
                $forceNo3DS = 0;
            }
            
            $request = [
                'Channel' => $this->PAYMENT_CHANNEL,
                'Store' => $this->MERCHANT_ID,
                'CardNumber' => $card['cno'],
                'Expiration' => $expiry,
                'CVC' => $card['cv'],
                'PosInputMode' => $this->POST_INPUT_MODE,
                'TrxType' => 'Sale',
                'Amount' => $this->parseAmount($card['amount']),
                'Itbis' => '000',
                'CurrencyPosCode' => '$',
                'Payments' => '1',
                'Plan' => '0',
                'AcquirerRefData' => '1',
                "RRN" => '',
                'CustomerServicePhone' => $phone_number,
                'OrderNumber' => $card['order_number'],
                'ECommerceUrl' => $this->ECOMMERCE_URL,
                'CustomOrderId' => $card['order_number'],
                'SaveToDataVault' => $saveVault,
                'DataVaultToken' => '',
                'ForceNo3DS' => $forceNo3DS
            ];
            if($saveVault){
               $this->saveCardToDatavault($user_id, $card['cno'], $expiry, $card['cv']);
            }
        }
        $response = $this->sendRequest($request);
       if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false,
                'data' => $response['data']
            ];
        }

        if ($response['data']->ResponseCode !== $this->AZUL_OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ErrorDescription,
                 'ok' => false,
                'data' => $response['data']
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ResponseMessage,
                'ok' => false,
                'data' => $response['data']
            ];
        }

        return [
            'message' => 'ok',
            'ok' => true,
            'data' => $response['data']
        ];

        // Checks if azul_payWithCard response is OK . dd($response);
        return $response;
    }

    /**
     * Make a payment(on Hold) with data vault.
     *
     * @param string $amount
     * @param integer $order_id
     * @param App\Models\UserDataVault $datavault
     *
     * @return array
     */
    public function payWithDatavault($amount, $order_id, UserDataVault $datavault)
    {
        $order = Order::find($order_id);

        if (is_null($order)) {
            return [
                'message' => 'Order not found',
                'ok' => true,
                'data' => null
            ];
        }

        $request = [
            'Channel' => $this->PAYMENT_CHANNEL,
            'Store' => $this->MERCHANT_ID,
            'CardNumber' => '',
            'Expiration' => '',
            'PosInputMode' => $this->POST_INPUT_MODE,
            'TrxType' => $this->HOLD_TRANSACTION,
            'Amount' => $this->parseAmount($amount),
            'Itbis' => '',
            'CurrencyPosCode' => '',
            'Payments' => '1',
            'Plan' => '0',
            'AcquirerRefData' => '1',
            'CustomerServicePhone' => '809-222-3344',
            'OrderNumber' => $order_id,
            'ECommerceUrl' => $this->ECOMMERCE_URL,
            'CustomOrderId' => $order_id,
            'DataVaultToken' => $datavault->token,
            "ForceNo3DS" => '1'
        ];

        $response = $this->sendRequest($request);

        if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false
            ];
        }

        if ($response['data']->ResponseCode !== $this->AZUL_OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ErrorDescription,
                'ok' => false
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ResponseMessage,
                'ok' => false
            ];
        }

        return [
            'message' => 'ok',
            'ok' => true,
            'data' => $response['data']
        ];
    }

    /**
     * Cancel a transaction
     *
     * @param
     *            $azul_order_id
     */
    public function voidTransaction($azul_order_id)
    {
        $request = [
            'Channel' => $this->PAYMENT_CHANNEL,
            'Store' => $this->MERCHANT_ID,
            'AzulOrderId' => $azul_order_id
        ];

        $response = $this->sendRequest($request, '?processvoid');

        if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ResponseMessage . ' ' . $response['data']->ErrorDescription,
                'ok' => false
            ];
        }

        return [
            'message' => 'ok',
            'ok' => true,
            'data' => $response['data']
        ];
    }

    /**
     * Refund a transaction
     *
     * @param
     *            $azul_order_id
     */
    public function refundTransaction($azul_order_id, $amount, $order_id, $order_date)
    {
        $phone_number = auth()->user()->phone_number;
        $request = [
            'Channel' => $this->PAYMENT_CHANNEL,
            'Store' => $this->MERCHANT_ID,
            'CardNumber' => '',
            'Expiration' => '',
            'CVC' => '',
            'PosInputMode' => $this->POST_INPUT_MODE,
            'TrxType' => $this->REFUND_TRANSACTION,
            'Amount' => $this->parseAmount($amount),
            'Itbis' => '',
            'CurrencyPosCode' => '',
            'Payments' => '1',
            'Plan' => '0',
            'OriginalDate' => date('Ymd', strtotime($order_date)),
            'OriginalTrxTicketNr' => '',
            'AuthorizationCode' => '',
            'ResponseCode' => '',
            'AcquirerRefData' => '',
            'RRN' => null,
            'AzulOrderId' => $azul_order_id,
            'CustomerServicePhone' => $phone_number,
            'OrderNumber' => '',
            'ECommerceUrl' => $this->ECOMMERCE_URL,
            'CustomOrderId' => $order_id,
            'DataVaultToken' => '',
            'SaveToDataVault' => '0',
            'ForceNo3DS' => '1'
        ];

        $response = $this->sendRequest($request);
        if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ResponseMessage,
                'ok' => false
            ];
        }

        return [
            'message' => 'ok',
            'ok' => true,
            'data' => $response['data']
        ];
    }

    /**
     * Confirm a transaction in hold.
     *
     * @param int $azul_order_id
     * @param double $amount
     * @param
     *            double #itbis
     * @return array
     */
    public function confirmTransaction($azul_order_id, $amount, $itbis = ""): array
    {
        $itbis = (int) $amount * 0.18;
        $request = [
            'Channel' => $this->PAYMENT_CHANNEL,
            'Store' => $this->MERCHANT_ID,
            'Amount' => $this->parseAmount($amount),
            'Itbis' => $this->parseAmount($itbis),
            'AzulOrderId' => $azul_order_id
        ];

        $response = $this->sendRequest($request, '?processpost');

        if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ResponseMessage,
                'ok' => false
            ];
        }

        return [
            'message' => 'ok',
            'ok' => true,
            'data' => $response['data']
        ];
    }

    /**
     * Make http request to provider.
     *
     * @param array $req
     * @param string $url_params
     */
    private function sendRequest($req, $url_params = null)
    {
        $response = [
            'code' => '',
            'message' => '',
            'data' => null
        ];

        try {
            $client = new Client(); // GuzzleHttp\Client
            $result = $client->post(! is_null($url_params) ? $this->MAIN_URL . $url_params : $this->MAIN_URL, [
                'headers' => [
                    "Content-type" => "application/json",
                    "Auth1" => $this->AUTH_1_HEADER,
                    "Auth2" => $this->AUTH_2_HEADER
                ],
                'json' => $req,
                'cert' => $this->SSL_CERTIFICATE,
                'ssl_key' => $this->SSL_KEY
            ]);
            $response['message'] = $result->getReasonPhrase();
            $response['code'] = $result->getStatusCode();
            $response['data'] = json_decode($result->getBody());
        } catch (ClientException $e) {
           $response['message'] =$e->getMessage(); 
        }

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => ! is_null($url_params) ? $this->MAIN_URL . $url_params : $this->MAIN_URL,
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_SSL_VERIFYPEER => false,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

        // CURLOPT_SSLCERT => public_path('certs/from_azul_speedy_pro.pem'),
        // CURLOPT_SSLKEY => public_path('certs/speedy-prod-v2.pem'),
        // CURLOPT_CUSTOMREQUEST => "POST",
        // CURLOPT_POSTFIELDS => json_encode($req),
        // CURLOPT_HTTPHEADER => array(
        // "Auth1: " . $this->AUTH_1_HEADER,
        // "Auth2: " . $this->AUTH_2_HEADER,
        // "Content-Type: application/json"
        // )
        // ));

        // $result = curl_exec($curl);

        // if (curl_errno($curl)) {
        // $response['message'] = curl_error($curl);
        // }

        // $response['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // // If server err, use alternate URL
        // if ($response['code'] == 500) {
        // curl_setopt_array($curl, [
        // CURLOPT_URL => ! is_null($url_params) ? $this->ALTERNATE_URL . $url_params : $this->ALTERNATE_URL
        // ]);

        // $result = curl_exec($curl);
        // if (curl_errno($curl)) {
        // $response['message'] = curl_error($curl);
        // }
        // $response['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // curl_close($curl);
        // } catch (\Exception $e) {
        // $response['message'] = $e->getMessage();
        // }

        return $response;
    }

    /**
     * Save user card to provider datavault.
     *
     * @param integet $user_id
     * @param string $card_number
     * @param string $expiration_date
     * @param integet $cvc
     * @return array
     *
     */
    public function saveCardToDatavault($user_id, $card_number, $expiration_date, $cvc): array
    {
        $request = [
            'Channel' => $this->PAYMENT_CHANNEL,
            'Store' => $this->MERCHANT_ID,
            'CardNumber' => $card_number,
            'Expiration' => $expiration_date,
            'CVC' => $cvc,
            'TrxType' => 'CREATE'
        ];

        $response = $this->sendRequest($request, '?ProcessDatavault');

        if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ErrorDescription,
                'ok' => false
            ];
        }
        $datavault = UserDataVault::create([
            'user_id' => $user_id,
            'token' => $response['data']->DataVaultToken,
            'expiration' => $response['data']->Expiration,
            'brand' => $response['data']->Brand,
            'card_hint' => $response['data']->CardNumber
        ]);
        
        return [
            'ok' => true,
            'data_vault' => $datavault,
            'full_response' => json_encode($response['data'])
        ];
    }

    /**
     * Retrieve user stored cards.
     */
    public function getUserCardsList(): Collection
    {
        return UserDataVault::where('user_id', auth()->user()->id)->orderBy('is_default', 'desc')->get();
    }

    /**
     * Delete user datavault.
     *
     * @param \App\Models\UserDataVault $datavault
     * @return array
     */
    public function deleteDatavault(UserDataVault $datavault): array
    {
        $request = [
            'Channel' => $this->PAYMENT_CHANNEL,
            'Store' => $this->MERCHANT_ID,
            'TrxType' => 'DELETE',
            'DataVaultToken' => $datavault->token
        ];

        $response = $this->sendRequest($request, '?ProcessDatavault');

        if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ErrorDescription,
                'ok' => false
            ];
        }

        $datavault->delete();

        return [
            'message' => 'ok',
            'ok' => true
        ];
    }

    /**
     * Parse amount value to be acceptable by provider.
     */
    public function parseAmount($amount)
    {
        if (strpos($amount, '.') !== false) {
            $decimals = explode('.', $amount)[1];
            if (strlen($decimals) == 1) {
                $amount .= '0';
            }
            return str_replace('.', '', $amount);
        }
        return $amount . '00';
    }

    /**
     * Verify a transaction
     *
     * @param
     *            $azul_order_id
     */
    public function verifyTransaction($order_id)
    {
        $request = [
            'Channel' => $this->PAYMENT_CHANNEL,
            'Store' => $this->MERCHANT_ID,
            'CustomOrderId' => $order_id
        ];

        $response = $this->sendRequest($request);

        if ($response['code'] != 200) {
            return [
                'message' => $response['message'],
                'ok' => false
            ];
        }

        if ($response['data']->IsoCode !== $this->OK_RESPONSE_CODE) {
            return [
                'message' => $response['data']->ResponseMessage,
                'ok' => false
            ];
        }

        return [
            'message' => 'ok',
            'ok' => true,
            'data' => $response['data']
        ];
    }

    private function formattErrorMessage($error_code): string
    {
        return isset($this->errors[$error_code]) ? $this->errors[$error_code] : $error_code;
    }
}

