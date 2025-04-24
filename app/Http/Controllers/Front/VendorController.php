<?php

namespace App\Http\Controllers\Front;

use Auth;
use Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\VendorTrait;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Front\FrontController;
use App\Models\{Currency, Banner,Tag ,Category, Brand, Product, ProductCategory, VendorSocialMediaUrls, ClientLanguage, Vendor, VendorCategory, ClientCurrency, ProductVariantSet,CabBookingLayout,ProductTag,Facilty,WebStylingOption,VendorSection};
use Log;
class VendorController extends FrontController
{
    use ApiResponser;
    use VendorTrait;
    private $field_status = 2;


    public function viewAll(){
        $langId = Session::get('customerLanguage');
        $vendorType = Session::get('vendorType');
        if(!$vendorType){
           $vendorType = 'delivery';
        }
        $preferences = (object)Session::get('preferences');
        $additionalPreference = getAdditionalPreference(['is_show_vendor_on_subcription']);
        $navCategories = $this->categoryNav($langId);
        $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 30;
        $ses_vendors = $this->getServiceAreaVendors();

        $categoryTypes = getServiceTypesCategory($vendorType);

        $vendors = Vendor::byVendorSubscriptionRule($preferences)->whereHas('getAllCategory.category',function($q)use ($categoryTypes){
            $q->whereIn('type_id',$categoryTypes);
        })->with('products')->select('id', 'name', 'banner', 'address', 'order_pre_time','is_show_vendor_details' ,'order_min_amount', 'logo', 'slug', 'latitude', 'longitude')->where(['status'=> 1,$vendorType => 1]);
       

        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            $latitude = Session::get('latitude') ?? $preferences->Default_latitude;
            $longitude = Session::get('longitude') ?? $preferences->Default_longitude;
            $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
            //3961 for miles and 6371 for kilometers
            $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
            $vendors = $vendors->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                    cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                    sin( radians(' . $latitude . ') ) *
                    sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
            $vendors = $vendors->whereIn('id', $ses_vendors);
        }

        $vendors = $vendors->paginate($pagiNate);

        foreach ($vendors as $key => $value) {
            $value = $this->getLineOfSightDistanceAndTime($value, $preferences);
            $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $value->id)->where('status', 1)->get();
            $categoriesList = '';
            foreach($vendorCategories as $key => $category){
                if($category->category){
                    $categoriesList = $categoriesList . (!is_null($category->category->translation_one) ? $category->category->translation_one->name : '');
                    if( $key !=  $vendorCategories->count()-1 ){
                        $categoriesList = $categoriesList . ', ';
                    }
                }
            }
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $latitude = Session::get('latitude');
                $longitude = Session::get('longitude');
                $value = $this->getVendorDistanceWithTime($latitude, $longitude, $value, $preferences);
            }
            $value->categoriesList = $categoriesList;
            $value->vendorRating = $this->vendorRating($value->products);
        }
        $page_title = __('All ').getNomenclatureName('Vendors', true);  ;
        $for_no_product_found_html = CabBookingLayout::with('translations')->where('is_active', 1)->web()->where('for_no_product_found_html',1)->orderBy('order_by')->get();
        return view('frontend/vendor-all')->with(['navCategories' => $navCategories,'for_no_product_found_html' => $for_no_product_found_html,'vendors' => $vendors,'page_title' => $page_title]);
    }
    /**
     * Display product By Vendor
     *
     * @return \Illuminate\Http\Response
     */
    public function vendorProducts(Request $request, $domain = '', $slug = 0){   
        
        if($request->ajax())
        {
            $returnHTML = $this->vendorFilters($request,'',$slug);
            return response()->json(array('success' => true, 'html'=>$returnHTML));
        }
        $tag_id = $request->has('tag') && $request->tag ? $request->tag : null;
        $preferences = Session::get('preferences');
        //die($slug);
        // this array for on demand service
        $cartData    = [];
        $period      = [];
        $time_slots  = [];
        $Map_vendors = [];
        $vendorMultiBanner = [];
        $vendor = Vendor::with('slot.day', 'slotDate', 'productsLive.reviews')
            ->select('id','email', 'name', 'slug', 'desc','short_desc', 'logo', 'banner', 'address', 'latitude', 'longitude', 'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery', 'vendor_templete_id', 'is_show_vendor_details', 'website', 'show_slot','closed_store_order_scheduled','instagram_url','country','state',
            'dynamic_html')->where('slug', $slug)->where('status', 1)->firstOrFail();
        $vendor->is_vendor_closed = 0;
        if($vendor->show_slot == 0){
            if( ($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty()) ){
                $vendor->is_vendor_closed = 1;
            }else{
                $vendor->is_vendor_closed = 0;
                if($vendor->slotDate->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slotDate->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slotDate->first()->end_time)->format('g:i A');
                }elseif($vendor->slot->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slot->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slot->first()->end_time)->format('g:i A');
                }
            }
        }
        $review_count = $vendor->whereHas('productsLive.reviews')->count();
        $vendor->review_count = $review_count;
        if( $request->has('table') ){
            if(!Auth::user()){
                session(['url.intended' => url()->full()]);
                return redirect()->route('customer.login');
            }else{
                if(!Session::has('vendorTable')){
                    Session::put('vendorTable', $request->table);
                    Session::put('vendorTableVendorId', $vendor->id);
                    Session::put('vendorType', 'dine_in');
                }
            }
        }

        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
        $brands = Product::with(['brand.translation'=> function($q) use($langId){
                    $q->select('title', 'brand_id')->where('brand_translations.language_id', $langId);
                 }])->select('brand_id')->where('vendor_id', $vendor->id)
                ->where('brand_id', '>', 0)->groupBy('brand_id')->get();

        $variantSets = ProductVariantSet::with(['options' => function($zx) use($langId){
                            $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id');
                            $zx->select('variant_options.*', 'vt.title');
                            $zx->where('vt.language_id', $langId);
                        },
                        'variantDetail.varcategory.cate'
                    ]
                )->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                    ->join('variant_translations as vt','vt.variant_id','vr.id')
                    ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                    ->where('vt.language_id', $langId)
                    ->whereIn('product_id', function($qry) use($vendor){
                        $qry->select('id')->from('products')
                            ->where('vendor_id', $vendor->id);
                    })->groupBy('product_variant_sets.variant_type_id')->get();
        $navCategories = $this->categoryNav($langId);
        $vendorIds[] = $vendor->id;

        $np = $this->productList($vendorIds, $langId, $curId, 'is_new');

        foreach($np as $new){
            $new->translation_title = (!empty($new->translation->first())) ? $new->translation->first()->title : $new->sku;
            $new->variant_multiplier = (!empty($new->variant->first())) ? $new->variant->first()->multiplier : 1;
            $new->variant_price = (!empty($new->variant->first())) ? $new->variant->first()->price : 0;
        }
        $newProducts = ($np->count() > 0) ? array_chunk($np->toArray(), ceil(count($np) / 2)) : $np;
        $listData = $this->listData($langId, $vendor->id, $vendor->vendor_templete_id,'',$tag_id);
        $inqury_count = 0;
        foreach($listData as $ld){
            if($ld->inquiry_only == 1){
                $inqury_count++;
            }
        }


        if($listData->count() == $inqury_count){
            $show_range = 0;
        }
        else{
            $show_range = 1;
        }
        $type = Session::get('vendorType');
        DB::enableQueryLog();
        $range_products = Product::byProductCategoryServiceType($type)
        ->whereHas('vendor', function($q) use($type){
            $q->where($type, 1);
        })->with('tags')->join('product_variants', 'product_variants.product_id', '=', 'products.id')->orderBy('product_variants.price', 'desc')->select('*')->where('is_live', 1)->where('vendor_id', $vendor->id)->get();
        // dd(DB::getQueryLog());
        // dd($range_products->toArray());
        if($vendor->vendor_templete_id == 2){
            $page = 'categories';
        }elseif($vendor->vendor_templete_id == 5){
            $page = 'products-with-categories';
            $products = Product::byProductCategoryServiceType($type)->select('averageRating')->where('is_live', 1)->where('vendor_id', $vendor->id)->get();
            $vendor->vendorRating = $this->vendorRating($products);
        }elseif($vendor->vendor_templete_id == 6){
            $set_template = WebStylingOption::where('web_styling_id',1)->where('is_selected',1)->first();
            if($set_template->template_id == 6)
            {
                $page = 'products-with-categories-extended';
                $products = Product::select('averageRating')->where('is_live', 1)->where('vendor_id', $vendor->id)->get();
                $vendor->vendorRating = $this->vendorRating($products);
                $Vendor_section = VendorSection::with(['headingTranslation'=> function($q) use($langId){
                                                        $q->where('language_id', $langId);
                                                    },'SectionTranslation'=> function($q) use($langId){
                                                        $q->where('language_id', $langId);
                                                    }])->where('vendor_id',$vendor->id)->get();
                $vendorMultiBanner = $this->getMultiBanner($vendor->id);
                $vendor->vendor_section = $Vendor_section;
                $vendor->facilty  = [];
                if( (isset($preferences->is_vendor_tags)) && ($preferences->is_vendor_tags == 1) ){
                    $vendor->facilty  = Facilty::with(['translations'=> function ($q) use ($langId) {
                        $q->where('language_id',$langId);
                    }])->get();
                }
                 // if vendor type selecter on demand service by harbans i don't want to do this garvage
                if($type == 'on_demand' || $type == 'appointment'){

                    $cartDataGet    = $this->getCartOnDemand($request);
                    $cartData       = $cartDataGet['cartData'];
                    $period         = $cartDataGet['period'];
                    $time_slots     = $cartDataGet['time_slots'];

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
                        $new_url = $request->path()."?step=2&dataset=1";
                        return redirect($new_url);
                        }
                    }
                    if($request->step == 2 && empty($request->addons))
                    {
                        $skip_addons = 0;
                        if ($request->session()->has('skip_addons')) {
                            $skip_addons =1;
                        }
                        if($skip_addons != 1){
                            $request->session()->put('skip_addons', '1');
                            $new_url = $request->path()."?step=2";
                            return redirect($new_url);
                        }

                    }

                    $page = 'products-with-categories-ondemand';
                }
                // get vendors for show on map
                $Map_vendors = Vendor::vendorOnline()->select('id', 'name', 'banner', 'address', 'order_pre_time','is_show_vendor_details' ,'order_min_amount', 'logo', 'slug', 'latitude', 'longitude')->where(['status'=> 1,$type => 1])->where('id','!=',$vendor->id); //->where('id','!=',$vendor->id)

                if (( $vendor->latitude) && ($vendor->longitude)) {
                    $latitude = $vendor->latitude;
                    $longitude = $vendor->longitude;
                    $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                    //3961 for miles and 6371 for kilometers
                    //$calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                    $calc_value = 20; // 20 km
                    $Map_vendors = $Map_vendors->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                            cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                            sin( radians(' . $latitude . ') ) *
                            sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
                }

                $Map_vendors = $Map_vendors->take('10')->get();;


            }else{
                $page = 'products-with-categories';
                $products = Product::byProductCategoryServiceType($type)->select('averageRating')->where('is_live', 1)->where('vendor_id', $vendor->id)->get();
                $vendor->vendorRating = $this->vendorRating($products);
            }
        }
        else{
            $page = 'products';
        }

        $is_vendor_closed = 0;
        if($vendor->show_slot == 0){
            if( ($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty()) ){
                $is_vendor_closed = 1;
            }else{
                $is_vendor_closed = 0;
            }
        }
        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){
            $vendors = $this->getServiceAreaVendors();
            if(isset($vendor) && isset($vendor->id)){
                if(!in_array($vendor->id, $vendors)){
                    $listData =collect();
                    return view('frontend/vendor-'.$page)->with(['show_range' => $show_range, 'range_products' => $range_products, 'vendor' => $vendor, 'listData' => $listData, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'variantSets' => $variantSets, 'brands' => $brands,'cartData'=>$cartData,'period' => $period,'time_slots'=>$time_slots,'Map_vendors' =>$Map_vendors,'vendorMultiBanner'=>$vendorMultiBanner, 'is_vendor_closed'=>$is_vendor_closed,]);
                }
            }
        }
        $product_tag_ids = Product::byProductCategoryServiceType($type)->where('vendor_id', $vendor->id)->where('is_live', 1)->pluck('id')->toArray();
        $tag_ids = ProductTag::whereIn('product_id',$product_tag_ids)->pluck('tag_id')->toArray();
        $tags = Tag::whereIn('id',$tag_ids)->with('primary')->get();        
        $socialMediaUrls = VendorSocialMediaUrls::where('vendor_id', $vendor->id)->get();
       
        return view('frontend/vendor-'.$page)->with(['show_range' => $show_range,'tags' => $tags, 'socialMediaUrls'=>$socialMediaUrls, 'range_products' => $range_products, 'vendor' => $vendor, 'listData' => $listData, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'variantSets' => $variantSets, 'brands' => $brands,'is_vendor_closed'=>$is_vendor_closed,'cartData'=>$cartData,'period' => $period ,'time_slots'=>$time_slots,'Map_vendors' =>$Map_vendors,'vendorMultiBanner'=>$vendorMultiBanner]);
    }

    /**
     * Display product By Vendor Category
     * vendor -> category -> product
     * @return \Illuminate\Http\Response
     */
    public function vendorCategoryProducts(Request $request, $domain = '', $slug1 = 0, $slug2 = 0){
        // slug1 =>vendor slug
        // slug2 =>category slug
        $show_range = 0;
        $tag_id = $request->has('tag') && $request->tag ? $request->tag : null;
        $preferences = Session::get('preferences');
        $vendor = Vendor::vendorOnline()->select('id','email', 'name', 'slug', 'desc', 'logo', 'banner', 'address', 'latitude', 'longitude', 'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery', 'vendor_templete_id', 'is_show_vendor_details', 'website', 'show_slot','closed_store_order_scheduled')->where('slug', $slug1)->where('status', 1)->firstOrFail();
        $category = Category::select('id')->where('slug', $slug2)->firstOrFail();
        $vendor_categories = VendorCategory::where('vendor_id', $vendor->id)->where('category_id', $category->id)->where('status', 1)->first();
        $vendor->is_vendor_closed = 0;
        if($vendor->show_slot == 0){
            if( ($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty()) ){
                $vendor->is_vendor_closed = 1;
            }else{
                $vendor->is_vendor_closed = 0;
                if($vendor->slotDate->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slotDate->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slotDate->first()->end_time)->format('g:i A');
                }elseif($vendor->slot->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slot->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slot->first()->end_time)->format('g:i A');
                }
            }
        }

        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();

        $brands = Product::with(['brand.translation'=> function($q) use($langId){
                    $q->select('title', 'brand_id')->where('brand_translations.language_id', $langId);
                }])->select('brand_id')->where('vendor_id', $vendor->id)
                ->where('brand_id', '>', 0)->groupBy('brand_id')->get();
                $variantSets = ProductVariantSet::with(['options' => function($zx) use($langId){
                    $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id');
                    $zx->select('variant_options.*', 'vt.title');
                    $zx->where('vt.language_id', $langId);
                }
                ])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                ->join('variant_translations as vt','vt.variant_id','vr.id')
                ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                ->where('vt.language_id', $langId)
                ->whereIn('product_id', function($qry) use($vendor){
                    $qry->select('id')->from('products')
                    ->where('vendor_id', $vendor->id);
                })->groupBy('product_variant_sets.variant_type_id')->get();
        $navCategories = $this->categoryNav($langId);
        $vendorIds[] = $vendor->id;
        $np = $this->productList($vendorIds, $langId, $curId, 'is_new');


        foreach($np as $new){
            $new->translation_title = (!empty($new->translation->first())) ? $new->translation->first()->title : $new->sku;
            $new->variant_multiplier = (!empty($new->variant->first())) ? $new->variant->first()->multiplier : 1;
            $new->variant_price = (!empty($new->variant->first())) ? $new->variant->first()->price : 0;
        }

        $newProducts = ($np->count() > 0) ? array_chunk($np->toArray(), ceil(count($np) / 2)) : $np;
        if(!empty($slug2) && ($vendor->vendor_templete_id == 2)){
            $vendor->vendor_templete_id = '';
        }

        $listData = $this->listData($langId, $vendor->id, $vendor->vendor_templete_id, $slug2,$tag_id);
        $inqury_count = 0;

        foreach($listData as $ld){
            if($ld->inquiry_only == 1){
                $inqury_count++;
            }
        }

        if($listData->count() == $inqury_count){
            $show_range = 0;
        }
        else{
            $show_range = 1;
        }
        if($vendor->vendor_templete_id == 2){
            $page = 'categories';
        }elseif($vendor->vendor_templete_id == 5){
            $page = 'products-with-categories';
            $products = Product::select('averageRating')->where('is_live', 1)->where('vendor_id', $vendor->id)->get();
            $vendor->vendorRating = $this->vendorRating($products);
        }else{
            $page = 'products';
        }

        // $page = ($vendor->vendor_templete_id == 2) ? 'categories' : 'products';
        $range_products = Product::join('product_variants', 'product_variants.product_id', '=', 'products.id')->orderBy('product_variants.price', 'desc')->select('*')->where('is_live', 1)->where('vendor_id', $vendor->id)->get();

        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){
            if(Session::has('vendors')){
                $vendors = Session::get('vendors');
                if(is_array($vendors))
                $vendors = $vendors;
                else
                $vendors = $vendors->toArray();
                if(!in_array($vendor->id, $vendors)){
                    $listData = collect();
                    return view('frontend/vendor-'.$page)->with(['vendor' => $vendor, 'show_range' => $show_range, 'listData' => $listData, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'variantSets' => $variantSets, 'brands' => $brands, 'range_products' => $range_products, 'vendor_category' => $slug2]);

                //    return view('frontend.vendor-not-in-location')->with(['vendor' => $vendor, 'show_range' => $show_range, 'listData' => $listData, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'variantSets' => $variantSets, 'brands' => $brands, 'range_products' => $range_products, 'vendor_category' => $slug2]);
                    abort(404);
                }
            }else{
                // abort(404);
            }
        }
        return view('frontend/vendor-'.$page)->with(['vendor' => $vendor, 'show_range' => $show_range, 'listData' => $listData, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'variantSets' => $variantSets, 'brands' => $brands, 'range_products' => $range_products, 'vendor_category' => $slug2]);
    }

    public function listData($langId, $vid, $type = '', $categorySlug = '',$tag_id=''){
        $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 12;
        $luxury_type = Session::get('vendorType');

        if($type == 2){
            // display categories
            $product_query = Product::byProductCategoryServiceType($luxury_type)
            ->whereHas('vendor', function($q) use($luxury_type){
                $q->where($luxury_type, 1);
            })
            ->select('category_id')->distinct()->where('vendor_id', $vid)->where('is_live', 1);
            if($tag_id){
               // $product_query
            }
            $products =  $product_query->get();
            $vendor_categories = array();
            foreach($products as $key => $product ){
                $vendor_categories[] = $product->category_id;
            }
            $categoryData = Category::select('id', 'icon', 'slug', 'type_id', 'image')->with(['translation' => function($q) use($langId){
                $q->where('category_translations.language_id', $langId);
            }])->whereIn('id', $vendor_categories);
            $categoryData = $categoryData->paginate($pagiNate);
            foreach ($categoryData as $key => $value) {
                $value->translation_name = ($value->translation->first()) ? $value->translation->first()->name : 'NA';
            }
            return $categoryData;
        }
        elseif($type == 5 || $type == 6){
            // listing category with products
            $user = Auth::user();
            if ($user) {
                $column = 'user_id';
                $value = $user->id;
            } else {
                $column = 'unique_identifier';
                $value = session()->get('_token');
            }
            $cur_ids = Session::get('customerCurrency');
            if(isset($cur_ids) && !empty( $cur_ids))
            $clientCurrency = ClientCurrency::where('currency_id','=', $cur_ids)->first();
            else
            {
                $primaryCurrency = ClientCurrency::where('is_primary','=', 1)->first();
                $cur_ids = $primaryCurrency->currency_id;
                $clientCurrency = ClientCurrency::where('currency_id','=', $cur_ids)->first();
            }
            $categoryTypes = getServiceTypesCategory($luxury_type);
            $vendor_categories = VendorCategory::with(['category.translation' => function($q) use($langId){
                $q->where('category_translations.language_id', $langId);
            }])
            ->whereHas('category' ,function ($q) use($categoryTypes){
                $q->whereIn('categories.type_id',$categoryTypes );
            })
            // /->whereHas('category')
            ->where('vendor_id', $vid);
            if($categorySlug != ''){
                $vendor_categories = $vendor_categories->whereHas('category', function($query) use($categorySlug) {
                    $query->where('slug', $categorySlug);
                });
                $vendor_categories = $vendor_categories->where('status', 1)->get();
                foreach($vendor_categories as $category){
                    $childs = $this->getChildCategoriesForVendor($category->category_id, $langId, $vid);
                    foreach($childs as $child){
                        $vendor_categories->push($child);
                    }
                }
            }
            else{
                // $vendor_categories = $vendor_categories->whereHas('category', function($query) {
                //     $query->whereIn('type_id', [1]);
                // });
                $vendor_categories = $vendor_categories->where('status', 1)->groupBy('category_id')->get();
            }
            foreach($vendor_categories as $ckey => $category) {
                $products = Product::byProductCategoryServiceType($luxury_type)
                        ->whereHas('vendor', function($q) use($luxury_type){
                            $q->where($luxury_type, 1);
                        })
                        ->with(['media.image',
                        'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId,$column,$value){
                            $q->select('id','sku', 'product_id', 'quantity', 'price', 'barcode', 'compare_at_price','markup_price')->orderBy('quantity', 'desc');
                            // $q->groupBy('product_id');
                        },'variant.checkIfInCart.addon',
                        'addOn' => function ($q1) use ($langId) {
                            $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                            $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                            $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                            $q1->where('set.status', 1)->where('ast.language_id', $langId);
                        },
                        'addOn.setoptions' => function ($q2) use ($langId) {
                            $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                            $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                            $q2->where('apt.language_id', $langId);
                        }
                    ])->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only','minimum_order_count','batch_count','minimum_duration_min');
                    $products = $products->where('is_live', 1)->where('category_id', $category->category_id)->where('vendor_id', $vid)->paginate($pagiNate);

                if(!empty($products)){
                    foreach ($products as $key => $value) {
                        foreach ($value->addOn as $key => $val) {
                            foreach ($val->setoptions as $k => $v) {
                                if($v->price == 0){
                                    $v->is_free = true;
                                }else{
                                    $v->is_free = false;
                                }
                                $v->multiplier = $clientCurrency->doller_compare??1;
                            }
                        }

                        $p_id = $value->id;
                        $variantData = $value->with(['variantSet' => function ($z) use ($langId, $p_id) {
                            $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                            $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                            $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                            $z->where('vt.language_id', $langId);
                            $z->where('product_variant_sets.product_id', $p_id)->where('vr.status', 1)->orderBy('product_variant_sets.variant_type_id', 'asc');
                        },'variantSet.option2'=> function ($zx) use ($langId, $p_id) {
                            $zx->where('vt.language_id', $langId)
                            ->where('product_variant_sets.product_id', $p_id);
                        }])->where('id', $p_id)->first();
                        $value->variantSet = $variantData->variantSet;
                        $value->product_image = ($value->media->isNotEmpty() && !is_null($value->media->first()->image)) ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                        $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                        $value->translation_description = ($value->translation->isNotEmpty()) ? html_entity_decode(strip_tags($value->translation->first()->body_html)) : '';
                        $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                        $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                        $value->variant_id = ($value->variant->isNotEmpty()) ? $value->variant->first()->id : 0;
                        $value->variant_quantity = ($value->variant->isNotEmpty()) ? $value->variant->first()->quantity : 0;
                        $value->category_type_id = (!empty($value->category->categoryDetail->first())) ? $value->category->categoryDetail->type_id : 0;
                    }
                }
                if($products->count() > 0){
                    $category->products = $products;
                    $category->products_count = $products->count();
                }else{
                    // if($categorySlug != ''){
                    //     $category->products_count = $products->count();
                    // }
                    // else{
                        unset($vendor_categories[$ckey]);
                    // }
                }
            }
            //   dd($vendor_categories->toArray());
            $listData = $vendor_categories;
            return $listData;
        }
        else{
            $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
            $products = Product::byProductCategoryServiceType($luxury_type)
                        ->whereHas('vendor', function($q) use($luxury_type){
                            $q->where($luxury_type, 1);
                        })
                        ->with(['category.categoryDetail.translation' => function($q) use($langId){
                            $q->where('category_translations.language_id', $langId);
                        },
                        'media.image',
                        'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId){
                            $q->select('sku', 'product_id', 'quantity', 'price','markup_price','barcode');
                            $q->groupBy('product_id');
                        },
                    ])->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only');
            if(!empty($categorySlug)){
                $category = Category::select('id')->where('slug', $categorySlug)->firstOrFail();
                $products = $products->where('category_id', $category->id);
            }
            $products = $products->where('is_live', 1)->where('vendor_id', $vid)->paginate($pagiNate);
            if(!empty($products)){
                foreach ($products as $key => $value) {
                    $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                    $value->translation_description = ($value->translation->isNotEmpty()) ? html_entity_decode(strip_tags($value->translation->first()->body_html)) : '';
                    $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    $value->variant_price = (!empty($value->variant->first())) ? $value->variant->first()->price : 0;
                    $value->category_name = ($value->category->categoryDetail->translation->first()) ? $value->category->categoryDetail->translation->first()->name : $value->category->categoryDetail->slug;
                    $value->image_url = $value->media->first() ? $value->media->first()->image->path['image_fit'] . '240/240' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                    // foreach ($value->variant as $k => $v) {
                    //     $value->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    // }
                }
            }
            $listData = $products;
            return $listData;
        }
    }

    public function vendorProductAddons(Request $request){
        $vendor = $request->vendor;
        $langId = Session::get('customerLanguage');
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
        $variant_id = ($request->has('variant')) ? $request->variant : 0;
        $AddonData = Product::with([
                'media.image',
                'translation' => function($q) use($langId){
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                },
                'variant' => function($q) use($langId, $variant_id){
                    $q->select('id','sku', 'product_id', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price');
                    $q->where('id', $variant_id);
                    // $q->groupBy('product_id');
                },'variant.media.pimage.image','variant.checkIfInCart',
                'addOn' => function ($q1) use ($langId) {
                    $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                    $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                    $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                    $q1->where('set.status', 1)->where('ast.language_id', $langId);
                },
                'addOn.setoptions' => function ($q2) use ($langId) {
                    $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                    $q2->select('addon_options.id', 'addon_options.price', 'apt.title', 'addon_options.addon_id', 'apt.language_id');
                    $q2->where('apt.language_id', $langId)->groupBy(['addon_options.id', 'apt.language_id']);
                }
            ]);
            $AddonData = $AddonData->whereHas('vendor',function($q) use($vendor){
                $q->where('id',$vendor);
            })->where('is_live', 1)->where('url_slug', $request->slug)->first();
        if(!empty($AddonData)){
            if(!is_null($AddonData->variant->first()) && $AddonData->variant->first()->media->isNotEmpty()){
                $image_fit = $AddonData->variant->first()->media->first()->pimage->image->path['image_fit'];
                $image_path = $AddonData->variant->first()->media->first()->pimage->image->path['image_path'];
            }else{
                $image_fit = ($AddonData->media->isNotEmpty()) ? $AddonData->media->first()->image->path['image_fit'] : '';
                $image_path = ($AddonData->media->isNotEmpty()) ? $AddonData->media->first()->image->path['image_path'] : '';
            }
            $AddonData->product_image = $image_fit . '800/800' . $image_path;
            $AddonData->averageRating = number_format($AddonData->averageRating,1);
            $AddonData->translation_title = ($AddonData->translation->isNotEmpty()) ? $AddonData->translation->first()->title : $AddonData->title;
            $AddonData->translation_description = ($AddonData->translation->isNotEmpty()) ? strip_tags($AddonData->translation->first()->body_html) : '';
            $AddonData->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
            $variant_price = ($AddonData->variant->isNotEmpty()) ? $AddonData->variant->first()->price : 0;
            $AddonData->variant_price = decimal_format(($variant_price * $AddonData->variant_multiplier));
        }
        return response()->json(array('status' => 'Success', 'data' => $AddonData));
    }

    /**
     * Product filters on category Page
     * @return \Illuminate\Http\Response
     */
    public function vendorFilters(Request $request, $domain = '', $slug = 0){
        $setArray = $optionArray = array();
        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
        if($request->has('variants') && !empty($request->variants)){
            $setArray = array_unique($request->variants);
        }
        $startRange = 0; $endRange = 20000;
        if($request->has('range') && !empty($request->range)){
            $range = explode(';', $request->range);
            $clientCurrency->doller_compare;
            $startRange = $range[0] / $clientCurrency->doller_compare;
            $endRange = $range[1] / $clientCurrency->doller_compare;
        }
        $multiArray = array();
        if($request->has('options') && !empty($request->options)){
            foreach ($request->options as $key => $value) {
                $multiArray[$request->variants[$key]][] = $value;
            }
        }
        $variantIds = $productIds = array();
        if(!empty($multiArray)){
            foreach ($multiArray as $key => $value) {
                $new_pIds = $new_vIds = array();
                $vResult = ProductVariantSet::join('product_categories as pc', 'product_variant_sets.product_id', 'pc.product_id')->select('product_variant_sets.product_variant_id', 'product_variant_sets.product_id')
                    ->where('product_variant_sets.variant_type_id', $key)
                    ->whereIn('product_variant_sets.variant_option_id', $value);

                if(!empty($variantIds)){
                    $vResult  = $vResult->whereIn('product_variant_sets.product_variant_id', $variantIds);
                }
                $vResult  = $vResult->groupBy('product_variant_sets.product_variant_id')->get();
                if($vResult){
                    foreach ($vResult as $key => $value) {
                        $new_vIds[] = $value->product_variant_id;
                        $new_pIds[] = $value->product_id;
                    }
                }
                $variantIds = $new_vIds;
                $productIds = $new_pIds;
            }
        }
        $order_type = $request->has('order_type') ? $request->order_type : '';
        $products = Product::with(['media.image', 'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId, $variantIds,$order_type){
                            $q->select('sku', 'product_id', 'quantity', 'price', 'markup_price','barcode');
                            // if(!empty($variantIds)){
                            //     $q->whereIn('id', $variantIds);
                            // }
                            // $q->groupBy('product_id');
                            // if(!empty($order_type) && $order_type == 'low_to_high'){
                            //     $q->orderBy('price', 'asc');
                            // }elseif(!empty($order_type) && $order_type == 'high_to_low'){
                            //     $q->orderBy('price', 'desc');
                            // }

                        },'category.categoryDetail.translation' => function($q) use($langId){
                            $q->where('category_translations.language_id', $langId);
                        },
                    ])->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock','products.inquiry_only', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count')
                            ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                            ->join('product_translations', 'product_translations.product_id', '=', 'products.id') // Or whatever the join logic is
                    // ->where('vendor_id', $vid)
                    ->whereHas('vendor', function($query) use($slug){
                        $query->where('slug',$slug);
                    })

                    ->where('is_live', 1)
                    ->whereIn('products.id', function ($qr) use ($startRange, $endRange) {
                        $qr->select('product_id')->from('product_variants')
                            ->where('price', '>=', $startRange)
                            ->where('price', '<=', $endRange);
                    });

        if(!empty($productIds)){
            $products = $products->whereIn('products.id', $productIds);
        }
        if($request->has('brands') && !empty($request->brands)){
            $products = $products->whereIn('products.brand_id', $request->brands);
        }

        //sorting
        if (!empty($order_type) && $request->order_type == 'rating') {
            $products = $products->orderBy('products.averageRating', 'desc');
        }elseif (!empty($order_type) && $order_type == 'low_to_high') {
            $products = $products->orderBy('product_variants.price', 'asc');
        }elseif (!empty($order_type) && $order_type == 'high_to_low') {
            $products = $products->orderBy('product_variants.price', 'desc');
        }elseif (!empty($order_type) && $order_type == 'newly_added') {
            $products = $products->orderBy('products.id', 'desc');
        }elseif (!empty($order_type) && $order_type == 'a_to_z') {
            $products = $products->orderBy('product_translations.title', 'asc');
        }elseif (!empty($order_type) && $order_type == 'z_to_a') {
            $products = $products->orderBy('product_translations.title', 'desc');
        }else{
            //
        }
        //end sorting

        $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 12;
        $products = $products->groupBy('products.id')->paginate($pagiNate);

        if(!empty($products)){
            foreach ($products as $key => $value) {
                $value->translation_title = (!empty($value->translation->first())) ? $value->translation->first()->title : $value->sku;
                $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                $value->variant_price = (!empty($value->variant->first())) ? $value->variant->first()->price : 0;
                $value->image_url = $value->media->first() ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                $value->category_name = ($value->category->categoryDetail->translation->first()) ? $value->category->categoryDetail->translation->first()->name : $value->category->categoryDetail->slug;
                // foreach ($value->variant as $k => $v) {
                //     $value->variant[$k]->multiplier = $clientCurrency->doller_compare;
                // }
            }
        }
        $listData = $products;
        $returnHTML = view('frontend.ajax.productList')->with(['listData' => $listData, 'data'=>$request->all()])->render();
        return $returnHTML;
        // return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    public function vendorProductsSearchResults(Request $request)
    {
        $response = [];
        $tagId = $request->input('tag_id');
        $keyword = $request->input('keyword');
        $vid = $request->input('vendor');
        $vCat = $request->input('vendor_category');
        $order_type = $request->input('order_type');
        $langId = Session::get('customerLanguage');
        $preferences = Session::get('preferences');

      
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();

        $vendor = Vendor::with('slot.day', 'slotDate')
            ->select('id','email', 'name', 'show_slot')->where('id', $vid)->where('status', 1)->firstOrFail();
        $vendor->is_vendor_closed = 0;
        if($vendor->show_slot == 0){
            if( ($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty()) ){
                $vendor->is_vendor_closed = 1;
            }else{
                $vendor->is_vendor_closed = 0;
                if($vendor->slotDate->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slotDate->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slotDate->first()->end_time)->format('g:i A');
                }elseif($vendor->slot->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slot->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slot->first()->end_time)->format('g:i A');
                }
            }
        }

        $vendorCategory = [];
        if($vCat != ''){
            $category = Category::select('id')->where('slug', $vCat)->firstOrFail();
            array_push($vendorCategory, $category->id);
            $childs = $this->getChildCategoriesForVendor($category->id, $langId, $vid);
            foreach($childs as $child){
                array_push($vendorCategory, $child->category->id);
            }
        }

        // Check vendor service area on hyperlocal
        $check_service_area = false;
        $vendors = [];
        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){
            $check_service_area = true;
            $vendors = $this->getServiceAreaVendors();
        }

        $vendor_categories = collect(); // final data
        if( !$check_service_area || ( $check_service_area && in_array($vid, $vendors) ) ){
            $type = Session::get('vendorType');
            $products = Product::byProductCategoryServiceType($type)->with(['media.image',
                'translation' => function($q) use($langId, $keyword){
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    if($keyword){
                        $q->where(function ($q1) use ($keyword) {
                            $q1->where('title', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('body_html', 'LIKE', '%' . $keyword . '%');
                        });
                    }

                },
                'variant' => function($q) use($langId){
                    $q->select('id','sku', 'product_id', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price');
                    // $q->groupBy('product_id');
                },'variant.checkIfInCart',
                'addOn' => function ($q1) use ($langId) {
                    $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                    $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                    $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                    $q1->where('set.status', 1)->where('ast.language_id', $langId);
                },
                'addOn.setoptions' => function ($q2) use ($langId) {
                    $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                    $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                    $q2->where('apt.language_id', $langId);
                },'tags'
            ])->select('products.id', 'products.sku','products.title', 'products.url_slug','products.weight_unit','products.category_id', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock','products.inquiry_only', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count','products.is_recurring_booking', 'products.calories')
            ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
            ->join('product_translations', 'product_translations.product_id', '=', 'products.id');

            if($keyword){
                $products->where(function ($q) use ($keyword, $langId) {
                    $q->where(function ($q1) use ($keyword) {
                        $q1->where('products.sku', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('products.url_slug', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('products.title', 'LIKE', '%' . $keyword . '%');
                    });
                    $q->orWhereHas('translation', function ($q1) use ($keyword, $langId) {
                        $q1->where(function ($q2) use ($keyword) {
                            $q2->where('title', 'LIKE', '%' . $keyword . '%');
                        });
                    });
                });
            }
            if($tagId){
                $products->whereHas('tags',function($query) use ($tagId){
                    $query->whereIn('tag_id',$tagId);
                });
            }

            if(count($vendorCategory) > 0){
                $products = $products->whereIn('category_id', $vendorCategory);
            }
            // Sorting
            if (!empty($order_type) && $request->order_type == 'rating') {
                $products = $products->orderBy('products.averageRating', 'desc');
            }elseif (!empty($order_type) && $order_type == 'low_to_high') {
                $products = $products->orderBy('product_variants.price', 'asc');
            }elseif (!empty($order_type) && $order_type == 'high_to_low') {
                $products = $products->orderBy('product_variants.price', 'desc');
            }elseif (!empty($order_type) && $order_type == 'newly_added') {
                $products = $products->orderBy('products.id', 'desc');
            }elseif (!empty($order_type) && $order_type == 'a_to_z') {
                $products = $products->orderBy('product_translations.title', 'asc');
            }elseif (!empty($order_type) && $order_type == 'z_to_a') {
                $products = $products->orderBy('product_translations.title', 'desc');
            }elseif (!empty($order_type) && ($order_type == 'cal_asc' || $order_type == 'cal_desc')) {
                if ($order_type == 'cal_asc') {
                    $products = $products->orderByRaw('CAST(products.calories AS SIGNED) IS NULL')
                    ->orderByRaw('CAST(products.calories AS SIGNED) asc');
                } elseif ($order_type == 'cal_desc') {
                    $products = $products->orderByRaw('CAST(products.calories AS SIGNED) IS NULL')
                    ->orderByRaw('CAST(products.calories AS SIGNED) desc');
                }
            }else{
                //
            }
            // End Sorting
            $products = $products->where('is_live', 1)
            ->groupBy('products.id')
            ->where('vendor_id', $vid)->get();


            $category_list = [];
            if($products->isNotEmpty()){
                foreach($products as $k => $value) {
                    foreach ($value->addOn as $key => $val) {
                        foreach ($val->setoptions as $k => $v) {
                            if($v->price == 0){
                                $v->is_free = true;
                            }else{
                                $v->is_free = false;
                            }
                            $v->multiplier = $clientCurrency->doller_compare;
                        }
                    }



                    $p_id = $value->id;
                    $variantData = $value->with(['variantSet' => function ($z) use ($langId, $p_id) {
                        $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                        $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                        $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                        $z->where('vt.language_id', $langId);
                        $z->where('product_variant_sets.product_id', $p_id)->orderBy('product_variant_sets.variant_type_id', 'asc');
                    },'variantSet.option2'=> function ($zx) use ($langId, $p_id) {
                        $zx->where('vt.language_id', $langId)
                        ->where('product_variant_sets.product_id', $p_id);
                    }])->where('id', $p_id)->first();
                    $value->variantSet = $variantData->variantSet;
                    $value->product_image = ($value->media->isNotEmpty() && isset($value->media->first()->image)) ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                    $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                    $value->translation_description = ($value->translation->isNotEmpty()) ? strip_tags($value->translation->first()->body_html) : '';
                    $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                    $value->variant_quantity = ($value->variant->isNotEmpty()) ? $value->variant->first()->quantity : 0;
                    $value->category_type_id = (!empty($value->category->categoryDetail->first())) ? $value->category->categoryDetail->type_id : 0;
                    $cid = $value->category_id;

                    if(!in_array($cid, $category_list)){
                        $category_list[] = $cid;
                        $vendor_category = VendorCategory::with(['category.translation' => function($q) use($langId){
                            $q->where('category_translations.language_id', $langId)->groupBy('category_translations.language_id');
                        }]);
                        // ->whereHas('category');
                        if(count($vendorCategory) < 1){ // if user is not coming to vendor by selecting a category
                            // $vendor_category = $vendor_category->whereHas('category', function($query) {
                            //     $query->whereIn('categories.type_id', [1]);
                            // });
                        }
                        $vendor_category = $vendor_category->where('status', 1)->where('vendor_id', $vid)->where('category_id', $cid)->first();
                        
                        if($vendor_categories){
                            $vendorProducts = $products->where('category_id', $cid);
                            if($vendor_category){
                                $vendor_category->category->translation_title = $vendor_category->category->translation->first() ? $vendor_category->category->translation->first()->name : '';
                                $vendor_category->products = $vendorProducts;
                                $vendor_category->products_count = $vendorProducts->count();
                                $vendor_categories->push($vendor_category);
                            }
                        }
                    }
                }
            }

        }

        $product_tag_ids = Product::where('vendor_id', $vid)->where('is_live', 1)->pluck('id')->toArray();
        $tag_ids = ProductTag::whereIn('product_id',$product_tag_ids)->pluck('tag_id')->toArray();
        $tags = Tag::whereIn('id',$tag_ids)->with('primary')->get();
        $listData = $vendor_categories;
        if( $request->has('vendor_template_id') && $request->vendor_template_id == 6) {
            $returnHTML = view('frontend.vendor-temp-six-search-products')->with(['vendor'=> $vendor,'tags'=>$tags,'tag_id'=> $tagId, 'listData'=>$listData,'tagId'=>$tagId, 'input'=>$request->all()])->render();
        } else {
            $returnHTML = view('frontend.vendor-search-products')->with(['vendor'=> $vendor,'tags'=>$tags,'tag_id'=> $tagId, 'listData'=>$listData,'tagId'=>$tagId, 'input'=>$request->all()])->render();
        }

        return response()->json(array('status'=>'Success', 'html'=>mb_convert_encoding($returnHTML, "UTF-8", "auto")));
    }


    // product search for edit order
    public function vendorProductsSearchResultsForEditOrder(Request $request)
    {
        $response = [];
        // $tagId = $request->input('tag_id');
        $keyword = $request->input('keyword');
        $vid = $request->input('vendor');
        // $vCat = $request->input('vendor_category');
        // $langId = Session::get('customerLanguage');
        // $preferences = Session::get('preferences');

        // $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();

        $clientLanguage = ClientLanguage::where('is_primary', 1)->first();
        $langId = $clientLanguage ? $clientLanguage->language_id : 1;

        $vendor = Vendor::with('slot.day', 'slotDate')
            ->select('id','email', 'name', 'show_slot')->where('id', $vid)->where('status', 1)->firstOrFail();
        $vendor->is_vendor_closed = 0;
        if($vendor->show_slot == 0){
            if( ($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty()) ){
                $vendor->is_vendor_closed = 1;
            }else{
                $vendor->is_vendor_closed = 0;
                if($vendor->slotDate->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slotDate->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slotDate->first()->end_time)->format('g:i A');
                }elseif($vendor->slot->isNotEmpty()){
                    $vendor->opening_time = Carbon::parse($vendor->slot->first()->start_time)->format('g:i A');
                    $vendor->closing_time = Carbon::parse($vendor->slot->first()->end_time)->format('g:i A');
                }
            }
        }

        // $vendorCategory = [];
        // if($vCat != ''){
        //     $category = Category::select('id')->where('slug', $vCat)->firstOrFail();
        //     array_push($vendorCategory, $category->id);
        //     $childs = $this->getChildCategoriesForVendor($category->id, $langId, $vid);
        //     foreach($childs as $child){
        //         array_push($vendorCategory, $child->category->id);
        //     }
        // }

        $products = Product::with(['media.image',
                'translation' => function($q) use($langId, $keyword){
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    if($keyword){
                        $q->where(function ($q1) use ($keyword) {
                            $q1->where('title', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('body_html', 'LIKE', '%' . $keyword . '%');
                        });
                    }
                    $q->groupBy('product_id');
                },
                'variant' => function($q) use($langId){
                    $q->select('id','sku', 'product_id', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price');
                    // $q->groupBy('product_id');
                },
                'addOn' => function ($q1) use ($langId) {
                    $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                    $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                    $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                    $q1->where('set.status', 1)->where('ast.language_id', $langId);
                },
                'addOn.setoptions' => function ($q2) use ($langId) {
                    $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                    $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                    $q2->where('apt.language_id', $langId);
                }
            ])
            ->select('id', 'sku', 'description', 'category_id', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only');
            if($keyword){
                $products->where(function ($q) use ($keyword, $langId) {
                    $q->where(function ($q1) use ($keyword) {
                        $q1->where('sku', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('url_slug', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('title', 'LIKE', '%' . $keyword . '%');
                    });
                    $q->orWhereHas('translation', function ($q1) use ($keyword, $langId) {
                        $q1->where(function ($q2) use ($keyword) {
                            $q2->where('title', 'LIKE', '%' . $keyword . '%');
                        });
                    });
                });
            }
            // if($tagId){
            //     $products->whereHas('tags',function($query) use ($tagId){
            //         $query->whereIn('tag_id',$tagId);
            //      });
            // }
        // if(count($vendorCategory) > 0){
        //     $products = $products->whereIn('category_id', $vendorCategory);
        // }
        $products = $products->where('is_live', 1)->where('vendor_id', $vid)->get();

        $vendor_categories = collect();
        $category_list = [];
        if($products->isNotEmpty()){
            foreach($products as $k => $value) {
                foreach ($value->addOn as $key => $val) {
                    foreach ($val->setoptions as $k => $v) {
                        if($v->price == 0){
                            $v->is_free = true;
                        }else{
                            $v->is_free = false;
                        }
                        // $v->multiplier = $clientCurrency->doller_compare;
                    }
                }

                $p_id = $value->id;
                $variantData = $value->with(['variantSet' => function ($z) use ($langId, $p_id) {
                    $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                    $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                    $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                    $z->where('vt.language_id', $langId);
                    $z->where('product_variant_sets.product_id', $p_id)->orderBy('product_variant_sets.variant_type_id', 'asc');
                },'variantSet.option2'=> function ($zx) use ($langId, $p_id) {
                    $zx->where('vt.language_id', $langId)
                    ->where('product_variant_sets.product_id', $p_id);
                }])->where('id', $p_id)->first();
                $value->variantSet = $variantData->variantSet;
                $value->product_image = ($value->media->isNotEmpty()) ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                $value->translation_description = ($value->translation->isNotEmpty()) ? strip_tags($value->translation->first()->body_html) : '';
                // $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                $value->variant_quantity = ($value->variant->isNotEmpty()) ? $value->variant->first()->quantity : 0;

                $cid = $value->category_id;

                if(!in_array($cid, $category_list)){
                    $category_list[] = $cid;
                    $vendor_category = VendorCategory::with(['category.translation' => function($q) use($langId){
                        $q->where('category_translations.language_id', $langId)->groupBy('category_translations.language_id');
                    }]);

                    $vendor_category = $vendor_category->where('status', 1)->where('vendor_id', $vid)->where('category_id', $cid)->first();
                    if($vendor_categories){
                        $vendorProducts = $products->where('category_id', $cid);
                        if($vendor_category){
                            $vendor_category->category->translation_title = $vendor_category->category->translation->first() ? $vendor_category->category->translation->first()->name : '';
                            $vendor_category->products = array_values($vendorProducts->toArray());
                            $vendor_category->products_count = $vendorProducts->count();
                            $vendor_categories->push($vendor_category);
                        }
                    }
                }
            }
        }
        // $tags = Tag::with('primary')->get();
        // dd($vendor_categories->toArray());

        $listData = $vendor_categories;
        $data['vendor'] = $vendor;
        $data['listData'] = $listData;

        // $returnHTML = view('frontend.vendor-search-products')->with(['vendor'=> $vendor,'tags'=>$tags,'tag_id'=> $tagId, 'listData'=>$listData])->render();
        // return response()->json(array('status'=>'Success', 'html'=>$returnHTML));
        return $this->successResponse($data, '', 200);
    }
    
    
    public function vendorAllProducts(Request $request, $domain, $cat_id, $vendor_id)
    {        
        $vendor = Vendor::with('slot.day', 'slotDate', 'productsLive.reviews')
        ->select('id','email','name','slug','desc','short_desc','logo','banner','address','latitude','longitude','order_min_amount','order_pre_time','auto_reject_time','dine_in','takeaway','delivery','vendor_templete_id','is_show_vendor_details','website','show_slot','closed_store_order_scheduled','instagram_url','country','state','dynamic_html'
            )->where('id', $vendor_id)->firstOrFail();
            $luxury_type = Session::get('vendorType');
            $products = Product::byProductCategoryServiceType($luxury_type)
            ->whereHas('vendor', function ($q) use ($luxury_type) {
                $q->where($luxury_type, 1);
            })->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only', 'minimum_order_count', 'batch_count', 'minimum_duration_min')
            ->where('is_live', 1)->where('category_id', $cat_id)->where('vendor_id', $vendor->id)->paginate(12);
            $langId = Session::get('customerLanguage');
            $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
            if (!empty($products)) {
                foreach ($products as $key => $value) {
                    $p_id = $value->id;
                    $variantData = $value->with(['variantSet' => function ($z) use ($langId, $p_id) {
                        $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                        $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                        $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                        $z->where('vt.language_id', $langId);
                        $z->where('product_variant_sets.product_id', $p_id)->where('vr.status', 1)->orderBy('product_variant_sets.variant_type_id', 'asc');
                    }, 'variantSet.option2' => function ($zx) use ($langId, $p_id) {
                        $zx->where('vt.language_id', $langId)
                        ->where('product_variant_sets.product_id', $p_id);
                    }])->where('id', $p_id)->first();
                    $value->variantSet = $variantData->variantSet;
                    $value->product_image = ($value->media->isNotEmpty() && !is_null($value->media->first()->image)) ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                    $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                    $value->translation_description = ($value->translation->isNotEmpty()) ? html_entity_decode(strip_tags($value->translation->first()->body_html)) : '';
                    $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                    $value->variant_id = ($value->variant->isNotEmpty()) ? $value->variant->first()->id : 0;
                    $value->variant_quantity = ($value->variant->isNotEmpty()) ? $value->variant->first()->quantity : 0;
                }
            }
            $category = Category::find($cat_id);
            return view('frontend.vendor-all-products')->with(['vendor' => $vendor, 'products' => $products, 'category' => $category]);
    }
}
