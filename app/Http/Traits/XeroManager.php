<?php

namespace App\Http\Traits;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use App\Http\Traits\XeroStorageClass as StorageClass; 
use XeroAPI, GuzzleHttp, Session, DateTime, Auth;


trait XeroManager{

    public function setProvider()
    {
        return new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $this->client_id,   
            'clientSecret'            => $this->secret_id,
            'redirectUri'             => route('callback_xero'),
            // 'redirectUri'             => "https://d026-103-72-170-243.ngrok.io/auth/callback/xero",
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
        ]);
    }

    public function authorization()
    {
        $provider = $this->setProvider();

        // Scope defines the data your app has permission to access.
        // Learn more about scopes at https://developer.xero.com/documentation/oauth2/scopes
        $options = [
            'scope' => ['openid email profile offline_access accounting.settings accounting.transactions accounting.contacts accounting.journals.read accounting.reports.read accounting.attachments']
        ];

        // This returns the authorizeUrl with necessary parameters applied (e.g. state).
        $authorizationUrl = $provider->getAuthorizationUrl($options);
        // dd($authorizationUrl);

        // Save the state generated for you and store it to the session.
        // For security, on callback we compare the saved state with the one returned to ensure they match.
        Session::put('oauth2state', $provider->getState());
        // dd(Session::get('oauth2state'));

        // Redirect the user to the authorization URL.
        header('Location: ' . $authorizationUrl);
        exit();
    }

    public function callback()
    {
        // Storage Classe uses sessions for storing token > extend to your DB of choice
        $storage = new StorageClass();  

        $provider = $this->setProvider();
   
        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {
            echo "Something went wrong, no authorization code found";
            exit("Something went wrong, no authorization code found");

        // Check given state against previously stored one to mitigate CSRF attack
        // } elseif (empty($_GET['state']) || ($_GET['state'] !== Session::get('oauth2state'))) {
        //     echo "Invalid State";
        //     unset($_SESSION['oauth2state']);
        //     exit('Invalid state');
        }else{
            try {
              // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
                   
                $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$accessToken->getToken() );
                $identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
                    new GuzzleHttp\Client(),
                    $config
                );
               
                $result = $identityApi->getConnections();

                // Save my tokens, expiration tenant_id
                $storage->setToken(
                    $accessToken->getToken(),
                    $accessToken->getExpires(),
                    $result[0]->getTenantId(),  
                    $accessToken->getRefreshToken(),
                    $accessToken->getValues()["id_token"]
                );  
                return ;
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                echo "Callback failed";
                exit();
            }
        }
    }
    public function authorizedResource()
    {
        // Storage Classe uses sessions for storing token > extend to your DB of choice
        $storage = new StorageClass();
        $xeroTenantId = (string)$storage->getSession()['tenant_id'];

        if ($storage->getHasExpired()) {
            $provider = $this->setProvider();

            $newAccessToken = $provider->getAccessToken('refresh_token', [
              'refresh_token' => $storage->getRefreshToken()
            ]);

            // Save my token, expiration and refresh token
            $storage->setToken(
                $newAccessToken->getToken(),
                $newAccessToken->getExpires(),
                $xeroTenantId,
                $newAccessToken->getRefreshToken(),
                $newAccessToken->getValues()["id_token"]
            );
        }

        $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );
        $apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
            new GuzzleHttp\Client(),
            $config
        );
        return $apiInstance;   
    }

    public function createContact($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp, $user)
    {

        $phone = new XeroAPI\XeroPHP\Models\Accounting\Phone;
        $phone->setPhoneNumber('+'.$user->dial_code??'91'.$user->phone_number??'9876543210');
        $phone->setPhoneType(XeroAPI\XeroPHP\Models\Accounting\Phone::PHONE_TYPE_MOBILE);
        $phones = [];
        array_push($phones, $phone);

        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setName($user->name ?? 'Default Name');
        $contact->setEmailAddress($user->email ?? 'default@yopmail.com');
        $contact->setPhones($phones);

        $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
        $arr_contacts = [];
        array_push($arr_contacts, $contact);
        $contacts->setContacts($arr_contacts);

        try {
          $result = $apiInstance->updateOrCreateContacts($xeroTenantId, $contacts, $summarizeErrors);
          return $result[0];
        } catch (Exception $e) {
            dd($e);
          echo 'Exception when calling AccountingApi->createContacts: ', $e->getMessage(), PHP_EOL;
        }
    }

    public function createItem($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp, $product)
    {
        $description = $product->product_name ;

        foreach($product->addon as $p_addon){
           $description = $description." + ".(isset($p_addon->option) ? $p_addon->option->title : "");
        }
        $item = new XeroAPI\XeroPHP\Models\Accounting\Item;
        //code length must be less than or equa lto 30 characters
        $item->setCode(isset($product->product) ? (strlen($product->product->sku) > 30 ? mt_rand(10000000,99999999) : $product->product->sku ) : mt_rand(10000000,99999999));
        $item->setName($product->product_name);
        $item->setDescription($description);

        $items = new XeroAPI\XeroPHP\Models\Accounting\Items;
        $arr_items = [];
        array_push($arr_items, $item);
        $items->setItems($arr_items);

        try {
          $result = $apiInstance->updateOrCreateItems($xeroTenantId, $items, $summarizeErrors, $unitdp);
          return $result[0];

        } catch (Exception $e) {
          echo 'Exception when calling AccountingApi->updateOrCreateItems: ', $e->getMessage(), PHP_EOL;
        }
    }
    public function createOtherItem($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp,$title)
    {
        $item = new XeroAPI\XeroPHP\Models\Accounting\Item;
        //code length must be less than or equa lto 30 characters
        $item->setCode(mt_rand(10000000,99999999));
        $item->setName($title);
        $item->setDescription($title);

        $items = new XeroAPI\XeroPHP\Models\Accounting\Items;
        $arr_items = [];
        array_push($arr_items, $item);
        $items->setItems($arr_items);

        try {
          $result = $apiInstance->updateOrCreateItems($xeroTenantId, $items, $summarizeErrors, $unitdp);
          return $result[0];

        } catch (Exception $e) {
          echo 'Exception when calling AccountingApi->updateOrCreateItems: ', $e->getMessage(), PHP_EOL;
        }
    }

    public function createInvoice($order)
    {
        $apiInstance = $this->authorizedResource();
        $storage = new StorageClass();
        $xeroTenantId = (string)$storage->getSession()['tenant_id'];

        $summarizeErrors = true;
        $unitdp = 4;
        $dateValue = date('Y-m-d');
        $dueDateValue = date('Y-m-d',strtotime('+7 day',strtotime($dateValue)));
        $createContact = $this->createContact($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp, $order->user);

        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactID($createContact['contact_id']); 
        $lineItems = [];
        foreach($order->products as $product)
        {
            $product_price = $product->price ?? 0;
            foreach($product->addon as $p_addon)
            {
                $product_price = $product_price + (isset($p_addon->option) ? $p_addon->option->price : 0); 
            }
            $discount_price = ($product->quantity * $product_price)/$order->subtotal_amount * $order->total_discount ;
            $item = $this->createItem($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp,$product);
            $lineItem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
            $lineItem->setDescription($item['description']);
            $lineItem->setQuantity($product->quantity ?? 1);
            $lineItem->setUnitAmount(decimal_format($product_price));
            $lineItem->setAccountCode('429'); //General Expense
            $lineItem->setItemCode($item['code']);
            $lineItem->setItem($item);
            $lineItem->setTaxAmount(decimal_format($product->taxable_amount));
            $lineItem->setDiscountAmount(decimal_format($discount_price));
            // $lineItem->setLineAmount(decimal_format($product->quantity * $product_price - $discount_price));
            
            array_push($lineItems, $lineItem);
        }
        //Add Shipping Charges
        if($order->delivery_fee > 0)
        {
            $item = $this->createOtherItem($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp,'Shipping Charges');
            $lineItem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
            $lineItem->setDescription($item['description']);
            $lineItem->setQuantity(1);
            $lineItem->setUnitAmount(decimal_format($order->delivery_fee));
            $lineItem->setAccountCode('429'); //General Expense
            $lineItem->setItemCode($item['code']);
            $lineItem->setItem($item);
            $lineItem->setTaxAmount(0);
            $lineItem->setDiscountAmount(0); 

            array_push($lineItems, $lineItem); 
        }
        // Add Tip Amount
        if($order->orderDetail->tip_amount > 0) 
        {
            $tip = 0;
            if($order->orderDetail->total_amount > 0)
            $tip = $order->subtotal_amount/$order->orderDetail->total_amount * $order->orderDetail->tip_amount;
            $item = $this->createOtherItem($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp,'Tip Amount');
            $lineItem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
            $lineItem->setDescription($item['description']);
            $lineItem->setQuantity(1);
            $lineItem->setUnitAmount(decimal_format($tip));
            $lineItem->setAccountCode('429'); //General Expense
            $lineItem->setItemCode($item['code']);
            $lineItem->setItem($item);
            $lineItem->setTaxAmount(0);
            $lineItem->setDiscountAmount(0);

            array_push($lineItems, $lineItem); 
        }
        //Add Service Fee
        if($order->service_fee_percentage_amount > 0){
            $item = $this->createOtherItem($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp,'Service Fee');
            $lineItem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
            $lineItem->setDescription($item['description']);
            $lineItem->setQuantity(1);
            $lineItem->setUnitAmount(decimal_format($order->service_fee_percentage_amount));
            $lineItem->setAccountCode('429'); //General Expense
            $lineItem->setItemCode($item['code']);
            $lineItem->setItem($item);
            $lineItem->setTaxAmount(0);
            $lineItem->setDiscountAmount(0); 

            array_push($lineItems, $lineItem); 
        }


        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCREC);
        $invoice->setContact($contact);
        $invoice->setDate($dateValue);
        $invoice->setDueDate($dueDateValue);
        $invoice->setLineItems($lineItems);
        $invoice->setReference($order->paymentOption->title ?? 'Cash On Delivery');
        $invoice->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED); 

        $invoices = new XeroAPI\XeroPHP\Models\Accounting\Invoices;
        $arr_invoices = [];
        array_push($arr_invoices, $invoice);
        $invoices->setInvoices($arr_invoices);
        try {
          $result = $apiInstance->updateOrCreateInvoices($xeroTenantId, $invoices, $summarizeErrors, $unitdp);
          $payment = $this->createPayment($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp,$result[0]['invoice_id'],$result[0]['amount_due']);
          return $result[0];
        } catch (Exception $e) {
          echo 'Exception when calling AccountingApi->updateOrCreateInvoices: ', $e->getMessage(), PHP_EOL;
        } 
    }
    public function getBankAccount($xeroTenantId,$apiInstance)
    {
        // READ only ACTIVE
        $where = 'Status=="' . \XeroAPI\XeroPHP\Models\Accounting\Account::STATUS_ACTIVE .'" AND Type=="' .  \XeroAPI\XeroPHP\Models\Accounting\Account::BANK_ACCOUNT_TYPE_BANK . '"';
        $result = $apiInstance->getAccounts($xeroTenantId, null, $where); 

        return $result[0]['account_id'];
    }   
    public function createPayment($apiInstance, $xeroTenantId, $summarizeErrors, $unitdp,$invoice_id,$amount_due)
    {
        $dateValue = date('Y-m-d');

        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setInvoiceID($invoice_id);

        $account_id = $this->getBankAccount($xeroTenantId, $apiInstance);

        $account = new XeroAPI\XeroPHP\Models\Accounting\Account;
        $account->setAccountID($account_id);

        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setInvoice($invoice);
        $payment->setAccount($account);
        $payment->setAmount($amount_due);
        $payment->setDate($dateValue);

        $payments = new XeroAPI\XeroPHP\Models\Accounting\Payments;
        $arr_payments = [];
        array_push($arr_payments, $payment);
        $payments->setPayments($arr_payments);

        try {
        $result = $apiInstance->createPayment($xeroTenantId, $payment);
        } catch (Exception $e) {
        echo 'Exception when calling AccountingApi->createPayment: ', $e->getMessage(), PHP_EOL;
        }
    }
}