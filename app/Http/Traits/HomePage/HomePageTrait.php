<?php

namespace App\Http\Traits\HomePage;

use App\Models\{CabBookingLayout, Category, HomePageLabel, HomeProduct, Order, OrderProductRating, OrderVendorProduct, Product, ProductCategory, ProductRecentlyViewed, Vendor, VendorCategory, VendorCities, PromoCodeDetail, Promocode, SubscriptionInvoicesVendor, VendorOrderStatus, UserAddress, ClientPreference, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Session, DB, Auth;
use Illuminate\Support\Str;
use GuzzleHttp\Client as GCLIENT;
use App\Http\Traits\{ProductActionTrait};
use Kreait\Firebase\Auth as FirebaseAuth;
use App\Http\Controllers\Api\v1\CartController;

trait HomePageTrait
{
    use ProductActionTrait;

    public $venderFilterOpenClose = null;
    public $venderFilterbest = null;

    public function getMostSellingVendors($preferences, $vendor_ids)
    {
        $latitude = Session::get('latitude');
        $longitude = Session::get('longitude');
        $mostSellingVendors = Vendor::with('slot.day', 'slotDate', 'products')->select('vendors.*', DB::raw('count(vendor_id) as max_sales'))->join('order_vendors', 'vendors.id', '=', 'order_vendors.vendor_id')->whereIn('vendors.id', $vendor_ids)->where('vendors.status', 1)->groupBy('order_vendors.vendor_id')->orderBy(DB::raw('count(vendor_id)'), 'desc');

        // add hyperlocal check to get vendors
        if (($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {

            if (!empty($latitude) && !empty($longitude)) {
                $mostSellingVendors = $mostSellingVendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                    $query->select('vendor_id')
                        ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                });
            }
        }
        $mostSellingVendors = $mostSellingVendors->get();

        if ((!empty($mostSellingVendors) && count($mostSellingVendors) > 0)) {
            foreach ($mostSellingVendors as $key => $value) {
                $value->vendorRating = $this->vendorRatings($value->products);
                // $value->name = Str::limit($value->name, 15, '..');
                if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                    $value = $this->getVendorDistanceWithTime($latitude, $longitude, $value, $preferences);
                }
                $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $value->id)->where('status', 1)->get();
                $categoriesList = '';
                foreach ($vendorCategories as $key => $category) {
                    if ($category->category) {
                        $categoriesList = $categoriesList . @$category->category->translation_one->name;
                        if ($key !=  $vendorCategories->count() - 1) {
                            $categoriesList = $categoriesList . ', ';
                        }
                    }
                }
                $value->categoriesList = $categoriesList;

                $value->is_vendor_closed = 0;
                if ($value->show_slot == 0) {
                    if (($value->slotDate->isEmpty()) && ($value->slot->isEmpty())) {
                        $value->is_vendor_closed = 1;
                    } else {
                        $value->is_vendor_closed = 0;
                        if ($value->slotDate->isNotEmpty()) {
                            $value->opening_time = Carbon::parse($value->slotDate->first()->start_time)->format('g:i A');
                            $value->closing_time = Carbon::parse($value->slotDate->first()->end_time)->format('g:i A');
                        } elseif ($value->slot->isNotEmpty()) {
                            $value->opening_time = Carbon::parse($value->slot->first()->start_time)->format('g:i A');
                            $value->closing_time = Carbon::parse($value->slot->first()->end_time)->format('g:i A');
                        }
                    }
                }
            }
        }
        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            $mostSellingVendors = $mostSellingVendors->sortBy('lineOfSightDistance')->values()->all();
        }
        return $mostSellingVendors;
    }


    public function getSpotLight($preferences, $vendor_ids, $language_id, $currency_id, $p_dim)
    {
        $spotlight_products = [];
        $products = Product::with([
            'category.categoryDetail.translation' => function ($q) use ($language_id) {
                $q->where('category_translations.language_id', $language_id);
            },
            'vendor',
            'media' => function ($q) {
                $q->groupBy('product_id');
            }, 'media.image',
            'translation' => function ($q) use ($language_id) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $language_id);
            },

        ])
            ->selectRaw('product_variants.sku, product_id, quantity,compare_at_price,  price, barcode, (compare_at_price - price) as discount_amount, ((compare_at_price - price)/compare_at_price)*100 as discount_percentage,   products.id, products.sku, url_slug, weight_unit, weight, vendor_id, has_variant, has_inventory, sell_when_out_of_stock, requires_shipping, Requires_last_mile, averageRating, inquiry_only
        ')

            ->join('product_variants', 'products.id', 'product_variants.product_id');

        $products = $products->whereHas('vendor', function ($q) use ($vendor_ids) {
            $q->where('status', 1);
            $q->whereIn('vendors.id', $vendor_ids);
        })->where('is_live', 1)
            ->orderBy(DB::raw("((product_variants.compare_at_price - product_variants.price)/product_variants.compare_at_price)*100"), 'desc')
            ->take(8)->get();

        if (!empty($products)) {
            foreach ($products as $key => $product) {

                $multiply =  Session::get('currencyMultiplier') ?? 1;
                $title = $product->translation->first() ? $product->translation->first()->title : $product->sku;
                $image_url = $product->media->first() && !is_null($product->media->first()->image) ? $product->media->first()->image->path['image_fit'] . $p_dim . $product->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                $spotlight_products[] = array(
                    'id' => $product->id,
                    'tag_title' => $spotlight_products_title ?? '0',
                    'image_url' => $image_url,
                    'media' => $product->media ,
                    'variant' => $product->variant ,
                    'sku' => $product->sku,
                    'title' => Str::limit($title, 18, '..'),
                    'url_slug' => $product->url_slug,
                    'discount_percentage' => (int) $product->discount_percentage,
                    'averageRating' => number_format($product->averageRating, 1, '.', ''),
                    'inquiry_only' => $product->inquiry_only,
                    'vendor_name' => $product->vendor ? $product->vendor->name : '',
                    'vendor' => $product->vendor,
                    'price' => Session::get('currencySymbol') . ' ' . (decimal_format(@$product->variant->first()->price * $multiply, ',')),
                    'compare_price' =>@$product->variant->first()->compare_at_price * $multiply,
                    'compare_price_numeric' =>@$product->variant->first()->compare_at_price * $multiply,
                    'price_numeric' =>@$product->variant->first()->price * $multiply,
                    'category' => (@$product->category->categoryDetail->translation) ? @$product->category->categoryDetail->translation->first()->name : @$product->category->categoryDetail->slug,
                    'categoryDetail' => (@$product->category->categoryDetail) ? @$product->category->categoryDetail: []
                );
            }
        }
        // $spotlight_products = [];

        return $spotlight_products;
    }

    public function getSingleCategoryProducts()
    {
        $product_ids = [];
        if (checkTableExists('home_products')) {
            $single_category_products = HomeProduct::whereSlug('single_category_products')->first();
            if (@$single_category_products) {
                $product_ids = ProductCategory::select('product_id')->where('category_id', $single_category_products->category_id)->get();
            }
        }
        
        return $product_ids;
    }

    public function getSingleCategoryWithProducts()
    {
        $product_ids = [];
        if (checkTableExists('home_products')) {
            $single_category_products = HomeProduct::with(['categoryDetail.products.variants','categoryDetail.products.media.image'])->whereSlug('single_category_products')->first();
        }
        
        return $single_category_products;
    }

    public function getSpotlightProducts()
    {
        $spotlight_products = Product::with(['variants','media.image'
            ])->select('id', 'sku','title', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only','spotlight_deals')->where('spotlight_deals', 1)->take(9)->get();
        return $spotlight_products; 
    }

    public function getSelectedProduct($layout_id)
    {
        $selected_products = HomeProduct::with(['products.variants','products.media.image'])->where('layout_id',$layout_id)->get();
        return $selected_products;
    }

    public function getProducts($preferences, $vendor_ids, $language_id, $currency_id = 'USD', $p_dim, $product_ids, $take = 8)
    {
        $productFiltered = [];
        if (@$product_ids) {
            $products = Product::with([
                'category.categoryDetail.translation' => function ($q) use ($language_id) {
                    $q->where('category_translations.language_id', $language_id);
                },
                'vendor',
                'media' => function ($q) {
                    $q->groupBy('product_id');
                }, 'media.image',
                'translation' => function ($q) use ($language_id) {
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $language_id);
                },

            ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only');


            $products = $products->where('is_live', 1);
            // ->whereHas('vendor', function ($q) use ($vendor_ids) {
            //     $q->where('status', 1);
            //     $q->whereIn('vendors.id', $vendor_ids);
            // });
            $products = $products->whereIn('id', $product_ids)
                ->take($take)->get();

            
            if (!empty($products)) {
                foreach ($products as  $product) {
                    $multiply =  Session::get('currencyMultiplier') ?? 1;
                    $title = $product->translation->first() ? $product->translation->first()->title : $product->sku;
                    $image_url = $product->media->first() && !is_null($product->media->first()->image) ? $product->media->first()->image->path['image_fit'] . $p_dim . $product->media->first()->image->path['image_path'] : $this->loadDefaultImage();
                    $is_p2p = 0;
                    if(@$product->category->categoryDetail->type_id && @$product->category->categoryDetail->type_id == 13){
                        $is_p2p = 1;
                    }
                    $productFiltered[] = array(
                        'id' => $product->id,
                        'tag_title' => $spotlight_products_title ?? 'Single Category Products',
                        'image_url' => $image_url,
                        'media' => $product->media ,
                        'variant' => $product->variant ,
                        'sku' => $product->sku,
                        'title' => Str::limit($title, 18, '..'),
                        'url_slug' => $product->url_slug,
                        'discount_percentage' => (int) $product->discount_percentage,
                        'averageRating' => number_format($product->averageRating, 1, '.', ''),
                        'inquiry_only' => $product->inquiry_only,
                        'vendor_name' => $product->vendor ? $product->vendor->name : '',
                        'vendor' => $product->vendor,
                        'price' => Session::get('currencySymbol') . ' ' . (decimal_format(@$product->variant->first()->price * $multiply, ',')),
                        'compare_price' =>@$product->variant->first()->compare_at_price * $multiply,
                        'compare_price_numeric' =>@$product->variant->first()->compare_at_price * $multiply,
                        'price_numeric' =>@$product->variant->first()->price * $multiply,
                        'categoryDetail' => (@$product->category->categoryDetail) ? @$product->category->categoryDetail: [],
                        'category' => (@$product->category->categoryDetail->translation) ? @$product->category->categoryDetail->translation->first()->name : @$product->category->categoryDetail->slug,
                        'is_p2p' => $is_p2p
                    );
                }
            }
        }
        return $productFiltered;
    }

    public function getSelectedProducts()
    {
        $product_ids = [];
        if (checkTableExists('home_products')) {
            $single_category_products = HomeProduct::whereSlug('selected_products')->first();
            if(@$single_category_products){
                $product_ids = json_decode($single_category_products->products);
            }
            
        }
        return $product_ids;
    }

    public function getMostPopularProducts()
    {
        $product_ids = [];
        $most_sold = OrderVendorProduct::selectRaw('id, product_id, count(product_id) as total')->whereHas('statusDelievered')->groupBy('product_id')->orderBy('total', 'DESC')->take(5)->get()->pluck('product_id');
        // dd($most_sold);
        $most_viewed = ProductRecentlyViewed::selectRaw('id, product_id, count(product_id) as total')->groupBy('product_id')->orderBy('total', 'DESC')->take(5)->get()->pluck('product_id');

        $product_ids = $most_sold->merge($most_viewed);
        return $product_ids;
    }


    public function getTopRatedProducts()
    {
        
        $product_ids = OrderProductRating::selectRaw('id, product_id, count(product_id) as total')->groupBy('product_id')->orderBy('total', 'DESC')->take(5)->get()->pluck('product_id');
        return $product_ids;
    }

     /* Get vendor rating from its products rating */
     public function vendorRatings($vendorProducts)
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

     /* Get vendor rating from its products rating */
     public function vendorNoOfRatings($vendorProducts)
     {
         $vendor_rating = 0;
         $product_rating = 0;
         $product_count = 0;
         if($vendorProducts->isNotEmpty()){
            
             foreach($vendorProducts as $product){
                 if($product->averageRating > 0){
                     $product_count++;
                 }
             }
         }
         return $product_count;
     }

     public function getVendorWisePromoCodes($vendor_id){
        $now = Carbon::now()->toDateTimeString();
        $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $vendor_id)->pluck('promocode_id');
        $result2 = Promocode::whereIn('id', $vendor_promo_code_details->toArray())->where('restriction_on', 1)->whereHas('details', function($q) use($vendor_id){
            $q->where('refrence_id', $vendor_id);
        })->where('restriction_on', 1)->where('is_deleted', 0)->whereDate('expiry_date', '>=', $now)->get();
        return $result2;
     }

     public function getRefrenceWisePromoCodes($vendor_ids = [], $product_ids = []){
        $promo_codes = new \Illuminate\Database\Eloquent\Collection;
        $now = Carbon::now()->toDateTimeString();

        $firstOrderCheck = 0;
        if( Auth::user()){
            $userOrder = auth()->user()->orders->first();
            if($userOrder){
                $firstOrderCheck = 1;
            }
        }
        if (!empty($product_ids)) {
            $promo_code_details = PromoCodeDetail::whereIn('refrence_id', $product_ids)->pluck('promocode_id');
            $result1 = Promocode::whereDate('expiry_date', '>=', $now)->where('restriction_on', 0)->where(function ($query) use ($promo_code_details ) {
                $query->where(function ($query2) use ($promo_code_details) {
                    $query2->where('restriction_type', 1);
                    if (!empty($promo_code_details->toArray())) {
                        $query2->whereNotIn('id', $promo_code_details->toArray());
                    }
                });
                $query->orWhere(function ($query1) use ($promo_code_details) {
                    $query1->where('restriction_type', 0);
                    if (!empty($promo_code_details->toArray())) {
                        $query1->whereIn('id', $promo_code_details->toArray());
                    } else {
                        $query1->where('id', 0);
                    }
                });
            });
            if($firstOrderCheck){
                $result1->where('first_order_only', 0);
            }
            $result1->where(['promo_visibility' => 'public']);
    
            $result1 = $result1->where('is_deleted', 0)->get();
            $promo_codes = $promo_codes->merge($result1);
        }

        if(!empty($vendor_ids)){
            $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->whereIn('refrence_id', $vendor_ids)->pluck('promocode_id');
            $result2 = Promocode::where('restriction_on', 1)
                ->where(function ($query) use ($vendor_promo_code_details) {
                    $query->where(function ($query2) use ($vendor_promo_code_details) {
                        $query2->where('restriction_type', 1);
                        if ($vendor_promo_code_details->isNotEmpty()) {
                            $query2->whereNotIn('id', $vendor_promo_code_details);
                        }
                    })
                    ->orWhere(function ($query1) use ($vendor_promo_code_details) {
                        $query1->where('restriction_type', 0);
                        if ($vendor_promo_code_details->isNotEmpty()) {
                            $query1->whereIn('id', $vendor_promo_code_details);
                        } else {
                            $query1->where('id', 0);
                        }
                    });
                })
                ->when($firstOrderCheck, function ($query) {
                    return $query->where('first_order_only', 0);
                })
                ->where('promo_visibility', 'public')
                ->where('is_deleted', 0)
                ->whereDate('expiry_date', '>=', $now)
                ->get();
            $promo_codes = $promo_codes->merge($result2);
        }
        return $promo_codes;
     }

     public function vendorProducts_v2($venderIds, $langId, $currency = 'USD', $where = '', $type)
     {
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
                 $q->select('sku', 'product_id', 'quantity', 'price', 'barcode','compare_at_price');
                 $q->groupBy('product_id');
             },
         ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only');
         if ($where !== '' && $where !== 'on_sale') {
             $products = $products->where($where, 1);
         }
         if($where == 'on_sale'){
            $products = $products->whereHas('variant' , function($q){
                $q->where('compare_at_price',  '>',  0);
            });
         }
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
                    //  $q->where($type, 1);
                 })->where('is_live', 1)->take(10)->inRandomOrder()->get();
         if (!empty($products)) {
             foreach ($products as $key => $value) {
                 foreach ($value->variant as $k => $v) {
                     $value->variant[$k]->multiplier = Session::get('currencyMultiplier');
                 }
             }
         }
        return $products;
         //pr( $products->toArray());
     }


     public function getCities_v2($language_id)
     {
         $this->cities =  VendorCities::with(['translations' => function ($q) use ($language_id) {
             $q->where('language_id', $language_id);
         }])->where(function ($q) {
             $q->where('latitude', '!=', null);
             $q->where('longitude', '!=', null);
         })->get();
 
         $this->cities = $this->cities->map(function ($da) {
             $da->title = $da->translations->first() ? $da->translations->first()->name : $da->slug;
             unset($da->translations);
             return $da;
         });
         return $this->cities;
     }


     public function postHomePageDataV2($request,$set_template,$enable_layout,$additionalPreference,$user='', $getSubCatIds='')
    {
        $client_timezone = DB::table('clients')->first('timezone');
     
        
        if(!empty($user)){
            $timezone        = $user->timezone ? $user->timezone :  ($client_timezone->timezone ?? 'Asia/Kolkata' );
        } else{
            $timezone = $client_timezone->timezone ?? 'Asia/Kolkata';
        }

        
        //pr($enable_layout);
       // $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating','is_show_vendor_on_subcription']);
        $vendor_ids = $vendors = [];
        $new_products = [];
        $feature_products = [];
        $on_sale_products = [];
        $long_term_service_products = [];
        $recently_viewed = [];
        $banners = [];
        
        $p_dim = '260/260';
        if (isset($set_template)  && $set_template->template_id == 3){
            $p_dim = '300/300';
        }elseif(isset($set_template)  && $set_template->template_id == 2){
            $p_dim = '260/260';
        }

        //st

        $latitude = Session::get('latitude');
        $longitude = Session::get('longitude');

        //pr($latitude);
        if($request->has('latitude') ){
            $latitude = $request->latitude;
            Session::put('latitude', $latitude);
        }
        if ($request->has('longitude')) {
            $longitude = $request->longitude;
            Session::put('longitude', $longitude);
        }
        $selectedAddress = ($request->has('selectedAddress')) ? Session::put('selectedAddress', $request->selectedAddress) : Session::get('selectedAddress');
        $selectedPlaceId = ($request->has('selectedPlaceId')) ? Session::put('selectedPlaceId', $request->selectedPlaceId) : Session::get('selectedPlaceId');
        $preferences = $this->client_preferences;
        $currency_id = Session::get('customerCurrency');
        $language_id = Session::get('customerLanguage');
        if (is_null($language_id)) {
            $local = ($request->hasHeader('language')) ? $request->header('language') : 1;
        
            $language_id = $local;
        }

        $currency_id = $this->setCurrencyInSesion();


        //sd

        $featured_products_title = $vendors_title = $new_products_title = $on_sale_title = $brands_title = $best_sellers_title = $recent_orders_title = $banner_title = $selected_products_title = $trending_vendors_title = '';

        $slugs = array("featured_products", "vendors", "new_products", "on_sale", "brands", "best_sellers", "recent_orders", "banner", "selected_products", "trending");

        $results = DB::select("
            SELECT 
                cab_booking_layout_transaltions.title, 
                cab_booking_layout_transaltions.cab_booking_layout_id, 
                cab_booking_layouts.slug 
            FROM 
                cab_booking_layout_transaltions 
                INNER JOIN 	cab_booking_layouts ON cab_booking_layout_transaltions.cab_booking_layout_id = 	cab_booking_layouts.id 
            WHERE 
                cab_booking_layout_transaltions.language_id = ? 
                AND cab_booking_layout_transaltions.title IS NOT NULL 
                AND 	cab_booking_layouts.slug IN (" . implode(',', array_fill(0, count($slugs), '?')) . ")
        ", array_merge([$language_id], $slugs));
       // pr($results);

        $titles = [];
        foreach ($results as $result) {
            switch ($result->slug) {
                case "featured_products":
                    $titles['featured_products_title'] = $result->title;
                    break;
                case "vendors":
                    $titles['vendors_title'] = $result->title;
                    break;
                case "new_products":
                    $titles['new_products_title'] = $result->title;
                    break;
                case "on_sale":
                    $titles['on_sale_title'] = $result->title;
                    break;
                case "brands":
                    $titles['brands_title'] = $result->title;
                    break;
                case "best_sellers":
                    $titles['best_sellers_title'] = $result->title;
                    break;
                case "recent_orders":
                    $titles['recent_orders_title'] = $result->title;
                    break;
                case "banner":
                    $titles['banner_title'] = $result->title;
                    break;
                case "selected_products":
                    $titles['selected_products_title'] = $result->title;
                    break;
                case "trending":
                    $titles['trending_vendors_title'] = $result->title;
                    break;
                default:
                    break;
            }
        }
        //extract($titles);

        $featured_products_title = $titles['featured_products_title'] ?? null;
        $vendors_title = $titles['vendors_title'] ?? null;
        $new_products_title = $titles['new_products_title'] ?? null;
        $on_sale_title = $titles['on_sale_title'] ?? null;
        $brands_title = $titles['brands_title'] ?? null;
        $best_sellers_title = $titles['best_sellers_title'] ?? null;
        $recent_orders_title = $titles['recent_orders_title'] ?? null;
        $selected_products_title = $titles['selected_products_title'] ?? null;
        $trending_vendors_title = $titles['trending_vendors_title'] ?? null;

        $vendor_ids = $this->getRandomVendorIdsForHomePage($preferences, $request->type, $preferences['is_admin_vendor_rating'], $latitude, $longitude,@$request->momo);
        $home_page_labels = HomePageLabel::with('translations')->get();
        if (in_array('brands', $enable_layout)) {     # if enable brands section in
             
            $brands = $this->getBrandsForHomePage($language_id, $this->field_status);
        }else{
            $brands = [];
        }

        Session::forget('vendorType');
        Session::put('vendorType', $request->type);
      
        if ($preferences) {
            // check vendor Subscription0
           
            if ((empty($latitude)) && (empty($longitude)) && (empty($selectedAddress))) {
                $selectedAddress = $preferences->Default_location_name;
                $latitude = $preferences->Default_latitude??null;
                $longitude = $preferences->Default_longitude??null;
                Session::put('latitude', $latitude);
                Session::put('longitude', $longitude);
                Session::put('selectedAddress', $selectedAddress);
            } else {
                if ($preferences && ($latitude == $preferences->Default_latitude) && ($longitude == $preferences->Default_longitude)) {
                    Session::put('selectedAddress', $preferences->Default_location_name);
                }
            }
        }

         
        if(count($vendor_ids) > 0){
            $vendors = $this->getVendorForHomePage($preferences, "random_or_admin_rating", $timezone, $additionalPreference['is_admin_vendor_rating'], $request->type, $language_id, $latitude, $longitude, $vendor_ids,null,$this->venderFilterOpenClose,$this->venderFilterbest);
            $preferences = ClientPreference::first();
            $getCartController = new CartController();
            foreach($vendors as $k => $vendorData){
                if($preferences->static_delivey_fee != 1){
                    $deliver_response_array = $getCartController->getDeliveryFeeDispatcher($vendorData->id, $dispatcher_tags='');
                    // lineeeeeeeeeeeeeeeee
                
                if (!empty($deliver_response_array[0])){
                    $totalRoute = '1';
                    $deliver_charge = (!empty($deliver_response_array[0]['delivery_fee']))?number_format(($deliver_response_array[0]['delivery_fee']*$totalRoute), 2, '.', ''):'0.00';
                    $delivery_duration = (!empty($deliver_response_array[0]['total_duration']))?number_format($deliver_response_array[0]['total_duration'], 0, '.', ''):'0.00';
                    $vendorData->delivery_fee = $deliver_charge;
                    $vendorData->delivery_time = $delivery_duration;
                }
                }elseif($preferences->static_delivey_fee == 1 ){
                    $vendorData->delivery_fee = 0.00;
                    $vendorData->delivery_time = 00.00;
                } 
            }
        }
        
        $trendingVendors = [];
        if (in_array('trending_vendors', $enable_layout)) {  # if enable trending_vendors section in 
            $now = Carbon::now()->toDateTimeString();
            $trending_vendors = SubscriptionInvoicesVendor::whereHas('features', function ($query) {
                $query->where(['subscription_invoice_features_vendor.feature_id' => 1]);
            })
            ->select('id', 'vendor_id', 'subscription_id')
            ->where('end_date', '>=', $now)
            ->pluck('vendor_id')->toArray();
            if(count($trending_vendors) > 0){
                $trendingVendors = $this->getVendorForHomePage($preferences, "trending_vendors", $timezone, 0, $request->type, $language_id, $latitude, $longitude, $trending_vendors);
            }
        } 
        
        if (($latitude) && ($longitude)) {
            Session::put('vendors', $vendor_ids);
            
        }
        
        //get Most Selling Vendors
        $mostSellingVendors = []; //best_sellers
        if (in_array('best_sellers', $enable_layout)) {
            if(count($vendor_ids) > 0){
                $dataMo = $this->getVendorForHomePage($preferences, "best_sellers", $timezone, 0, $request->type, $language_id, $latitude, $longitude, $vendor_ids);
                if(sizeof($dataMo)){
                    $mostSellingVendors = $dataMo;
                }
            }
        }
        //pr($mostSellingVendors);
        $on_sale_product_details =$on_sale_products = [];
        if (in_array('on_sale', $enable_layout)) {  # if enable new_products section in 
            $on_sale_products = $on_sale_product_details = $this->vendorProducts($vendor_ids, $language_id, 'USD', 'on_sale', $request->type,$on_sale_title, $p_dim, $getSubCatIds);
        }
        $new_product_details =$new_products = [];
        if (in_array('new_products', $enable_layout)) {  # if enable new_products section in 
            $new_products = $new_product_details = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'is_new', $request->type,$new_products_title,$p_dim, $getSubCatIds);
        }
        $feature_product_details = $feature_products = [];
      
        if (in_array('featured_products', $enable_layout)) {  # if enable featured_products section in
            $feature_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'is_featured', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
        } 
        
        $top_rated_products = '';
         //get long term service 
        $long_term_service_products =[];
        if( in_array('long_term_service', $enable_layout) && @$additionalPreference['is_long_term_service'] == 1 && count($vendor_ids) > 0){ # if enable long_term_service section in 
            $long_term_service_products = $this->longTermServiceProducts($vendor_ids, $additionalPreference, $language_id, $currency_id,'', $request->type,$p_dim);
        }
        //pr($set_template->template_id);
        //if($set_template->template_id ==10){
            $recently_viewed = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'recent_viewed', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
            //$spot_light_products = $this->getSpotLight($preferences, $vendor_ids, $language_id, $currency_id, $p_dim); // get spotlight product i.e. max discounted products
            $spot_light_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'spotlight_deals', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
            // pr($spot_light_products);
            //$single_category_product_ids = $this->getSingleCategoryProducts(); // get single selected category's products
            $single_category_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'single_category_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
            // dd($single_category_products);
            //$selected_product_ids = $this->getSelectedProducts(); // get single selected category's products
            $selected_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'selected_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);

            //$popular_product_ids = $this->getMostPopularProducts();  // get selected products to display 
            $popular_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'popular_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);

            //$top_rated_products_ids = $this->getTopRatedProducts();  // get selected products to display 
            $top_rated_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'top_rated_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);

           // $ordered_products = $this->vendorProducts($preferences, $vendor_ids, $language_id, $currency_id, $p_dim, $this->getLastProductOrdered(), 10);

            //pr($top_rated_products);
        //}
        /**  Recent order */
        $activeOrders = [];
        if (in_array('recent_orders', $enable_layout)) {  # if enable recent_orders section in 
            $user = Auth::user();

            if ($user) {
                    $activeOrders = Order::with([
                        'vendors' => function ($q) {
                            $q->where('order_status_option_id', '!=', 6);
                        },
                        'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        }, 'vendors.dineInTable.category', 'vendors.products', 'vendors.products.media.image', 'vendors.products.pvariant.media.pimage.image', 'user', 'address'
                    ])->whereHas('vendors', function ($q) {
                        $q->where('order_status_option_id', '!=', 6);
                    })
                        ->where('orders.user_id', $user->id)->take(10)
                        ->orderBy('orders.id', 'DESC')->get();
                        foreach ($activeOrders as $order) {
                            foreach ($order->vendors as $vendor) {
                                $vendor->tag_title = $vendor_title??'0';
                                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();
                                $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                                foreach ($vendor->products as $product) {
                                    if (isset($product->pvariant) && $product->pvariant->media->isNotEmpty()) {
                                        $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                                    } elseif ($product->media->isNotEmpty() && isset($product->media->first()->image)) {
                                        $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                                    } else {
                                        $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                                    }
                                    $product->pricedoller_compare = 1;
                                }
                                if ($vendor->delivery_fee > 0) {
                                    $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                                    $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                                    $ETA = $order_pre_time + $user_to_vendor_time;
                                    $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                                }
                                if ($vendor->dineInTable) {
                                    $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                                    $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                                    $vendor->dineInTableCategory = $vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
                                }
                            }
                            $order->converted_scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
                        }
            }
        }
        /**  Recent order end */

        /**  Get cities */
        if (in_array('cities', $enable_layout)) {   # if enable recent_orders section in 
            if($preferences->is_hyperlocal==1){
                $this->getCities_v2($language_id);
            }
        }
        /**  Get cities end */




        /** Respose data */
            //   print_r($vendors);exit;
        
            $data = [
                'vendor_ids'=>$vendor_ids,
                'brands' => $brands,
                'vendors' => $vendors,
                'new_products' => $new_products,
                'top_rated'       => $top_rated_products ?? '',
                'recently_viewed' => $recently_viewed,
                'homePageLabels' => $home_page_labels,
                'featured_products' => $feature_products,
                'on_sale' => $on_sale_products,
                'cities' => $this->cities,
                'long_term_service' => $long_term_service_products,
                'trending_vendors' => (!empty($trendingVendors) && count($trendingVendors) > 0)?$trendingVendors:[],
                'best_sellers'     => (!empty($mostSellingVendors) && count($mostSellingVendors) > 0)?$mostSellingVendors:[],
                'spotlight_deals'  => (!empty($spot_light_products) && count($spot_light_products) > 0)?$spot_light_products:[],
                'single_category_products'  => (!empty($single_category_products) && count($single_category_products) > 0)?$single_category_products:[],
                'selected_products'  => (!empty($selected_products) && count($selected_products) > 0)?$selected_products:[],
                'most_popular_products'  => (!empty($popular_products) && count($popular_products) > 0)?$popular_products:[],
                'additionalPreference' => $additionalPreference,
            ];
            return $data ;
    }

}
