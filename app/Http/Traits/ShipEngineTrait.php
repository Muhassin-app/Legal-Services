<?php
namespace App\Http\Traits;

use App\Models\Cart;
use App\Models\ShippingOption;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Http;

trait ShipEngineTrait
{
    use TaxJarTrait{
        TaxJarTrait::__construct as TaxJarTraitConstruct;
    }
    public $ship_engine_api_key;
    public $url;
    public $header;
    public $service_code;
    public $status;
    public $carrier_ids;
    
    public function __construct()
    {
        $this->TaxJarTraitConstruct();
        $creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'shipengine')->where('status', 1)->first();

        if(isset($creds) && !empty($creds)){
            $creds_arr = json_decode($creds->credentials);

            $this->ship_engine_api_key = $creds_arr->api_key;
            $this->service_code = $creds_arr->service_code;
            $this->url = "https://api.shipengine.com/v1";
            $this->header = [
                'API-Key' => $this->ship_engine_api_key,
                'Content-Type' => 'application/json',
            ];
            $this->status = $creds->status ?? 0;
            $this->carrier_ids = $creds_arr->carrier_ids;
        } 
    }

    public function shipEngineAddressValidate($address)
    {
        // \Log::info(['address' => $address]);
        $addr = explode(',',$address['address']);
        $response =  Http::withHeaders($this->header)->post($this->url.'/addresses/validate',[
            [
                "address_line1"  =>  $addr[0],
                "city_locality"  =>  $address['city'],
                "state_province" =>  $address['state'],
                "postal_code"    =>  $address['pincode'],
                "country_code"   =>  $address['country_code']
            ]
        ])->json();
        $response['message'] = "Address is not Validate for Shipping";
        // \Log::info($response);
        return $response;
    }

    public function shipEngineRateEstimate($data)
    {
        $vendor = $data['vendor'];

        $total_weight = 0;

        foreach ($data->vendorProducts as $key => $vendor_product) {
            $total_weight += $vendor_product['product']['weight'] ?? 0;
        }
                
        $user_address = UserAddress::where('user_id', auth()->user()->id)->where('status',1)->orderBy('is_primary','Desc')->first();

        $response =  Http::withHeaders($this->header)->post($this->url.'/rates/estimate',[
            "carrier_ids" => [
                $this->carrier_ids
            ],
            "service_codes" => [
                $this->service_code
            ],
            "from_postal_code" =>  $vendor['pincode'],
            "from_country_code" =>  $vendor['country_code'],
            "to_city_locality" => $user_address['city'],
            "to_state_province" => $user_address['state_code'],
            "to_postal_code" => $user_address['pincode'],
            "to_country_code" => $user_address['country_code'],
            "weight" => [
                "value" => $total_weight,
                "unit" => "ounce"
            ],
            "confirmation" => "none",
            "address_residential_indicator" => "no"
            
        ])->json();
        // dd($response);
        // \Log::info($response);
        return $response[0]['shipping_amount']['amount'];
    }

    public function shipEngineWebhooks()
    {
        // \Log::info(['address' => $address]);
        $response =  Http::withHeaders($this->header)->get($this->url.'/shipments/se-786508496')->json();
        // \Log::info($response);
        return $response;
    }

    public function webhookTrack()
    {
        // \Log::info(['address' => $address]);
        $response =  Http::withHeaders($this->header)->post($this->url.'/environment/webhooks',[
            "resource_url" => $this->url."/tracking?carrier_code=usps&tracking_number=9400111298370264401222",
            "resource_type" => "API_TRACK"
        ])->json();
        // \Log::info($response);
        return $response;
    }

    public function getShippingDetail($id)
    {
        return  Http::withHeaders($this->header)->get($this->url.'/shipments/'.$id);
    }

    public function getLabelFee($data)
    {
        $vendor = $data['ordervendor']['vendor'];
        $total_weight = 0;

        foreach ($data->products as $key => $order_product) {
            $total_weight += $order_product['product']['weight'] ?? 0;
        }
                
        $user_address = UserAddress::where('id', $data['address_id'])->first();

        $response =  Http::withHeaders($this->header)->post($this->url.'/labels',[
            "shipment" => [
                "service_code" => $this->service_code,
                "ship_to" => [
                    "name" => $data['user']['name'],
                    "address_line1" => explode(',',$user_address['address'])[0],
                    "city_locality" => $user_address['city'],
                    "state_province" => $user_address['state_code'],
                    "postal_code" => $user_address['pincode'],
                    "country_code" => $user_address['country_code'],
                    "address_residential_indicator" => "yes",
                ],
                "ship_from" => [
                    "name" => "John Doe",
                    "company_name" =>  $vendor['name'],
                    "phone" =>  $vendor['phone_no'],
                    "address_line1" =>  explode(',',$vendor['address'])[0],
                    "city_locality" =>  $vendor['city'],
                    "state_province" => $vendor['state_code'],
                    "postal_code" =>  $vendor['pincode'],
                    "country_code" =>  $vendor['country_code'],
                    "address_residential_indicator" => "yes",
                ],
                "packages" => [
                    [
                        "weight" => [
                            "value" => $total_weight,
                            "unit" => "pound"
                        ]
                    ]
                ]
            ]
        ]);
        // \Log::info(['response' => $response['errors'][0]['message']]);
        if (isset($response['errors'])) {
            return ['status' => 208,'message' => $response['errors'][0]['message']];
        }

        return $response;
    }

    public function trackingUrlByLabelId($label_id)
    {
        $response = Http::withHeaders($this->header)->get($this->url.'/labels/'.$label_id.'/track/');
        return $response['tracking_url'];
    }
}
