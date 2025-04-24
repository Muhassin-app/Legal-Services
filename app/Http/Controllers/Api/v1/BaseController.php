<?php

namespace App\Http\Controllers\Api\v1;
use DB;
use App;
use Mail;
use Config;
use Session;
use Carbon\Carbon;
use App\Models\User;
use ConvertCurrency;
use App\Models\Cart;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GCLIENT;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client as TwilioClient;
use App\Models\{Client, Category, Product,UserSavedPaymentMethods, ClientPreference, ClientCurrency, Wallet, UserLoyaltyPoint, LoyaltyCard, Order, Nomenclature, ProductVariant, ServiceArea, Vendor, VendorCategory};
use Illuminate\Support\Facades\Crypt;
use JWT\Token;

class BaseController extends Controller{

    use \App\Http\Traits\smsManager;

    private $field_status = 2;
    private $categoryOptionData = [];

	protected function sendSms($provider, $sms_key, $sms_secret, $sms_from, $to, $body){
        try{
            $client_preference =  getClientPreferenceDetail();
            if($client_preference->sms_provider == 1)
            {
                $client = new TwilioClient($sms_key, $sms_secret);
                $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
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
                $client = new TwilioClient($sms_key, $sms_secret);
                $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
            }
        }
        catch(\Exception $e){
            return '2';
        }
        return '1';
	}

    protected function sendSmsNew($provider, $sms_key, $sms_secret, $sms_from, $to, $body){
        try{
            $body = $body['body']??'';
            $template_id = $body['template_id']??''; //sms Template_id
            $client_preference =  getClientPreferenceDetail();
            if($client_preference->sms_provider == 1)
            {
                $client = new TwilioClient($sms_key, $sms_secret);
                $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
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
            $send = $this->vonage_sms($to, $body, $crendentials);
            }
            elseif($client_preference->sms_provider == 8) //for SMS partner gateway France
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->sms_partner_gateway($to, $body, $crendentials);
            }
            elseif($client_preference->sms_provider == 9) //for  ethiopia
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
                $client = new TwilioClient($sms_key, $sms_secret);
                $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
            }
        }
        catch(\Exception $e){
            return '2';
        }
        return '1';
	}


    public function getParentCategories($child, $langId, $parentCategories=[]){
        $category = Category::with(['translation' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')->where('category_translations.language_id', $langId)->groupBy(['category_translations.language_id', 'category_translations.category_id']);
        }])->where('id', $child)->where('status', 1)->select('id', 'slug', 'parent_id')->first();
        if($category){
            $parentCategories[] = $category->translation->first() ? $category->translation->first()->name : $category->slug;
            if($category->parent_id != 1){
                $parentCategories = $this->getParentCategories($category->parent_id, $langId, $parentCategories);
            }
        }
        return $parentCategories;
    }

    /*      Category options heirarchy      */
    public function getCategoryOptionsHeirarchy($tree, $langId)
    {
        if (!is_null($tree) && count($tree) > 0) {
            foreach ($tree as $key => $node) {

                // type_id 1 means product in type table
                if (isset($node['children']) && count($node['children']) > 0) {

                    // start including parent category
                    $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];

                    $parentCategories = array_reverse($this->getParentCategories($node['id'], $langId));
                    $hierarchyName = implode(' > ', $parentCategories);

                    $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'name'=>$category, 'can_add_products'=>$node['can_add_products'], 'cat_image'=>$node['image']);
                    // end including parent category

                    $this->getCategoryOptionsHeirarchy($node['children'], $langId);
                }
                else{
                    // if ($node['type_id'] == 1 || $node['type_id'] == 3 || $node['type_id'] == 7 || $node['type_id'] == 8) {
                        $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                        $parentCategories = array_reverse($this->getParentCategories($node['id'], $langId));
                        $hierarchyName = implode(' > ', $parentCategories);

                        $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'name'=>$category, 'can_add_products'=>$node['can_add_products'], 'cat_image'=>$node['image']);
                    // }
                }
            }
        }
        return $this->categoryOptionData;
    }

    /*      Category options heirarchy      */
    public function printCategoryOptionsHeirarchy($tree, $parentCategory = [])
    {
        if (!is_null($tree) && count($tree) > 0) {
            foreach ($tree as $key => $node) {
                if($node['parent_id'] == 1){
                    $parentCategory = array($node['translation'][0]['name']??'');
                }
                // type_id 1 means product in type table
                if (isset($node['children']) && count($node['children']) > 0) {
                    if($node['parent_id'] != 1 && !empty($node['translation'][0]['name'])){
                        $parentCategory[] = $node['translation'][0]['name'];
                    }

                    // start including parent category
                    $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                    $hierarchyName = $category; // assume first category is parent
                    if(count($parentCategory) > 0){
                        if($node['parent_id'] != 1){ // if category is not parent then make heirarchy
                            $hierarchyName = implode(' > ', $parentCategory);
                            $hierarchyName = $hierarchyName.' > '.$category;
                        }
                    }
                    $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                    // end including parent category

                    $this->printCategoryOptionsHeirarchy($node['children'], $parentCategory);
                }
                else{
                    // if ($node['type_id'] == 1 || $node['type_id'] == 3 || $node['type_id'] == 7 || $node['type_id'] == 8) {
                        $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                        if($node['parent_id'] == 1){
                            $parentCategory = [];
                            $hierarchyName = $category;
                        }else{
                            $hierarchyName = implode(' > ', $parentCategory);
                            $hierarchyName = $hierarchyName.' > '.$category;
                        }
                        // $this->optionData .= '<option value="'.$node['id'].'">'.$hierarchyName.'</option>';
                        $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                    // }
                }
            }
        }
        return $this->categoryOptionData;
    }
    /* Save user payment method */
    public function saveUserPaymentMethod($request)
    {
        $payment_method = new UserSavedPaymentMethods;
        $payment_method->user_id = Auth::user()->id;
        $payment_method->payment_option_id = $request->payment_option_id;
        $payment_method->card_last_four_digit = $request->card_last_four_digit;
        $payment_method->card_expiry_month = $request->card_expiry_month;
        $payment_method->card_expiry_year = $request->card_expiry_year;
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

	public function buildTree($elements, $parentId = 1) {
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
                        if($vendorCategory){
                            $category_list[] = $vendorCategory;
                        }
                        $this->getChildCategoriesForVendor($child->id, $langId, $vid);
                    }
                }

                $vendorCategory = VendorCategory::with(['category.translation' => function($q) use($langId){
                    $q->where('category_translations.language_id', $langId);
                }])->where('vendor_id', $vid)->where('category_id', $cate->id)->where('status', 1)->first();
                if($vendorCategory){
                    $category_list[] = $vendorCategory;
                }
                $this->getChildCategoriesForVendor($cate->id, $langId, $vid);
            }
        }
        return $category_list;
    }

    public function categoryNav($lang_id, $vends=[],$type = 'delivery', $request = []) {

        $categoryTypes = getServiceTypesCategory($type);

        // pr($categoryTypes);
        $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
        $preferences = ClientPreference::select('is_hyperlocal', 'client_code', 'language_id', 'celebrity_check')->first();
        $categories = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
        ->leftjoin('types', 'types.id', 'categories.type_id') // Include the join with "types" table
        ->select(
            'categories.id',
            'categories.icon',
            'categories.image',
            'categories.slug',
            'categories.parent_id',
            'cts.name',
            'categories.warning_page_id',
            'categories.template_type_id',
            'types.title as redirect_to',
            'categories.type_id'
        );

                    // if(@$getAdditionalPreference['is_rental_weekly_monthly_price']){
                    //     $categories->whereIn('categories.type_id',[10] );
                    // }else{
                        $categories->whereIn('categories.type_id',$categoryTypes );
                    // }

                $categories =  $categories->distinct('categories.slug');

        $status = $this->field_status;
        $include_categories = [4,8]; // type 4 for brands
        if(@$getAdditionalPreference['is_rental_weekly_monthly_price']){
            $include_categories[] = 10;
        }

        $celebrity_check = 0;
        if ($preferences) {
            if((isset($preferences->celebrity_check)) && ($preferences->celebrity_check == 1)){
                $celebrity_check = 1;
                $include_categories[] = 5; // type 5 for celebrity
            }
           if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {

                // $categories = $categories->when($vends, function ($query) use($vends , $include_categories) {
                //         $query->leftJoin('vendor_categories as vct', 'categories.id', 'vct.category_id')
                //                 ->where(function ($q1) use ($vends , $include_categories) {
                //                     $q1->whereIn('vct.vendor_id', $vends)
                //                         ->where('vct.status', 1)
                //                         ->orWhere(function ($q2) use($include_categories) {
                //                             $q2->whereIn('categories.type_id', $include_categories);
                //                         });
                //                 });
                //         });


                $categories = $categories->when($vends, function ($query) use($vends , $include_categories) {
                    $query->leftJoin('vendor_categories as vct', 'categories.id', 'vct.category_id')
                            ->where(function ($q1) use ($vends , $include_categories) {
                                $q1->whereIn('vct.vendor_id', $vends)
                                    ->where('vct.status', 1)
                                    ->orWhere(function ($q2) use($include_categories) {
                                        $q2->whereIn('categories.type_id', $include_categories);
                                    });
                            });
                    });

           }
        }




        $categories = $categories
                        ->where('categories.id', '>', '1')
                        ->whereNotNull('categories.type_id');
        if($celebrity_check == 0){
            $categories = $categories->where('categories.type_id', '!=', 5);
        }

        $categories = $categories->where('categories.is_visible', 1)
                        ->where('categories.status', '!=', $status)
                        ->where('categories.is_core', 1)
                        ->where('categories.is_visible', 1)
                        ->where('cts.language_id', $lang_id)
                        ->orderBy('categories.parent_id', 'asc')
                        ->whereNull('categories.vendor_id')
                        ->withCount('products')
                        ->orderBy('categories.position', 'asc')
                        ->groupBy('id');


        if(@$request['category_limit'] && $request['category_limit'] > 0){
            $categories = $categories->take($request['category_limit'])->get();
        }else{

            $categories = $categories->get();
        }


        // dd($categories);
        if($categories){
            $categories = $this->buildTree($categories->toArray());
        }

        return $categories;

    }

    public function subCategoryNav($lang_id, $vends=[],$type = 'delivery', $cid) {

        $categoryTypes = getServiceTypesCategory($type);

        $preferences = ClientPreference::select('is_hyperlocal', 'client_code', 'language_id', 'celebrity_check')->first();
        $categories = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
                    ->select('categories.id', 'categories.icon', 'categories.image', 'categories.slug', 'categories.parent_id', 'cts.name', 'categories.warning_page_id', 'categories.template_type_id', 'types.title as redirect_to')
                    ->whereIn('categories.type_id',$categoryTypes )
                    ->distinct('categories.slug');

        $status = $this->field_status;
        $include_categories = [4,8]; // type 4 for brands
        $celebrity_check = 0;
        if ($preferences) {
            if((isset($preferences->celebrity_check)) && ($preferences->celebrity_check == 1)){
                $celebrity_check = 1;
                $include_categories[] = 5; // type 5 for celebrity
            }
            if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {
                $categories = $categories->leftJoin('vendor_categories as vct', 'categories.id', 'vct.category_id')
                    ->where(function ($q1) use ($vends, $include_categories) {
                        $q1->whereIn('vct.vendor_id', $vends)
                            ->where('vct.status', 1)
                            ->orWhere(function ($q2) use($include_categories) {
                                $q2->whereIn('categories.type_id', $include_categories);
                            });
                    });
            }
        }
        $categories = $categories->leftjoin('types', 'types.id', 'categories.type_id')
                        ->where('categories.id', '>', '1')
                        ->whereNotNull('categories.type_id');
        if($celebrity_check == 0){
            $categories = $categories->where('categories.type_id', '!=', 5);
        }
        $categories = $categories->where('categories.is_visible', 1)
                        ->where('categories.status', '!=', $status)
                        ->where('categories.is_core', 1)
                        ->where('categories.is_visible', 1)
                        ->where('cts.language_id', $lang_id)
                        ->orderBy('categories.parent_id', 'asc')
                        ->whereNull('categories.vendor_id')
                        ->withCount('products')
                        ->orderBy('categories.position', 'asc')
                        ->groupBy('id')->get();
        if($categories){
            $categories = $this->buildTree($categories->toArray(), $cid);
        }
        return $categories;
    }

    public function metaProduct($langId, $multiplier, $for = 'related', $productArray = []){
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
        ->whereIn('id', $productIds);
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
                $value->variant_price = (!empty($value->variant->first())) ? decimal_format(($value->variant->first()->price * $multiplier)) : 0;
                $value->averageRating = number_format($value->averageRating, 1, '.', '');
                $value->category_name = $value->category->categoryDetail->translation->first()->name??null;
                // foreach ($value->variant as $k => $v) {
                //     $value->variant[$k]->multiplier = $multiplier;
                // }
            }
        }
        return $products;
    }

    function getVendorDistanceWithTime($userLat='', $userLong='', $vendor, $preferences, $type = 'delivery'){
        if(($preferences) && ($preferences->is_hyperlocal == 1)){
            if( (empty($userLat)) && (empty($userLong)) ){
                $userLat = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_latitude) : 0;
                $userLong = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_longitude) : 0;
            }

            $lat1   = $userLat;
            $long1  = $userLong;
            $lat2   = $vendor->latitude;
            $long2  = $vendor->longitude;
            $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
            $unit_abbreviation = ($distance_unit == 'mile') ? 'miles' : 'km';
            $distance_to_time_multiplier = (!empty($preferences->distance_to_time_multiplier)) ? $preferences->distance_to_time_multiplier : 2;
            $distance = $this->calulateDistanceLineOfSight($lat1, $long1, $lat2, $long2, $distance_unit);
            $vendor->lineOfSightDistance = number_format($distance, 1, '.', '') .' '. $unit_abbreviation;

            // recreate line of sight distance without unit to ensure backwards compatibility
            $vendor->line_of_sight_distance = number_format($distance, 1, '.', '');
            $vendor->distance_unit = $unit_abbreviation;

            if($type == 'delivery')
            {
                $pretime =  number_format(floatval($vendor->order_pre_time), 0, '.', '') + number_format(($distance * $distance_to_time_multiplier), 0, '.', '');
                // distance is multiplied by distance time multiplier to calculate travel time
            }else{
                $pretime =  number_format(floatval($vendor->order_pre_time), 0, '.', '') + 0;
            }
            // if($pretime >= 60){
            //     $vendor->timeofLineOfSightDistance =  $this->vendorTime($pretime) . '-' . $this->vendorTime((intval($pretime) + 5)).' '. __('hour');
            // }else{
            //     $vendor->timeofLineOfSightDistance = $pretime . '-' . (intval($pretime) + 5).' '. __('min');
            // }
            $vendor->timeofLineOfSightDistance = $pretime ;
        }
        return $vendor;
    }

    function getLineOfSightDistanceAndTime($vendor, $preferences){
        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
            $unit_abbreviation = ($distance_unit == 'mile') ? 'miles' : 'km';
            $distance_to_time_multiplier = ($preferences->distance_to_time_multiplier > 0) ? $preferences->distance_to_time_multiplier : 2;
            $distance = $vendor->vendorToUserDistance;
            $vendor->lineOfSightDistance = number_format($distance, 1, '.', '') .' '. $unit_abbreviation;
            $pretime = number_format(floatval($vendor->order_pre_time), 0, '.', '') + number_format(($distance * $distance_to_time_multiplier), 0, '.', ''); // distance is multiplied by distance time multiplier to calculate travel time
            // if($pretime >= 60){
            //     $vendor->timeofLineOfSightDistance =  $this->vendorTime($pretime) . '-' . $this->vendorTime((intval($pretime) + 5)).' '. __('hour');
            // }else{
            //     $vendor->timeofLineOfSightDistance = $pretime . '-' . (intval($pretime) + 5).' '. __('min');
            // }
            $vendor->timeofLineOfSightDistance =  $pretime;
            // $pretime = $this->getEvenOddTime($vendor->timeofLineOfSightDistance);
            // $vendor->timeofLineOfSightDistance = $pretime . '-' . (intval($pretime) + 5);
        }
        return $vendor;
    }

    protected function in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y){
      $i = $j = $c = 0;
      for ($i = 0, $j = $points_polygon-1 ; $i < $points_polygon; $j = $i++) {
        if ( (($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
        ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) ) {
            $c = !$c;
        }
      }
      return $c;
    }

    public function getServiceAreaVendors($lat=0, $lng=0, $type='delivery'){
        $preferences = ClientPreference::where('id', '>', 0)->first();
        $user = Auth::user();
        $latitude = ($user->latitude) ? $user->latitude : $lat;
        $longitude = ($user->longitude) ? $user->longitude : $lng;
        $vendorType = $user->vendorType ? $user->vendorType : $type;
        $serviceAreaVendors = Vendor::vendorOnline()->select('id', 'show_slot');
        $vendors = [];
        if($vendorType){
            $serviceAreaVendors = $serviceAreaVendors->where($vendorType, 1);
        }
        if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){
            $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
            $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;

            if(!empty($latitude) && !empty($longitude) ){
                $serviceAreaVendors = $serviceAreaVendors->whereHas('serviceArea', function($query) use($latitude, $longitude){
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
        return $vendors;
    }

    public function loadDefaultImage(){
        $proxy_url = \Config::get('app.IMG_URL1');
        $image_path = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url('default/default_image.png');
        $image_fit = \Config::get('app.FIT_URl');
        $default_url = $image_fit .'300/300'. $image_path.'@webp';
        return $default_url;
    }

    protected function contains($point, $polygon){
        if($polygon[0] != $polygon[count($polygon)-1]){
            $polygon[count($polygon)] = $polygon[0];
            $j = 0;
            $oddNodes = false;
            $x = $point[1];
            $y = $point[0];
            $n = count($polygon);
            for ($i = 0; $i < $n; $i++){
                $j++;
                if ($j == $n){
                    $j = 0;
                }
                if ((($polygon[$i]['lat'] < $y) && ($polygon[$j]['lat'] >= $y)) || (($polygon[$j]['lat'] < $y) && ($polygon[$i]['lat'] >=
                    $y))){
                    if ($polygon[$i]['lng'] + ($y - $polygon[$i]['lat']) / ($polygon[$j]['lat'] - $polygon[$i]['lat']) * ($polygon[$j]['lng'] -
                        $polygon[$i]['lng']) < $x)
                    {
                        $oddNodes = !$oddNodes;
                    }
                }
            }
        }
        return $oddNodes;
    }

    public function sendNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        $SERVER_API_KEY = 'XXXXXX';
        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,
            ]
        ];
        $dataString = json_encode($data);
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
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
        dd($response);

    }

    protected function changeCurrency($curr, $price)
    {
        $currency = ConvertCurrency::convert('USD',[$curr], $price);
        return $currency[0]['convertedAmount'];
    }

    public function setMailDetail($mail_driver, $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption ){
        $config = array(
            'driver' => $mail_driver,
            'host' => $mail_host,
            'port' => $mail_port,

            'encryption' => $mail_encryption,
            'username' => $mail_username,
            'password' => $mail_password,
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
        );
        Config::set('mail', $config);
        $app = App::getInstance();
        // $app->register('Illuminate\Mail\MailServiceProvider');
        return  $config;
    }

    /*** check if cookie already exist */
    public function checkCookies($userid){
        if (isset(Auth::user()->system_user) && !empty(Auth::user()->system_user)) {
            $userFind = User::where('system_id', Auth::user()->system_user)->first();
            if($userFind){
                $cart = Cart::where('user_id', $userFind->id)->first();
                if($cart){
                    $cart->user_id = $userid;
                    $cart->save();
                }
                $userFind->delete();
            }
        }
        return $userid;
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
    public function getWallet($userid, $multiplier, $currency = 147){
        $wallet = Wallet::where('user_id', $userid)->first();
        if(!$wallet){
            $wallet = new Wallet();
            $wallet->user_id = $userid;
            $wallet->type = 1;
            $wallet->balance = 0;
            $wallet->card_id = $this->randomData('wallets');
            $wallet->card_qr_code = $this->randomBarcode('wallets');
            $wallet->meta_field = '';
            $wallet->currency_id = $currency;
            $wallet->save();
        }
        $balance = $wallet->balance * $multiplier;
        return $balance;
    }

    /* Create random and unique client code*/
    public function randomData($table){
        $random_string = substr(md5(microtime()), 0, 6);
        // after creating, check if string is already used
        while(\DB::table($table)->where('refferal_code', $random_string)->exists()){
            $random_string = substr(md5(microtime()), 0, 6);
        }
        return $random_string;
    }

    public function randomBarcode($table){
        $barCode = substr(md5(microtime()), 0, 14);
        while( \DB::table($table)->where('card_qr_code', $barCode)->exists()){
            $barCode = substr(md5(microtime()), 0, 14);
        }
        return $barCode;
    }

    /**     * check if cookie already exist     */
    public function userMetaData($userid, $device_type = 'web', $device_token = 'web', $currency = 147){
        $device = UserDevice::where('user_id', $userid)->first();
        if(!$device){
            $user_device[] = [
                'user_id' => $userid,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'access_token' => ''
            ];
            UserDevice::insert($user_device);
        }
        $loyaltyPoints = UserLoyaltyPoint::where('user_id', $userid)->first();
        if(!$loyaltyPoints){
            $loyalty[] = [
                'user_id' => $userid,
                'points' => 0
            ];
            UserLoyaltyPoint::insert($loyalty);
        }
        $wallet = Wallet::where('user_id', $userid)->first();
        if(!$wallet){
            $walletData[] = [
                'user_id' => $userid,
                'type' => 1,
                'balance' => 0,
                'card_id' => $this->randomData('wallets'),
                'card_qr_code' => $this->randomBarcode('wallets'),
                'meta_field' => '',
                'currency_id' => $currency,
            ];
            Wallet::insert($walletData);
        }
        return 1;
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
            $datetime = dateTimeInUserTimeZone($datetime, $timezone);
        }else{
            $datetime = Carbon::parse($order_vendor_created_at)->addMinutes($minutes);
            $datetime = dateTimeInUserTimeZone($datetime, $timezone);
        }
        if(Carbon::parse($datetime)->isToday()){
            if($time_format == '12'){
                $time_format = 'hh:mm A';
            }else{
                $time_format = 'HH:mm';
            }
            $datetime = Carbon::parse($datetime)->isoFormat($time_format);
        }
        return $datetime;
    }

    public function getNomenclatureName($searchTerm, $langId, $plural = true){
        $result = Nomenclature::with(['translations' => function($q) use($langId) {
                    $q->where('language_id', $langId);
                }])->where('label', 'LIKE', "%{$searchTerm}%")->first();
        if($result){
            $searchTerm = $result->translations->count() != 0 ? $result->translations->first()->name : ucfirst($searchTerm);
        }
        return $searchTerm;
    }

    /* doller compare amount */
    public function getDollarCompareAmount($amount, $customerCurrency='')
    {
        $user = Auth::user();
        $customerCurrency = $user->currency ? $user->currency : '';
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


    public function checkIfLastMileDeliveryOn()
    {

        $preference = ClientPreference::first();

        if( isset($preference)  && $preference->business_type == 'taxi'){

                if($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
                return $preference;
                else
                return false;
        }elseif(  isset($preference)  &&  $preference->business_type == 'laundry'){
                if($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url))
                return $preference;
                else
                return false;
        } else{
            if (isset($preference)  ) {
                if($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
                return $preference;
                else
                return false;
            }
        }
        return false;
    }

    public function driverDocuments()
    {
        try {
            $dispatch_domain = $this->checkIfLastMileDeliveryOn();
            if($dispatch_domain->business_type == 'taxi'){
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key, 'shortcode' => $dispatch_domain->pickup_delivery_service_key_code]]);
            } elseif($dispatch_domain->business_type == 'laundry'){
                $url = $dispatch_domain->laundry_service_key_url;
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->laundry_service_key, 'shortcode' => $dispatch_domain->laundry_service_key_code]]);
            } else{
                $url = $dispatch_domain->delivery_service_key_url;
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->delivery_service_key, 'shortcode' => $dispatch_domain->delivery_service_key_code]]);
            }
            $endpoint =$url . "/api/send-documents";
            $response = $client->post($endpoint);
            $response = json_decode($response->getBody(), true);
            return json_encode($response['data']);
        } catch (\Exception $e) {
            $data = [];
            $data['status'] = 400;
            $data['message'] = $e->getMessage();
            return $data;
        }
    }
    public function vendorTime($minutes){
        $hours = intdiv($minutes, 60).':'. ($minutes % 60);

        return $hours;
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


    public function sendTestMail(){
        $after7days = Carbon::now()->addDays(7)->toDateString();
        $now = Carbon::now()->toDateString();

        $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();

            if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
                $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);

                $client_name = $client->name;
                $mail_from = 'dineshk@codebrewinnovations.com';
                $sendto = 'dkdenni7@gmail.com';
                try{

                    Mail::send([], [],
                    function ($message) use($sendto, $client_name, $mail_from) {
                        $message->from($mail_from, $client_name);
                        $message->to($sendto)->subject('Upcoming Subscription Billing');
                        $message->setBody('TEst data', 'text/html'); // for HTML rich messages
                    });
                    $response['send_email'] = 1;
                    return count(Mail::failures());
                }
                catch(\Exception $e){
                    return response()->json(['data' => $e->getMessage()]);
                }
            }

    }


    /******************    ---- check Keys from order Panel keys -----   ******************/
    public function checkOrderPanelKeys(Request $request){


        $user =  User::where('is_panel_auth_user', 1)->first();
        if(!$user){
            $user =  User::first();
        }

        $token1 = new Token;
        $token = $token1->make([
            'key' => 'royoorders-jwt',
            'issuer' => 'royoorders.com',
            'expiry' => strtotime('+2 hour'),
            'issuedAt' => time(),
            'algorithm' => 'HS256',
        ])->get();
        $token1->setClaim('user_id', $user->id);

        $device = UserDevice::updateOrCreate(
            ['device_token' => 'dispather-login'],
            [
                'user_id' => $user->id,
                'device_type' => 'web',
                'access_token' => $token,
                'is_vendor_app' => 0
            ]
        );

        return response()->json([
        'status' => 200,
        'token' => $token,
        'message' => 'Valid Order Panel API keys']);
    }
    public function generateBarcodeNumber()
    {
        $random_string = substr(md5(microtime()), 0, 14);
        while (ProductVariant::where('barcode', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 14);
        }
        return $random_string;
    }

    public function getPanelDetail(Request $request)
    {
        try{
            if($request->inventory_code){
                $inventory_url = $request->inventory_url;
                $inventory_code = $request->inventory_code;


                $client = Client::select('database_name')->where('id', '>', 0)->first();
                if($client){
                    $client_prefrence = ClientPreference::where('id', '>', 0)->first();
                    $client_prefrence->inventory_service_key_url =  $inventory_url;
                    $client_prefrence->inventory_service_key_code =  $inventory_code;
                    $client_prefrence->update();

                    $data = ['key' => $client->database_name];
                    return response()->json([
                        'status' => 200,
                        'data' => $data,
                        'message' => 'success']);
                }

                return response()->json([
                        'status' => 400,
                        'message' => 'Order Panel Not found']);
            }
            return response()->json([
                'status' => 400,
                'message' => 'Invalid Code']);
        }catch(\Exception $e){
            return response()->json(['data' => $e->getMessage()]);
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


    public function getServiceArea($lat = 0, $lng = 0, $type = 'delivery')
    {
        $preferences = ClientPreference::where('id', '>', 0)->first();
        $user = Auth::user();
        $latitude = ($user->latitude) ? $user->latitude : $lat;
        $longitude = ($user->longitude) ? $user->longitude : $lng;
        $vendorType = $user->vendorType ? $user->vendorType : $type;
        $serviceAreaVendors = Vendor::vendorOnline()->select('id', 'show_slot');
        $vendors = [];
        if ($vendorType) {
            $serviceAreaVendors = $serviceAreaVendors->where($vendorType, 1);
        }
        if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {
            $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
            $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;
            if (!empty($latitude) && !empty($longitude)) {
                $serviceAreaVendors = ServiceArea::whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")
                             ->pluck('id');
                // if (isset($preferences->slots_with_service_area) && ($preferences->slots_with_service_area == 1)) {
                //     $slot_vendors = clone $serviceAreaVendors;
                //     $data = $slot_vendors->get();
                //     foreach ($data as $key => $value) {
                //         $serviceAreaVendors = $serviceAreaVendors->when(($value->show_slot == 0), function ($query) use ($latitude, $longitude) {
                //             return $query->where(function ($query1) use ($latitude, $longitude) {
                //                 $query1->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                //                     $q->select('id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                //                 })
                //                     ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                //                         $q->select('id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                //                     });
                //             });
                //         });
                //     }
                // }
            }
        }

        if ($serviceAreaVendors->isNotEmpty()) {
            foreach ($serviceAreaVendors as $value) {

                $vendors[] = $value;
            }
        }

        return $vendors;
    }

}
