<?php
namespace App\Http\Traits;
use Log;
use App\Models\{Order};
use Carbon\Carbon;

trait WhatsappApi{
    
    /**
     * Create Order on https://logisy.tech/
     */
    public function createOrder($order_id) {
        try{
            $order = Order::where('id', $order_id)->first();
            if( !empty($order->payable_amount) ) {
    
                // if order payable amount is not empty
                $order_address = optional($order)->address ?? '';
                $address = [
                    "id"            => $order_address->id ?? '',
                    "zip"           => $order_address->pincode ?? '100000',
                    "city"          => $order_address->city ?? 'City',
                    "phone"         => optional($order->user)->phone_number ?? '1234567890',
                    "country"       => $order_address->country ?? 'country',
                    "default"       => true,
                    "address1"      => $order_address->address ?? 'address',
                    "address2"      => $order_address->address ?? 'address',
                    "province"      => $order_address->city ?? 'City',
                    "country_code"  => $order_address->country_code ?? 'Country Code',
                    "country_name"  => $order_address->country ?? 'Country',
                    "province_code" => 'PRV',
                    "latitude"      => $order_address->latitude ?? '0.00',
                    "longitude"     => $order_address->longitude ?? '0.00'
                ];
    
                // Order Product
                $order_products     = [];
                if( !empty($order->orderVendorProduct) ) {
                    foreach ($order->orderVendorProduct as $key => $value) {
                        
                        $order_products[] = [
                            "id"                => $value->product_id ?? '',
                            "sku"               => optional($value->product)->sku ?? 'sku',
                            "name"              => optional($value->product)->title ?? 'Product Name/Title',
                            "title"             => optional($value->product)->title ?? 'Product Name/Title',
                            "grams"             => 0,
                            "total_tax"         => $value->taxable_amount ?? '0.00',
                            "total_discounts"   => "0.00",
                            "price_without_tax" => "0.00",
                            "total_price"       => "0.00",
                            "quantity"          => $value->quantity ?? '0',
                            "product_id"        => $value->product_id ?? '',
                            "variant_id"        => $value->product_id ?? ''
                        ];
                        
                    }
                }
                
                $api_data = [
                    "id"                => $order->order_number,
                    "note"              => $order->comment_for_vendor ?? '',
                    "tags"              => "some_tag_1,some_tag_2",
                    "first_name"        => optional($order->user)->name ?? 'First Name',
                    "last_name"         => optional($order->user)->name ?? 'Last Name',
                    "email"             => optional($order->user)->email ?? '',
                    "phone"             => optional($order->user)->phone_number ?? '',
                    "currency"          => "INR",
                    "shipping_cost"     => "0.00",
                    "total_tax"         => $order->taxable_amount ?? "0.00",
                    "total_discounts"   => $order->total_discount ?? "0.00",
                    "price_without_tax" => $order->total_amount ?? "0.00",
                    "total_price"       => $order->payable_amount ?? "0.00",
                    "created_at"        => Carbon::parse($order->created_at)->toDateTimeString(),
                    "line_items"        => $order_products,
                    "source_name"       => "web",
                    "order_number"      => $order->order_number,
                    "billing_address"   => $address,
                    "financial_status"  => "pending",
                    "order_status_url"  => "https://yourstore.com/orders/2711891284034/",
                    "shipping_address"  => $address,
                    "payment_gateway_names" => [
                        "cash_on_delivery",
                        "cashfree"
                    ],
                    
                ];
                $url        = 'https://logisy.tech/api/stores/order/create/';
                $api_data   = json_encode($api_data);
                $this->hitCurl($url, $api_data);

            }
        }
        catch(\Exception $e) {
            \Log::info($e->getMessage());
        }
    }

    /**
     * Hit custom event 
     */
     public function customEvents($order_status_option, $order_data) {
        if( !empty($order_data->products) && !empty($order_data->products->first()) && !empty($order_data->products->first()->image)) {
            $product_image  = $order_data->products->first()->image;
            $proxy_url      = $product_image['proxy_url'];
            $image_path     = $product_image['image_path'];
            $image_url      = $proxy_url.'400/400'.$image_path;
            $image_url      = str_replace("@webp","",$image_url);
        }
        switch ($order_status_option) {
            case '2':
                $this->acceptOrderEvent($order_data, $image_url);
                break;
            case '3':
                $this->cancelOrderEvent($order_data, $image_url);
                break;
            case '4':
                $this->processingOrderEvent($order_data, $image_url);
                break;
            case '5':
                $this->outForDeliveryOrderEvent($order_data, $image_url);
                break;
            case '6':
                $this->deliveredOrderEvent($order_data, $image_url);
                break;
        }
     }

     /**
      * When admin accept the order
      */
     function acceptOrderEvent($order_data, $image_url) {
        try {
            $header = [
                "type"=> "image",
                "link" => $image_url
            ];
            $body = [
                "parameters" => [
                    optional($order_data->user)->name,
                    $order_data->order_number
                ]
            ];
            $data = [
                'order_data' => $order_data,
                'template_name' => 'clickocart_order_accept_v1',
                'header' => $header ?? [],
                'body' => $body ?? [],
                'message' => $message ?? null
            ];
            $template = $this->createTemplate($data);
            $this->hitCurl('https://logisy.tech/api/custom_events/whatsapp/', json_encode($template));
        }
        catch(\Exception $e) {
           Log::info($e->getMessage());
        }
     }

     /**
      * When admin cancel the order
      */
     function cancelOrderEvent($order_data, $image_url) {
        try {
            $header = [
                "type"=> "image",
                "link" => $image_url
            ];
            $body = [
                "parameters" => [
                    optional($order_data->user)->name,
                    $order_data->order_number
                ]
            ];
            $data = [
                'order_data' => $order_data,
                'template_name' => 'clickocart_order_reject_v1',
                'header' => $header ?? [],
                'body' => $body ?? [],
                'message' => $message ?? null
            ];
            $template = $this->createTemplate($data);
            $this->hitCurl('https://logisy.tech/api/custom_events/whatsapp/', json_encode($template));
        }
        catch(\Exception $e) {
           Log::info($e->getMessage());
        }
     }

     /**
      * When order in to processong state
      */
     function processingOrderEvent($order_data, $image_url) {
       
        try {
            $header = [
                "type"=> "image",
                "link" => 'https://hub.360dialog.com/dist/aa3a1c9718745e4b5cf6d31742e2ee74.svg'
            ];
            $body = [
                "parameters" => [
                    optional($order_data->user)->name,
                    $order_data->order_number
                ]
            ];
            $data = [
                'order_data' => $order_data,
                'template_name' => 'clickocart_order_processing_v1',
                'header' => $header ?? [],
                'body' => $body ?? [],
                'message' => $message ?? null
            ];
            $template = $this->createTemplate($data);
           
            $this->hitCurl('https://logisy.tech/api/custom_events/whatsapp/', json_encode($template));
        }
        catch(\Exception $e) {
           Log::info($e->getMessage());
        }
     }

     /**
      * When order out for delivery
      */
     function outForDeliveryOrderEvent($order_data, $image_url) {
        try {
            $header = [
                "type"=> "image",
                "link" => $image_url
            ];
            $body = [
                "parameters" => [
                    optional($order_data->user)->name,
                    $order_data->order_number
                ]
            ];
            $data = [
                'order_data' => $order_data,
                'template_name' => 'clickocart_order_out_for_delivery_v1',
                'header' => $header ?? [],
                'body' => $body ?? [],
                'message' => $message ?? null
            ];
            $template = $this->createTemplate($data);
            $this->hitCurl('https://logisy.tech/api/custom_events/whatsapp/', json_encode($template));
        }
        catch(\Exception $e) {
           Log::info($e->getMessage());
        }
     }

     /**
      * When order is delivered
      */
      function deliveredOrderEvent($order_data, $image_url) {
        try {
            $header = [
                "type"=> "image",
                "link" => $image_url
            ];
            $body = [
                "parameters" => [
                    optional($order_data->user)->name,
                    $order_data->order_number
                ]
            ];
            $data = [
                'order_data' => $order_data,
                'template_name' => 'clickocart_order_delivered_v1',
                'header' => $header ?? [],
                'body' => $body ?? [],
                'message' => $message ?? null
            ];
            $template = $this->createTemplate($data);
            $this->hitCurl('https://logisy.tech/api/custom_events/whatsapp/', json_encode($template));
        }
        catch(\Exception $e) {
           Log::info($e->getMessage());
        }
      }

     /**
      * Create whatsapp template
      */
     function createTemplate($data) {
        extract($data);
        // $order = Order::where('id', $order_id)->first();
        // optional($order->user)->phone_number
        return $api_data = [
            "to_number"     => optional($order_data->user)->dial_code.optional($order_data->user)->phone_number,
            "customer_name" => optional($order_data->user)->name,
            "template_name" => $template_name,
            "headers"       => $header,
            "body"          => $body,
            "message"       => $message
        ];
     }

     /**
      * Common Curl function
      */
     public function hitCurl($url, $data) {
        try{
            set_time_limit(0);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT,500); // 500 seconds
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $headers = array();
            $headers[] = 'X-Api-Key: fCnPbahHymjjKsqJgZU6qGoXCY9nTj5q';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
            }
            curl_close($ch);
        }
        catch(\Exception $e) {
            Log::info($e->getMessage());
        }
     }
}