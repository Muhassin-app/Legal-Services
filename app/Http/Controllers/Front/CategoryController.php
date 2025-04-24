<?php

namespace App\Http\Controllers\Front;
use DB;
use Auth;
use Session;
use Carbon\CarbonPeriod;
use DateTime;
use DateInterval;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Front\FrontController;
use App\Models\{Currency, CategoryKycDocuments,Banner, Category, Brand, Product, Celebrity, ClientLanguage, Vendor, VendorCategory, ClientCurrency, ProductVariantSet, ServiceArea, UserAddress,Country,Cart,CartProduct,SubscriptionInvoicesUser,ClientPreference,LoyaltyCard,Order,CaregoryKycDoc,Rider, Attribute, Company, ProductVariant};
use Redirect;
use Log;
use \App\Http\Traits\{VendorTrait};
use App\Models\Client as ModelsClient;
class CategoryController extends FrontController{
    private $field_status = 2;
    use \App\Http\Traits\DispatcherSlot,VendorTrait;


      /**
     * Display product and vendor list By Category id
     *
     * @return \Illuminate\Http\Response
     */
    public function companyCategoryProduct(Request $request, $domain = '',$id = 0,$slug = 0)
    {
        //$preferences = Session::get('preferences');

        if(@$id && !empty($id))
        {
            session()->put('company_id', $id);
        }

        return redirect()->to('/');

        $vendorType = Session::get('vendorType');
        $preferences = !empty(Session::get('preferences')) ? (object)Session::get('preferences'):  getClientPreferenceDetail();
        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $category = Category::with(['tags', 'brands.translation' => function($q) use($langId){
            $q->where('brand_translations.language_id', $langId);
        },
        'type'  => function($q){
            $q->select('id', 'title as redirect_to' ,'service_type' );
        },
        'childs.translationLatest'  => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'translationLatest' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'allParentsAccount'])
        ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products', 'parent_id', 'sub_cat_banners')
        ->where('slug', $slug)->firstOrFail();

        $category->translation_name = ($category->translationLatest) ? $category->translationLatest->name : $category->slug;
        foreach($category->childs as $key => $child){
            $child->translation_name = ($child->translationLatest) ? $child->translationLatest->name : $child->slug;
        }
        $service_type = $category->type->service_type;
        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) && (isset($category->type_id)) && !in_array($category->type_id,[4,5]) ){
            $latitude = Session::get('latitude');
            $longitude = Session::get('longitude');
            $vendors = $this->getServiceAreaVendors();
            $redirect_to = $category->type->redirect_to;
            $page = (strtolower($redirect_to) != '') ? strtolower($redirect_to) : 'product';

            if( is_array($vendors) &&  (count($vendors) > 0) ){

                Session::put('vendors', $vendors);

                //remake child categories array
                if($category->childs->isNotEmpty()){
                    $childArray = array();
                    foreach($category->childs as $key => $child){
                        $child_ID = $child->id;
                        $category_vendors = VendorCategory::where('category_id', $child_ID)->where('status', 1)->first();
                        if($category_vendors){
                            $childArray[] = $child;
                        }
                    }
                    $category->childs = collect($childArray);
                }
                //Abort route if category from route does not exist as per hyperlocal vendors
                if($page != 'pickup/delivery'){
                    $category_vendors = VendorCategory::select('vendor_id')->where('category_id', $category->id)->where('status', 1)->get();
                    if($category_vendors->isNotEmpty()){
                        $index = 1;
                        foreach($category_vendors as $key => $value){
                            if(in_array($value->vendor_id, $vendors)){
                                break;
                            }
                            elseif(count($category_vendors) == $index){
                               // abort(404);
                            }
                            $index++;
                        }
                    }
                    else{
                       // abort(404);
                    }
                }

            }else{
                // abort(404);
            }
        }

        $navCategories = $this->categoryNav($langId);

        if(isset($vendors)){
            $vendorIds = $vendors;
        }else{
            $vendorIds = array();
            $vendorList = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->select('id', 'name')->where('status', '!=', $this->field_status);
            if(!empty($vendorType)){
                $vendorList= $vendorList->where($vendorType, 1);
            }
            $vendorList = $vendorList->get();
            if(!empty($vendorList)){
                foreach ($vendorList as $key => $value) {
                    $vendorIds[] = $value->id;
                }
            }
        }
        // $variantSets = ProductVariantSet::with(['options' => function($zx) use($langId){
        //                     $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id');
        //                     $zx->select('variant_options.*', 'vt.title');
        //                     $zx->where('vt.language_id', $langId);
        //                 }
        //             ])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
        //             ->join('variant_translations as vt','vt.variant_id','vr.id')
        //             ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
        //             ->where('vt.language_id', $langId)
        //             ->where('vr.status', 1)
        //             ->whereIn('product_variant_sets.product_id', function($qry) use($category){
        //                 $qry->select('product_id')->from('product_categories')
        //                     ->where('category_id', $category->id);
        //                 })
        //             ->groupBy('product_variant_sets.variant_type_id')->get();
                 //   pr($variantSets);
        $redirect_to = $category->type->redirect_to;

        $listData = $this->listData($langId, $category->id, $redirect_to,$vendorIds,false);
        $maxPrice = DB::select("SELECT MAX(product_variants.price) as max_price FROM product_variants INNER JOIN products ON products.id = product_variants.product_id WHERE product_variants.status = 1 AND products.is_live = 1 AND products.category_id = ?", [$category->id])[0]->max_price;
        $page = (strtolower($redirect_to) != '') ? strtolower($redirect_to) : 'product';
        // $newProducts =  $this->getNewProducts($vendorIds, $langId, $curId);
        $productAttributes = '';
        $getAdditionalPreference = getAdditionalPreference(['is_attribute','is_postpay_enable','is_cab_pooling','is_bid_ride_enable','is_particular_driver','is_recurring_booking','is_share_ride_users']);
        if( checkTableExists('product_attributes') ) {


            if( $category->type_id == 13 && $getAdditionalPreference['is_attribute'] ) {

                $productAttributes = Attribute::with('option', 'varcategory.cate.primary')
                    ->select('attributes.*')
                    ->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
                    ->where('attribute_categories.category_id', $category->id)
                    ->where('attributes.status', '!=', 2)
                    ->orderBy('position', 'asc')->get();
            }
        }

        $newProducts = [];
        if($page == 'pickup/delivery'){
            if(!Auth::user()){
                return redirect()->route('customer.login');
            }else{
                $user_addresses = UserAddress::whereNotNull('latitude')->whereNotNull('longitude')->get();
                $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
                $wallet_balance = Auth::user()->balanceFloat * ($clientCurrency->doller_compare ?? 1);
                $riders = Rider::where('user_id',Auth::user()->id)->orderBy('id','DESC')->get();

                return view('frontend.booking.index')->with(['maxPrice'=>$maxPrice,'clientCurrency' => $clientCurrency ,'wallet_balance' => $wallet_balance, 'user_addresses' => $user_addresses, 'navCategories' => $navCategories,'category' => $category,'riders'=>$riders, 'is_cab_pooling' => $getAdditionalPreference['is_cab_pooling'], 'is_bid_ride_enable' => $getAdditionalPreference['is_bid_ride_enable'],'is_postpay_enable' => $getAdditionalPreference['is_postpay_enable'], 'is_particular_driver' => $getAdditionalPreference['is_particular_driver'],'is_recurring_booking' => $getAdditionalPreference['is_recurring_booking'],'is_share_ride_users'=>$getAdditionalPreference['is_share_ride_users']]);
            }
        }
    }

    /**
     * Display product and vendor list By Category id
     *
     * @return \Illuminate\Http\Response
     */
    public function categoryProduct(Request $request, $domain = '', $slug = 0, $service = null)
    {
        //$preferences = Session::get('preferences');
        if(!empty($service) && $service == 'pick_drop'){
            Session::forget('vendorType');
            $vendorType = Session::put('vendorType', $service);
        }
        $preferences = !empty(Session::get('preferences')) ? (object)Session::get('preferences'):  getClientPreferenceDetail();
        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $category = Category::with(['tags', 'brands.translation' => function($q) use($langId){
            $q->where('brand_translations.language_id', $langId);
        },
        'type'  => function($q){
            $q->select('id', 'title as redirect_to' ,'service_type' );
        },
        'childs.translationLatest'  => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'translationLatest' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'allParentsAccount'])
        ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products', 'parent_id', 'sub_cat_banners')
        ->where('slug', $slug)->firstOrFail();

        $category->translation_name = ($category->translationLatest) ? $category->translationLatest->name : $category->slug;
        foreach($category->childs as $key => $child){
            $child->translation_name = ($child->translationLatest) ? $child->translationLatest->name : $child->slug;
        }
        $service_type = $category->type->service_type ?? "";

        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) && (isset($category->type_id)) && !in_array($category->type_id,[4,5]) ){
            $latitude = Session::get('latitude');
            $longitude = Session::get('longitude');
            $vendors = $this->getServiceAreaVendors();
            $redirect_to = $category->type->redirect_to;
            $page = (strtolower($redirect_to) != '') ? strtolower($redirect_to) : 'product';

            if( is_array($vendors) &&  (count($vendors) > 0) ){

                Session::put('vendors', $vendors);

                //remake child categories array
                if($category->childs->isNotEmpty()){
                    $childArray = array();
                    foreach($category->childs as $key => $child){
                        $child_ID = $child->id;
                        $category_vendors = VendorCategory::where('category_id', $child_ID)->where('status', 1)->first();
                        if($category_vendors){
                            $childArray[] = $child;
                        }
                    }
                    $category->childs = collect($childArray);
                }
                //Abort route if category from route does not exist as per hyperlocal vendors
                if($page != 'pickup/delivery'){
                    $category_vendors = VendorCategory::select('vendor_id')->where('category_id', $category->id)->where('status', 1)->get();
                    if($category_vendors->isNotEmpty()){
                        $index = 1;
                        foreach($category_vendors as $key => $value){
                            if(in_array($value->vendor_id, $vendors)){
                                break;
                            }
                            elseif(count($category_vendors) == $index){
                               // abort(404);
                            }
                            $index++;
                        }
                    }
                    else{
                       // abort(404);
                    }
                }

            }else{
                // abort(404);
            }
        }

        $navCategories = $this->categoryNav($langId);

        if(isset($vendors)){
            $vendorIds = $vendors;
        }else{
            $vendorIds = array();
            $vendorList = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->select('id', 'name')->where('status', '!=', $this->field_status);
            if(!empty($vendorType)){
                $vendorList= $vendorList->where($vendorType, 1);
            }
            $vendorList = $vendorList->get();
            if(!empty($vendorList)){
                foreach ($vendorList as $key => $value) {
                    $vendorIds[] = $value->id;
                }
            }
        }
        $variantSets = ProductVariantSet::with(['options' => function($zx) use($langId){
                            $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id');
                            $zx->select('variant_options.*', 'vt.title');
                            $zx->where('vt.language_id', $langId);
                        }
                    ])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                    ->join('variant_translations as vt','vt.variant_id','vr.id')
                    ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                    ->where('vt.language_id', $langId)
                    ->where('vr.status', 1)
                    ->whereIn('product_variant_sets.product_id', function($qry) use($category){
                        $qry->select('product_id')->from('product_categories')
                            ->where('category_id', $category->id);
                        })
                    ->groupBy('product_variant_sets.variant_type_id')->get();
                 //   pr($variantSets);
        $redirect_to = $category->type->redirect_to;
        $listData = $this->listData($langId, $category->id, $redirect_to,$vendorIds,false);

        $maxPrice = DB::select("SELECT MAX(product_variants.price) as max_price FROM product_variants INNER JOIN products ON products.id = product_variants.product_id WHERE product_variants.status = 1 AND products.is_live = 1 AND products.category_id = ?", [$category->id])[0]->max_price;
        $page = (strtolower($redirect_to) != '') ? strtolower($redirect_to) : 'product';
        // $newProducts =  $this->getNewProducts($vendorIds, $langId, $curId);
        $productAttributes = '';
        $getAdditionalPreference = getAdditionalPreference(['is_attribute','is_postpay_enable','is_cab_pooling','is_bid_ride_enable','is_particular_driver','is_recurring_booking','is_share_ride_users']);
        if( checkTableExists('product_attributes') ) {


            if (in_array($category->type_id, [13, 10]) && !empty($getAdditionalPreference['is_attribute'])) {

                $productAttributes = Attribute::with('option', 'varcategory.cate.primary')
                    ->select('attributes.*')
                    ->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
                    ->where('attribute_categories.category_id', $category->id)
                    ->where('attributes.status', '!=', 2)
                    ->orderBy('position', 'asc')->get();
            }
        }


        $newProducts = [];
        if($page == 'pickup/delivery' || $page == 'product' && $slug == 'yacht'){
            if(!Auth::user()){
                return redirect()->route('customer.login');
            }else{

                $product = Product::where('category_id', $category->id)->orderBy('per_hour_price','asc')->first();


                $user_addresses = UserAddress::whereNotNull('latitude')->whereNotNull('longitude')->get();
                $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
                $wallet_balance = Auth::user()->balanceFloat * ($clientCurrency->doller_compare ?? 1);
                $riders = Rider::where('user_id',Auth::user()->id)->orderBy('id','DESC')->get();
                $companies  = Company::get();


                // if($preferences->is_hourly_pickup_rental == 1)
                // {
                //     return view('frontend.booking.hourly_rental')->with(['maxPrice'=>$maxPrice,'clientCurrency' => $clientCurrency ,'wallet_balance' => $wallet_balance, 'user_addresses' => $user_addresses, 'navCategories' => $navCategories,'category' => $category,'riders'=>$riders, 'is_cab_pooling' => $getAdditionalPreference['is_cab_pooling'], 'is_bid_ride_enable' => $getAdditionalPreference['is_bid_ride_enable'],'is_postpay_enable' => $getAdditionalPreference['is_postpay_enable'], 'is_particular_driver' => $getAdditionalPreference['is_particular_driver'],'is_recurring_booking' => $getAdditionalPreference['is_recurring_booking'],'is_share_ride_users'=>$getAdditionalPreference['is_share_ride_users'],'companies'=>$companies,'product'=> $product]);

                // }else{

                    return view('frontend.booking.index')->with(['maxPrice'=>$maxPrice,'clientCurrency' => $clientCurrency ,'wallet_balance' => $wallet_balance, 'user_addresses' => $user_addresses, 'navCategories' => $navCategories,'category' => $category,'riders'=>$riders, 'is_cab_pooling' => $getAdditionalPreference['is_cab_pooling'], 'is_bid_ride_enable' => $getAdditionalPreference['is_bid_ride_enable'],'is_postpay_enable' => $getAdditionalPreference['is_postpay_enable'], 'is_particular_driver' => $getAdditionalPreference['is_particular_driver'],'is_recurring_booking' => $getAdditionalPreference['is_recurring_booking'],'is_share_ride_users'=>$getAdditionalPreference['is_share_ride_users'],'companies'=>$companies,'product'=> $product]);
                // }

            }
        }elseif($page == 'on demand service' || $page == 'appointment'){
            $cartDataGet = $this->getCartOnDemand($request);
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
                   // pr($cartDataGet['period']->toArray());
                    $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
                    return view('frontend.ondemand.index')->with(['maxPrice'=>$maxPrice,'clientCurrency' => $clientCurrency,'time_slots' =>  $cartDataGet['time_slots'], 'period' =>  $cartDataGet['period'] ,'cartData' => $cartDataGet['cartData'], 'addresses' => $cartDataGet['addresses'], 'countries' => $cartDataGet['countries'], 'subscription_features' => $cartDataGet['subscription_features'], 'guest_user'=>$cartDataGet['guest_user'],'listData' => $listData, 'category' => $category,'navCategories' => $navCategories]);
                }
                $request->session()->put('skip_addons', '1');
                $new_url = $request->path()."?step=2";
                return redirect($new_url);
            }
            $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
            return view('frontend.ondemand.index')->with(['maxPrice'=>$maxPrice,'clientCurrency' => $clientCurrency,'time_slots' =>  $cartDataGet['time_slots'], 'period' =>  $cartDataGet['period'] ,'cartData' => $cartDataGet['cartData'], 'addresses' => $cartDataGet['addresses'], 'countries' => $cartDataGet['countries'], 'subscription_features' => $cartDataGet['subscription_features'], 'guest_user'=>$cartDataGet['guest_user'],'listData' => $listData, 'category' => $category,'navCategories' => $navCategories]);
        }else{

            if($page == 'laundry' || $service_type == 'rental_service')
                $page = 'product';

            if(view()->exists('frontend/cate-'.$page.'s')){
                return view('frontend/cate-'.$page.'s')->with(['maxPrice'=>$maxPrice,'listData' => $listData, 'category' => $category, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'variantSets' => $variantSets, 'productAttributes'=> $productAttributes]);
            }else{
                abort(404);
            }
        }
    }

    public function getNewProducts($vendorIds, $langId, $curId)
    {
        $np = $this->productList($vendorIds, $langId, $curId, 'is_new');

        foreach($np as $new){
            $new->translation_title = (!empty($new->translation->first())) ? $new->translation->first()->title : $new->sku;
            $new->variant_multiplier = (!empty($new->variant->first())) ? $new->variant->first()->multiplier : 1;
            $new->variant_price = (!empty($new->variant->first())) ? $new->variant->first()->price : 0;
        }
        return $newProducts = ($np->count() > 0) ? array_chunk($np->toArray(), ceil(count($np) / 2)) : $np;
    }


    public function listData($langId, $category_id, $type = '',$vendorIds = array(),$is_max = false){

        $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 12;
        $vendorType = Session::get('vendorType');

        if(strtolower($type) == 'vendor'){
            //$preferences= ClientPreference::first();
            $preferences = !empty(Session::get('preferences')) ? (object)Session::get('preferences'): ClientPreference::first();;
            $vendorData = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->with('products')->select('vendors.id', 'name', 'banner','is_show_vendor_details' ,'address', 'order_pre_time', 'order_min_amount', 'logo', 'slug', 'latitude', 'longitude', 'vendor_templete_id');

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $latitude = Session::get('latitude') ?? $preferences->Default_latitude;
                $longitude = Session::get('longitude') ?? $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                    cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                    sin( radians(' . $latitude . ') ) *
                    sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
                // $vendors= $this->getServiceAreaVendors();
                $vendorData= $vendorData->whereIn('vendors.id', $vendorIds);
            }
            $vendorData = $vendorData->whereHas('getAllCategory' , function ($q)use($category_id){
                $q->where('category_id', $category_id)->where('status', 1);
            });
            if(!empty($vendorType)){
                $vendorData= $vendorData->where($vendorType, 1);
            }
            $vendorData = $vendorData->where('vendors.status', 1)->paginate($pagiNate);

            foreach ($vendorData as $key => $value) {
                $value = $this->getLineOfSightDistanceAndTime($value, $preferences);
                $value->vendorRating = $this->vendorRating($value->products);
                $vendorCategories = VendorCategory::with(['category.translation' => function($q) use($langId){
                    $q->where('category_translations.language_id', $langId);
                }])->where('vendor_id', $value->id)->groupBy('category_id')->where('status', 1)->get();
                $categoriesList = '';
                foreach ($vendorCategories as $key => $category) {
                    if ($category->category) {
                        $categoryName = $category->category->translation->first() ? $category->category->translation->first()->name : '';
                        $categoriesList = $categoriesList . $categoryName;
                        if ($key !=  $vendorCategories->count() - 1) {
                            $categoriesList = $categoriesList . ', ';
                        }
                    }
                }
                $value->categoriesList = $categoriesList;
            }
            return $vendorData;
        }
        elseif(strtolower($type) == 'brand'){
            $brands = Brand::with(['bc.categoryDetail', 'bc.categoryDetail.translation' =>  function ($q) use ($langId) {
                $q->select('category_translations.name', 'category_translations.category_id', 'category_translations.language_id')->where('category_translations.language_id', $langId);
            }, 'translation' => function ($q) use ($langId) {
                $q->select('title', 'brand_id', 'language_id')->where('language_id', $langId);
            }])
            ->whereHas('bc.categoryDetail', function ($q){
                $q->where('categories.status', 1);
            })
            ->wherehas('bc', function($q) use($category_id){
                $q->where('category_id', $category_id);
            })
            ->select('id', 'title', 'image', 'image_banner')->where('status', 1)->orderBy('position', 'asc')->paginate($pagiNate);
            foreach ($brands as $brand) {
                $brand->redirect_url = route('brandDetail', $brand->id);
            }
            return $brands;
        }
        elseif(strtolower($type) == 'celebrity'){
            $celebs = Celebrity::orderBy('name', 'asc')->paginate($pagiNate);
            return $celebs;
        }else{
            $user = Auth::user();
            if ($user) {
                $column = 'user_id';
                $value = $user->id;
            } else {
                $column = 'unique_identifier';
                $value = session()->get('_token');
            }

            $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();

            $vendors =  $vendorIds;
            if(count($vendorIds)==0){
                if(Session::has('vendors')){
                    $vendors = Session::get('vendors');
                }
            }
            $products = Product::with(['vendor','media.image', 'category', 'ProductAttribute',
                        'translation' => function($q) use($langId){
                          $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                          $q->groupBy('language_id','product_id');
                        },
                        'variant' => function($q) use($langId,$column,$value){
                            $q->select('sku', 'product_id', 'quantity', 'price', 'barcode','id', 'compare_at_price');
                            $q->groupBy('product_id');
                        },'variant.checkIfInCart'])
                        ->select('products.id', 'products.sku', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating', 'products.inquiry_only','products.minimum_order_count','products.batch_count','products.updated_at')
                        ->where('products.is_live', 1)->whereHas('vendor',function($q){
                            $q->where('status',1);
                        })
                        ->where('products.category_id', $category_id);
            if (!in_array(strtolower($type), ['rental service', 'p2p'])) {
                $products = $products->whereIn('products.vendor_id', $vendors);
            }
            $maxPrice = 0;
            $products = $products->paginate($pagiNate);
            if(!empty($products)){
                foreach ($products as $key => $value) {
                    $value->translation_title = (!empty($value->translation->first())) ? $value->translation->first()->title : $value->sku;
                    $value->translation_description = (!empty($value->translation->first())) ? html_entity_decode(strip_tags($value->translation->first()->body_html)) : $value->sku;
                    $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    $value->variant_price = (!empty($value->variant->first())) ? $value->variant->first()->price : 0;
                    $value->variant_compare_at_price = (!empty($value->variant->first())) ? $value->variant->first()->compare_at_price : 0;
                    $value->image_url = $value->media->first() ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();

//                     if($value->variant_price > $maxPrice){
//                         $maxPrice = $value->variant_price;
//                     }
                    // foreach ($value->variant as $k => $v) {
                    //     $value->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    // }
                }
            }
            $listData = $products;
            return $listData;
        }
    }

    /**
     * Display category->vendor->products list
     *
     * @return \Illuminate\Http\Response
     */
    public function categoryVendorProducts(Request $request, $domain = '', $slug1 = 0, $slug2 = 0)
    {

        // slug1 => category slug
        // slug2 => vendor slug
        $maxPrice = 0;
        $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 12;
        $preferences = Session::get('preferences');
        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $navCategories = $this->categoryNav($langId);
        $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
        $category = Category::with(['tags', 'brands.translation' => function($q) use($langId){
            $q->where('brand_translations.language_id', $langId);
        },
        'type'  => function($q){
            $q->select('id', 'title as redirect_to');
        },
        'childs.translation'  => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'translation' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'allParentsAccount'])
        ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products', 'parent_id')
        ->where('slug', $slug1)->firstOrFail();

        $category->translation_name = ($category->translation->first()) ? $category->translation->first()->name : $category->slug;
        foreach($category->childs as $key => $child){
            $child->translation_name = ($child->translation->first()) ? $child->translation->first()->name : $child->slug;
        }
        $vendor = Vendor::vendorOnline()->select('id', 'name')->where('slug', $slug2)->where('status', 1)->firstOrFail();
        if($category && $request->ajax() )
        {
            $vendor_id = isset($vendor) ?  $vendor->id : '';
            $request->merge(['vendor_id'=>$vendor_id]);
            $returnHTML = $this->categoryFilters($request,'',$category->id);
            return response()->json(array('success' => true, 'html'=>$returnHTML));
        }
        $variantSets = ProductVariantSet::with(['options' => function($zx) use($langId){
            $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id');
            $zx->select('variant_options.*', 'vt.title');
            $zx->where('vt.language_id', $langId);
        }
        ])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
        ->join('variant_translations as vt','vt.variant_id','vr.id')
        ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
        ->where('vt.language_id', $langId)
        ->where('vr.status', 1)
        ->whereIn('product_variant_sets.product_id', function($qry) use($category){
            $qry->select('product_id')->from('product_categories')
                ->where('category_id', $category->id);
        })
        ->groupBy('product_variant_sets.variant_type_id')->get();
        $redirect_to = $category->type->redirect_to;
        // $newProducts =  $this->getNewProducts([$vendor->id], $langId, $curId,);
        $newProducts = [];

        $products = Product::with(['media.image',
            'translation' => function($q) use($langId){
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
            },
            'variant' => function($q) use($langId){
                $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                $q->groupBy('product_id');
            },
        ])
        ->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only','minimum_order_count','batch_count')
        ->where('is_live', 1)->where('category_id', $category->id)->where('vendor_id', $vendor->id)->paginate($pagiNate);
        if(!empty($products)){
            foreach ($products as $key => $value) {
                $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                $value->translation_description = ($value->translation->isNotEmpty()) ? html_entity_decode(strip_tags($value->translation->first()->body_html)) : '';
                $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                $value->image_url = $value->media->first() ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
            }
        }
        $listData = $products;
        $maxPrice =  DB::select("SELECT MAX(product_variants.price) as max_price FROM product_variants INNER JOIN products ON products.id = product_variants.product_id WHERE product_variants.status = 1 AND products.is_live = 1 AND products.category_id = ?", [$category->id])[0]->max_price;
        return view('frontend/cate-products')->with(['listData' => $listData, 'category' => $category, 'navCategories' => $navCategories, 'newProducts' => $newProducts, 'variantSets' => $variantSets,"vendor_id"=>$vendor->id,'maxPrice'=>$maxPrice]);
    }

    /**
     * Product filters on category Page
     * @return \Illuminate\Http\Response
     */
    public function categoryFilters(Request $request, $domain = '', $cid = 0)
    {
        // dd($request->all());
        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $limit = $request->has('limit') ? $request->limit : 12;
        $page = $request->has('page') ? $request->page : 1;
        $vendor_id = $request->has('vendor_id') ? $request->vendor_id : '';
        $setArray = $optionArray = array();
        $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();

        if($request->has('variants') && !empty($request->variants)){
            $setArray = array_unique($request->variants);
        }

        $startRange = 0; $endRange = 20000;
        if($request->has('range') && !empty($request->range)){
            $range = explode(';', $request->range);
            $clientCurrency->doller_compare;
            $startRange = $range[0] * $clientCurrency->doller_compare;
            $endRange = $range[1] * $clientCurrency->doller_compare;
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
                $vResult = ProductVariantSet::join('product_categories as pc', 'product_variant_sets.product_id', 'pc.product_id')
                                            ->select('product_variant_sets.product_variant_id', 'product_variant_sets.product_id')
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

        /*if($request->has('options') && !empty($request->options)){
            $optionArray = $request->options;
        }
        $variantSetData = ProductVariantSet::join('product_categories as pc', 'product_variant_sets.product_id', 'pc.product_id' )->select('product_variant_sets.*');
        if(!empty($setArray)){
            $variantSetData = $variantSetData->whereIn('product_variant_sets.variant_type_id', $setArray);
        }
        if(!empty($optionArray)){
            $variantSetData = $variantSetData->whereIn('product_variant_sets.variant_option_id', $optionArray);
        }
        echo $variantSetData = $variantSetData->groupBy('product_variant_sets.product_id')->toSql();die;

        dd($variantSetData->toArray());

        foreach ($variantSetData as $key => $value) {
            $variantIds[] = $value->product_variant_id;
            $productIds[] = $value->product_id;
        }*/
       // print_r($variantIds);die;
        $category = Category::where('id',$cid)->first();
        $order_type = $request->has('order_type') ? $request->order_type : '';
        $products = Product::with(['media.image', 'ProductAttribute',
                        'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId, $variantIds){
                        $q->select('sku', 'product_id', 'quantity', 'price', 'barcode','id','compare_at_price');
                            if(!empty($variantIds)){
                                $q->whereIn('id', $variantIds);
                            }
                            $q->groupBy('product_id');
                        },
                    ])
                    ->select('products.id', 'products.sku', 'products.brand_id', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock','products.inquiry_only', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count', 'products.is_featured','products.batch_count', 'products.updated_at')
                            ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                            ->join('product_translations', 'product_translations.product_id', '=', 'products.id') // Or whatever the join logic is
                    // ->where('vendor_id', $vid)
                    ->where('products.category_id', $cid)
                    ->where('products.is_live', 1)
                    ->distinct('products.id')
                    ->whereHas('vendor',function($q){
                        $q->where('status',1);
                    })
                    ->whereIn('products.id', function ($qr) use ($startRange, $endRange) {
                        $qr->select('product_id')->from('product_variants')
                            ->where('price', '>=', $startRange)
                            ->where('price', '<=', $endRange);
                    });


            $getAdditionalPreference = getAdditionalPreference(['is_attribute']);

            $calc_value = 30; //kilometer
            if(@$request->latitude && @$request->longitude){
                $products->whereHas('ProductAttribute', function($q) use( $calc_value, $request){
                    $latitude = $request->latitude;
                    $longitude = $request->longitude;
                    // dd($longitude);
                    $q->select('*', DB::raw(' ( 6371  * acos( cos( radians(' . $latitude . ') ) *
                    cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                    sin( radians(' . $latitude . ') ) *
                    sin( radians( latitude ) ) ) )  AS distance'))
                    ->having("distance", "<", $calc_value);
                });
            }
            // Dynamic search fields
            if($getAdditionalPreference['is_attribute']) {
                if( !empty($request->dynamic_options) ) {
                    foreach($request->dynamic_options as $key => $val) {
                        foreach($val as $inn_key => $inn_val) {
                            if( !empty($inn_key) && !empty($inn_val) ) {
                                $products->whereHas('ProductAttribute', function($q) use($inn_key, $inn_val, $calc_value, $request){

                                    $q->where('key_name', $inn_key);
                                    if( is_array($inn_val) ) {
                                        $q->whereIn('key_value', $inn_val);
                                    }
                                    else {
                                        $q->where('key_value', $inn_val);
                                    }
                                });
                            }
                        }
                    }
                }

            }

            if( $vendor_id ){
                $products = $products->where('vendor_id', $vendor_id);
            }
            if(!empty($productIds) || !empty($multiArray)){
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
            }elseif (!empty($order_type) && $order_type == 'featured') {
                $products = $products->where('products.is_featured', 1);
            }else{
                //
            }

            // $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 12;
            $products = $products->paginate($limit, $page);

        if(!empty($products)){
            foreach ($products as $key => $value) {
                $value->translation_title = (!empty($value->translation->first())) ? $value->translation->first()->title : $value->sku;
                $value->translation_description = (!empty($value->translation->first())) ? strip_tags($value->translation->first()->body_html) : $value->sku;
                $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                $value->variant_price = (!empty($value->variant->first())) ? $value->variant->first()->price : 0;
                $value->variant_compare_at_price = (!empty($value->variant->first())) ? $value->variant->first()->compare_at_price : 0;

                $value->image_url = $value->media->first() ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                // foreach ($value->variant as $k => $v) {
                //     $value->variant[$k]->multiplier = $clientCurrency->doller_compare;
                // }
            }
        }
        $listData = $products;

        $returnHTML = view('frontend.ajax.productList')->with(['data'=>$request->all(),'listData' => $listData,'category'=>$category])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    private function array_combinations($arrays)
    {
        $result = array();
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i ++)
        {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j ++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn -1); $j >= 0; $j --)
            {
                if (next($arrays[$j]))
                    break;
                elseif (isset ($arrays[$j]))
                    reset($arrays[$j]);
            }
        }
        return $result;
    }

    // ***********   getTimeSlotsForOndemand ************** /////////////////
    public function getTimeSlotsForOndemand(Request $request){

        // get slot from dispatcher by harbans :)
        if($request->has('product_category_type') &&  $request->has('product_id')){
            $product = $this->productDetail($request->product_id);

            $cateTypeId = $product ? ($product->productcategory ? $product->productcategory->type_id : '') : '';
            $is_slot_from_dispatch = $product ? $product->is_slot_from_dispatch  : '';
            $show_dispatcher_agent = $product ? $product->is_show_dispatcher_agent  : '';

            $last_mile_check       = $product ? $product->Requires_last_mile  : '';
            $vendorStartDate       = $vendorStartTime  = '';
            $slotsDate = findSlot('',$product->vendor_id,'','webFormet');


            if($slotsDate){
                $vendorStartDate = (($slotsDate)?$slotsDate['date']:'');
                $vendorStartTime = (($slotsDate)?$slotsDate['time']:'');
            }

            // ch
            if(($cateTypeId ==  12) && ($is_slot_from_dispatch == 1) && ( $last_mile_check ==1) ){
                $Dispatch =  $this->getDispatchAppointmentDomain();
                $dispatchAgents = [];
                $cart_product_id = $request->cart_product_id??0;
                if($Dispatch){
                   $unique = ModelsClient::first()->code;
                   $email =  $unique.$product->vendor->id."_royodispatch@dispatch.com";
                   $vendor_latitude =  $product->vendor ? $product->vendor->latitude : 30.71728880;
                   $vendor_longitude =  $product->vendor ? $product->vendor->longitude : 76.80350870;
                    $location[] = array(
                        'latitude' =>   $vendor_longitude,
                        'longitude' =>  $vendor_longitude
                    );
                    $dispatchData=[
                        'service_key'      => $Dispatch->appointment_service_key,
                        'service_key_code' => $Dispatch->appointment_service_key_code,
                        'service_key_url'  => $Dispatch->appointment_service_key_url,
                        'service_type'     => 'appointment',
                        'tags'             => $product->tags,
                        'latitude'         => $vendor_latitude,
                        'longitude'        => $vendor_longitude,
                        'service_time'     => $product->minimum_duration_min,
                        'schedule_date'    => $request->cur_date,
                        'slot_start_time'  => $vendorStartTime,
                        'team_email'       => $email
                    ];
                    $dispatchAgents = $this->getSlotFeeDispatcher($dispatchData);
                }
                if ($request->ajax()) {
                    return \Response::json(\View::make('frontend.ondemand.dispatcher_agent_slots', array('dispatch_agents' => $dispatchAgents,'cart_product_id'=> $cart_product_id,'show_dispatcher_agent'=>$show_dispatcher_agent))->render());
                }
            }
        }
        $user = Auth::user();
        $timezone = $user->timezone ?? 'Asia/Kolkata';

        $dates = new DateTime("now", new DateTimeZone($timezone) );
        $today = $dates->format('Y-m-d');
        if($today < $request->cur_date){
            $curr_time = date('Y-m-d 00:00');
        }else{
            $daten = new DateTime("now", new DateTimeZone($timezone) );
            $curr_time = $daten->format('H:i');
        }

        if(!empty($request->cur_date)){
            $date = $request->cur_date;
        }else{
            $date = $today;
        }

        $slots = showSlot($date,$request->product_vendor_id,'delivery');


        $vendor = Vendor::where('id', $product->vendor_id)->select('show_slot')->first();


        $time_slots = [];
        if(!empty($slots)){
            $time_slots = $slots;
            // $i = 0;
            // foreach($slots as $slot){
            //     $newSlot = explode('-', $slot['value']);
            //     $time_slots[$i++] = trim($newSlot[0]);
            // }
        }else{
            $end_time = date('Y-m-d 23:59');
            if($vendor->show_slot == 1)
            {
                $timing   = $this->SplitTime($curr_time, $end_time, "60");
                foreach ($timing as $k=> $slt) {
                    if($k+1 < count($timing)){
                        $viewSlot['name'] = date('h:i:A', strtotime($slt)).' - '.date('h:i:A', strtotime($timing[$k+1]));
                        $viewSlot['value'] = $slt.' - '.$timing[$k+1];
                        $time_slots[] =  $viewSlot;
                    }
                }

            }

          //$time_slots  // this is for static slots
        }

        //pr($time_slots);
        $cart_product_id = $request->cart_product_id??0;


        if ($request->ajax()) {
           return \Response::json(\View::make('frontend.ondemand.time-slots-for-date', array('time_slots' => $time_slots,'cart_product_id'=> $cart_product_id))->render());
        }
    }

    # get product faq
    public function getcategoryKycDocument(Request $request,$domain = ''){
        $user = Auth::user();
        if ($user) {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
        }
        $is_alrady_submit = [];
        if ($cart) {
            $is_alrady_submit = CaregoryKycDoc::where('cart_id',$cart->id)->pluck('category_kyc_document_id');
            $is_alrady_submit = $is_alrady_submit->isNotEmpty() ? $is_alrady_submit->toArray() : [];
        }

        $category_ids = explode(",",$request->category_ids);

        $langId = Session::get('customerLanguage');

        if(empty($langId))
        $langId = ClientLanguage::orderBy('is_primary','desc')->value('language_id');
        $product_faqs=[];

        $category_kyc_documents = CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_ids){
            $q->whereIn('category_id',$category_ids);
           })->with(['translations' => function ($qs) use($langId){
                $qs->where('language_id',$langId);
            },'primary'])
            ->whereNotIn('id',$is_alrady_submit)->get();
        $category_id = rand(9,10);
        if(isset($category_kyc_documents)){
            if ($request->ajax()) {
             return \Response::json(\View::make('frontend.modals.category_kyc_form', array('category_kyc_documents'=>  $category_kyc_documents,'category_id'=> $category_id,'category_ids'=>$request->category_ids))->render());
            }
        }
        return \Response::json(\View::make('frontend.modals.category_kyc_form', array('category_kyc_documents'=>  $category_kyc_documents,'category_id'=> $category_id,'category_ids'=>$request->category_ids))->render());

        return $this->errorResponse('Invalid product form ', 404);
    }


    public function getRentalView(Request $request)
    {


        if($request->has('category_id'))
        {
            $product_ids = Product::where('category_id', $request->category_id)->pluck('id');
            $productVariant = ProductVariant::whereIn('product_id', $product_ids)->orderBy('price','asc')->first();



            return response()->json(['product' => $productVariant]);
        }
        else{

            $langId = Session::get('customerLanguage');

            $navCategories = $this->categoryNav($langId);

            $view = view('frontend.booking.hourlyRental',['navCategories' => $navCategories])->render();

            return response()->json(['view' => $view]);
        }

    }
}
