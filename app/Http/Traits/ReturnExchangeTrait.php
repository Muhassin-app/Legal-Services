<?php
namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\{ClientCurrency, ClientPreference, Order, OrderProduct, OrderReturnRequest, OrderReturnRequestFile, OrderVendor, PaymentOption, Product, ProductVariant, ProductVariantSet, User, UserAddress, Vendor, VendorOrderStatus, VerificationOption};
use Illuminate\Support\Facades\Auth;
use App\Models\Client as CP;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

trait ReturnExchangeTrait{


    public function placeReturnRequestToDispatch($order, $vendor, $dispatch_domain)
    {
        try {

            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order->payable_amount;
            } else {
                $cash_to_be_collected = 'No';
                $payable_amount = 0.00;
            }
            $dynamic = uniqid($order->id . $vendor);
            $call_back_url = route('dispatch-order-update', $dynamic);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'phone_no', 'email', 'name', 'latitude', 'longitude', 'address')->first();
            $tasks = array();
            $meta_data = '';

            $unique = Auth::user()->code;
            $team_tag = $unique . "_" . $vendor;

            if (isset($order->scheduled_date_time) && !empty($order->scheduled_date_time)) {
                $task_type = 'schedule';
                $schedule_time = $order->scheduled_date_time ?? null;
            } else {
                $task_type = 'now';
            }

            $orderVendorDetails = OrderVendor::where('vendor_id', $vendor_details->id)->where('order_id', $order->id)->get()->first();
            if (!empty($orderVendorDetails->scheduled_date_time) && $orderVendorDetails->scheduled_date_time > 0) {
                $task_type = 'schedule';
                $user = Auth::user();
                $selectedDate = dateTimeInUserTimeZone($orderVendorDetails->scheduled_date_time, $user->timezone);
                $slot = trim(explode("-", $orderVendorDetails->schedule_slot)[0]);

                $slotTime = date('H:i:s', strtotime("$slot"));
                $selectedDate = date('Y-m-d', strtotime($selectedDate));
                $scheduleDateTime = $selectedDate . ' ' . $slotTime;
                $schedule_time =  $scheduleDateTime ?? null;
            }
           
            $tasks[] = array(
                'task_type_id' => 1,
                'latitude' => $cus_address->latitude ?? '',
                'longitude' => $cus_address->longitude ?? '',
                'short_name' => '',
                'address' => $cus_address->address ?? '',
                'post_code' => $cus_address->pincode ?? '',
                'barcode' => '',
                'flat_no'     => $cus_address->house_number ?? null,
                'email'       => $customer->email ?? null,
                'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
            );
            $tasks[] = array(
                'task_type_id' => 2,
                'latitude' => $vendor_details->latitude ?? '',
                'longitude' => $vendor_details->longitude ?? '',
                'short_name' => '',
                'address' => $vendor_details->address ?? '',
                'post_code' => '',
                'barcode' => '',
                'flat_no'     => null,
                'email'       => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null,
            );

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }
            $client = CP::orderBy('id', 'asc')->first();
            $postdata =  [
                'order_number' =>  $order->order_number,
                'customer_name' => $customer->name ?? 'Dummy Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => "Order From :" . $vendor_details->name,
                'allocation_type' => 'a',
                'task_type' => $task_type,
                'schedule_time' => $schedule_time ?? null,
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'royo_order_number' => $order->order_number,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'is_restricted' => $orderVendorDetails->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => $orderVendorDetails->id,
                'dbname' => $client->database_name,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image
            ];
            //pr($postdata);
            if ($orderVendorDetails->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }

            $client = new Client([
                'headers' => [
                    'personaltoken' => $dispatch_domain->delivery_service_key,
                    'shortcode' => $dispatch_domain->delivery_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->delivery_service_key_url;

            $res = $client->post(
                $url . '/api/return-to-warehouse-task',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
            
            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                    ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);

                return 1;
            }
            return 2;
        } catch (\Exception $e) {
           Log::info($e->getMessage());
            return 2;
        }
    }

    public function saveVendorOrderStatus($order, $orderVendorProductOld, $order_vender)
    {
        $order_status = new VendorOrderStatus();
        $order_status->order_id = $order->id;
        $order_status->vendor_id = $orderVendorProductOld->vendor_id;
        $order_status->order_vendor_id = $order_vender->id;
        $order_status->order_status_option_id = 1;
        $order_status->save();
        return $order_status;
    }
    protected function saveOrderVendorProduct($request, $order, $orderVendorProductOld, $order_vender)
    {
        $currency_id = Session::get('customerCurrency');
        $language_id = Session::get('customerLanguage');
        $order_product = new OrderProduct;
        $order_product->order_id = $order->id;
        $order_product->price = $orderVendorProductOld->price;
        $order_product->markup_price = 0;
        $order_product->additional_increments_hrs_min = 0;
        $order_product->start_date_time = null;
        $order_product->end_date_time = null;
        $order_product->product_delivery_fee = 0;
        /**
         * for rental case total_booking_time as a total time 
         * for on_demand and appointment total booking time as single service duration time as per service for get totel service time multiply by quantity
         */
        $order_product->total_booking_time = 0; 
        
        $order_product->container_charges = 0;
        $order_product->order_vendor_id = $order_vender->id;
        $order_product->taxable_amount = 0;
        $order_product->incremental_price = 0;

        $order_product->quantity = $orderVendorProductOld->quantity;
        $order_product->vendor_id = $orderVendorProductOld->vendor_id;
        $order_product->product_id = $orderVendorProductOld->product_id;
        $order_product->user_product_order_form = null;
        $product_category = Product::where('id', $orderVendorProductOld->product_id)->first();
        if ($product_category) {
            $order_product->category_id = $product_category->category_id;
        }
        $order_product->created_by = null;
        $order_product->variant_id = $request->variant_id;
        $product_variant_sets = '';
        if (isset($request->variant_id) && !empty($request->variant_id)) {
            $var_sets = ProductVariantSet::where('product_variant_id', $request->variant_id)->where('product_id', $orderVendorProductOld->product_id)
                ->with([
                    'variantDetail.trans' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    },
                    'optionData.trans' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    }
                ])->get();
            if (count($var_sets)) {
                foreach ($var_sets as $set) {
                    if (isset($set->variantDetail) && !empty($set->variantDetail)) {
                        $product_variant_set = @$set->variantDetail->trans->title . ":" . @$set->optionData->trans->title . ", ";
                        $product_variant_sets .= $product_variant_set;
                    }
                }
            }
        }

        $order_product->product_variant_sets = $product_variant_sets;
        if (!empty($product_category->title)) {
            $product_category->title = $product_category->title;
        } elseif (empty($product_category->title)  && !empty($product_category->translation)) {
            $product_category->title = $product_category->translation[0]->title;
        } else {
            $product_category->title = $product_category->sku;
        }



        $order_product->product_name = $product_category->title ?? $product_category->sku;

        $order_product->product_dispatcher_tag = $product_category->tags;
        $order_product->schedule_type =  null;
        $order_product->scheduled_date_time =  null;
        $order_product->schedule_slot =  '';
        $order_product->dispatch_agent_id =  null;
        if ($product_category->pimage) {
            $order_product->image = $product_category->pimage->first() ? $product_category->pimage->first()->path : '';
        }
        // added some columen for rental case 
        $order_product->start_date_time = null;
        $order_product->end_date_time = null;
        $order_product->additional_increments_hrs_min = 0;

        $order_product->save();

        return $order_product;
    }


    protected function saveOrderVendor($order, $orderVendorProductOld)
    {
        /* Update details related to order vendor */
        $orderStatusPlaced = 1;
        $user = Auth::user();
        $OrderVendor = new OrderVendor();
        $OrderVendor->status = 0;
        $OrderVendor->user_id = $user->id;
        $OrderVendor->delivery_fee = 0.00;
        $OrderVendor->subtotal_amount = $orderVendorProductOld->subtotal_amount ?? 0.00;
        $OrderVendor->payable_amount = 0.00;
        $OrderVendor->total_container_charges = 0.00;
        $OrderVendor->service_fee_percentage_amount = 0.00;
        $OrderVendor->additional_price = 0.00;
        $OrderVendor->discount_amount = 0.00;
        $OrderVendor->taxable_amount = 0.00;
        $OrderVendor->order_id = $order->id;
        $OrderVendor->vendor_id = $orderVendorProductOld->vendor_id;
        $OrderVendor->vendor_dinein_table_id = null;
        $OrderVendor->order_status_option_id = $orderStatusPlaced;
       
        $OrderVendor->exchange_order_vendor_id = $orderVendorProductOld->order_vendor_id;
        $OrderVendor->save();

        return $OrderVendor;
    }

    protected function saveOrder($request, $orderVendorOld)
    {
        /* Generate order object */

        $user = Auth::user();


        $order = new Order;
        $order->user_id = $user->id;
        $order->order_number = generateOrderNo();

        if(@$request->address_id){
            $order->address_id = $address_id = $request->address_id;
        }else{
            $orderOld = Order::select('address_id')->where('id',$orderVendorOld->order_id)->first();
            $order->address_id = $address_id =  $orderOld->address_id;
        }
        
        $cus_address = UserAddress::find($address_id);
        $latitude = $cus_address->latitude ?? Session::get('latitude');
        $longitude = $cus_address->longitude ?? Session::get('longitude');

        /* Uodating client other details in order object */
        $order->payment_option_id = 0;
        $order->payment_status = 1;
        $order->total_other_taxes = 0;
        $order->comment_for_pickup_driver =  null;
        $order->comment_for_dropoff_driver = null;
        $order->comment_for_vendor =  null;
        $order->schedule_pickup  =  null;
        $order->schedule_dropoff =  null;
        $order->fixed_fee_amount = 0.00;
        $order->payable_amount = 0.00;
        $order->specific_instructions =  null;
        $order->is_gift =  0;
        $order->user_latitude = $latitude ? $latitude : null;
        $order->user_longitude = $longitude ? $longitude : null;
        
        /* Save initial details of order */
        $order->save();

        return $order;
    }

    public function getDispatchDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }

    protected function markAsExchangePending($orderVendorProduct)
    {
        $replace_pending = 9;
        $updateData = ['order_status_option_id' => $replace_pending, 'dispatcher_status_option_id' => null ];
        $updateData['is_exchanged_or_returned'] = 1;
        OrderVendor::where('id', $orderVendorProduct->order_vendor_id)
        ->update($updateData);
        return true;
    }

    protected function markAsReturnPending($orderVendorProduct)
    {
        
        $updateData = [ 'dispatcher_status_option_id' => null ];
        $updateData['is_exchanged_or_returned'] = 2;
        OrderVendor::where('id', $orderVendorProduct->order_vendor_id)
        ->update($updateData);
        return true;
    }

    protected function saveReturnExchangeRequest($request, $order_details, $type = 1)
    {
        $returns = 0;
        if($order_details){
            $order_deliver = VendorOrderStatus::where(['order_id' => $order_details->order_id,'vendor_id' => $order_details->vendor_id,'order_status_option_id' => 6])->count();
        }
        

        if($order_deliver > 0){
            $returns = OrderReturnRequest ::updateOrCreate(
                ['order_vendor_product_id' => $request->order_vendor_product_id,
                'order_id' => $order_details->order_id,
                'return_by' => Auth::id()],
                ['reason' => $request->reason??null,
                'coments' => $request->coments??null,
                'type' => $type
                ]
            );

            if(isset($request->add_files) && is_array($request->add_files))    # send  array of insert images
            {
                foreach ($request->add_files as $storage) {
                    $img = new OrderReturnRequestFile();
                    $img->order_return_request_id = $returns->id;
                    $img->file = $storage;
                    $img->save();

                }
            }

            if(isset($request->remove_files) && is_array($request->remove_files)){    # send index array of deleted images
                $removefiles = OrderReturnRequestFile::where('order_return_request_id',$returns->id)->whereIn('id',$request->remove_files)->delete();
            }
        }

        return $returns;
    }

    /**
     * Display product variant data
     *
     * @return \Illuminate\Http\Response
     */
    private function getVariantData($request, $product_id){
        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);

        $customerCurrency = Session::get('customerCurrency');
        if(isset($customerCurrency) && !empty($customerCurrency)){
        }
        else{
            $primaryCurrency = ClientCurrency::where('is_primary','=', 1)->first();
            Session::put('customerCurrency', $primaryCurrency->currency_id);
        }
        $data = array();
        $is_available = true;
        $vendors = $this->getServiceAreaVendors();
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
        $product = Product::select('id', 'vendor_id')->where('id', $product_id)->firstOrFail();
        if(!in_array($product->vendor_id, $vendors)){
            $is_available = false;
        }
        $data['is_available'] = $is_available;

        $pv_ids = array();
        $product_variant = '';
        if ($request->has('addon_options') && !empty($request->addon_options)) {
            foreach ($request->addon_options as $key => $value) {
                
                 
                
                $product_variant = ProductVariantSet::where('variant_type_id', $request->variants[$key])
                ->where('variant_option_id', $request->addon_options[$key])->where('product_variant_sets.product_id', $product->id)->get();
                if($product_variant){
                    foreach ($product_variant as $k => $variant) {
                        if(!in_array($variant->product_variant_id, $pv_ids)){
                            $pv_ids[] = $variant->product_variant_id;
                        }
                    }
                }
                
                
            }
        }
        $sets = array();
        $clientCurrency = ClientCurrency ::where('currency_id', Session::get('customerCurrency'))->first();
        $availableSets = Product::with(['variantSet.variantDetail','variantSet.option2'=>function($q)use($product, $pv_ids){
            $q->where('product_variant_sets.product_id', $product->id); //->whereIn('product_variant_id', $pv_ids);
        }])
        //return $product;
        ->select('id')
        ->where('products.id', $product->id)->first();
        $data['availableSets'] = $availableSets->variantSet;
        if($pv_ids){
            $variantData = ProductVariant::with(['product.media.image', 'product.addOn', 'media.pimage.image', 'checkIfInCart'])
            ->select('id', 'sku', 'quantity', 'price', 'compare_at_price', 'barcode', 'product_id')
            ->whereIn('id', $pv_ids)->get();

            if ($variantData) {
                foreach($variantData as $variant){

                    $variant->productPrice =  decimal_format(($variant->price * $clientCurrency->doller_compare));
                    
                }
                if(count($variantData) <= 1){
                    $image_fit = "";
                    $image_path = "";
                    $variantData = $variantData->first()->toArray();
                    if(!empty($variantData['media'])){
                        $image_fit = $variantData['media'][0]['pimage']['image']['path']['image_fit'];
                        $image_path = $variantData['media'][0]['pimage']['image']['path']['image_path'];
                    }else if(!is_null($variantData['product']['media']) && !empty($variantData['product']['media']) && !is_null($variantData['product']['media'][0]['image'])){
                        $image_fit = $variantData['product']['media'][0]['image']['path']['image_fit'];
                        $image_path = $variantData['product']['media'][0]['image']['path']['image_path'];
                    }
                    if(empty($image_path)){
                        $image_fit = \Config::get('app.FIT_URl');
                        $image_path = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url('default/default_image.png').'@webp';
                    }
                    $variantData['image_fit'] = $image_fit;
                    $variantData['image_path'] = $image_path;
                    if(count($variantData['check_if_in_cart']) > 0){
                        $variantData['check_if_in_cart'] = $variantData['check_if_in_cart'][0];
                    }
                    $variantData['isAddonExist'] = 0;
                    if(count($variantData['product']['add_on']) > 0){
                        $variantData['isAddonExist'] = 1;
                    }

                    $variantData['variant_multiplier'] = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    // dd($variantData);
                }else{
                    $variantData = array();
                }
                $data['variant'] = $variantData;
                
                return response()->json(array('status' => 'Success', 'data' => $data));
            }

        }
        return response()->json(array('status' => 'Error', 'message' => 'This option is currenty not available', 'data' => $data));
    }


    public function checkreplaceProduct($request, $orderVendorProductOld)
    {

        $oldPrice =  $this->oldOrderedProductPrice($orderVendorProductOld);
        return $oldPrice;
        // if(@$request->variant_id && $request->variant_id == $orderVendorProductOld->variant_id){
        //     return true;
        // }
        // return false;
    }

    public function oldOrderedProductPrice($orderVendorProductOld)
    {
        $price = $orderVendorProductOld->variant[0]->price;
        foreach($orderVendorProductOld->addon as  $addon){
            $price += $addon->option->price;
        }
        return $price;
    }

    public function oldNewSelectedProductPrice($orderVendorProductOld)
    {
        $price = $orderVendorProductOld->variant[0]->price;
        foreach($orderVendorProductOld->addon as  $addon){
            $price += $addon->option->price;
        }
        return $price;
    }

   
}