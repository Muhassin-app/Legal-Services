<?php
namespace App\Http\Traits;

use App\Models\Cart;
use App\Models\ClientPreferenceAdditional;
use App\Models\ShippingOption;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Http;

trait TaxJarTrait
{
    public $api_token;
    public $taxjar_url;
    public $taxjar_header;
    
    public function __construct()
    {
        $key = ['is_taxjar_enable','taxjar_testmode','taxjar_api_token'];
        $creds = ClientPreferenceAdditional::select('key_name','key_value')->whereIn('key_name',$key)->get();
        $creds = array_column($creds->toArray(), 'key_value', 'key_name');
        
        if(isset($creds) && !empty($creds)){
            $this->api_token = $creds['taxjar_api_token'];
            $this->taxjar_url = $creds['taxjar_testmode'] == 1 ? "https://api.sandbox.taxjar.com/v2" : "https://api.taxjar.com/v2";
            $this->taxjar_header = [
                'Authorization' => 'Bearer '.$this->api_token
            ];
        } 
    }

    public function taxRateEstimate($data)
    {
        $vendor = $data['products'][0]['vendor'];
        $line_items = [];
        foreach ($data->products as $key => $order_product) {
            foreach ($order_product['vendor_products'] as $key => $product) {
                $line_items = [
                    "quantity" => $product['quantity'],
                    "unit_price" => $product['pvariant']['price'],
                    "product_tax_code" => "31000"
                ];
            }
        }
        
        $user_address = UserAddress::where('user_id', auth()->user()->id)->where('status',1)->orderBy('is_primary','Desc')->first();

        // \Log::info($user_address);
        $response =  Http::withHeaders($this->taxjar_header)->post($this->taxjar_url.'/taxes',[
            
            "from_country" => $vendor['country_code'],
            "from_zip" => $vendor['pincode'],
            "from_state" => $vendor['state_code']??'CA',
            "to_country" => $user_address['country_code'],
            "to_zip" => $user_address['pincode'],
            "to_state" => $user_address['state_code']??'CA',
            "amount" => $data['gross_amount'],
            "shipping" => $data['delivery_charges'],
            "line_items" => [
               $line_items
            ]
              
        ])->json();
        // \Log::info($response);
        return isset($response['tax']) ? $response['tax']['rate'] : 0;
    }

    public function createTaxJarOrder($data)
    {
        $line_items = [];
        $total_price = 0;
        // dd($data['orderDetail']);
        foreach ($data->products as $key => $product) {
            $line_items = [
                "quantity" => $product['quantity'],
                "product_identifier" => $product['product_id'],
                "description" => "Fuzzy Widget",
                "unit_price" => $product['price'],
                "sales_tax" => 0
              ];
            $total_price += $product['price'];
        }
                
        $user_address = UserAddress::where('id', $data['orderDetail']['address_id'])->first();
        
        $response =  Http::withHeaders($this->taxjar_header)->post($this->taxjar_url.'/transactions/orders',[
            "transaction_id" => $data['orderDetail']['order_number'],
            "transaction_date" => date('Y-m-d'),
            "to_street" => $user_address['address'],
            "to_city" => $user_address['city'],
            "to_country" => $user_address['country_code'],
            "to_zip" => $user_address['pincode'],
            "to_state" => $user_address['state_code']??'CA',
            "amount" => $total_price + $data['delivery_fee'],
            "shipping" => $data['delivery_fee'],
            "sales_tax" => $data['orderDetail']['taxable_amount'],
            "line_items" => [
              $line_items
            ]
        ])->json();
        \Log::info(['response' => $response]);
        if (isset($response['error'])) {
            return ['status' => 208,'message' => $response['detail']];
        }

        return $response;
    }
}
