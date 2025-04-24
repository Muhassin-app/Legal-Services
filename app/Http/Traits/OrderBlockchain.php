<?php
namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\{Client, ClientPreferenceAdditional, PaymentOption, UserAddress, Vendor, VerificationOption};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

trait OrderBlockchain{ 


    public function saveBlockchainOrderDetail($orderData)
    {
        
        $api_domain = ClientPreferenceAdditional::where('key_name','blockchain_api_domain')->first();
        $from_id = ClientPreferenceAdditional::where('key_name','blockchain_address_id')->first();
        $client = Client::first();
        $data = [
            "status" => $orderData->ordervendor->OrderStatusOption->title ?? 'Pending',
            "orderID" => $orderData->id,
            "order_detail" => (array) $orderData,
            "address_short_code" => $client->code,
            "address_f" => $from_id->key_value ?? '',
            'user_id' => $orderData->user_id,
        ];
        
        \Log::info('post data');
        \Log::info($data);
        $headers = [
            'Content-Type' => 'application/json',
        ];
        if(isset($api_domain))
        {
            \Log::info('api domain');
            \Log::info($api_domain->key_value);
            $response = Http::withHeaders($headers)->post($api_domain->key_value.'/createOrder', $data);
        
            $responseData = $response->json();
        }

        \Log::info('create order');
        \Log::info($responseData);
        return response()->json([
            'message' => 'Order created successfully',
            'data' => $data ?? '',
            'api_response' => $responseData ?? '',
        ], 200);
    }
    public function updateBlockchainOrderDetail($orderData)
    {
        
        $api_domain = ClientPreferenceAdditional::where('key_name','blockchain_api_domain')->first();
        $from_id = ClientPreferenceAdditional::where('key_name','blockchain_address_id')->first();
        $client = Client::first();
        $data = [
            "newStatus" => $orderData->ordervendor->OrderStatusOption->title ?? 'Pending',
            "orderID" => $orderData->id,
            "address_short_code" => $client->code,
            "order_detail" => (array) $orderData,
            "address_f" => $from_id->key_value ?? '',
            // 'user_id' => $orderData->user_id
        ];
        \Log::info('update post data');
        \Log::info($data);
        $headers = [
            'Content-Type' => 'application/json',
        ];
        if(isset($api_domain))
        {
            $response = Http::withHeaders($headers)->post($api_domain->key_value.'/updateOrderStatus', $data);
        
            $responseData = $response->json();
        }

        \Log::info('update order');
        \Log::info($responseData);
        return response()->json([
            'message' => 'Order Updated successfully',
            'data' => $data ?? '',
            'api_response' => $responseData ?? '',
        ], 200);
    }
    public function moveOrderToWarehouse($orderData,$request)
    {
        
        $api_domain = ClientPreferenceAdditional::where('key_name','blockchain_api_domain')->first();
        $from_id = ClientPreferenceAdditional::where('key_name','blockchain_address_id')->first();
        $client = Client::first();
        $movement = [];
        if($request->has('warehouse_name') && $request->has('lat') && $request->has('lng'))
        {
            $movement = [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'name' => $request->warehouse_name
            ];
            $json_data = json_encode($movement);
        }

        $data = [
            "lat" => '3.2',
            "address_f" => $from_id->key_value ?? '',
            // "newStatus" => $orderData->ordervendor->OrderStatusOption->title ?? 'Pending',
            "orderID" => $orderData->id,
            "address_short_code" => $client->code,
            "movement" => $json_data ?? [],
            // 'user_id' => $orderData->user_id
        ];
        \Log::info('request data');
        \Log::info($request->all());
        \Log::info('moveOrderToWarehouse data');
        \Log::info($data);
        $headers = [
            'Content-Type' => 'application/json',
        ];
        if(isset($api_domain))
        {
            $response = Http::withHeaders($headers)->post($api_domain->key_value.'/moveOrderToWarehouse', $data);
        
            $responseData = $response->json();
        }

        \Log::info('moveOrderToWarehouse api response');
        \Log::info($responseData);
        return response()->json([
            'message' => 'Order Updated successfully',
            'data' => $data ?? '',
            'api_response' => $responseData ?? '',
        ], 200);
    }
 

}