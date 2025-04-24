<?php

namespace App\Http\Controllers\Front;

use DB;
use Auth;
use Session;
use Redirect;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Measurements;
use Illuminate\Http\Request;

use App\Models\ProductMeasurement;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\{ProductActionTrait, ProductTrait,ProductVariantActionTrait};
use App\Models\{AddonSet, Cart, CartAddon, CartProduct, User, Product, ClientCurrency, ProductVariant, ProductVariantSet,OrderProduct,VendorOrderStatus,OrderProductRating,Category, Vendor,ProductFaq,ClientLanguage, ProductFaqSelectOption, WebStylingOption,ProductRecentlyViewed, Attribute, Client, ProductAttribute,DeliverySlotProduct, UserVendor, DeliverySlot,UserAddress,ProcessorProduct, ProductAvailability, ProductBooking, VendorDocs, VendorRegistrationDocument};

class ProductController extends FrontController{
    private $field_status = 2;
    use ProductActionTrait,ProductTrait,ProductVariantActionTrait;

    public function __construct()
    {


    }


    /**
     * Display product By Id
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $domain = '',$vendor,$url_slug)
    {
        $productMeasurementData=0;
        $startTime = microtime(true); // Start time in seconds with microseconds
        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role','product_measurment']);
        $pickup_time = $request->pickup;
        $drop_time = $request->drop;
        $user = Auth::user();
        $preferences = Session::get('preferences');
        $langId = Session::get('customerLanguage');
        $customerCurrency = Session::get('customerCurrency');
        $client = Client::first();
        if(isset($customerCurrency) && !empty($customerCurrency)){
        }
        else{
            $primaryCurrency = ClientCurrency::where('is_primary','=', 1)->first();
            Session::put('customerCurrency', $primaryCurrency->currency_id);
        }
        $curId = Session::get('customerCurrency');
        $navCategories = $this->categoryNav($langId);
        $serviceType = Session::get('serviceType');
          
        
        
        $product = Product::select('id', 'vendor_id','security_amount')->where('url_slug', $url_slug)
            ->whereHas('vendor',function($q) use($vendor){
                $q->where('slug',$vendor);
            })->with(['ProductAttribute' => function($q) use($serviceType){
                if($serviceType == 'rental'){
                    $q->whereIn('key_name', ['Transmission', 'Fuel Type', 'Seats']);
                }else{
                    $q->whereIn('key_name', ['Cabins', 'Berths', 'Baths']);
                }
            }, 'ProductAttribute.attributeOption'])->firstOrFail();
        $product_in_cart = CartProduct::where(["product_id" => $product->id]);
        $processorProduct = ProcessorProduct::where('product_id', $product->id)->first();
        if ($user) {
             $product_in_cart = $product_in_cart->whereHas('cart', function($query) use($user){
                $query->where(['user_id' => $user->id]);
            });
        } else {
            $user_token = session()->get('_token');
            $product_in_cart = $product_in_cart->whereHas('cart', function($query) use($user_token){
                $query->where(['unique_identifier' => $user_token]);
            });
        }
        $product_in_cart = $product_in_cart->first();
        $is_available = true;

        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){
            if($product){
                $productVendorId = $product->vendor_id;
                $vendors = $this->getServiceAreaVendors();
                if(!in_array($productVendorId, $vendors)){
                    $is_available = false;
                }
            }
        }


        $p_id = $product->id;
        $product =  $this->getProduct($p_id,$vendor,$url_slug,$user,$langId);
       
  
        if(@$product->product_availability && @$product->OrderProduct){
            $product_notavailability = [];
            foreach($product->OrderProduct as $OrderProducts){
                // dd($OrderProducts);
                $dates = [];
                if(@$OrderProducts->start_date_time && @$OrderProducts->end_date_time){
                    $period = CarbonPeriod::create(date('Y-m-d',strtotime($OrderProducts->start_date_time)), date('Y-m-d',strtotime($OrderProducts->end_date_time)));
                    
                    foreach ($period as $date) {
                        $dates[] =  $date->format('Y-m-d');
                    }
                    
                    if(@$dates){
                        foreach($product->product_availability as $product_availability){
                            foreach($dates as $date){
                                if( date('Y-m-d',strtotime($product_availability->date_time)) == $date){
                                    $product_notavailability[] = $product_availability->date_time;
                                    $product_availability->not_available = 1;
                                }
                            }
                           
                        }
                    }
            }
        }
    }

        $product_availability = json_encode($product->product_availability->pluck('date_time'));

      
        $productAvailability = json_encode(ProductAvailability::where('product_id', $product->id)
        ->where('not_available', 0)
        ->selectRaw('DATE_FORMAT(date_time, "%Y-%m-%d") as formatted_date')
        ->pluck('formatted_date'));
        
        if($this->checkTemplateForAction(8)){
            $this->RecentView($p_id);
        }
        $doller_compare = 1;
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
        if($clientCurrency){
            $doller_compare = $clientCurrency->doller_compare;
        }else{
            $clientCurrency = ClientCurrency::where('is_primary','=', 1)->first();
            $doller_compare = $clientCurrency->doller_compare ?? 1;
        }
        $product->related_products = $this->metaProduct($langId, $doller_compare, 'related', $product->related);
        $rating_details = '';
        $rating_details = OrderProductRating::select('*','created_at as time_zone_created_at')->where(['product_id' => $product->id])->get();
        foreach ($product->variant as $key => $value) {
            if(isset($product->variant[$key])){
            $product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
            }
        }
         /**
          * long_term service product
          * */

        if($product->is_long_term_service == 1){
            $product_id = $product->LongTermProducts->product_id;
            $url_slug   = $product->LongTermProducts->product->url_slug;

            $LongTermProducts                    = $this->getProduct($product->LongTermProducts->product_id,$vendor,$url_slug,$user,$langId);
            $LongTermProducts->long_term_product = $product->LongTermProducts;
            $addon =  $product->LongTermProducts->addons->pluck('option_id','addon_id')->toArray() ?? [];
            if($product->ServicePeriod){
                $product->ServicePeriods = $product->ServicePeriod->pluck('service_period')->toArray();
            }

            $LongTermProducts->product_addon     =  $addon;

            return view('frontend.long_term_service_product')->with(['product' => $product, 'navCategories' => $navCategories,  'rating_details' => $rating_details,  'product_in_cart' => $product_in_cart,'is_available'=>$is_available,'LongTermProducts'=> $LongTermProducts]);
        }

        $vendorIds[] = $product->vendor_id;
        $np = $this->productList($vendorIds, $langId, $curId, 'is_new');
        $newProducts = ($np->count() > 0) ? array_chunk($np->toArray(), ceil(count($np) / 2)) : $np;
        foreach ($product->addOn as $key => $value) {
            foreach ($value->setoptions as $k => $v) {
                $v->multiplier = $clientCurrency->doller_compare;
            }
        }

        $is_inwishlist_btn = 0;
        if($product->category){
            $category_detail = Category::select()->where('id',$product->category->category_id)->first();
            if($category_detail && $user){
                $is_inwishlist_btn = $category_detail ? $category_detail->show_wishlist : 0;
            }
        }

        $availableSet = ProductVariantSet::where('product_id', $p_id)->get();
        $sets = array();
        foreach($availableSet->groupBy('product_variant_id') as $avSets){
            $variant_type_id = array();
            $variant_option_id = array();
            foreach($avSets as $avSet){
                $variant_type_id[] = $avSet->variant_type_id;
                $variant_option_id[] = $avSet->variant_option_id;
            }
            $sets[] = ['variant_types' => $variant_type_id, 'variant_options' => $variant_option_id];
        }
        if(  in_array($product->category->categoryDetail->type_id ,[8,12])  && $product->is_recurring_booking !=1){ // onDemand and appointent

            $cartDataGet = $this->getCartOnDemand($request);
            $nlistData = clone $product;
            $nlistData = $nlistData->where('url_slug', $url_slug)->paginate(10);
            if(!empty($nlistData)){
                foreach ($nlistData as $key => $value) {
                    $value->translation_title = (!empty($value->translation->first())) ? $value->translation->first()->title : $value->sku;
                    $value->translation_description = (!empty($value->translation->first())) ? $value->translation->first()->body_html : $value->sku;
                    $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    $value->variant_price = (!empty($value->variant->first())) ? $value->variant->first()->price : 0;
                    $value->category_type_id = (!empty($value->category->categoryDetail->first())) ? $value->category->categoryDetail->type_id : 0;
                }
            }
            $listData = $nlistData;
            $category = $category_detail;

            if($request->step == 2 && empty($request->addons) && empty($request->dataset)){

                $addos = 0;
                foreach($cartDataGet['cartData'] as $cp){
                    if(count($cp->product->addOn) > 0)
                    $addos = 1;
               }
               if($addos == 1){
                $name = \Request::route()->getName();
                $new_url = $request->path()."?step=1&addons=1";
                return redirect($new_url);
               }else{
                $name = \Request::route()->getName();
                if(request()->query('mode') == 'instant'){
                    $new_url = $request->path()."?step=2&dataset=1&mode=instant";
                }else{
                    $new_url = $request->path()."?step=2&dataset=1";
                }
                return redirect($new_url);
               }
            }
            if($request->step == 2 && empty($request->addons))
            {
                if ($request->session()->has('skip_addons')) {
                    $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
                    return view('frontend.ondemand.index')->with(['product'=>$product,'clientCurrency' => $clientCurrency,'time_slots' =>  $cartDataGet['time_slots'], 'period' =>  $cartDataGet['period'] ,'cartData' => $cartDataGet['cartData'], 'addresses' => $cartDataGet['addresses'], 'countries' => $cartDataGet['countries'], 'subscription_features' => $cartDataGet['subscription_features'], 'guest_user'=>$cartDataGet['guest_user'],'listData' => $listData, 'category' => $category,'navCategories' => $navCategories]);
                }
                $request->session()->put('skip_addons', '1');
                $new_url = $request->path()."?step=2";
                return redirect($new_url);
            }
            $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
            return view('frontend.ondemand.index')->with(['product'=>$product,'clientCurrency' => $clientCurrency,'time_slots' =>  $cartDataGet['time_slots'], 'period' =>  $cartDataGet['period'] ,'cartData' => $cartDataGet['cartData'], 'addresses' => $cartDataGet['addresses'], 'countries' => $cartDataGet['countries'], 'subscription_features' => $cartDataGet['subscription_features'], 'guest_user'=>$cartDataGet['guest_user'],'listData' => $listData, 'category' => $category,'navCategories' => $navCategories]);
        }
        elseif($product->category->categoryDetail->type_id == 7)
        {
            $slug = $product->category->categoryDetail->slug ?? '';
            return Redirect::route('categoryDetail',$slug);
        }
        else{
            $vendor = Vendor::where('id', $product->vendor_id)->with('slot', 'slotDate')->first();
            if($vendor){
                // if($vendor->show_slot == 1){
                //     $vendor->show_slot_option = 1;
                // }elseif ($vendor->slot->count() > 0) {
                //     $vendor->show_slot_option = 1;
                // }else{
                //     $vendor->show_slot_option = 0;
                // }
                $vendor->is_vendor_closed = 0;
                if($vendor->show_slot == 0){
                    if( ($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty()) ){
                        $vendor->is_vendor_closed = 1;
                    }else{
                        $vendor->is_vendor_closed = 0;
                    }
                }
            }
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                    $url = "https://";
            else
                    $url = "http://";
            // Append the host(domain name, ip) to the URL.
            $url.= $_SERVER['HTTP_HOST'];

            // Append the requested resource location to the URL
            $url.= $_SERVER['REQUEST_URI'];

            $shareComponent = \Share::page(
                $url,
                'Your share text comes here',
            )
            ->facebook()
            ->twitter()
            // ->linkedin()
            // ->telegram()
            ->whatsapp();
            // ->reddit();
                // dd($vendor);
               // dd( $vendor->is_vendor_closed);
            $category = $product->category->categoryDetail;
            $set_template = WebStylingOption::where('web_styling_id',1)->where('is_selected',1)->first();
            if($set_template->template_id == 4)
            {
                $product_page = "template_four.product";
            }else{
                $product_page = "product";
            }
            $suggested_category_products = $suggested_brand_products = $suggested_vendor_products = [];
            $suggested_product = Product::with(['vendor', 'translation', 'variant', 'productVariantByRoles']);
            if( !empty($product->category->category_id) ) {
                $suggested_category_products = $suggested_product->where('category_id', $product->category->category_id)
                ->whereHas('vendor',function ($q){
                    $q->whereIn('id',session()->get('vendors'));
                })
                ->where('id','!=',$p_id)
                ->groupBy('id')
                ->orderby('id', 'desc')
                ->limit(10)->get();
            }
            // $suggested_product = Product::with(['vendor', 'translation', 'variant', 'productVariantByRoles']);
            // if( !empty($product->category->category_id) ) {
            //     $suggested_category_products = $suggested_product->where('category_id', $product->category->category_id)
            //     ->whereHas('vendor',function ($q){
            //         $q->whereIn('id',session()->get('vendors'));
            //     })
            //     ->where('id','!=',$p_id)
            //     ->groupBy('id')
            //     ->orderby('id', 'desc')
            //     ->limit(20)->get();
            // }


            // foreach($suggested_category_products as $r_product){
            //     foreach ($r_product->variant as $key => $value) {
            //         if(isset($r_product->variant[$key])){
            //             $r_product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
            //         }
            //     }
            // }

            // if( !empty($product->brand_id) ) {
            //     $suggested_product = Product::with(['media.image', 'vendor', 'translation', 'variant']);
            //     $suggested_brand_products = $suggested_product->where('brand_id', $product->brand_id)->orderby('id', 'desc')->limit(20)->get();
            // }


            //     foreach($suggested_brand_products as $r_product){
            //     foreach ($r_product->variant as $key => $value) {
            //         if(isset($r_product->variant[$key])){
            //         $r_product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
            //         }
            //     }
            // }

            // if( !empty($product->vendor_id) ) {
            //     $suggested_product = Product::with(['media.image', 'vendor', 'translation', 'variant']);
            //     $suggested_vendor_products = $suggested_product->where('vendor_id', $product->vendor_id)->orderby('id', 'desc')->limit(20)->get();
            // }


            //     foreach($suggested_vendor_products as $r_product){
            //     foreach ($r_product->variant as $key => $value) {
            //         if(isset($r_product->variant[$key])){
            //         $r_product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
            //         }
            //     }
            // }

            $promoCodeController = new PromoCodeController();
            $coupon_list = $promoCodeController->coupon_code_list($product->id, $product->vendor_id);

            // Product Attribute
            $product_attr = $attr_array = [];
            if( checkTableExists('product_attributes') ) {
                if( !empty($product->ProductAttribute) ) {
                    foreach( $product->ProductAttribute as $key => $value ) {
                        if( !empty($value->attribute) && !empty($value->attribute->status) && $value->attribute->status == 1 ) {
                            $product_attr[$key]['title'] = optional($value->attribute)->title ?? '';
                            $product_attr[$key]['attribute_id'] = $value->attribute_id ?? '';
                            $product_attr[$key]['hexacode'] = optional($value->attributeOption)->hexacode ?? '';
                            $product_attr[$key]['type'] = optional($value->attribute)->type ?? '';
                            
                            if($value->attribute->type != 4 && $value->attribute->type != 6 && $value->attribute->type != 7) {
                                $product_attr[$key]['value'] = optional($value->attributeOption)->title ?? '';
                            }
                            else {
                                $product_attr[$key]['value'] = $value['key_value'] ?? '';
                            }
                        }
                    }
                }

                $attr_id = '';
                $attr_array = [];
                foreach($product_attr as $pro_att_key => $pro_att_val) {

                    if( empty($attr_id) || ($pro_att_val['attribute_id'] != $attr_id) ) {
                        $attr_id = $pro_att_val['attribute_id'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['title'] = $pro_att_val['title'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['attribute_id'] = $pro_att_val['attribute_id'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['value'] = $pro_att_val['value'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['hexacode'] = $pro_att_val['hexacode'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['type'] = $pro_att_val['type'];
                    }
                    else {
                        $attr_id = $pro_att_val['attribute_id'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['title'] = $pro_att_val['title'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['attribute_id'] = $pro_att_val['attribute_id'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['value'] = $pro_att_val['value'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['hexacode'] = $pro_att_val['hexacode'];
                        $attr_array[$pro_att_val['title']][$pro_att_key]['type'] = $pro_att_val['type'];
                    }
                }
            }

            $user_vendor = [];
            if($user){
                $user_vendor =  UserVendor::where('user_id', $user->id)->first();
            }

            // Date Time Comparison
            $cutoff_time            = $product->vendor->cutOff_time??'';

            $current_time           = Carbon::now()->toTimeString();

            $parsed_cutoff_time     = Carbon::parse($cutoff_time);
            $current_time_response  = false;

            if( $parsed_cutoff_time->gt($current_time) ) {
                $current_time_response = true;
            }

     
            // return view('frontend.'.$product_page)->with(['user_vendor' => $user_vendor, 'shareComponent' => $shareComponent, 'sets' => $sets, 'vendor_info' => $vendor, 'product' => $product, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'rating_details' => $rating_details, 'is_inwishlist_btn' => $is_inwishlist_btn, 'category' => $category, 'product_in_cart' => $product_in_cart,'is_available'=>$is_available, 'getAdditionalPreference' => $getAdditionalPreference, 'suggested_category_products' => $suggested_category_products, 'suggested_brand_products'=> $suggested_brand_products, 'suggested_vendor_products'=>$suggested_vendor_products, 'coupon_list' => $coupon_list, 'attr_array' => $attr_array, 'set_template' => $set_template, 'current_time_response' => $current_time_response, 'processorProduct'=> $processorProduct, 
            // 'product_notavailability' => $product_notavailability,
            // 'product_availability' => $product_availability]);
            $productAttributes = [];
            if( checkTableExists('attributes') && 0) {
                $productAttributes = Attribute::with('option', 'varcategory.cate.primary')
                    ->select('attributes.*')
                    ->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
                    ->where('attribute_categories.category_id', $product->category_id)
                    ->where('attributes.status', '!=', 2)
                    ->orderBy('position', 'asc')->get();
    
                if( !empty($product->ProductAttribute) ) {
                    foreach($product->ProductAttribute as $key => $val) {
                        $attribute_value[] = $val->attribute_option_id;
                        $attribute_key_value[$val->attribute_option_id] = $val->key_value;
                        if(!empty($val->latitude)){
                            $attribute_latitude[$val->attribute_option_id] = $val->latitude;
                        }
                        if (!empty($val->longitude)) {
                            $attribute_longitude[$val->attribute_option_id] = $val->longitude;
                        }
                    }
                }
            }

            $productBookingsCount = ProductBooking::whereHas('products', function ($q) use ($product) {
                $q->whereHas('product', function($q) use($product){
                    $q->where('vendor_id', $product->vendor_id);
                });
            })->count();
            $template = WebStylingOption::where('is_selected','1')->first();
   
        // Your code to be measured goes here
        //Product Measurements Code
        $measurements =Measurements::where('category_id',$product->category_id)->where('vendor_id',$product->vendor_id)->get();
        $variants = '';
        if($getAdditionalPreference['product_measurment'] == 1){
            $productMeasurements=ProductMeasurement::with('measurements')->where('product_id',$product->id)->get();
            $uniqueVariantIds = ProductMeasurement::where('product_id', $product->id)
            ->pluck('product_variant_id')
            ->unique();
            $variants = ProductVariant::whereIn('id', $uniqueVariantIds)->get();
            $productMeasurementData = [];
            foreach ($productMeasurements as $mment) {
                $productMeasurementData[$mment->key_id][$mment->product_variant_id] = $mment->key_value;
            }
        
        }
        //end here
        
        $endTime = microtime(true); // End time in seconds with microseconds
        $executionTime = $endTime - $startTime; // Calculate execution time in seconds
    
        //  \Log::info('Execution time Product Detials:'.$client->database_name.':' . $executionTime . ' seconds');
        
            if(!empty($pickup_time)&&!empty($drop_time)){
              
                return view('frontend.yacht.'.$product_page)->with(['productAttributes' => $productAttributes, 'pickup_time' => $pickup_time,'drop_time' => $drop_time,'user_vendor' => $user_vendor, 'shareComponent' => $shareComponent, 'sets' => $sets, 'vendor_info' => $vendor, 'product' => $product, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'rating_details' => $rating_details, 'is_inwishlist_btn' => $is_inwishlist_btn, 'category' => $category, 'product_in_cart' => $product_in_cart,'is_available'=>$is_available, 'getAdditionalPreference' => $getAdditionalPreference, 'suggested_category_products' => $suggested_category_products, 'suggested_brand_products'=> $suggested_brand_products, 'suggested_vendor_products'=>$suggested_vendor_products, 'coupon_list' => $coupon_list, 'attr_array' => $attr_array, 'set_template' => $set_template, 'current_time_response' => $current_time_response, 'processorProduct'=> $processorProduct, 'productBookingsCount' => $productBookingsCount]);
            } else{
            return view('frontend.'.$product_page)->with(['variants'=>$variants,'productMeasurementData'=>$productMeasurementData,'measurements'=>$measurements,'productAvailability' => $productAvailability,'productAttributes' => $productAttributes, 'pickup_time' => $pickup_time,'drop_time' => $drop_time,'user_vendor' => $user_vendor, 'shareComponent' => $shareComponent, 'sets' => $sets, 'vendor_info' => $vendor, 'product' => $product, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'rating_details' => $rating_details, 'is_inwishlist_btn' => $is_inwishlist_btn, 'category' => $category, 'product_in_cart' => $product_in_cart,'is_available'=>$is_available, 'getAdditionalPreference' => $getAdditionalPreference, 'suggested_category_products' => $suggested_category_products, 'suggested_brand_products'=> $suggested_brand_products, 'suggested_vendor_products'=>$suggested_vendor_products, 'coupon_list' => $coupon_list, 'attr_array' => $attr_array, 'set_template' => $set_template, 'current_time_response' => $current_time_response, 'processorProduct'=> $processorProduct]);
            }
     

        }
   }

    /**
     * Display product variant data
     *
     * @return \Illuminate\Http\Response
     */

    public function getVariantData(Request $request, $domain = '', $sku){
        // dd($request->all());
        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role', 'is_token_currency_enable']);

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
        $product = Product::select('id', 'vendor_id')->where('sku', $sku)->firstOrFail();
        if(!in_array($product->vendor_id, $vendors)){
            $is_available = false;
        }
        $data['is_available'] = $is_available;

        $pv_ids = array();
        $product_variant = [];
        if ($request->has('options') && !empty($request->options)) {
            foreach ($request->options as $key => $value) {
                if ($product_variant) {
                    $pv_ids = array();
                    foreach ($product_variant as $k => $variant) {
                        if($request->options[$key]){
                            $variantSet = ProductVariantSet::whereIn('variant_type_id', $request->variants)
                            ->whereIn('variant_option_id', $request->options)
                            ->where('product_variant_id', $variant->product_variant_id)
                            ->whereHas('productVariants', function($q){
                                $q->where('status', '=', 1);
                                $q->where('quantity', '>', 0);

                            })->get();
                           // pr($variantSet->toArray());
                           // if(count($variantSet) == count($request->variants)){
                                // if(!in_array($variantSet->product_variant_id, $pv_ids)){
                                    $pv_ids[] = $variant->product_variant_id;
                                // }
                            //}
                        }
                    }
                }
                else{
                    $product_variant = ProductVariantSet::where('variant_type_id', $request->variants[$key])
                    ->where('variant_option_id', $request->options[$key])->where('product_variant_sets.product_id', $product->id)->get();
                    if($product_variant){
                        foreach ($product_variant as $k => $variant) {
                            if(!in_array($variant->product_variant_id, $pv_ids)){
                                $pv_ids[] = $variant->product_variant_id;
                            }
                        }
                    }
                }

            }
        }
        $sets = array();

        if ($request->has('variants') && $request->has('options')) {
            $selected_variant = DB::table('product_variant_sets')->join('product_variants', 'product_variants.id', '=', 'product_variant_sets.product_variant_id')->where('product_variant_sets.product_id', $product->id)
            ->whereIn('variant_option_id', $request->options)
            ->whereIn('variant_type_id', $request->variants)
            ->groupBy('product_variant_id')
            ->havingRaw("COUNT(DISTINCT variant_option_id) = ". count($request->options). " " )
            ->havingRaw("COUNT(DISTINCT variant_type_id) = ".count($request->variants)." ")
            ->select('product_variant_sets.*', 'product_variants.price', 'product_variants.compare_at_price', 'product_variants.quantity')
            ->first();
        }

        $selected_variant_title = $request->selected_variant_title;
        //pr($pv_ids);
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
        $availableSets = Product::with(['variantSet.variantDetail' => function ($q) {
            $q->orderBy('position','ASC');
        },'variantSet.option2'=>function($q)use($product, $pv_ids){
            $q->where('product_variant_sets.product_id', $product->id)->whereIn('product_variant_id', $pv_ids);
        }])
        //return $product;
        ->select('id')
        ->where('products.id', $product->id)->first();
        // Assuming $availableSets is an array of objects with a 'title' property
        foreach ($availableSets->variantSet as $key => $sets) {
            if ($sets->variantDetail->title === $selected_variant_title) {
                unset($availableSets->variantSet[$key]);
            }
        }
        // Convert the object to an array
        $availableSets = json_decode(json_encode($availableSets->variantSet), true);

        usort($availableSets, function ($a, $b) {
            return $a['variant_detail']['position'] - $b['variant_detail']['position'];
        });

        $availableSets = json_decode(json_encode($availableSets), false);

        // pr($availableSets);
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
                $tokenAmount = 1;
                $is_token_enable = $getAdditionalPreference['is_token_currency_enable'];
                if($is_token_enable){
                    $tokenAmount = getJsToken();
                }
                

                $data['variant'] = $variantData;
                $data['tokenAmount'] = $tokenAmount;
                $data['is_token_enable'] = $is_token_enable;
                $keyss =  $request->key ;
                
                $returnHTML = view('frontend.product-part.product-variant-ajax')->with(['availableSets' => $availableSets, 'selected_variant_title' => $selected_variant_title,'is_variant_checked' => $request->is_variant_checked,'keyss'=>$keyss])->render();

                return response()->json(array('status' => 'Success', 'html' => $returnHTML, 'selected_variant' => $selected_variant,'data' => $data));

                // return response()->json(array('status' => 'Success', 'data' => $data));
            }
        }
        //pr($data['availableSets']->toArray());
        return response()->json(array('status' => 'Error', 'message' => 'This option is currenty not available', 'data' => $data));
    }

      # get product faq
    public function getProductCompare(Request $request){
        $comIds = [];
        $idsUnque = $request->compareItems;
        $productId[] = $request->productId;
        if(isset($request->compareItems) && count($request->compareItems)>0)
        {
            $idsUnque = array_merge($request->compareItems,$productId);
        }else{
            $idsUnque[] = $request->productId;
        }
       

        $compareProducts = Product::with(['media.image', 'vendor', 'translation', 'variant','reviews'])->where('category_id', $request->category_id)
        ->whereIn('id', $idsUnque)
        ->orderby('id', 'desc')->get();
        $html ='';
        if(isset($compareProducts)){
            $html = view('frontend.compare-product-table')->with(['compareProducts'=>$compareProducts,'ajax'=>1])->render();
        }     
        return response()->json(['ids'=>$idsmerge??$request->compareItems,'html'=>$html]);
    }

    # get product faq
    public function getProductFaq(Request $request,$domain = '',$product_id){
            $langId = Session::get('customerLanguage');

            if(empty($langId))
            $langId = ClientLanguage::orderBy('is_primary','desc')->value('language_id');

            $product_faqs = ProductFaq::where('product_id',$product_id)->with(['translations' => function ($qs) use($langId){
                $qs->where('language_id',$langId);
            }],'selection')->get();
            if(isset($product_faqs)){

                if ($request->ajax()) {
                 return \Response::json(\View::make('frontend.modals.product-order-form', array('product_faqs'=>  $product_faqs))->render());
                }

            }
            return \Response::json(\View::make('frontend.modals.product-order-form', array('product_faqs'=>  $product_faqs))->render());

            //return $this->errorResponse('Invalid product form ', 404);


    }

    # get product faq
    public function getFreeLincerFromDispatcher(Request $request){
       
       $selecterVariant = ProductVariant::where('id',$request->variant_id)->first();
       if($selecterVariant){
            $latitude = '';
            $longitud = '';
            $address = UserAddress::find(($request->address_id ?? ''));
            if($address){
                $latitude = $address->latitude ;
                $longitud = $address->longitude ;
            }
           $res = $this->getProductPriceFromDispatcher($request->onDemandBookingdate,$selecterVariant->sku, $latitude, $longitud,$request->slot);
           return response()->json(array('status' => 'Success', 'data' => $res['data']));
       }
       return response()->json(array('status' => 'Success', 'data' => []));
    }

    public function getShippingProductDeliverySlots(Request $request){
        if($request->ajax()){
            $product_id = $request->product_id;
            $input_date = $request->input_date;
            $mytime = Carbon::now();
            $current_date = $mytime->format('Y-m-d');
            $current_time = $mytime->format('H:i');
            // $vendor_cut_off_time = Carbon::parse($request->vendor_cutOff_time)->format('H:i');
            $product_delivery_slots = DeliverySlotProduct::with('deliverySlot')->where('product_id', $product_id);

            if($current_date == $input_date){
                $product_delivery_slots = $product_delivery_slots->whereHas('deliverySlot' ,function ($q) use ($current_time) { //Call to a member function format() on string
                    $q->whereTime('cutOff_time', '>=', $current_time);
                    // ->whereTime('end_time', '<=', $vendor_cut_off_time);
                    // $q->where('start_time', '<=', $current_time)
                    // ->orwhere('end_time', '<=', $vendor_cut_off_time);
                    // $q->whereBetween('start_time', [$current_time, $vendor_cut_off_time])
                    // ->orWhereBetween('end_time', [$current_time, $vendor_cut_off_time]);
                })->get();
            }else{
                $product_delivery_slots = $product_delivery_slots->get();
            }
            return view('frontend.shipping-method-slots-ajax')->with(['product_delivery_slots' => $product_delivery_slots]);
        }
    }

    public function getShippingSlotsInterval(Request $request){
        if($request->ajax()){
            $product_delivery_slots_interval = DeliverySlot::where('parent_id', $request->slot_id)->get();
            return view('frontend.shipping-method-slots-interval-ajax')->with(['product_delivery_slots_interval' => $product_delivery_slots_interval]);
        }
    }
    public function getGerenalSlot(Request $request){
        $html  = '';
        $date =  $request->date ??  Carbon::now()->format('Y-m-d');
        $Slots = $this->getGerenalSlotFromDispatcher($date); // GerenalSlot($request->date, '00:00:00', '24:00:00', $Duration="60");
       
      
        foreach ($Slots as $Slot){
            // $StartTime = $date.' '.$Slot['start_time'];
            // $EndTime = $date.' '.$Slot['end_time'];
          
            // $name =  Carbon::parse($StartTime)->format('h:i A').' - '.Carbon::parse($EndTime)->format('h:i A');
            // $value =  Carbon::parse($StartTime)->format('G:i').' - '.Carbon::parse($EndTime)->format('G:i');
          
            $html .= '<option value="'.$Slot['value'].'">'.$Slot['name'].'</option>';
        }
        return response()->json(array('status' => 'Success', 'html' => $html));
       
    }

}