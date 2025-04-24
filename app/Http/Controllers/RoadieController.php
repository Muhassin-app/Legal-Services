<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\VendorOrderStatus;
use App\Models\ShippingOption;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\VendorOrderDispatcherStatus;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;
use Log,DB;
use Illuminate\Support\Str;
use \App\Http\Traits\RoadieTrait;
use \App\Http\Traits\ApiResponser;

class RoadieController extends Controller
{
    use RoadieTrait, ApiResponser;
    
    private $api_access_token;
    private $api_base_url;
    public $roadie_status;
    
    public function __construct(){
        $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'roadie')->where('status', 1)->first();
        if($simp_creds && $simp_creds->credentials){
            $creds_arr = json_decode($simp_creds->credentials);
            $this->api_access_token = $creds_arr->api_access_token??'';
            $this->api_base_url = $creds_arr->api_base_url??'';
            $this->roadie_status = $simp_creds->status??'';
        }
    }

    public function getEstimate($vendorData,$address){
        try {  
            $items = [];
            foreach($vendorData->vendorProducts as $vendorProduct){
                $items[] = [
                    'weight' => $vendorProduct->product->weight,
                    'length' => $vendorProduct->product->length,
                    'width' => $vendorProduct->product->breadth,
                    'height' => $vendorProduct->product->height,
                    'quantity' => $vendorProduct->quantity
                ];
            }
            $postData = [
                "items" => $items,
                "pickup_location" => [
                    "address" => [
                        "name" => $vendorData->vendor->address,
                        "street1" => $vendorData->vendor->address,
                        "street2" => null,
                        "city" => $vendorData->vendor->address,
                        "state" => $vendorData->vendor->address,
                        "zip" => $vendorData->vendor->pincode,
                        "latitude" => $vendorData->vendor->latitude,
                        "longitude" => $vendorData->vendor->longitude
                    ]
                ],
                "delivery_location" => [
                    "address" => [
                        "name" => $address->address,
                        "street1" => $address->street,
                        "street2" => null,
                        "city" => $address->city,
                        "state" => $address->state,
                        "zip" => $address->pincode,
                        "latitude" => $address->latitude,
                        "longitude" => $address->longitude
                    ]
                ],
                "pickup_after" => date('Y-m-d H:i:s'),
                "deliver_between" => [
                    "start" => date('Y-m-d H:i:s', strtotime('+1 days')),
                    "end" => date('Y-m-d H:i:s', strtotime('+5 days'))
                ]
            ];
            $quotation = $this->getQuotations($postData);
            return $quotation;
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createShipmentRequestRoadie($orderVendor, $checkOrderData){
        try {
            $date = $checkOrderData->scheduled_date_time ?? date('Y-m-d H:i:s');
            $reference_id = "Refr".$orderVendor->orderDetail->order_number;
            $items = [];
            foreach($orderVendor->products as $vendorProduct){
                $items[] = [
                    "description" => $vendorProduct->product->description??"Item description",
                    'weight' => $vendorProduct->product->weight,
                    'length' => $vendorProduct->product->length,
                    'width' => $vendorProduct->product->breadth,
                    'height' => $vendorProduct->product->height,
                    'quantity' => $vendorProduct->quantity,
                    'value' => $vendorProduct->price
                ];
            }
            $postData = [
                "reference_id" => $reference_id,
                "items" => $items,
                "pickup_location" => [
                    "address" => [
                        "name" => $orderVendor->vendor->name??'',
                        "street1" => $orderVendor->vendor->address??'',
                        "street2" => null,
                        "city" => $orderVendor->vendor->city??'',
                        "state" => $orderVendor->vendor->state??'',
                        "zip" => $orderVendor->vendor->pincode??'',
                        "latitude"=> $orderVendor->vendor->latitude??'',
                        "longitude"=> $orderVendor->vendor->longitude??''
                    ],
                    "contact" => [
                        "name" => $orderVendor->vendor->name,
                        "phone" => $orderVendor->vendor->phone_no
                    ],
                    "notes" => null
                ],
                "delivery_location" => [
                    "address" => [
                        "name" => $checkOrderData->address->house_number,
                        "store_number" => null,
                        "street1" => $checkOrderData->address->address,
                        "street2" => null,
                        "city" => $checkOrderData->address->city,
                        "state" => $checkOrderData->address->state,
                        "zip" => $checkOrderData->address->pincode,
                        "latitude"=> $checkOrderData->address->latitude,
                        "longitude"=> $checkOrderData->address->longitude
                    ],
                    "contact" => [
                        "name" => $checkOrderData->user->name,
                        "phone" => $checkOrderData->user->phone_number
                    ],
                    "notes" => null
                ],
                "pickup_after" => $date,
                "deliver_between" => [
                    "start" => date('Y-m-d H:i:s', strtotime($date. '+1days')),
                    "end" => date('Y-m-d H:i:s', strtotime($date. ' +5days'))
                ],
                "options" => [
                    "signature_required" => true,
                    "notifications_enabled" => false,
                    "over_21_required" => false,
                    "extra_compensation" => 0.0,
                    "trailer_required" => false,
                    "decline_insurance" => true
                ]
            ];
            $response = $this->createShipmentRoadie($postData);
            return $response;
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function roadieWebhook(Request $request){
        $json = json_decode($request->getContent());
        dd($json);
        try {
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}