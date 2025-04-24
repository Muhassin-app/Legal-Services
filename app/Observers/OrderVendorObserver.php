<?php

namespace App\Observers;
use Log;
use App\Models\{OrderVendor, ClientPreference, Order, ProductVariant};

class OrderVendorObserver
{
    /**
     * Handle the OrderVendor "created" event.
     *
     * @param  \App\Models\OrderVendor  $orderVendor
     * @return void
     */
    public function created(OrderVendor $orderVendor)
    {
        
    }

    /**
     * Handle the OrderVendor "updated" event.
     *
     * @param  \App\Models\OrderVendor  $orderVendor
     * @return void
     */
    public function updated(OrderVendor $orderVendor) 
    {
        $client_preferences = ClientPreference::first();
        if(checkColumnExists('client_preferences', 'inventory_service_key_url'))
        {
            if(!empty($client_preferences->inventory_service_key_url) && !empty($client_preferences->inventory_service_key_code))
            {
                if($orderVendor->order_status_option_id == 6 && inventorySyncOnOff($orderVendor->vendor_id))  // 6 = marked as delivered
                {
                    $orders = Order::with(['vendors.products'=>function($q){
                        $q->withoutAppends();
                    }, 'vendors.status', 'orderStatusVendor', 'address', 'user', 'vendors.products.product', 'vendors.vendor'])
                    ->where('id', $orderVendor->order_id)->whereHas('vendors.products.product', function ($q) {
                        $q->where('sync_from_inventory', 1);
                    })->get();
                    
                    $product_details = [];
                    if(!empty($orders)){
                        foreach($orders as $key => $val) {

                            // order table data
                            $product_details[$key]['order_id'] = $val->id;
                            $product_details[$key]['order_number'] = $val->order_number;
                            $product_details[$key]['date_time'] = \Carbon\Carbon::parse($val->created_at)->toDateString();

                            $product_details[$key]['customer_name'] = optional($val->user)->name;
                            $product_details[$key]['customer_email'] = optional($val->user)->email;
                            $product_details[$key]['customer_dial_code'] = optional($val->user)->dial_code;
                            $product_details[$key]['customer_phone_number'] = optional($val->user)->phone_number;
                            $product_details[$key]['customer_status'] = optional($val->user)->status;
                            
                            $product_details[$key]['payment_methods'] = $val->payment_option_id ?? '';
                            // $product_details[$key]['profit_loss'] = '';

                            if( !empty($val->vendors) ) {
                                
                                foreach($val->vendors as $inn_key => $inn_val) {
                                    
                                    // order vendor table data
                                    $product_details[$key]['subtotal_amount']   = $inn_val->subtotal_amount;
                                    $product_details[$key]['payable_amount']    = $inn_val->payable_amount;
                                    $product_details[$key]['discount_amount']   = $inn_val->discount_amount;        
                                    $product_details[$key]['taxable_amount']   = $inn_val->taxable_amount;        
                                    $product_details[$key]['order_status_option_id'] = $inn_val->order_status_option_id;        
                                    $product_details[$key]['order_side_vendor_id'] = $inn_val->vendor_id;        

                                    if( !empty($inn_val->products) ) {

                                        foreach($inn_val->products as $product_key => $product_val) {
                                            if( !empty($product_val->product) && !empty($product_val->product->sku)) {
                                                // product table data
                                                $product_details[$key]['products_list'][$product_key]['product_id'] = $product_val->product_id ?? null;
                                                $product_details[$key]['products_list'][$product_key]['product_quantity'] = $product_val->quantity ?? null;

                                                // we use model inside the loop because one product had multiple variant to fetach exact variant used sku code
                                                $product_varaint = ProductVariant::where('id', $product_val->variant_id)->first();
                                                if( !empty($product_varaint) ) {
                                                    $product_details[$key]['products_list'][$product_key]['sku'] = $product_varaint->sku;
                                                    $product_details[$key]['products_list'][$product_key]['product_amount'] = $product_varaint->price ?? '0.00';
                                                } else {
                                                    $product_details[$key]['products_list'][$product_key]['sku'] = '';
                                                    $product_details[$key]['products_list'][$product_key]['product_amount'] = '0.00';
                                                }
                                            }
                                            
                                        }
                                    }
                                }
                            }
                            
                            // vendor or warehouse name
                            if( !empty($val->vendors[$key]) && !empty($val->vendors[$key]->vendor) ) {
                                $product_details[$key]['warehouse'] = $val->vendors[$key]->vendor->name;
                            } else {
                                $product_details[$key]['warehouse'] = '';
                            }
                            
                        }
                    }
                    $client = new \GuzzleHttp\Client(['headers' => ['shortcode' => $client_preferences->inventory_service_key_code,
                        'content-type' => 'application/json']
                    ]);
                    $url = $client_preferences->inventory_service_key_url;
                    // $url = '127.0.0.1:9002';
                    // $base_url = $url.'/api/v1/log-order';
                    
                    $request = $client->get($url.'/api/v1/log-order', [
                        'json' => ['product_details' => $product_details]
                    ]);
                    
                    echo $request->getStatusCode(); 
                    // Product decrement successfully
                    if($request->getStatusCode() == 200) {
                    }
                    else {
                    }
                }
            }
        }
    }


    /**
     * Handle the OrderVendor "deleted" event.
     *
     * @param  \App\Models\OrderVendor  $orderVendor
     * @return void
     */
    public function deleted(OrderVendor $orderVendor)
    {
    }

    /**
     * Handle the OrderVendor "restored" event.
     *
     * @param  \App\Models\OrderVendor  $orderVendor
     * @return void
     */
    public function restored(OrderVendor $orderVendor)
    {
    }

    /**
     * Handle the OrderVendor "force deleted" event.
     *
     * @param  \App\Models\OrderVendor  $orderVendor
     * @return void
     */
    public function forceDeleted(OrderVendor $orderVendor)
    {
    }
}
        