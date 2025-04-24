<?php

namespace App\Http\Controllers\Front;

use App\Models\Cart;
use App\Models\User;
use DB;
use App;
use Auth;
use Config;
use Session,Log;
use Carbon\CarbonPeriod;
use DateTime;
use DateInterval;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Twilio\Rest\Client as TwilioClient;
use App\Models\{Client, Category, Product,Type, SmsTemplate, ClientPreference,EmailTemplate, ClientCurrency, UserDevice, UserLoyaltyPoint, Wallet, UserSavedPaymentMethods, SubscriptionInvoicesUser,Country,UserAddress,CartProduct, Vendor, VendorCategory, ClientLanguage, LoyaltyCard, Nomenclature, NomenclatureTranslation, Order};
use App\Models\PermissionsOld;
use App\Models\UserPermissions;
use Illuminate\Support\Facades\Cache;

class FrontController extends Controller
{
    use \App\Http\Traits\smsManager;
    use \App\Http\Traits\DispatcherSlot;

    private $field_status = 2;
    protected function sendSms($provider="", $sms_key="", $sms_secret="", $sms_from="", $to, $body){
        try{
            $client_preference =  getClientPreferenceDetail();

            if($client_preference->sms_provider == 1)
            {
                if(!empty($client_preference->sms_secret) && !empty($client_preference->sms_from)){
                    $client = new TwilioClient($client_preference->sms_key, $client_preference->sms_secret);
                    $send =  $client->messages->create($to, ['from' => $client_preference->sms_from, 'body' => $body]);
                }else{
                    return 2;
                }

            }elseif($client_preference->sms_provider == 2) //for mtalkz gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->mTalkz_sms($to,$body,$crendentials);
            }elseif($client_preference->sms_provider == 3) //for mazinhost gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->mazinhost_sms($to,$body,$crendentials);
            }elseif($client_preference->sms_provider == 4) //for unifonic gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->unifonic($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 5) //for arkesel_sms gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->arkesel_sms($to,$body,$crendentials);
                if( isset($send->code) && $send->code != 'ok'){
                    return '2';
                }
            }
            elseif($client_preference->sms_provider == 6) //for AfricasTalking gateway
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->africasTalking_sms($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 7) //for Vonage gateway
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->vonage_sms($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 8) //for SMS partner gateway France
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->sms_partner_gateway($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 9) //for ethiopia
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->ethiopia($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 10) //sms country
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->sms_country($to,$body,$crendentials);
            }
            else{
                if(!empty($sms_secret) && !empty($sms_from)){
                    $client = new TwilioClient($sms_key, $sms_secret);
                    $send =  $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
                    //// Log::info('SMS twilio respons');
                    //// Log::info($send);
                }else{
                    return 2;
                }
            }
            //return $send;
        }
        catch(\Exception $e){
            Log::info('SMS logs');
            Log::info($e->getMessage());
            return '2';
        }
        return '1';
	}
    protected function sendSmsNew($provider="", $sms_key="", $sms_secret="", $sms_from="", $to, $body){
        try{
            $smsbody = $body['body']??'';
            $body = $body['body']??'';
            $template_id = $body['template_id']??'';
            $client_preference =  getClientPreferenceDetail();
            if($client_preference->sms_provider == 1)
            {
                if(!empty($client_preference->sms_secret) && !empty($client_preference->sms_from)){
                    $client = new TwilioClient($client_preference->sms_key, $client_preference->sms_secret);
                    $send =  $client->messages->create($to, ['from' => $client_preference->sms_from, 'body' => $body]);
                    //// Log::info('SMS twilio respons');
                    //// Log::info($send);
                }else{
                    return 2;
                }

            }elseif($client_preference->sms_provider == 2) //for mtalkz gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->mTalkz_sms($to,$body,$crendentials,$template_id);
            }elseif($client_preference->sms_provider == 3) //for mazinhost gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->mazinhost_sms($to,$body,$crendentials);
            }elseif($client_preference->sms_provider == 4) //for unifonic gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->unifonic($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 5) //for arkesel_sms gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->arkesel_sms($to,$body,$crendentials);
                if( isset($send->code) && $send->code != 'ok'){
                    return '2';
                }
            }
            elseif($client_preference->sms_provider == 6) //for AfricasTalking gateway
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->africasTalking_sms($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 7) //for Vonage gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->vonage_sms($to,$smsbody,$crendentials);
            }
            elseif($client_preference->sms_provider == 8) //for SMS partner gateway France
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->sms_partner_gateway($to,$smsbody,$crendentials);
            }
            elseif($client_preference->sms_provider == 9) //for  Ethiopia
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->ethiopia($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 10) //sms country
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->sms_country($to,$body,$crendentials);
            }
            else{
                if(!empty($sms_secret) && !empty($sms_from)){
                    $client = new TwilioClient($sms_key, $sms_secret);
                    $send =  $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
                    //// Log::info('SMS twilio respons');
                    //// Log::info($send);
                }else{
                    return 2;
                }
            }
            //return $send;
        }
        catch(\Exception $e){
            return '2';
        }
        return '1';
	}


    public function testsms(Request $request)
    {
        $prefer = ClientPreference::select('sms_credentials',
                        'sms_provider', 'sms_key', 'sms_secret', 'sms_from' )->first();
        $to = $request->to ? '+91'.$request->to :'+917508983302';
        $provider = $prefer->sms_provider;
        $body = "Dear ".ucwords('Rajat').", Please enter OTP (12345) to verify your account.";
       // $send = $this->sendSms($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
        // $to = '+917508983302';
        // $body = "this is test sms from codebrew";
        // $crendentials = [
        //     'api_key' =>'Om15akt3STZwNXNzMEFjRzY=',
        //     'sender_id' => 'Arkesel',
        // ];
        $send = $this->sendSms($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
        pr($send);
    }

    public function categoryNav($lang_id,$only_id = false)
    {
        return $this->categoryNavOld($lang_id,$only_id);
        
        $preferences = session()->get('preferences');
        $vendorType = session()->get('vendorType');
        $categoryTypes = getServiceTypesCategory($vendorType) ;
        $primary = ClientLanguage::orderBy('is_primary', 'desc')->first();
        $status = $this->field_status;
        $include_categories = [4, 8]; // type 4 for brands
        $celebrity_check = 0;

        // Check if celebrity_check is set in preferences
        if ($preferences && isset($preferences->celebrity_check) && $preferences->celebrity_check == 1) {
            $celebrity_check = 1;
            $include_categories[] = 5; // type 5 for celebrity
        }


        // Check if request_from is set and get vendors accordingly
        if (isset($_REQUEST['request_from']) && $_REQUEST['request_from'] == 1) {

            $vendors = $this->getServiceAreaVendors();
        } else {

            $vendors = (session()->has('vendors')) ? session()->get('vendors') : $this->getServiceAreaVendors();
        }

    // Define a unique cache key based on your criteria
    $cacheKey = 'categories_query_' . implode('_', $categoryTypes) . '_' . $lang_id . '_celeb_' . $celebrity_check;
    // dd($cacheKey);
    // Define the cache duration in minutes (adjust as needed)
    $cacheDuration = 60; // Cache for 60 minutes

    $categories = Cache::remember($cacheKey, $cacheDuration, function () use ($categoryTypes, $status, $lang_id, $primary, $celebrity_check,$only_id,$vendors,$include_categories) {
         $cat = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
            ->select('categories.id', 'categories.icon', 'categories.icon_two', 'categories.slug', 'categories.parent_id', 'cts.name', 'categories.type_id')
            ->when($vendors, function ($query) use($vendors , $include_categories) {
            $query->leftJoin('vendor_categories as vct', 'categories.id', 'vct.category_id')
                    ->where(function ($q1) use ($vendors , $include_categories) {
                        $q1->whereIn('vct.vendor_id', $vendors)
                            ->where('vct.status', 1)
                            ->orWhere(function ($q2) use($include_categories) {
                                $q2->whereIn('categories.type_id', $include_categories);
                            });
                    });
            })
            ->whereIn('categories.type_id', $categoryTypes)
            ->where('categories.id', '>', 1) // Exclude categories with id <= 1
            ->whereNotNull('categories.type_id')
            ->where('categories.is_visible', 1)
            ->where('categories.is_core', 1)
            ->where('categories.status', '!=', $status)
            ->where('cts.language_id', $lang_id)
            ->where(function ($qrt) use ($lang_id, $primary) {
                $qrt->where('cts.language_id', $lang_id)->orWhere('cts.language_id', $primary->language_id);
            })
            ->whereNull('categories.vendor_id')
            ->when($celebrity_check == 0, function ($query) {
                // Conditionally add the where clause if $celebrity_check is 0
                $query->where('categories.type_id', '!=', 5);
            })
            ->orderBy('categories.parent_id', 'asc')
            ->groupBy('categories.id')
            ->distinct('categories.slug');

            if ($only_id) {
               return $cat->pluck('id')->toArray();
            } else {
                $cat = $cat->get();
                return $cat = $this->buildTree($cat);
            }
    });



        return $categories;
    }


    public function categoryNavOld($lang_id,$only_id = false)
    {
        $preferences = Session::get('preferences');
        // get selected vendor type
        $vendorType  = Session::get('vendorType');
        // set category layout by on behalf of vendor type
        $categoryTypes = getServiceTypesCategory($vendorType);
       // pr($categoryTypes);
        $primary     = ClientLanguage::orderBy('is_primary','desc')->first();
       // DB::enableQueryLog();
        $categories  = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
                                ->select('categories.id', 'categories.icon', 'categories.icon_two' , 'categories.slug', 'categories.parent_id','cts.name','categories.type_id')
                                ->whereIn('categories.type_id',$categoryTypes )
                                ->orderBy('position')->distinct('categories.slug');
        $status = $this->field_status;
        $include_categories = [4,8]; // type 4 for brands
        $celebrity_check = 0;
        if ($preferences) {
                if((isset($preferences->celebrity_check)) && ($preferences->celebrity_check == 1)){
                    $celebrity_check = 1;
                    $include_categories[] = 5; // type 5 for celebrity
                }
                if(isset($_REQUEST['request_from']) && ($_REQUEST['request_from'] == 1) ){
                    $vendors = $this->getServiceAreaVendors();
                } else {
                    $vendors = (Session::has('vendors')) ? Session::get('vendors') : $this->getServiceAreaVendors();
                }
                $categories = $categories->leftJoin('vendor_categories as vct', 'categories.id', 'vct.category_id')
                    ->where(function ($q1) use ($vendors , $include_categories) {
                        $q1->whereIn('vct.vendor_id', $vendors)
                            ->where('vct.status', 1)
                            ->orWhere(function ($q2) use($include_categories) {
                                $q2->whereIn('categories.type_id', $include_categories);
                            });
                    });
        }
        $categories = $categories->leftjoin('types', 'types.id', 'categories.type_id')
                                ->where('categories.id', '>', '1')
                                ->whereNotNull('categories.type_id');
         if($celebrity_check == 0){
            $categories = $categories->where('categories.type_id', '!=', 5);
        }

        $categories = $categories->where('categories.id', '>', '1')
                               // ->whereNotNull('categories.type_id')
                                //->whereNotIn('categories.type_id', [7])
                                ->where('categories.is_visible', 1)
                                ->where('categories.is_core', 1)
                                ->where('categories.status', '!=', $status)
                                ->where('cts.language_id', $lang_id)
                                ->where(function ($qrt) use($lang_id,$primary){
                                    $qrt->where('cts.language_id', $lang_id)->orWhere('cts.language_id',$primary->language_id);
                                })->whereNull('categories.vendor_id')
                              //  ->orderBy('categories.position', 'asc')
                                ->orderBy('categories.parent_id', 'asc')->groupBy('categories.id');
        if($only_id){
           return $categories = $categories->select('categories.id')->pluck('id')->toArray();
        }else{
            $categories = $categories->get();
        }
        if ($categories) {
            $categories = $this->buildTree($categories);
        }

        return $categories;
    }


    public function fixedFee($lang_id){
        if(Nomenclature::where('label','Fixed Fee')->exists()){
            $nomenclatures_translation_id=Nomenclature::where('label','Fixed Fee')->first()->id;
            return NomenclatureTranslation::where(['nomenclature_id'=>$nomenclatures_translation_id,'language_id'=>$lang_id])->exists() ? NomenclatureTranslation::where(['nomenclature_id'=>$nomenclatures_translation_id,'language_id'=>$lang_id])->first()->name : "Fixed Fee";
        }else{
            return "Fixed Fee";
        }
    }

    public function buildTree($elements, $parentId = 1)
    {
        $branch = array();
        foreach ($elements as $element) {

            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function getChildCategoriesForVendor($category_id, $langId=1, $vid=0)
    {
        $category_list = array();

        $categories = Category::with(['translation' => function($q) use($langId){
                $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                ->where('category_translations.language_id', $langId);
            }, 'childs'])
            ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products', 'parent_id')
            ->where('parent_id', $category_id)->where('status', 1)->get();
        if($categories){
            foreach($categories as $cate){
                if($cate->childs){
                    foreach($cate->childs as $child){
                        $vendorCategory = VendorCategory::with(['category.translation' => function($q) use($langId){
                            $q->where('category_translations.language_id', $langId);
                        }])->where('vendor_id', $vid)->where('category_id', $child->id)->where('status', 1)->first();
                        if ($vendorCategory) {
                            $category_list[] = $vendorCategory;
                        }
                        $this->getChildCategoriesForVendor($child->id, $langId, $vid);
                    }
                }


                $vendorCategory = VendorCategory::with(['category.translation' => function ($q) use ($langId) {
                    $q->where('category_translations.language_id', $langId);
                }])->where('vendor_id', $vid)->where('category_id', $cate->id)->where('status', 1)->first();
                if ($vendorCategory) {
                    $category_list[] = $vendorCategory;
                }
                $this->getChildCategoriesForVendor($cate->id, $langId, $vid);
            }



            }

        return $category_list;
    }

    public function getServiceAreaVendors(){
        $client_preferences = ClientPreference::where('id', '>', 0)->first();
        $latitude = Session::get('latitude');
        $longitude = Session::get('longitude');
        $vendorType = Session::get('vendorType');
        if($vendorType=="car_rental"){
            $vendorType = "rental";
        }
        $preferences = Session::has('preferences') ? Session::get('preferences') : $client_preferences;
        $serviceAreaVendors = Vendor::vendorOnline()->select('id', 'show_slot');
        $vendors = [];
        if($vendorType){
            $serviceAreaVendors = $serviceAreaVendors->where($vendorType, 1);
        }
        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){

            if (!empty($latitude) && !empty($longitude)) {
                $serviceAreaVendors = $serviceAreaVendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                    $query->select('vendor_id')
                    ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$latitude." ".$longitude.")'))");
                });


                if (isset($preferences->slots_with_service_area) && ($preferences->slots_with_service_area == 1)) {
                    $slot_vendors = clone $serviceAreaVendors;
                    $data = $slot_vendors->get();
                    foreach ($data as $key => $value) {
                        $serviceAreaVendors = $serviceAreaVendors->when(($value->show_slot == 0), function($query) use ($latitude, $longitude) {
                            return $query->where(function($query1) use ($latitude, $longitude) {
                                $query1->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                })
                                ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                });
                            });
                        });
                    }
                }
            }
        }
        $serviceAreaVendors = $serviceAreaVendors->where('status', 1)->get();
        if($serviceAreaVendors->isNotEmpty()){
            foreach($serviceAreaVendors as $value){
                $vendors[] = $value->id;
            }
        }

        Session::put('vendors', $vendors);

        return $vendors;
    }

    public function getServiceAreaVendorsWithoutHyperlocal($latitude, $longitude){
        $vendorType = Session::get('vendorType');
        $preferences = Session::has('preferences') ? Session::get('preferences') : ClientPreference::where('id', '>', 0)->first();;
        $serviceAreaVendors = Vendor::vendorOnline()->select('id', 'show_slot');
        $vendors = [];
        if($vendorType){
            $serviceAreaVendors = $serviceAreaVendors->where($vendorType, 1);
        }

        if (!empty($latitude) && !empty($longitude)) {
            $serviceAreaVendors = $serviceAreaVendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                $query->select('vendor_id')
                ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$latitude." ".$longitude.")'))");
            });
        }
        $serviceAreaVendors = $serviceAreaVendors->where('status', 1)->get();


        if($serviceAreaVendors->isNotEmpty()){
            foreach($serviceAreaVendors as $value){
                $vendors[] = $value->id;
            }
        }
        return $vendors;
    }

    public function loadDefaultImage(){
        $proxy_url = \Config::get('app.IMG_URL1');
        $image_path = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url('default/default_image.png');
        $image_fit = \Config::get('app.FIT_URl');
        $default_url = $image_fit .'300/300'. $image_path.'@webp';

        if ($this->imageExists($default_url)) {
            return $default_url;
        } else {
            return asset('assets/images/bg-material.png');

        }
    }

    private function imageExists($url) {
        // You can use either File or Storage to check if the image exists.
        // Here, I'm using the File class.
        return \File::exists(public_path($url));
    }

    public function productList($vendorIds, $langId, $currency = 'USD', $where = '')
    {
        $type = Session::get('vendorType');
        $clientCurrency = ClientCurrency::where('currency_id', $currency)->first();
        $multiplier = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
        $products = Product::byProductCategoryServiceType($type)->with([
            'category.categoryDetail.translation' => function ($q) use ($langId) {
                $q->where('category_translations.language_id', $langId);
            },
            'vendor',
            'media' => function ($q) {
                $q->groupBy('product_id');
            }, 'media.image',
            'translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
            },
            'variant' => function ($q) use ($langId) {
                $q->select('sku', 'product_id', 'quantity', 'price', 'barcode')->orderBy('price');
                $q->groupBy('product_id');
            },
        ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only','minimum_order_count','batch_count','minimum_duration_min');

        if ($where !== '') {
            $products = $products->where($where, 1);
        }
        // if(is_array($vendorIds) && count($vendorIds) > 0){
            if (is_array($vendorIds)) {
                $products = $products->whereIn('vendor_id', $vendorIds);
            }
            $products = $products->where('is_live', 1)->whereNotNull('category_id')->take(6)->get();
        // pr($products->toArray());die;
        if (!empty($products)) {
            foreach ($products as $key => $value) {
                foreach ($value->variant as $k => $v) {
                    $value->variant[$k]->multiplier = Session::get('currencyMultiplier');
                }

                $value->vendor_name = $value->vendor ? $value->vendor->name : '';
                $value->translation_title = (!empty($value->translation->first())) ? $value->translation->first()->title : $value->sku;
                $value->translation_description = (!empty($value->translation->first())) ? $value->translation->first()->body_html : $value->sku;
                $value->variant_multiplier = $multiplier;
                $value->variant_price = (!empty($value->variant->first())) ? decimal_format(($value->variant->first()->price * $multiplier),',') : 0;
                $value->averageRating = number_format($value->averageRating, 1, '.', '');
                $value->image_url = ($value->media->first() && !is_null($value->media->first()->image))  ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                $value->category_name = (@$value->category->categoryDetail->translation->first()) ? $value->category->categoryDetail->translation->first()->name :  $value->category->slug;
               // $value->category_type_id = ($value->category->categoryDetail->first()) ? $value->category->categoryDetail->first()->type_id : '';
            }
        }
        return $products;
    }

    public function metaProduct($langId, $multiplier = 1, $for = 'related', $productArray = []){
        if(empty($productArray)){
            return $productArray;
        }
        $productIds = array();
        foreach ($productArray as $key => $value) {
            if($for == 'related'){
                $productIds[] = $value->related_product_id;
            }
            if($for == 'upSell'){
                $productIds[] = $value->upsell_product_id;
            }
            if($for == 'crossSell'){
                $productIds[] = $value->cross_product_id;
            }
        }
        $products = Product::with([
                        'category.categoryDetail.translation' => function ($q) use ($langId) {
                            $q->where('category_translations.language_id', $langId);
                        },
                        'vendor', 'media' => function($q){
                            $q->groupBy('product_id');
                        }, 'media.image',
                        'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId){
                            $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                            $q->groupBy('product_id');
                        },
                    ])->select('id', 'sku', 'averageRating', 'url_slug', 'is_new', 'is_featured', 'vendor_id', 'inquiry_only','minimum_order_count','batch_count')
                    ->whereIn('id', $productIds)
                    ->whereNotNull('category_id');
        $products = $products->get();
        if(!empty($products)){
            foreach ($products as $key => $value) {
                if($value->is_new == 1){
                    $value->product_type = 'New Product';
                }elseif($value->is_featured == 1){
                    $value->product_type = 'Featured Product';
                }else{
                    $value->product_type ='On Sale';
                }
                $value->product_media = $value->media ? $value->media->first() : NULL;
                $value->vendor_name = $value->vendor ? $value->vendor->name : '';
                $value->translation_title = (!empty($value->translation->first())) ? $value->translation->first()->title : $value->sku;
                $value->translation_description = (!empty($value->translation->first())) ? $value->translation->first()->body_html : $value->sku;
                $value->variant_multiplier = $multiplier ? $multiplier : 1;
                $value->variant_price = (!empty($value->variant->first())) ? decimal_format(($value->variant->first()->price * $multiplier),',') : 0;
                $value->averageRating = number_format($value->averageRating, 1, '.', '');
                $value->category_name = $value->category->categoryDetail->translation->first() ? $value->category->categoryDetail->translation->first()->name : '';
                $value->image_url = $value->media->first() && !is_null($value->media->first()->image) ? $value->media->first()->image->path['image_fit'] . '600/600' . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                // foreach ($value->variant as $k => $v) {
                //     $value->variant[$k]->multiplier = $multiplier;
                // }
            }
        }
        return $products;
    }

    public function setMailDetail($mail_driver, $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption)
    {
        // $config = array(
        //     'driver' => $mail_driver,
        //     'host' => $mail_host,
        //     'port' => $mail_port,
        //     'encryption' => $mail_encryption,
        //     'username' => $mail_username,
        //     'password' => $mail_password,
        //     'sendmail' => '/usr/sbin/sendmail -bs',
        //     'pretend' => false,
        // );

        // Config::set('mail', $config);
        $app = App::getInstance();
        $app->register('Illuminate\Mail\MailServiceProvider');
        return '1';

        // return '2';
    }

    /**     * check if cookie already exist     */
    public function checkCookies($userid)
    {
        if (isset($_COOKIE['uuid'])) {
            $userFind = User::where('system_id', Auth::user()->system_user)->first();
            if ($userFind) {
                $cart = Cart::where('user_id', $userFind->id)->first();
                if ($cart) {
                    $cart->user_id = $userid;
                    $cart->save();
                }
                $userFind->delete();
            }
            setcookie("uuid", "", time() - 3600);
            return redirect()->route('user.checkout');
        }
    }

    /**     * check if cookie already exist     */
    public function getLoyaltyPoints($userid, $multiplier){
        $loyalty_earned_amount = 0;
        $redeem_points_per_primary_currency = '';
        $loyalty_card = LoyaltyCard::where('status', '0')->first();
        if ($loyalty_card) {
            $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
        }
        $order_loyalty_points_earned_detail = Order::where('user_id', $userid)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
        if ($order_loyalty_points_earned_detail) {
            $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
            if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                $loyalty_earned_amount = $loyalty_points_used / $redeem_points_per_primary_currency;
            }
        }
        return $loyalty_earned_amount;
    }

    /**     * check if cookie already exist     */
    public function userMetaData($userid, $device_type = 'web', $device_token = 'web')
    {
        $device = UserDevice::where('user_id', $userid)->first();
        if (!$device) {
            $user_device[] = [
                'user_id' => $userid,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'access_token' => ''
            ];
            UserDevice::insert($user_device);
        }
        $loyaltyPoints = UserLoyaltyPoint::where('user_id', $userid)->first();
        if (!$loyaltyPoints) {
            $loyalty[] = [
                'user_id' => $userid,
                'points' => 0
            ];
            UserLoyaltyPoint::insert($loyalty);
        }
        $wallet = Wallet::where('user_id', $userid)->first();
        if (!$wallet) {
            $walletData[] = [
                'user_id' => $userid,
                'type' => 1,
                'balance' => 0,
                'card_id' => $this->randomData('wallets', 6, 'card_id'),
                'card_qr_code' => $this->randomBarcode('wallets'),
                'meta_field' => '',
            ];

            Wallet::insert($walletData);
        }
        return 1;
    }

    /* Create random and unique client code*/
    public function randomData($table, $digit, $where)
    {
        $random_string = substr(md5(microtime()), 0, $digit);
        // after creating, check if string is already used

        while (\DB::table($table)->where($where, $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, $digit);
        }
        return $random_string;
    }

    public function randomBarcode($table)
    {
        $barCode = substr(md5(microtime()), 0, 14);
        // $number = mt_rand(1000000000, 9999999999);

        while (\DB::table($table)->where('card_qr_code', $barCode)->exists()) {
            $barCode = substr(md5(microtime()), 0, 14);
        }
        return $barCode;
    }

    /* Save user payment method */
    public function saveUserPaymentMethod($request)
    {
        $payment_method = new UserSavedPaymentMethods;
        $payment_method->user_id = Auth::user()->id;
        $payment_method->payment_option_id = $request->payment_option_id;
        $payment_method->card_last_four_digit = $request->card_last_four_digit ?? NULL;
        $payment_method->card_expiry_month = $request->card_expiry_month ?? NULL;
        $payment_method->card_expiry_year = $request->card_expiry_year ?? NULL;
        $payment_method->customerReference = ($request->has('customerReference')) ? $request->customerReference : NULL;
        $payment_method->cardReference = ($request->has('cardReference')) ? $request->cardReference : NULL;
        $payment_method->save();
    }

    /* Get Saved user payment method */
    public function getSavedUserPaymentMethod($request)
    {
        $saved_payment_method = UserSavedPaymentMethods::where('user_id', Auth::user()->id)
                        ->where('payment_option_id', $request->payment_option_id)->first();
        return $saved_payment_method;
    }

    public function sendMailToSubscribedUsers(){
        $after7days = Carbon::now()->addDays(7)->toDateString();
        $now = Carbon::now()->toDateString();
        $active_subscriptions = SubscriptionInvoicesUser::with(['plan', 'features.feature', 'user'])
                                ->whereBetween('end_date', [$now, $after7days])
                                ->whereNull('cancelled_at')->get();
        $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();

        foreach($active_subscriptions as $subscription){
            if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
                $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
                $client_name = $client->name;
                $mail_from = $data->mail_from;
                $sendto = $subscription->user->email;
                try{
                    $data = [
                        'customer_name' => $subscription->user->name,
                        'code_text' => '',
                        'logo' => $client->logo['original'],
                        'frequency' => $subscription->frequency,
                        'end_date' => $subscription->end_date,
                        'link'=> "http://local.myorder.com/user/subscription/select/".$subscription->plan->slug,
                    ];
                    Mail::send('email.notifyUserSubscriptionBilling', ['mailData'=>$data],
                    function ($message) use($sendto, $client_name, $mail_from) {
                        $message->from($mail_from, $client_name);
                        $message->to($sendto)->subject('Upcoming Subscription Billing');
                    });
                    $response['send_email'] = 1;
                }
                catch(\Exception $e){
                    return response()->json(['data' => $e->getMessage()]);
                }
            }
        }
    }

    public function testOrderMail($emailData){
        $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();

        if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
            $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
            $client_name = $emailData['client_name'];
            $mail_from = $emailData['mail_from'];
            $sendto = $emailData['email'];
            try{
                Mail::send([], [],
                function ($message) use($sendto, $client_name, $mail_from, $emailData) {
                    $message->from($mail_from, $client_name);
                    $message->to($sendto)->subject('Order mail');
                    $message->setBody($emailData['email_template_content'], 'text/html'); // for HTML rich messages
                });
                $response['send_email'] = 1;
                return count(Mail::failures());
            }
            catch(\Exception $e){
                return response()->json(['data' => $e->getMessage()]);
            }
        }
    }

    /* Get vendor rating from its products rating */
    public function vendorRating($vendorProducts)
    {
        $vendor_rating = 0;
        if($vendorProducts->isNotEmpty()){
            $product_rating = 0;
            $product_count = 0;
            foreach($vendorProducts as $product){
                if($product->averageRating > 0){
                    $product_rating = $product_rating + $product->averageRating;
                    $product_count++;
                }
            }
            if($product_count > 0){
                $vendor_rating = $product_rating / $product_count;
            }
        }
        return number_format($vendor_rating, 1, '.', '');
    }

    /* doller compare amount */
    public function getDollarCompareAmount($amount, $customerCurrency='')
    {
        $customerCurrency = Session::has('customerCurrency') ? Session::get('customerCurrency') : ( (!empty($customerCurrency)) ? $customerCurrency : '' );
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        if(empty($customerCurrency)){
            $clientCurrency = $primaryCurrency;
        }else{
            $clientCurrency = ClientCurrency::where('currency_id', $customerCurrency)->first();
        }
        $divider = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
        $amount = ($amount / $divider) * $primaryCurrency->doller_compare;
        $amount = decimal_format($amount);
        return $amount;
    }

    public function setVendorType($type = ''){
        if(empty($type)){
           $type = 'delivery';
        }
        Session::put('vendorType', $type);
        return Session::get('vendorType');
    }


    // get cart data in on demand product listing page
    public function getCartOnDemand($request)
    {
        $cartData = [];
        $user = Auth::user();
        $client_data = Client::first();
        $countries = Country::get();
        $langId = Session::get('customerLanguage');
        $additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch','is_service_price_selection']);
        $is_service_product_price_from_dispatch_forOnDemand = 0;

        $getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
        if($getOnDemandPricingRule['is_price_from_freelancer']==1){
            $is_service_product_price_from_dispatch_forOnDemand =1;
        }

        $guest_user = true;
        if ($user) {
            $cart = Cart::select('id', 'is_gift', 'item_count','scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
            $addresses = UserAddress::where('user_id', $user->id)->get();
            $guest_user = false;
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count','scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
            $addresses = collect();
        }
        if ($cart) {
            $cartData = CartProduct::with('vendor')->where('status', [0, 1])->where('cart_id', $cart->id)->orderBy('created_at', 'asc')->get();
        }

        $navCategories = $this->categoryNav($langId);
        $subscription_features = array();
        if ($user) {
            $now = Carbon::now()->toDateTimeString();
            $user_subscription = SubscriptionInvoicesUser::with('features')
                ->select('id', 'user_id', 'subscription_id')
                ->where('user_id', $user->id)
                ->where('end_date', '>', $now)
                ->orderBy('end_date', 'desc')->first();
            if ($user_subscription) {
                foreach ($user_subscription->features as $feature) {
                    $subscription_features[] = $feature->feature_id;
                }
            }
        }

            if($user && $user->timezone)
            $timezone = $user->timezone ?? $client_data->timezone;
            elseif($client_data && $client_data->timezone)
            $timezone = $client_data->timezone;
            else
            $timezone = 'Asia/Kolkata';

        foreach($cartData as $key => $data){


            $selectedDate = Carbon::parse($data->scheduled_date_time, 'UTC')->setTimezone($timezone)->format('Y-m-d');
            $cartData[$key]->scheduled_date_time = $selectedDate;
            $cartData[$key]->is_dispatch_slot = 0 ;
            $vendorStartDate =  $vendorStartTime  ='';
            $cartData[$key]->period = [];
             if( $data->vendor->show_slot ==1 ){ // IF VENDOR 24*7 Availability
                $time_slots = [];
                $start_date = new DateTime("now", new  DateTimeZone($timezone) );
                $start_date = $start_date->format('Y-m-d');
                $end_date   = Date('Y-m-d', strtotime('+13 days'));


                $period = CarbonPeriod::create($start_date, $end_date);

                $cartData[$key]->period = $period;
            }else{
                $slotsDate = findSlot('',$data->vendor_id,'','webFormet');
                if($slotsDate){
                    $vendorStartDate = (($slotsDate)?$slotsDate['date']:'');
                    $vendorStartTime = (($slotsDate)?$slotsDate['time']:'');
                    $vendorEndDate = Date('Y-m-d', strtotime($vendorStartDate. '+13 days'));
                    $cartData[$key]->period = CarbonPeriod::create($vendorStartDate, $vendorEndDate);
                }
            }

            // check product
            $productDetail = $this->productDetail($data->product_id);
            $cateTypeId = $productDetail ? ($productDetail->productcategory ? $productDetail->productcategory->type_id : '') : '';
            $is_slot_from_dispatch = $productDetail ? $productDetail->is_slot_from_dispatch  : '';
            $last_mile_check = $productDetail ? $productDetail->Requires_last_mile  : '';
            $cartData[$key]->cateTypeId = $cateTypeId;
            if(($cateTypeId ==  12) && ($is_slot_from_dispatch == 1) && ($last_mile_check == 1)){
                $Dispatch =  $this->getDispatchAppointmentDomain();
                $dispatchAgents = [];
                if($Dispatch){
                    $vendor_latitude =  $productDetail->vendor ? $productDetail->vendor->latitude : 30.71728880;
                    $vendor_longitude =  $productDetail->vendor ? $productDetail->vendor->longitude : 76.80350870;
                    $unique = Client::first()->code;
                    $email =  $unique.$productDetail->vendor_id."_royodispatch@dispatch.com";

                    $location[] = array(
                        'latitude' =>  $vendor_latitude,
                        'longitude' => $vendor_longitude
                    );
                    $dispatchData=[
                        'service_key'      => $Dispatch->appointment_service_key,
                        'service_key_code' => $Dispatch->appointment_service_key_code,
                        'service_key_url'  => $Dispatch->appointment_service_key_url,
                        'service_type'     => 'appointment',
                        'tags'             => $productDetail->tags,
                        'latitude'         => $vendor_latitude,
                        'longitude'        => $vendor_longitude,
                        'service_time'     => $productDetail->minimum_duration_min,
                        'schedule_date'    => $selectedDate,
                        'slot_start_time'  => $vendorStartTime,
                        'team_email'       => $email
                    ];

                    $dispatchAgents = $this->getSlotFeeDispatcher($dispatchData);
                }
                $cartData[$key]->timeSlots = [];
                $cartData[$key]->dispatchAgents = $dispatchAgents;
                $cartData[$key]->is_dispatch_slot = 1 ;
            }else{
                $time_slots = [];
                if(($cateTypeId == 8) && ($is_service_product_price_from_dispatch_forOnDemand !=1 )){ // no need to geting verdor slot when we get driver price
                    if( $data->vendor->show_slot ==1 ){ // IF VENDOR 24*7 Availability
                        $start_time = new DateTime("now", new  DateTimeZone($timezone) );
                        $today = $start_time->format('Y-m-d');
                        if($today < $selectedDate){
                            $curr_time = date('Y-m-d 00:00');
                        }else{
                            $daten = new DateTime("now", new DateTimeZone($timezone) );
                            $curr_time = $daten->format('Y-m-d H:i');
                        }
                        $start_time = $start_time->format('Y-m-d H:m');
                        $end_time = date('Y-m-d 23:59');
                        $timing   = $this->SplitTime($curr_time, $end_time, "60");
                        foreach ($timing as $k=> $slt) {
                            if($k+1 < count($timing)){
                                $viewSlot['name'] = date('h:i:A', strtotime($slt)).' - '.date('h:i:A', strtotime($timing[$k+1]));
                                $viewSlot['value'] = $slt.' - '.$timing[$k+1];
                                $time_slots[] =  $viewSlot;
                            }
                        }
                    }else{
                        $slotsRes = getShowSlot($selectedDate,$data->vendor_id,'delivery');
                        $slots = (object)$slotsRes['slots'];
                        $time_slots =  $slots;
                    }
                }

                //$cartData->is_service_product_price_from_dispatch  = $additionalPreference['is_service_product_price_from_dispatch'] ;
                $cartData[$key]->timeSlots = $time_slots;
                $cartData[$key]->dispatchAgents = [];
            }



        }



        $start_date = new DateTime("now", new  DateTimeZone($timezone) );
        $start_date =  $start_date->format('Y-m-d');
        $end_date = Date('Y-m-d', strtotime('+13 days'));

        $start_time = new DateTime("now", new  DateTimeZone($timezone) );
        $start_time = $start_time->format('Y-m-d H:m');
        $end_time = date('Y-m-d 23:59');
        $period = CarbonPeriod::create($start_date, $end_date);
        $time_slots = $this->SplitTime($start_time, $end_time, "60");

        return ['time_slots' => $time_slots,'period' => $period,'cartData' => $cartData, 'addresses' => $addresses, 'countries' => $countries, 'subscription_features' => $subscription_features, 'guest_user'=>$guest_user];
    }


    // get slot fron dispatcher
    public function getSlotFromDispatchDemand(Request $request)
    {
           $product = $this->productDetail($request->product_id);

            $cateTypeId = $product ? ($product->productcategory ? $product->productcategory->type_id : '') : '';
            $is_slot_from_dispatch = checkColumnExists('products', 'is_slot_from_dispatch') ? ($product ? $product->is_slot_from_dispatch  : '') : '';
            $show_dispatcher_agent = checkColumnExists('products', 'is_slot_from_dispatch') ? ($product ? $product->is_show_dispatcher_agent  : '') :' ';
            $last_mile_check       = $product ? $product->Requires_last_mile  : '';
            $vendorStartDate       = $vendorStartTime  = '';
            $html = "";
            if(($cateTypeId ==  12) && ($is_slot_from_dispatch == 1) && ( $last_mile_check ==1) ){

                $Dispatch =  $this->getDispatchAppointmentDomain();
                $dispatchAgents = [];
                $cart_product_id = $request->cart_product_id??0;

                if($Dispatch){

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
                        'slot_start_time'  => $vendorStartTime
                    ];

                    $dispatchAgents = $this->getSlotFeeDispatcher($dispatchData);

                }

                if((isset($dispatchAgents)) && (isset($dispatchAgents['slots'])) && ( count($dispatchAgents['slots']) > 0 ) ){
                      $html .= "<option value=''>".__('Select Slot')." </option>";
                      foreach($dispatchAgents['slots'] as $slot){

                          $html .= "<option value='".$slot['value']."'  data-show_agent='".json_encode($slot['agent_id'],TRUE)."' >".$slot['name'].`"</option>"`;
                      }
                }else{
                    $html .= "<option value=''>".__('No Slot Available')." </option>";
                }
                return response()->json(['status'=>'Success','html'=>$html, 'message'=>'get slots']);
            }
            $html .= "<option value=''>".__('No Slot Available')." </option>";
            return response()->json(['status'=>'Success','html'=>$html, 'message'=>"get slots"]);

    }

    /////////// ***************    get all time slots *******************************  /////////////////////
    function SplitTime($StartTime, $EndTime, $Duration="30"){


        $ReturnArray = array ();// Define output
        if(date ("i", strtotime($StartTime)) > 30)
        $startwith = 00;
        else
        $startwith = 30;
        $StartTime = date ("Y-m-d G", strtotime($StartTime));
        $StartTime = $StartTime.":".$startwith;
        $StartTime    = strtotime ($StartTime); //Get Timestamp
        $EndTime      = strtotime ($EndTime); //Get Timestamp
        $AddMins  = $Duration * 30;


        while ($StartTime <= $EndTime) //Run loop
        {
            $ReturnArray[] = date ("G:i", $StartTime);
            $StartTime += $AddMins; //Endtime check
        }
        return $ReturnArray;
    }

    public function getEvenOddTime($time) {
        return ($time % 5 === 0) ? $time : ($time - ($time % 5));
    }

    function getLineOfSightDistanceAndTime($vendor, $preferences){
        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
            $unit_abbreviation = ($distance_unit == 'mile') ? 'miles' : 'km';
            $distance_to_time_multiplier = ($preferences->distance_to_time_multiplier > 0) ? $preferences->distance_to_time_multiplier : 2;
            $distance = $vendor->vendorToUserDistance;
            $vendor->lineOfSightDistance = number_format($distance, 1, '.', '') .' '. $unit_abbreviation;
            $vendor->timeofLineOfSightDistance = number_format(floatval($vendor->order_pre_time), 0, '.', '') + number_format(($distance * $distance_to_time_multiplier), 0, '.', ''); // distance is multiplied by distance time multiplier to calculate travel time
            $pretime = $this->getEvenOddTime($vendor->timeofLineOfSightDistance);
           // $vendor->timeofLineOfSightDistance = $pretime . '-' . (intval($pretime) + 5);
            if($pretime >= 60){
                $vendor->timeofLineOfSightDistance =  '~ '.$this->vendorTime($pretime) .' '. __('hour');
            }else{
                $vendor->timeofLineOfSightDistance = $pretime . '-' . (intval($pretime) + 5).' '. __('min');
            }
        }
        return $vendor;
    }

    function getVendorDistanceWithTime($userLat='', $userLong='', $vendor, $preferences){
        if(($preferences) && ($preferences->is_hyperlocal == 1)){
            if( (empty($userLat)) && (empty($userLong)) ){
                $userLat = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_latitude) : 0;
                $userLong = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_longitude) : 0;
            }

            $lat1   = $userLat;
            $long1  = $userLong;
            $lat2   = $vendor->latitude;
            $long2  = $vendor->longitude;
            if($lat1 && $long1 && $lat2 && $long2){
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                $unit_abbreviation = ($distance_unit == 'mile') ? 'miles' : 'km';
                $distance_to_time_multiplier = ($preferences->distance_to_time_multiplier > 0) ? $preferences->distance_to_time_multiplier : 2;
                $distance = $this->calulateDistanceLineOfSight($lat1, $long1, $lat2, $long2, $distance_unit);
                $vendor->lineOfSightDistance = number_format($distance, 1, '.', '') .' '. $unit_abbreviation;

                $vendor->timeofLineOfSightDistance = number_format(floatval($vendor->order_pre_time), 0, '.', '') + number_format(($distance * $distance_to_time_multiplier), 0, '.', ''); // distance is multiplied by distance time multiplier to calculate travel time
                $pretime = $this->getEvenOddTime($vendor->timeofLineOfSightDistance);
                if($pretime >= 60){

                    $vendor->timeofLineOfSightDistance =  '~ '.$this->vendorTime($pretime) .' '. __('hour');
                }else{
                    $vendor->timeofLineOfSightDistance = $pretime . '-' . (intval($pretime) + 5).' '. __('min');
                }

            }else{
                $vendor->lineOfSightDistance = 0;
                $vendor->timeofLineOfSightDistance = 0;
            }
        }
        return $vendor;
    }

    // Find distance between two lat long points
    function calulateDistanceLineOfSight($lat1, $lon1, $lat2, $lon2, $unit) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
          return 0;
        }
        else {
          $theta = $lon1 - $lon2;
          $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
          $dist = acos($dist);
          $dist = rad2deg($dist);
          $miles = $dist * 60 * 1.1515;
          $unit = strtolower($unit);

          if ($unit == "kilometer") {
            return ($miles * 1.609344);
          } else if ($unit == "nautical mile") {
            return ($miles * 0.8684);
          } else {
            return $miles;
          }
        }
    }

    public function formattedOrderETA($minutes, $order_vendor_created_at, $scheduleTime='', $user=''){
        $d = floor ($minutes / 1440);
        $h = floor (($minutes - $d * 1440) / 60);
        $m = $minutes - ($d * 1440) - ($h * 60);
        // return (($d > 0) ? $d.' days ' : '') . (($h > 0) ? $h.' hours ' : '') . (($m > 0) ? $m.' minutes' : '');

        // if($scheduleTime != ''){
        //     $datetime = Carbon::parse($scheduleTime)->setTimezone(Auth::user()->timezone)->toDateTimeString();
        // }else{
        //     $datetime = Carbon::parse($order_vendor_created_at)->setTimezone(Auth::user()->timezone)->addMinutes($minutes)->toDateTimeString();
        // }

        // if(Carbon::parse($datetime)->isToday()){
        //     $format = 'h:i A';
        // }else{
        //     $format = 'M d, Y h:i A';
        // }
        // // $time = convertDateTimeInTimeZone($datetime, Auth::user()->timezone, $format);
        // $time = Carbon::parse($datetime)->format($format);



        if(isset($user) && !empty($user))
        $user =  $user;
        else
        $user = Auth::user();

        $timezone = $user->timezone;
        $preferences = ClientPreference::select('date_format', 'time_format')->where('id', '>', 0)->first();
        $date_format = $preferences->date_format;
        $time_format = $preferences->time_format;

        if($scheduleTime != ''){
            $datetime = Carbon::parse($scheduleTime)->addMinutes($minutes);
        }else{
            $datetime = Carbon::parse($order_vendor_created_at)->addMinutes($minutes);
        }
        if(Carbon::parse($datetime)->isToday()){
            if($time_format == '12'){
                $time_format = 'hh:mm A';
            }else{
                $time_format = 'HH:mm';
            }
        }
        $datetime = dateTimeInUserTimeZone($datetime, $timezone);
        return $datetime;
    }

    public function getClientCode(){
        $code = Client::orderBy('id','asc')->value('code');
        return $code;
    }

    public function sendmailtest(Request $request,$domain='',$to){

        $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();

        if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
                $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
                $client_name = $client->name;
                $mail_from = $data->mail_from;
                $sendto = $to;
                try{
                    $data = [
                        'customer_name' => "Test user Email",
                        'code_text' => '',
                        'logo' => $client->logo['original'],
                        'frequency' => 'asd',
                        'end_date' => "asd",
                        'link'=> "http://local.myorder.com/user/subscription/select/",
                    ];
                     $mail=   Mail::send('email.notifyUserSubscriptionBilling', ['mailData'=>$data],
                        function ($message) use($sendto, $client_name, $mail_from) {
                            $message->from($mail_from, $client_name);
                            $message->to($sendto)->subject('Testing Email Credentials');
                        });
                    echo "<pre>";
                    print_r($mail);
                    exit();
                $response['send_email'] = 1;
                }
                catch(\Exception $e){
                    return response()->json(['data' => $e->getMessage()]);
                }
            }

    }

    public function vendorTime($minutes){
        $hours = intdiv($minutes, 60);//.':'. ($minutes % 60);
        if(($minutes % 60) > 30){
            $hours = $hours+1;
        }
        return $hours;

    }
    protected function sendSuccessSMS($request, $order, $vendor_id = '')
    {
        //Log::info('sendSuccessSMS FrontController');
        try {

            $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from','digit_after_decimal')->first();
            // $currId = Session::get('customerCurrency');
            // $currSymbol = Session::get('currencySymbol');
            $customerCurrency = ClientCurrency::with('currency')->where('is_primary', '1')->first();
            $currSymbol =$customerCurrency->currency->symbol;
            $user = User::where('id', $order->user_id)->first();
            if ($user) {
                if ($user->dial_code == "971") {
                    $to = '+' . $user->dial_code . "0" . $user->phone_number;
                } else {
                    $to = '+' . $user->dial_code . $user->phone_number;
                }

                $provider = $prefer->sms_provider;
                $order->payable_amount = number_format((float)$order->payable_amount, $prefer->digit_after_decimal, '.', '');

                $smsTemplates =  SmsTemplate::where('slug', 'order-place-Successfully')->first()->content;
                if(!empty($smsTemplates)){
                    $smsTemplates = str_replace("{user_name}", $user->name, $smsTemplates);
                    $smsTemplates = str_replace("{amount}", $currSymbol . $order->payable_amount, $smsTemplates);
                    $body = str_replace("{order_number}", $order->order_number, $smsTemplates);
                }else{
                    $body = __("Hi ") . $user->name . __(", Your order of amount ") . $currSymbol . $order->payable_amount . __(" for order number ") . $order->order_number . __(" has been placed successfully.");
                }
            //    if (!empty($prefer->sms_key) && !empty($prefer->sms_secret) && !empty($prefer->sms_from)) {
                if (!empty($prefer->sms_provider)) {
                    $send = $this->sendSms($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                }
            }
        } catch (\Exception $ex) {

        }

    }
     # get prefereance if appointment on in config
     public function getDispatchAppointmentDomain()
     {
         $preference = ClientPreference::select('need_appointment_service','appointment_service_key','appointment_service_key_url','appointment_service_key_code')->first();
         if ($preference->need_appointment_service == 1 && !empty($preference->appointment_service_key) && !empty($preference->appointment_service_key_url) && !empty($preference->appointment_service_key_code)) {
             return $preference;
         } else {
             return false;
         }
     }

    public function test_notification(Request $request){
        $new[] = $request->token ;
        $fcm_server_key = $request->fcm_server_key ;
        // if(  $request->token  ){

        //     echo 'fcm_server_key or tokon inveled';
        //     exit();
        // }
        $order = Order::with(['vendors.vendor:id,name,auto_accept_order,logo'])->select('id', 'order_number', 'payable_amount', 'payment_option_id', 'user_id', 'address_id', 'loyalty_amount_saved', 'total_discount', 'total_delivery_fee', 'total_amount', 'taxable_amount', 'created_at')->first();

      // pr($order);
        $item['title']     = 'notification test by harbans';
        $item['body']      = 'this is test by h:) ';
        $data = [
            "registration_ids" => $new,
            "notification" => [
                'title' => 'notification test by harbans',
                'body'  => 'notification test by harbans',
                'sound' => "notification.wav",
                'click_action' => route('order.index'),
                "android_channel_id" => "sound-channel-id"
            ],
            "data" => [
                'title' => 'notification test by harbans',
                'body'  => 'notification test by harbans',
                'data' => $order,
                'type' => "order_created"
            ],
            "priority" => "high"
        ];

        $headers = [
            'Authorization: key=AAAAJo1U6_Q:APA91bGawE2fcj6IKUMlUbBgyQIFZ0_-SRJtkghEqKvuyBXq83HZQOLfLTenfWT-eEXSnvU06Hk4LYeWqxkpH1xQn_MQhqIuEDfPZb-e52GJ-aXZzs5LHg2XPotX2oMDDO3iacYT75ho',
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        // if ($result === FALSE) {
        //     die('Oops! FCM Send Error: ' . curl_error($ch));
        // }
        echo  $new[0];
        curl_close($ch);
        return $result;

    }

    public function sendWalletNotification($user_id,$order_number)
    {
        $firebaseToken = UserDevice::select('device_token')->whereNotNull('device_token')->where('user_id',$user_id)->orderBy('id','desc')->limit(1)->pluck('device_token')->toArray();
        if(!empty($firebaseToken)){
            $preference = ClientPreference::select('fcm_server_key')->first();
            $fcm_server_key = !empty($preference->fcm_server_key)? $preference->fcm_server_key : 'null';

            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => "Refund Added in Wallet",
                    "body" => 'Wallet has been <b>refunded</b> for cancellation or failed payment of order #' .$order_number
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Authorization: key=' . $fcm_server_key,
                'Content-Type: application/json',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
            curl_close($ch);
        }
        return true;
    }

}
