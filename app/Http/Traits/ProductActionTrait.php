<?php
namespace App\Http\Traits;
use App\Models\{ProductRecentlyViewed,WebStylingOption,Product,Category, ClientCurrency, HomeProduct,ProductCategory,OrderVendorProduct,OrderProductRating,OrderProduct, VendorCategory, Vendor, SubscriptionInvoicesVendor};
use Illuminate\Support\Str;
use Auth;
use Session;
use Carbon\Carbon;
use DB;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Http\Controllers\Front\FrontController;
use DateTime;
use DateTimeZone;
use Carbon\CarbonPeriod;

trait ProductActionTrait{



    public function getRandomVendorIdsForHomePage($preferences, $type, $is_admin_vendor_rating = 0, $latitude, $longitude,$action='2')
    {
        try
        {
            $vendors = Vendor::vendorOnline()->select('id')->where('status', 1)->where($type, 1);
            if (($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
                    $point = new Point($longitude, $latitude);
                    $vendors->whereHas('serviceArea', function ($query) use ($point) {
                        $query->whereRaw("ST_Contains(service_areas.polygon, ST_GeomFromText(?))", [$point->toWKT()]);
                    });
            }

            if($is_admin_vendor_rating == 1){
                $vendors = $vendors->orderBy('admin_rating', 'DESC');
            }else{
                $vendors = $vendors->inRandomOrder();
            }
            return $vendors->pluck('id')->toArray();
        }
        catch (\Exception $e) {
            return [];
        }
    }

    public function getLastProductOrdered()
    {
        try
        {
            $user_id = Auth::user()->id;
            // $user_id = 233;
            $vendors =  OrderProduct::distinct('product_id')->whereHas('order_vendor', function($q) use ($user_id){
                $q->where('user_id', $user_id);
            })->take(10);
            // ->inRandomOrder();
            return $vendors->pluck('product_id')->toArray();
        }
        catch (\Exception $e) {
            return [];
        }
    }



         /**
     * getRecentProductIds
     *
     * @param  mixed $user_id
     * @return void
     */
    public function getRecentProductIds()
    {
        try {
            $query =  ProductRecentlyViewed::query();
            if(Auth::check()){
                $query =  $query->where('user_id', Auth::user()->id);
            } else{
                $query = $query->where('token_id', session()->get('_token'));
            }
            $return = $query->orderBy('updated_at','DESC')->take(6)->pluck('product_id');
            if(sizeof($return) > 0){
                $return = $return->toArray();
            }
            return $return;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }


    }
    /**
     * RecentView
     *
     * @param  mixed $p_id
     * @return void
     */
    public function RecentView($p_id)
    {
        try {
            $token_id = session()->get('_token');
            $user_id = 0;
            $update_by['product_id'] = $p_id;
             if(Auth::check()){
                $user_id = Auth::user()->id;
                $update_by['user_id'] = $user_id;
            } else{
                $update_by['token_id'] = $token_id;
            }
            $RecentlyViewed = [
                'product_id' => $p_id,
                'token_id' => $token_id,
                'user_id' => $user_id,
                'updated_at' => Carbon::now()
            ];
            ProductRecentlyViewed::updateOrCreate(
                    $update_by
                ,$RecentlyViewed);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }


    }

    /**
     * LoginActionRecentView
     *
     * @param  mixed $user_id
     * @return void
     */
    public function LoginActionRecentView($user_id)
    {
        try {
            ProductRecentlyViewed::where('token_id', session()->get('_token'))->update(['user_id' => $user_id, 'token_id' => '']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }


    /**
     * LoginActionRecentView
     *
     * @param  mixed $user_id
     * @return void
     */
    public function checkTemplateForAction($t_id)
    {
        try {
            $set_template = WebStylingOption::where('is_selected', 1)->first();
            $val = 0;
            if(isset($set_template)  && $set_template->template_id == $t_id){
                $val = 1;
            }
            return $val;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function productvendorProducts($venderIds, $langId, $currency = 'USD', $where = '', $type,$p_dim,$is_paginate = '')
    {
        try {
                $recent_ids = $this->getRecentProductIds();
                $rc_ids = [];
                if(sizeof($recent_ids) <= 0){
                    return [];
                }
                $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 30;
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
                        $q->select('sku', 'product_id', 'quantity', 'price', 'barcode', 'compare_at_price');
                        $q->groupBy('product_id');
                    },
                ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only');
                if ($where !== '') {
                    $products = $products->where($where, 1);
                }
                $products = $products->whereIn('id', $recent_ids);

                $pndCategories = Category::where('type_id', 7)->pluck('id');
                // if (is_array($venderIds)) {
                //     $products = $products->whereIn('vendor_id', $venderIds);
                // }
                if ($pndCategories) {
                    $products = $products->whereNotIn('category_id', $pndCategories);
                }
                $products = $products->whereHas('vendor', function($q) use ($type,$venderIds){
                            $q->where('status',1);
                            $q->whereIn('id',$venderIds);
                            $q->where($type, 1);
                        })->where('is_live', 1);
                if($is_paginate ==1){
                    $products  =  $products->paginate($pagiNate);
                    foreach ($products as $key => $value) {
                        $multiply = Session::get('currencyMultiplier') ?? 1;
                        $title = $value->translation->first() ? $value->translation->first()->title : $value->sku;
                        $value->image_url = $value->media->first() ? $value->media->first()->image->path['proxy_url'] . $p_dim . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();

                        $value->title = Str::limit($title, 18, '..');
                        $value->averageRating = number_format($value->averageRating, 1, '.', '');
                        $value->inquiry_only = $value->inquiry_only;
                        $value->vendor_name = $value->vendor ? $value->vendor->name : '';
                        $value->price = Session::get('currencySymbol') . ' ' . (decimal_format(@$value->variant->first()->price??0 * $multiply,','));
                        $value->compare_at_price = Session::get('currencySymbol') . ' ' . (decimal_format(@$value->variant->first()->compare_at_price??0 * $multiply,','));
                        $value->compare_price_numeric = decimal_format(@$value->variant->first()->compare_at_price??0 * $multiply,',');
                        $value->category =  (@$value->category->categoryDetail->translation) ? @$value->category->categoryDetail->translation->first()->name : @$value->category->categoryDetail->slug;
                    }
                    return $products;
                }else{
                    $products  =  $products->take(10)->inRandomOrder()->get();
                }

                $productArray = [];
                if (!empty($products)) {

                    foreach ($products as $key => $value) {
                        $multiply = Session::get('currencyMultiplier') ?? 1;
                        $title = $value->translation->first() ? $value->translation->first()->title : $value->sku;
                        $image_url = $value->media->first() ? $value->media->first()->image->path['proxy_url'] . $p_dim . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                        $productArray[] = array(
                            'tag_title' => $products_tag_title??0,
                            'image_url' => $image_url,
                            'sku' => $value->sku,
                            'title' => Str::limit($title, 18, '..'),
                            'url_slug' => $value->url_slug,
                            'averageRating' => number_format($value->averageRating, 1, '.', ''),
                            'inquiry_only' => $value->inquiry_only,
                            'vendor_name' => $value->vendor ? $value->vendor->name : '',
                            'vendor' => $value->vendor,
                            'price' => Session::get('currencySymbol') . ' ' . (decimal_format(@$value->variant->first()->price??0 * $multiply,',')),
                            'compare_price' =>@$value->variant->first()->compare_at_price * $multiply,
                            'compare_price_numeric' =>@$value->variant->first()->compare_at_price * $multiply,
                            'price_numeric' =>@$value->variant->first()->price * $multiply,
                            'category' => (@$value->category->categoryDetail->translation) ? @$value->category->categoryDetail->translation->first()->name : @$value->category->categoryDetail->slug
                        );

                    }
                }

            return $productArray;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }

     public function longTermServiceProducts($long_term_vendors, $additionalPreference, $langId, $currency = '', $where = '', $type,$p_dim ='260/100',$requestFrom='web' )
    {
        $venderIds = Vendor::where('status', 1)->whereIn('id', $long_term_vendors)
        ->whereHas('long_term_products')->pluck('id');
        $products = Product::byLongTermProductCategoryServiceType($type)->byProductLongTerm()->with([
            'vendor','LongTermProducts.product',
            'media' => function ($q) {
                $q->groupBy('product_id');
            }, 'media.image',
            'translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
            },
            'variant' => function ($q) use ($langId) {
                $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                $q->groupBy('product_id');
            },
        ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only','is_long_term_service')
        ->whereHas('LongTermProducts.product', function($q){$q->where('is_live',1); });

        if ($where !== '') {
            $products = $products->where($where, 1);
        }


        $products = $products->whereHas('vendor', function($q) use ($type,$venderIds){
                    $q->where('status',1);
                    $q->whereIn('id',$venderIds);
                    $q->where($type, 1);
                })->take(10)->inRandomOrder()->get();


        $return = [];
        if (!empty($products)) {
            // return response from to app
            if($requestFrom == 'app'){
                foreach ($products as $key => $value) {
                    foreach ($value->variant as $k => $v) {
                        $value->variant[$k]->multiplier = $currency ? $currency->doller_compare : 1;
                    }
                }
                return $products;
            }

            foreach ($products as $key => $value) {
                $multiply = Session::get('currencyMultiplier') ?? 1;
                $title = $value->translation->first() ? $value->translation->first()->title : $value->sku;
                $image_url = $value->media->first() ? $value->media->first()->image->path['proxy_url'] . $p_dim . $value->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                $return[] = array(
                    'tag_title' => $title??'0',
                    'image_url' => $image_url,
                    'sku' => $value->sku,
                    'title' => Str::limit($title, 18, '..'),
                    'url_slug' => $value->url_slug,
                    'averageRating' => number_format($value->averageRating, 1, '.', ''),
                    'inquiry_only' => $value->inquiry_only,
                    'vendor_name' => $value->vendor ? $value->vendor->name : '',
                    'vendor' => $value->vendor,
                    'price' => @$additionalPreference['is_token_currency_enable'] ? "<i class='fa fa-money' aria-hidden='true'></i> ".getInToken(@$value->variant->first()->price * $multiply) : Session::get('currencySymbol') . ' ' . (decimal_format(@$value->variant->first()->price * $multiply)),
                    'category' => ''
                );
            }
        }
       return $return;

    }

    public function getProductsId($type='', $vendorWhereIN = '', $whereProductType = '')
    {
        try
        {
            $product_ids = [];

            $single_category_products = [];

            if (($type == 'single_category_products' || $type == 'selected_products')) {
                $single_category_products = HomeProduct::whereSlug($type)->latest()->first();
            }

            if($type == 'single_category_products' && !empty($single_category_products)){
                $product_ids = ProductCategory::select('product_id')->where('category_id', $single_category_products->category_id)->take(10)->pluck('product_id')->toArray();

            } elseif($type == 'selected_products' && !empty($single_category_products)){
                $product_ids = json_decode($single_category_products->products);
            } elseif($type == 'popular_products'){
                $most_sold = OrderVendorProduct::selectRaw('id, product_id, count(product_id) as total')->whereHas('statusDelievered')->groupBy('product_id')->orderBy('total', 'DESC')->take(5)->get()->pluck('product_id')->toArray();
                $most_viewed = ProductRecentlyViewed::selectRaw('id, product_id, count(product_id) as total')->groupBy('product_id')->orderBy('total', 'DESC')->take(5)->get()->pluck('product_id')->toArray();
                //$product_ids = $most_sold->merge($most_viewed);
                $product_ids = array_merge($most_sold, $most_viewed);
            } elseif($type == 'top_rated_products'){
                $product_ids = OrderProductRating::selectRaw('id, product_id, count(product_id) as total')->groupBy('product_id')->orderBy('total', 'DESC')->take(10)->get()->pluck('product_id')->toArray();
            } elseif($type == 'recent_viewed'){
                $product_ids = $this->getRecentProductIds();
            }elseif($type == 'all' || $type == 'is_new' || $type == 'is_featured'|| $type == 'on_sale' || $type = 'spotlight_deals'){
                $completeWhere = ' ';
                if($type != 'all'){
                    $completeWhere = ' AND `products`.`'.$type.'` = 1';
                }
                if($type = 'on_sale' || $type = 'spotlight_deals' ){
               
                    $completeWhere = "";
                }
                $raw_query = "SELECT
                    `products`.`id`
                    FROM
                        `products` LEFT JOIN   `categories` as `categories` ON `products`.`category_id` = `categories`.`id`  AND `categories`.`type_id` != 7
                        LEFT JOIN   `vendors` as `vendors` ON `vendors`.`id` = `products`.`vendor_id` AND `vendors`.`status` = 1
                    WHERE
                        `products`.`deleted_at` IS NULL
                            AND `vendors`.`status` = 1
                            AND `products`.`is_live` = 1

                            $completeWhere

                            $vendorWhereIN

                            $whereProductType

                            GROUP BY `products`.`id`";
                            // LIMIT 6";

                $products = DB::select( DB::raw($raw_query));

                $products = collect($products);
                $product_ids = $products->pluck('id')->toArray();
            }

            return $product_ids;
        }
        catch (\Exception $e) {
            return [];
        }
    }

    public function vendorProducts($venderIds, $langId, $currency = 'USD', $where = '', $type = '',$Products_title = '', $p_dim = '',$getSubCatIds='', $preferences = NULL, $categoryTypes = NULL)
    {
        try
        {
            $user = Auth::user();
            $user_currency  = $user->currency ?? $currency;
            $clientCurrency = ClientCurrency::where('currency_id', $user_currency)->first();
            $comparePrice = $clientCurrency->doller_compare ?? 1.00;

            $vendorWhereIN = ' ';
            $getSubCatIdsIn = ' ';
            $completeWhere = ' ';
            $whereProductType = ' ';
            if(!empty($venderIds)){
                $venid = implode(',',$venderIds);
                $vendorWhereIN = ' AND `vendors`.`id` IN ('.$venid.')';
            } else{
                $venid = '0';
            }
            $vendorWhereIN = ' AND `vendors`.`id` IN ('.$venid.')';
            if($where!=='all' && $where!=='on_sale'){

                    if($where =='single_category_products' || $where == 'selected_products' || $where == 'popular_products' || $where == 'top_rated_products' ||  $where == 'recent_viewed'){
                        $single_category_product_ids = $this->getProductsId($where);

                        if(count($single_category_product_ids) > 0){
                            $single_category_product_ids = @implode(',',$single_category_product_ids);
                            if($single_category_product_ids){
                                $completeWhere = ' AND  `products`.`id` IN  ('.$single_category_product_ids.')';
                            }


                        }
                    } else {
                        $completeWhere = ' AND `products`.'.$where.' = 1';
                    }
            }
            $whereComparePriceNotNull = '';
            // if($where == 'on_sale'){
            //     $whereComparePriceNotNull = ' and `product_variant`.`compare_at_price` > 0  ';
            // }
            //Check product of selected category type
            $whereProductType = '';
            if(!empty($categoryTypes)){
                $categoryTypesArray = $categoryTypes;
            }else{
                $categoryTypesArray = @getServiceTypesCategory($type, $preferences);
            }

            if(!empty($categoryTypesArray)){
                $categoryTypesArray = implode(',',$categoryTypesArray);
                $whereProductType = ' and `categories`.`type_id`  IN ('.$categoryTypesArray.') ';
            }

            if (is_array($getSubCatIds) && count($getSubCatIds) > 0) {
                $subCatIdsArray = implode(',',$getSubCatIds);
                $getSubCatIdsIn = " AND `products`.`category_id` IN ($subCatIdsArray)";
            }


            $single_category_product_ids = $this->getProductsId($where, $vendorWhereIN, $whereProductType);

            if(count($single_category_product_ids) > 0 && $where!=='recent_viewed'){
                shuffle($single_category_product_ids);
                $random_numbers = array_slice($single_category_product_ids, 0, 6);
                $single_category_product_ids = @implode(',',$random_numbers);
                if($single_category_product_ids){
                    $completeWhere .= ' AND  `products`.`id` IN  ('.$single_category_product_ids.')';
                }
            }


            $raw_query = "SELECT
            `products`.`id`,
            `products`.`description`,
            `products`.`is_live`,
            `products`.`sku`,
            `products`.`url_slug`,
            `products`.`weight_unit`,
            `products`.`weight`,
            `products`.`vendor_id`,
            `products`.`has_variant`,
            `products`.`has_inventory`,
            `products`.`sell_when_out_of_stock`,
            `products`.`requires_shipping`,
            `products`.`Requires_last_mile`,
            `products`.`inquiry_only`,
            `products`.`updated_at`,
            `products`.`is_featured`,
            `products`.`is_new`,
            `products`.`category_id`,
            `products`.`calories`,
            `categories`.`id` AS `category_id`,
            `categories`.`type_id`,
            `product_images`.`media_id`,
            `vendor_media`.`path`,
            `product_translation`.`title`,
            `product_translation`.`body_html`,
            `product_translation`.`meta_title`,
            `product_translation`.`meta_keyword`,
            `product_translation`.`meta_description`,
            `vendors`.`address`,
            `product_translation`.`language_id`,
            CAST(`product_variant`.`compare_at_price` * $comparePrice AS DECIMAL(10,2)) AS `compare_price_numeric`,
            CAST(`product_variant`.`price` * $comparePrice AS DECIMAL(10,2)) AS `price_numeric`,
            `category_translation`.`name` AS `category_name`,
            `category_translation`.`meta_title` AS `category_meta_title`,
            `category_translation`.`meta_keywords` AS `category_meta_keyword`,
            `category_translation`.`meta_description` AS `category_meta_description`,
            CAST((`products`.`averageRating`) AS DECIMAL(2,1)) AS `averageRating`,
            CASE
                WHEN `product_variant`.`compare_at_price` > 0 THEN 
                    CAST((`product_variant`.`compare_at_price` - `product_variant`.`price`) / `product_variant`.`compare_at_price` * 100 AS DECIMAL(12,2))
                ELSE 0
            END AS `discount_percentage`,
            `vendors`.`name` AS `vendor_name`,
            `vendors`.`id` AS `vendor_id`,
            `vendors`.`slug` AS `vendor_slug`,
            IFNULL(`products`.`is_long_term_service`, 0) AS `is_long_term_service`,
        
            -- Retrieve product attributes as a JSON object
            (
                SELECT JSON_OBJECTAGG(`key_name`,
                    CASE
                        WHEN `pa`.`attribute_option_id` = `pa`.`key_value`
                        THEN (SELECT `title` FROM `attribute_options` WHERE `id` = `pa`.`attribute_option_id`)
                        ELSE `pa`.`key_value`
                    END
                )
                FROM `product_attributes` AS `pa`
                WHERE `pa`.`product_id` = `products`.`id`
            ) AS `product_attributes`,
        
            `product_variant`.`id` AS `variant_id` -- Include variant_id
        
        FROM `products`
        LEFT JOIN `categories` AS `categories` ON `products`.`category_id` = `categories`.`id` AND `categories`.`type_id` != 7
        LEFT JOIN `product_images` AS `product_images` ON `product_images`.`product_id` = `products`.`id`
        LEFT JOIN `vendors` AS `vendors` ON `vendors`.`id` = `products`.`vendor_id` AND `vendors`.`status` = 1
        LEFT JOIN `vendor_media` AS `vendor_media` ON `vendor_media`.`id` = `product_images`.`media_id`
        LEFT JOIN `product_translations` AS `product_translation` ON `product_translation`.`product_id` = `products`.`id` AND `product_translation`.`language_id` = $langId
        LEFT JOIN `product_variants` AS `product_variant` ON `product_variant`.`product_id` = `products`.`id`
        LEFT JOIN `category_translations` AS `category_translation` ON `category_translation`.`category_id` = `products`.`category_id` AND `category_translation`.`language_id` = $langId
        
        WHERE
            `products`.`deleted_at` IS NULL
            AND `vendors`.`status` = 1
            AND `products`.`is_live` = 1
            $whereComparePriceNotNull
            $completeWhere
            $vendorWhereIN
            $getSubCatIdsIn
            $whereProductType
        
        GROUP BY `products`.`id`, `product_variant`.`id` -- Add variant_id to GROUP BY
        
        LIMIT 6";
        


            $returnArray = DB::select( DB::raw($raw_query));
            // //$collectionproducts = collect($products)->unique('id');
            // if(empty($single_category_product_ids)){
            //     //$collectionproducts = $collectionproducts->random(10);
            // }

            // $returnArray = $products;

            return $returnArray;
        }
        catch (\Exception $e) {
            return [];
        }
    }

    public function getEvenOddTime($time) {
        return ($time % 5 === 0) ? $time : ($time - ($time % 5));
    }

    public function getVendorForHomePage($preferences, $vendor_title, $timezone, $is_admin_vendor_rating = '', $type, $language_id, $latitude , $longitude, $vendor_ids = [], $set_template = NULL,$venderFilterOpenClose=null,$venderFilterbest=null,$nearest_vendor=0)
    {
        try
        {
            $mytime = Carbon::now()->setTimezone($timezone);
            $day = $mytime->dayOfWeek+1;
            $current_time = $mytime->toTimeString();
            $current_date = Carbon::now()->format('Y-m-d');
            $multiply = Session::get('currencyMultiplier') ?? 1;
            $currencySymbol = Session::get('currencySymbol');
            $earth_radius = 6371;
            if( (empty($latitude)) && (empty($longitude)) ){
                $latitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_latitude) : 0;
                $longitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_longitude) : 0;
            }
            //------based on hyper location------------
            if (($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                $unit_abbreviation = ($distance_unit == 'mile') ? 'miles' : 'km';
                $earth_radius = ($distance_unit == 'mile') ? 3959 : 6371;
                $distance_to_time_multiplier = ($preferences->distance_to_time_multiplier > 0) ? $preferences->distance_to_time_multiplier : 2;
            }

            $selectQuery = "`vendors`.`id`,
                `vendors`.`name`,
                `vendors`.`address`,
                `vendors`.`order_pre_time`,
                `vendors`.`logo`,
                `vendors`.`banner`,
                `vendors`.`slug`,
                `vendors`.`show_slot`,
                `vendors`.`admin_rating`,
                `vendors`.`rating`,
                `vendors`.`closed_store_order_scheduled`,
                `vendors`.`delivery_fee_minimum`,
                (
                    SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END
                    FROM user_vendor_wishlists
                    WHERE user_vendor_wishlists.vendor_id = vendors.id
                    AND user_vendor_wishlists.user_id = :user_id
                ) AS is_wishlist,

                (SELECT (CASE WHEN `promo`.`promo_type_id` = 1 THEN CONCAT(CAST(`promo`.`amount` AS DECIMAL(2,0)), '% OFF | use ', `promo`.`name`) ELSE CONCAT('FLAT ', '$currencySymbol', '', CAST(`promo`.`amount`* $multiply AS DECIMAL(2,0)), ' OFF | use ', `promo`.`name`) END) FROM `promocodes` AS `promo` left join `promocode_details` AS `promo_info` ON `promo_info`.`promocode_id` = `promo`.`id` WHERE `promo`.`restriction_on` = 1 AND ((`promo`.`restriction_type` = 0 AND `promo_info`.`refrence_id` = `vendors`.`id`) OR (`promo`.`restriction_type` = 1 AND `promo_info`.`refrence_id` != `vendors`.`id`)) AND `promo`.`expiry_date` >= $current_date AND `promo`.`amount` > 0 ORDER BY `promo`.`amount` DESC LIMIT 1) as `promo_discount`,
                (select MIN(`price`) FROM `product_variants` AS `pv` JOIN `products` AS `pr` ON `pr`.`id` = `pv`.`product_id` where `pr`.`vendor_id` = `vendors`.`id`) AS `minimum_price`,
                GROUP_CONCAT(DISTINCT `category_translations`.`name` SEPARATOR ', ') AS `categoriesList`,
                (SELECT count(`order_vendors`.`id`) FROM `order_vendors` WHERE `order_vendors`.`vendor_id` = `vendors`.`id`) AS `selling_count`,
                (SELECT CONCAT(`vendor_slot_dates`.`start_time`, '##', `vendor_slot_dates`.`end_time`) FROM `vendor_slot_dates` WHERE `vendor_slot_dates`.`vendor_id` = `vendors`.`id` LIMIT 0,1) AS `slotdate_start_end_time`,
                (SELECT CONCAT(`vendor_slots`.`start_time`, '##', `vendor_slots`.`end_time`) FROM `vendor_slots` left join `slot_days` on `vendor_slots`.`id` = `slot_days`.`slot_id` WHERE `vendor_slots`.`vendor_id` = `vendors`.`id` AND  `slot_days`.`day` = ".$day." AND `vendor_slots`.`start_time` < CAST('".$current_time."' AS time) AND `vendor_slots`.`end_time` > CAST('".$current_time."' AS time)  LIMIT 0,1) AS `slot_start_end_time`,
                ROUND( $earth_radius * acos(cos(radians(" . $latitude . "))
                                            * cos(radians(`vendors`.`latitude`))
                                            * cos(radians(`vendors`.`longitude`) - radians(" . $longitude . "))
                                            + sin(radians(" .$latitude. "))
                                            * sin(radians(`vendors`.`latitude`))),2
                                            ) AS `lineOfSightDistance`";

                $joinQuery  = " LEFT JOIN `vendor_categories` ON `vendor_categories`.`vendor_id`= `vendors`.`id` ";
                $joinQuery .= " LEFT JOIN `categories` ON `categories`.`id`= `vendor_categories`.`category_id` ";
                $joinQuery .= " LEFT JOIN `category_translations` ON `category_translations`.`category_id`= `categories`.`id` AND `category_translations`.`language_id` = $language_id ";
                $whereQuery  = " where `vendors`.`status` = 1 AND `vendor_categories`.`status` = 1";

            $whereInQuery = '';

            if(!empty($vendor_ids)){
                $whereInQuery = " AND `vendors`.`id` IN (".implode(',', $vendor_ids).") ";
            }

            $mainQuery = "SELECT $selectQuery FROM `vendors` $joinQuery $whereQuery $whereInQuery";

            $mainQuery .= " GROUP BY `vendors`.`id` ORDER BY `lineOfSightDistance` ASC";

            if ($vendor_title == "best_sellers") {
                $mainQuery.= " ORDER BY `selling_count` DESC";
            }
            if (($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude) && $vendor_title == "trending_vendors") {
                $mainQuery.= " ORDER BY `lineOfSightDistance` DESC";
            }else{
                //-------------if admin rating is on otherwise random---------------
                if($is_admin_vendor_rating == 1 && $venderFilterbest == 1){
                    $mainQuery.= " ORDER BY admin_rating DESC";
                }
            }

            if (($latitude) && ($longitude) && $nearest_vendor == 1) {
                $mainQuery.= " ORDER BY `lineOfSightDistance` ASC";
            }


            if ($preferences->rating == 'asc' || $preferences->rating == 'desc') {
                $mainQuery.= " ORDER BY `rating` ".$preferences->rating;
            }

            //if(!empty($set_template) && $set_template->template_id != 3){
                $mainQuery .= " LIMIT 10";
            //}

            $vendors = DB::select( DB::raw($mainQuery),['user_id' => Auth::id()]);

            $vendor_ids = [];
            $user = Auth::user();
            $timezone = isset($user) && $user->timezone ?  $user->timezone : 'Asia/Kolkata';
            $start_date = new DateTime("now", new  DateTimeZone($timezone) );
            $start_date =  $start_date->format('Y-m-d');
            $end_date = Date('Y-m-d', strtotime('+13 days'));

            foreach ($vendors as $key => $value) {
                $vendor_ids[] = $value->id;
                // get or update rating
                $value->vendorRating = number_format($value->rating, 1);


                if(($preferences) && ($preferences->is_hyperlocal == 1))
                {
                    if($type == 'delivery')
                    {
                        $pretime =  number_format(floatval($value->order_pre_time), 0, '.', '') + number_format(($value->lineOfSightDistance * $distance_to_time_multiplier), 0, '.', '');
                    }else{
                        $pretime =  number_format(floatval($value->order_pre_time), 0, '.', '') + 0;
                    }
                    $pretime = $this->getEvenOddTime($pretime);
                    $value->deliveryTime = $pretime;

                    if($pretime >= 60){
                        $value->timeofLineOfSightDistance =  '~ '.$this->vendorTime($pretime) .' '. __('hour');
                    }else{
                        $value->timeofLineOfSightDistance = $pretime . '-' . (intval($pretime) + 5).' '. __('min');
                    }
                    $value->lineOfSightDistance = $value->lineOfSightDistance.' '.$unit_abbreviation;
                }

                $value->type_title = $value->categoriesList;

                $value->is_vendor_closed = 0;
                if($value->show_slot == 0){
                    if(empty($value->slotdate_start_end_time) && empty($value->slot_start_end_time)){
                        $value->is_vendor_closed = 1;
                    }else{
                        $value->is_vendor_closed = 0;
                        if(!empty($value->slotdate_start_end_time)){
                            $slotdate_start_end_time = explode('##', $value->slotdate_start_end_time);
                            if($slotdate_start_end_time[0]!='' && $slotdate_start_end_time[1]!=''){
                                $value->opening_time  = date('g:i A',strtotime($slotdate_start_end_time[0]));
                                $value->closing_time = date('g:i A',strtotime($slotdate_start_end_time[1]));
                            }

                        }elseif(!empty($value->slot_start_end_time)){
                            $slot_start_end_time = explode('##', $value->slot_start_end_time);
                            if($slot_start_end_time[0]!='' && $slot_start_end_time[1]!=''){
                                $value->opening_time  = date('g:i A',strtotime($slot_start_end_time[0]));
                                $value->closing_time = date('g:i A',strtotime($slot_start_end_time[1]));
                            }
                        }
                    }
                }

                $slotsDate = 0;
                $value->date_with_slots = [];
                if($value->closed_store_order_scheduled == 1){
                    $slotsDate = findSlot('',$value->id,$type );
                    $value->delaySlot = $slotsDate;
                    $value->closed_store_order_scheduled = (($slotsDate)?$value->closed_store_order_scheduled:0);

                    if(!empty($slotsDate)){
                        $period = CarbonPeriod::create($start_date, $end_date);
                        $slotWithDate = [];
                        foreach($period as $key => $date){
                            $slotDate = trim(date('Y-m-d', strtotime($date)));
                            $slots = showSlot($slotDate,$value->id,'delivery');
                            if(!empty($slots)){
                                $slotData['date']  =  $slotDate;
                                $slotData['slots'] = $slots;
                                $slotWithDate[] = $slotData;
                            }
                        }
                        $value->date_with_slots = $slotWithDate;
                    }
                }else{
                    $value->delaySlot = 0;
                    $value->closed_store_order_scheduled = 0;
                }

                if($value->closed_store_order_scheduled == 1){
                    $slotsDate = findSlot('',$value->id,$type );
                    $value->closed_store_order_scheduled = (($slotsDate)?$value->closed_store_order_scheduled:0);

                }else{
                    $value->closed_store_order_scheduled = 0;
                }
            }
            $keyToFilter = 'is_vendor_closed';
            $valueToFilter = $venderFilterOpenClose;
            // $my_array = ['foo' => 1, 'bar' => 'baz', 'hello' => 'wld'];
            if($venderFilterOpenClose === 1 ){
                $filteredArray = array_filter($vendors, function($item) use ($keyToFilter, $valueToFilter) {
                    return isset($item->$keyToFilter) && $item->$keyToFilter == $valueToFilter;
                });
                $filtered = array_values($filteredArray);
            }else {
                $filtered = $vendors;
            }
            return $filtered;
        }
        catch (\Exception $e) {
            return [];
        }
    }

    public function getBrandsForHomePage($language_id, $field_status)
    {
        try
        {
            $frontController = new FrontController();
            $navCategories = $frontController->categoryNav($language_id,true);
            $category_ids = implode(",", $navCategories);
            $redirect_url = route('brandDetail', "brands_id");
            $mainQuery = "SELECT `br`.`id`,
            `br`.`image`,
            `br`.`title`,
            REPLACE('".$redirect_url."', 'brands_id', `br`.`id`) AS `redirect_url`,
            (CASE WHEN `bt`.`title` IS NULL THEN `br`.`title` ELSE `bt`.`title` END) AS `translation_title`
            FROM `brands` AS `br`
            LEFT JOIN `brand_categories` AS `bc` on `bc`.`brand_id` = `br`.id
            LEFT JOIN `brand_translations` AS `bt` ON `bt`.`brand_id` = `br`.`id` AND `bt`.`language_id` = $language_id

            WHERE `br`.`status` !=$field_status AND `bc`.`category_id` in ($category_ids)
            GROUP BY `br`.`id`";

            $brands = DB::select( DB::raw($mainQuery));
            return $brands;
        }
        catch (\Exception $e) {
            return [];
        }
    }


    public function getBannersForHomePage($client_preferences, $banner_type, $latitude, $longitude)
    {
        try
        {
            $carbon_now = Carbon::now();

            if($banner_type == 'banners'){
                $banner_table                   = 'banners';
                $banner_service_areas_table     = 'banner_service_areas';
                $service_area_for_banners_table = 'service_area_for_banners';
                $type                           = 1;
            }else{
                $banner_table                   = 'mobile_banners';
                $banner_service_areas_table     = 'mobile_banner_service_areas';
                $service_area_for_banners_table = 'service_area_for_banners';
                $type                           = 2;
            }

            $mainQuery = "SELECT
                `ba`.`image`,
                `ba`.`name`,
                `ba`.`link`,
                `ba`.`link_url`,
                `ct`.`slug` AS `category_slug`,
                `vn`.`slug` AS `vendor_slug`
                FROM $banner_table AS `ba`";

            $joinQuery = "LEFT JOIN `categories` AS `ct` ON `ct`.`id` = `ba`.`redirect_category_id` AND `ct`.`deleted_at` IS NULL ";
            $joinQuery.= "LEFT JOIN `vendors` AS `vn` ON `vn`.`id` = `ba`.`redirect_vendor_id` ";

            $mainQuery.= " $joinQuery WHERE `ba`.`status` =1 AND `ba`.`validity_on` = 1 AND (`ba`.`start_date_time` is null or (date(`ba`.`start_date_time`) <= '".$carbon_now."' and date(`ba`.`end_date_time`) >= '".$carbon_now."'))  ";

            if(isset($client_preferences->is_service_area_for_banners) && ($client_preferences->is_service_area_for_banners == 1) && ($client_preferences->is_hyperlocal == 1) && (!empty($latitude) && !empty($longitude))){

                $point = new Point($longitude, $latitude);
                //$mainQuery .= " HAVING (SELECT `id` FROM `$banner_service_areas_table` AS `bsa` where `ba`.`id` = `bsa`.`banner_id` AND EXISTS (select `id` from `$service_area_for_banners_table` AS `safb` WHERE `bsa`.`service_area_id` = `safb`.`id` AND ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT($latitude $longitude)')) and `type` = $type) > 0) > 0 ";
                $mainQuery .= " HAVING (SELECT `id` FROM `$banner_service_areas_table` AS `bsa` where `ba`.`id` = `bsa`.`banner_id` AND EXISTS (select `id`,'polygon' from `$service_area_for_banners_table` AS `safb` WHERE `bsa`.`service_area_id` = `safb`.`id` AND ST_Contains(safb.polygon, ST_GeomFromText('".$point->toWKT()."')) and `type` = $type) > 0) > 0 ";
            }

            $mainQuery.= " ORDER BY `ba`.`sorting` ASC";


            $banners = DB::select( DB::raw($mainQuery));
            return $banners;
        }
        catch (\Exception $e) {
            return [];
        }
    }

    public function getVendorIds($preferences, $latitude, $longitude, $vendor_ids=[], $enable_vendor_title)
    {
        try
        {
            if($enable_vendor_title == "trending_vendors")
            {
                $now = Carbon::now()->toDateTimeString();
                $whereIn = " ";
                if(!empty($vendor_ids)){
                    $whereIn = " AND `siv`.`vendor_id` IN (".implode(',', $vendor_ids).") ";
                }
                $raw_query = "SELECT `siv`.`vendor_id` FROM `subscription_invoices_vendor` AS `siv`
                JOIN `subscription_invoice_features_vendor` AS `sifv` ON `sifv`.`subscription_invoice_id` = `siv`.`id`
                WHERE `sifv`.`feature_id` = 1
                $whereIn
                AND `siv`.`end_date` >= $now
                GROUP BY `siv`.`vendor_id`";
                $vendors = DB::select( DB::raw($raw_query));
                $vendors = collect($vendors);
                return $vendor_ids->pluck('vendor_id')->toArray();
            }else{
                return collect($vendor_ids)->toArray();
            }
        }
        catch (\Exception $e) {
            return [];
        }
    }


    public function vendorProducts_2($enable_layout, $venderIds, $langId, $currency = 'USD', $type = '', $p_dim = '', $preferences = NULL, $categoryTypes = NULL)
    {
        try
        {
            $vendorWhereIN = ' ';
            $whereProductType = ' ';
            $where = array('on_sale' => 'all', 'new_products' => 'is_new', 'featured_products' => 'is_featured');

            $image_url = get_file_path('', "FILL_URL", $height="250", $width="240");
            if(!empty($venderIds)){
                $venid = implode(',',$venderIds);
                $vendorWhereIN = ' AND `vendors`.`id` IN ('.$venid.')';
            }

            //Check product of selected category type
            $whereProductType = '';
            if(!empty($categoryTypes)){
                $categoryTypesArray = $categoryTypes;
            }else{
                $categoryTypesArray = @getServiceTypesCategory($type, $preferences);
            }

            if(!empty($categoryTypesArray)){
                $categoryTypesArray = implode(',',$categoryTypesArray);
                $whereProductType = ' and `categories`.`type_id` IN ('.$categoryTypesArray.')';
            }

            $user = auth()->user();
            $vendor_id = $user->userVendor->vendor_id ?? 0;
            $raw_query = "";

            foreach($enable_layout as $enable_layout1){
                $completeWhere = ' ';
                if($enable_layout1 == "on_sale" || $enable_layout1 == "new_products" || $enable_layout1 == "featured_products"){
                    $single_category_product_ids = $this->getProductsId($where[$enable_layout1], $vendorWhereIN, $whereProductType);
                    if(!empty($single_category_product_ids)){
                        $single_category_product_ids = @implode(',',$single_category_product_ids);
                        if($single_category_product_ids){
                            $completeWhere = ' AND  `products`.`id` IN  ('.$single_category_product_ids.')';
                        }
                    }
                    $raw_query = (($raw_query=="")?'':$raw_query." UNION ALL ")." SELECT
                    `products`.`id`,
                    `products`.`sku`,
                    `products`.`url_slug`,
                    `products`.`weight_unit`,
                    `products`.`weight`,
                    `products`.`vendor_id`,
                    `products`.`category_id`,
                    '".$enable_layout1."' AS `product_type`,
                    '".$image_url."' AS `d_image_url`,
                    `categories`.`id` as `category_id`,
                    (CASE WHEN SUBSTRING_INDEX(`vendor_media`.`path`, '.', -1) = 'svg' THEN REPLACE(REPLACE('".$image_url."', 'default/default_image.png', `vendor_media`.`path`),'@webp','svg') ELSE REPLACE('".$image_url."', 'default/default_image.png', `vendor_media`.`path`) END) AS `media_url`,
                    `product_translation`.`title`,
                    `product_variant`.`compare_at_price` as `compare_price_numeric`,
                    `product_variant`.`price` as `price_numeric`,
                    `category_translation`.`name` as `category_name` ,
                    CAST((`products`.`averageRating`) AS DECIMAL(2,1)) AS averageRating,
                    CASE
                        when `product_variant`.`compare_at_price` > 0 then CAST((`product_variant`.`compare_at_price` - `product_variant`.`price`)/`product_variant`.`compare_at_price`*100 as decimal(12,2))
                        else 0
                    end as discount_percentage,
                    `vendors`.`name` as `vendor_name`,
                    `vendors`.`slug` as `vendor_slug`,

                    IFNULL(`products`.`is_long_term_service`, 0) AS `is_long_term_service`
                    FROM
                        `products` LEFT JOIN   `categories` as `categories` ON `products`.`category_id` = `categories`.`id`  AND `categories`.`type_id` != 7
                        LEFT JOIN   `product_images` as `product_images` ON `product_images`.`product_id` = `products`.`id`
                        LEFT JOIN   `vendors` as `vendors` ON `vendors`.`id` = `products`.`vendor_id` AND `vendors`.`status` = 1
                        LEFT JOIN   `vendor_media` as `vendor_media` ON `vendor_media`.`id` = `product_images`.`media_id`
                        LEFT JOIN   `product_translations` as `product_translation` ON `product_translation`.`product_id` = `products`.`id` AND `product_translation`.`language_id` = $langId
                        LEFT JOIN   `product_variants` as `product_variant` ON `product_variant`.`product_id` = `products`.`id`
                        LEFT JOIN   `category_translations` as `category_translation` ON `category_translation`.`category_id` = `products`.`category_id` AND `category_translation`.`language_id` = $langId

                    WHERE
                        `products`.`deleted_at` IS NULL
                            AND `vendors`.`status` = 1
                            AND `products`.`is_live` = 1
                            AND (`products`.`vendor_id` IS NULL OR `products`.`vendor_id` != $vendor_id)
                            $completeWhere

                            $vendorWhereIN

                            $whereProductType

                            ";
                }
            }

            $products       = DB::select( DB::raw($raw_query));

            $returnArray    = collect($products)->unique(function ($item)
                            {
                                return $item->id . $item->product_type;
                            });

            return $returnArray;
        }
        catch (\Exception $e) {
            return [];
        }
    }

}
