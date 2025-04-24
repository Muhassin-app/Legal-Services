<?php

namespace App\Http\Controllers\Api\v1\v2;

use DB;
use Validation;
use Carbon\Carbon;
// use Client;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,ProductActionTrait};
use App\Http\Traits\HomePage\HomePageTrait;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{User, Product, Category, ProductVariantSet, ProductVariant, ProductAddon, ProductRelated, ProductUpSell, ProductCrossSell, ClientCurrency, Vendor, Brand, VendorCategory, ProductCategory, Client, ClientPreference, Type};
use Illuminate\Support\Facades\Cache;
use Log;
class CategoryController extends BaseController
{
    private $field_status = 2;
    use ApiResponser,HomePageTrait,ProductActionTrait;
    /**     * Get Company ShortCode     *     */
    
    public function categoryData(Request $request, $cid = 0)
    {
        
        try {
            $limit = $request->has('limit') ? $request->limit : 12;
            $page = $request->has('page') ? $request->page : 1;
            $product_list = $request->has('product_list') ? $request->product_list : 'false';
            $mod_type = $request->has('type') ? $request->type : 'delivery';
         
            if ($cid == 0) {
                return response()->json(['error' => 'No record found.'], 404);
            }
            $userid = Auth::user()->id;
            $langId = Auth::user()->language;
            $category = Category::with([
                // 'tags', 
                'type'  => function ($q) {
                    $q->select('id', 'title as redirect_to');
                },
                'childs.translation'  => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                },
                'translation' => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                }
            ])->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products')
                ->where('id', $cid)->first();
            $mode_of_service = "";

            // if (!empty($category)) {

            //     $mode_of_service_data = Product::where('category_id',$cid)->first();

            //     $mode_of_service = $mode_of_service_data->mode_of_service ?? null;
               
            // }

           


            $variantSets = ProductVariantSet::with(['options' => function ($zx) use ($langId) {
                $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id')
                    ->where('vt.language_id', $langId)
                    ->select('variant_options.*', 'vt.title');
            }])
            ->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
            // ->join('product_variants as pv', 'product_variant_sets.product_variant_id', 'pv.id')
            ->join('variant_translations as vt', 'vt.variant_id', 'vr.id')
            ->where('vt.language_id', $langId)
            ->whereIn('product_variant_sets.product_id', function ($qry) use ($cid) {
                $qry->select('product_id')->from('product_categories')->where('category_id', $cid);
            })
            ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
            ->groupBy('product_variant_sets.variant_type_id')
            ->get();

            if (!$category) {
                return response()->json(['error' => 'No record found.'], 404);
            }
            $code = $request->header('code');
            // $client = Client::where('code', $code)->first();
            $cacheKey = 'client_'.$code;
            $client = Cache::remember($cacheKey, 60, function () use ($code) {
                return Client::where('code', $code)->first();
            });
            $category->share_link = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/category/" . $category->slug;
            $response['category'] = $category;
            $response['filterData'] = $variantSets;
            $response['listData'] = $this->listData($langId, $cid, strtolower($category->type->redirect_to), $userid, $product_list, $mod_type, $mode_of_service, $limit, $page);
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function listData($langId, $category_id, $type = '', $userid, $product_list, $mod_type, $mode_of_service = null, $limit = 12, $page = 1)
    {
        $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'pickup_delivery_service_area')->where('id', '>', 0)->first();


        $user = Auth::user();
        $ses_vendors = $this->getServiceAreaVendors($user->latitude, $user->longitude, $mod_type);
        if ($type == 'vendor' && $product_list == 'false') {
            $vendor_ids = [];
            $vendor_categories = VendorCategory::where('category_id', $category_id)->where('status', 1)->get();
            foreach ($vendor_categories as $vendor_category) {
                if (!in_array($vendor_category->vendor_id, $vendor_ids)) {
                    $vendor_ids[] = $vendor_category->vendor_id;
                }
            }
            $vendorData = Vendor::vendorOnline()->select('id', 'slug', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'latitude', 'longitude');
           

           

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $latitude = ($user->latitude) ? $user->latitude : $preferences->Default_latitude;
                $longitude = ($user->longitude) ? $user->longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
                $vendorData = $vendorData->whereIn('id', $ses_vendors);
            }

            $vendorData = $vendorData->where($mod_type, 1)->where('status', 1)->whereIn('id', $vendor_ids)->with('slot')->withAvg('product', 'averageRating')->paginate($limit, $page);
            foreach ($vendorData as $vendor) {
                unset($vendor->products);
                $vendor = $this->getLineOfSightDistanceAndTime($vendor, $preferences);
                $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4 ) ? 1 : 0;
                $vendor->is_show_products_with_category = ($vendor->vendor_templete_id == 5) ? 1 : 0;
                $vendorCategories = VendorCategory::with(['category.translation' => function($q) use($langId){
                    $q->where('category_translations.language_id', $langId);
                }])->where('vendor_id', $vendor->id)->where('status', 1)->get();
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
                $vendor->categoriesList = $categoriesList;

                $vendor->is_vendor_closed = 0;
                if ($vendor->show_slot == 0) {
                    if (($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty())) {
                        $vendor->is_vendor_closed = 1;
                    } else {
                        $vendor->is_vendor_closed = 0;
                        if ($vendor->slotDate->isNotEmpty()) {
                            $vendor->opening_time = Carbon::parse($vendor->slotDate->first()->start_time)->format('g:i A');
                            $vendor->closing_time = Carbon::parse($vendor->slotDate->first()->end_time)->format('g:i A');
                        } elseif ($vendor->slot->isNotEmpty()) {
                            $vendor->opening_time = Carbon::parse($vendor->slot->first()->start_time)->format('g:i A');
                            $vendor->closing_time = Carbon::parse($vendor->slot->first()->end_time)->format('g:i A');
                        }
                    }
                }
            }
            return $vendorData;
        } elseif ($type == 'vendor' && $product_list == 'true') {
            $vendor_ids = Vendor::where('status', 1)->whereIn('id', $ses_vendors)->pluck('id')->toArray();
            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
            $products = Product::has('vendor')->with([
                'category.categoryDetail', 'category.categoryDetail.translation' => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                }, 'inwishlist' => function ($qry) use ($userid) {
                    $qry->where('user_id', $userid);
                },
                'media.image', 'translation' => function ($q) use ($langId) {
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                },
                'variant' => function ($q) use ($langId) {
                    $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode', 'compare_at_price');
                    // $q->groupBy('product_id');
                }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                'tags.tag.translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }
            ])->select('products.category_id', 'products.id',  'products.sku', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count', 'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.mode_of_service','products.tags','products.is_recurring_booking','products.inquiry_only')
                ->where('products.category_id', $category_id)->where('products.is_live', 1)->whereIn('products.vendor_id', $vendor_ids)->paginate($limit, $page);
               // ->where('mode_of_service', $mode_of_service)
            if (!empty($products)) {
                foreach ($products as $key => $product) {
                    $product->vendor->is_vendor_closed = 0;
                    if ($product->vendor->show_slot == 0) {
                        if (($product->vendor->slotDate->isEmpty()) && ($product->vendor->slot->isEmpty())) {
                            $product->vendor->is_vendor_closed = 1;
                        } else {
                            $product->vendor->is_vendor_closed = 0;
                            if ($product->vendor->slotDate->isNotEmpty()) {
                                $product->vendor->opening_time = Carbon::parse($product->vendor->slotDate->first()->start_time)->format('g:i A');
                                $product->vendor->closing_time = Carbon::parse($product->vendor->slotDate->first()->end_time)->format('g:i A');
                            } elseif ($product->vendor->slot->isNotEmpty()) {
                                $product->vendor->opening_time = Carbon::parse($product->vendor->slot->first()->start_time)->format('g:i A');
                                $product->vendor->closing_time = Carbon::parse($product->vendor->slot->first()->end_time)->format('g:i A');
                            }
                        }
                    }
                    $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                    $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                    $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                    $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html)) : '';
                    $product->translation_description = !empty($product->translation_description) ? mb_substr(strip_tags($product->translation_description), 0, 70) . '...' : '';
                    $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                    $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                    $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                    if ($product->variant->count() > 0) {
                        foreach ($product->variant as $k => $v) {
                            $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
                        }
                    } else {
                        $product->variant =  $product;
                    }
                }
            }
            return $products;
        } elseif ($type == 'Pickup/Delivery' || $type == 'pickup/delivery') {
            $vendor_ids = [];
            $user = Auth::user();
            $pickup_latitude = $user->latitude ? $user->latitude : '';
            $pickup_longitude = $user->longitude ? $user->longitude : '';

            // return $user;

            $vendor_categories = VendorCategory::where('category_id', $category_id)->where('status', 1)->get();
            foreach ($vendor_categories as $vendor_category) {
                if (!in_array($vendor_category->vendor_id, $vendor_ids)) {
                    $vendor_ids[] = $vendor_category->vendor_id;
                }
            }
            $vendorData = Vendor::vendorOnline()->select('id', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id');
            if(isset($preferences->pickup_delivery_service_area) && ($preferences->pickup_delivery_service_area == 1)){

                if (!empty($pickup_latitude) && !empty($pickup_longitude)) {
                    $vendorData = $vendorData->whereHas('serviceArea', function ($query) use ($pickup_latitude, $pickup_longitude) {
                        $query->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$pickup_latitude." ".$pickup_longitude.")'))");
                    });
                }
            }
            $vendorData = $vendorData->where('status', 1)->whereIn('id', $vendor_ids)->with('slot', 'products')->paginate($limit, $page);
            foreach ($vendorData as $vendor) {
                unset($vendor->products);
                $vendor->is_show_category = ($vendor->vendor_templete_id == 1) ? 0 : 1;
            }
            return $vendorData;
        } elseif (strtolower($type) == 'subcategory') {
            
            $category_details = [];
            $category_list = Category::with([
                'tags', 'type'  => function ($q) {
                    $q->select('id', 'title as redirect_to');
                },
                'childs.translation'  => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                },
                'translation' => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                }
            ])->where('parent_id', $category_id)->get();
            foreach ($category_list as $category) {
                $category_details[] = array(
                    'id' => $category->id,
                    'name' => $category->translation->first() ? $category->translation->first()->name : $category->slug,
                    'icon' => $category->icon,
                    'image' => $category->image,
                    'redirect_to' => $category->type->redirect_to
                );
            }
            return $category_details;
        } elseif ($type == 'product' || $type == 'appointment' || $type == 'on demand service' || strtolower($type) == 'laundry' || $type = 'rental service') {
            $vendors = Vendor::where('status', 1)->whereIn('id', $ses_vendors)->pluck('id')->toArray();
            

            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
            $multipli = $clientCurrency ? $clientCurrency->doller_compare : 1;
                
            $products = Product::has('vendor')->with([
                // 'category.categoryDetail', 
                'media.image',
                'translation' => function ($q) use ($langId) {
                    $q->select('id','product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    $q->groupBy('language_id','product_id');
                },
                'variant' => function ($q) use ($langId) {
                    $q->select('id','sku', 'product_id', 'quantity', 'price','markup_price', 'barcode','compare_at_price');
                },
                'variant.checkIfInCartApp', 'checkIfInCartApp',
                'tags.tag.translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }, 'inwishlist' => function ($qry) use ($userid) {
                    $qry->where('user_id', $userid);
                }
            ])->where('products.category_id', $category_id); 

            $products = $products->select('is_live','products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count', DB::raw("'$multipli' as variant_multiplier")  ,'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.mode_of_service','products.tags','products.is_recurring_booking','products.inquiry_only')
                ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                ->join('product_translations', 'product_translations.product_id', '=', 'products.id') // Or whatever the join logic is
                ->withCount('OrderProduct');
            
                // $sess_vendors = [];
                //  if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                //      $user = Auth::user();
                //      $sess_vendors = $this->getServiceAreaVendors($user->latitude, $user->longitude, $mod_type);
                // }
                // if(!empty($ses_vendors)){
                //     $vendor_ids = $sess_vendors;
                // }else{
                //     $vendor_ids = $vendors;
                // }
                $products = $products->whereIn('products.vendor_id', $vendors);
                
            $products = $products->orderBy('product_translations.title', 'asc');
            
            $products = $products->withCount(['variantSet','addOn'])->groupBy('id');
            $products = $products->paginate($limit, $page);

            if (!empty($products)) {
                foreach ($products as $key => $product) {
                    $vendor = $product->vendor;
                    $vendor->is_vendor_closed = 0;
                    if ($vendor->show_slot == 0) {
                        if (($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty())) {
                            $vendor->is_vendor_closed = 1;
                        } else {
                            $vendor->is_vendor_closed = 0;
                            if ($vendor->slotDate->isNotEmpty()) {
                                $vendor->opening_time = Carbon::parse($product->vendor->slotDate->first()->start_time)->format('g:i A');
                                $vendor->closing_time = Carbon::parse($product->vendor->slotDate->first()->end_time)->format('g:i A');
                            } elseif ($vendor->slot->isNotEmpty()) {
                                $vendor->opening_time = Carbon::parse($product->vendor->slot->first()->start_time)->format('g:i A');
                                $vendor->closing_time = Carbon::parse($product->vendor->slot->first()->end_time)->format('g:i A');
                            }
                        }
                    }

                    // $product->vendor->is_vendor_closed = 0;
                    // if ($product->vendor->show_slot == 0) {
                    //     if (($product->vendor->slotDate->isEmpty()) && ($product->vendor->slot->isEmpty())) {
                    //         $product->vendor->is_vendor_closed = 1;
                    //     } else {
                    //         $product->vendor->is_vendor_closed = 0;
                    //         if ($product->vendor->slotDate->isNotEmpty()) {
                    //             $product->vendor->opening_time = Carbon::parse($product->vendor->slotDate->first()->start_time)->format('g:i A');
                    //             $product->vendor->closing_time = Carbon::parse($product->vendor->slotDate->first()->end_time)->format('g:i A');
                    //         } elseif ($product->vendor->slot->isNotEmpty()) {
                    //             $product->vendor->opening_time = Carbon::parse($product->vendor->slot->first()->start_time)->format('g:i A');
                    //             $product->vendor->closing_time = Carbon::parse($product->vendor->slot->first()->end_time)->format('g:i A');
                    //         }
                    //     }
                    // }

                    $p_id = $product->id;
                    $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                    if(($media = $product->media->first())){
                        $image = $media->image;
                        $image = $image->path['image_fit'] . '300/300' . $image->path['image_path'];
                    }
                    $product->product_image = $image ?? '';//($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                    // $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                    // $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html)) : '';
                    // $product->translation_description = !empty($product->translation_description) ? mb_substr($product->translation_description, 0, 70) . '...' : '';
                    // $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    if(($translation = $product->translation->first())){
                        $title = $translation->title;
                        $body_html = $translation->body_html;
                    }
                    $product->translation_title = $title ?? $product->sku;
                    $product->translation_description = $body_html ??  '';
                    $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    if(($variant = $product->variant()->select('id', 'price', 'quantity')->first())){
                        $variant_price = $variant->price;
                        $variant_id = $variant->id;
                        $variant_quantity = $variant->quantity;
                    }
                    $product->variant_price = $variant_price ?? 0;//($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                    $product->variant_id = $variant_id ?? 0; //($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                    $product->variant_quantity = $variant_quantity ?? 0; //($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                    if ($product->variant->count() > 0) {
                        foreach ($product->variant as $k => $v) {
                            $product->variant[$k]->multiplier = $clientCurrency->doller_compare ?? 1;
                        }
                    } else {
                        $product->variant =  $product;
                    }
                }
            }
            $listData = $products;
            return $listData;
        }
        elseif($type == 'brand'){
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
            ->select('id', 'title', 'image', 'image_banner')->where('status', 1)->orderBy('position', 'asc')->paginate($limit, $page);
            return $brands;
        }
        else {
            $arr = array();
            return $arr;
        }
    }

    /**
     * Product filters on category Page
     * @return \Illuminate\Http\Response
     */
    public function categoryFilters(Request $request, $cid = 0)
    {
       // pr($request->all());
        try {
            if ($cid == 0 || $cid < 0) {
                return response()->json(['error' => 'No record found.'], 404);
            }
            $langId = Auth::user()->language;
            $curId = Auth::user()->currency;
            $setArray = $optionArray = array();
            $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
            if ($request->has('variants') && !empty($request->variants)) {
                $setArray = array_unique($request->variants);
            }
            $startRange = 0;
            $endRange = 20000;
            $type = $request->has('type') ? $request->type : 'delivery';
            if ($request->has('range') && !empty($request->range)) {
                $range = explode(';', $request->range);
                $clientCurrency->doller_compare;
                $startRange = $range[0] * $clientCurrency->doller_compare;
                $endRange = $range[1] * $clientCurrency->doller_compare;
            }
            $multiArray = array();
            if ($request->has('options') && !empty($request->options)) {
                foreach ($request->options as $key => $value) {
                    $multiArray[$request->variants[$key]][] = $value;
                }
            }
            $variantIds = $productIds = array();
            if (!empty($multiArray)) {
                foreach ($multiArray as $key => $value) {
                    $new_pIds = $new_vIds = array();
                    $vResult = ProductVariantSet::join('product_categories as pc', 'product_variant_sets.product_id', 'pc.product_id')->select('product_variant_sets.product_variant_id', 'product_variant_sets.product_id')
                        ->where('product_variant_sets.variant_type_id', $key)
                        ->whereIn('product_variant_sets.variant_option_id', $value);

                    if (!empty($variantIds)) {
                        $vResult  = $vResult->whereIn('product_variant_sets.product_variant_id', $variantIds);
                    }
                    $vResult  = $vResult->groupBy('product_variant_sets.product_variant_id')->get();

                    if ($vResult) {
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

            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
            $multipli = $clientCurrency ? $clientCurrency->doller_compare : 1;
        
            $products = Product::byProductCategoryServiceType($type)->has('vendor')->with([
                'category.categoryDetail', 'media.image',
                'translation' => function ($q) use ($langId) {
                    $q->select('id','product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    $q->groupBy('language_id','product_id');
                },
                'variant' => function ($q) use ($langId, $variantIds,$order_type) {
                    $q->select('id','sku', 'product_id', 'quantity', 'price','markup_price', 'barcode');
                    if (!empty($variantIds)) {
                        $q->whereIn('id', $variantIds);
                    }
                   

                 //   $q->groupBy('product_id');
                },
                'variant.checkIfInCartApp', 'checkIfInCartApp',
                'tags.tag.translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }
            ])->where('products.category_id', $cid)
                ->where('products.is_live', 1)
                ->whereIn('products.id', function ($qr) use ($startRange, $endRange) {
                $qr->select('product_id')->from('product_variants')
                    ->where('price', '>=', $startRange)
                    ->where('price', '<=', $endRange);
                }); 

            if (!empty($productIds)) {
            $products = $products->whereIn('id', $productIds);
            }

            $products = $products->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count', 'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.mode_of_service','products.tags','products.is_recurring_booking')
                ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                ->join('product_translations', 'product_translations.product_id', '=', 'products.id') // Or whatever the join logic is
                ->withCount('OrderProduct');
          

            if ($request->has('brands') && !empty($request->brands)) {
                $products = $products->whereIn('products.brand_id', $request->brands);
                }
                if (!empty($order_type) && $request->order_type == 'rating') {
                $products = $products->orderBy('products.averageRating', 'desc');
                }
                if (!empty($order_type) && $order_type == 'low_to_high') {
                $products = $products->orderBy('product_variants.price', 'asc');
                }
                if (!empty($order_type) && $order_type == 'high_to_low') {
                $products = $products->orderBy('product_variants.price', 'desc');
                }
                if (!empty($order_type) && $order_type == 'a_to_z') {
                    $products = $products->orderBy('product_translations.title', 'asc');
                }
                if (!empty($order_type) && $order_type == 'z_to_a') {
                    $products = $products->orderBy('product_translations.title', 'desc');
                }
                if (!empty($order_type) && $order_type == 'newly_added') {
                    $products = $products->orderBy('products.id', 'desc');
                }
                if (!empty($order_type) && $order_type == 'popular_product') {
                    $products = $products->orderBy('order_product_count', 'desc');
                }

            $paginate = $request->has('limit') ? $request->limit : 12;
            $products = $products->withCount(['variantSet','addOn'])->groupBy('id');
            $products = $products->paginate($paginate);
            
            if (!empty($products)) {
                foreach ($products as $key => $product) {
                   

                    $product->vendor->is_vendor_closed = 0;
                    if ($product->vendor->show_slot == 0) {
                        if (($product->vendor->slotDate->isEmpty()) && ($product->vendor->slot->isEmpty())) {
                            $product->vendor->is_vendor_closed = 1;
                        } else {
                            $product->vendor->is_vendor_closed = 0;
                            if ($product->vendor->slotDate->isNotEmpty()) {
                                $product->vendor->opening_time = Carbon::parse($product->vendor->slotDate->first()->start_time)->format('g:i A');
                                $product->vendor->closing_time = Carbon::parse($product->vendor->slotDate->first()->end_time)->format('g:i A');
                            } elseif ($product->vendor->slot->isNotEmpty()) {
                                $product->vendor->opening_time = Carbon::parse($product->vendor->slot->first()->start_time)->format('g:i A');
                                $product->vendor->closing_time = Carbon::parse($product->vendor->slot->first()->end_time)->format('g:i A');
                            }
                        }
                    }

                    $p_id = $product->id;
                    $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                    $product->product_image = ($product->media->isNotEmpty() && !empty($product->media->first()->image)) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                    $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                    $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html)) : '';
                    $product->translation_description = !empty($product->translation_description) ? mb_substr($product->translation_description, 0, 70) . '...' : '';
                    $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                    $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                    $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                    if ($product->variant->count() > 0) {
                        foreach ($product->variant as $k => $v) {
                            $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
                        }
                    } else {
                        $product->variant =  $product;
                    }
                }
            }
            return $this->successResponse($products);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function getCategoryAllData(Request $request, $cid = 0)
    {
        try {
            if ($cid == 0) {
                return response()->json(['error' => 'No record found.'], 404);
            }
            $p_ids = [];
            $v_ids = [];
            $promo = [];
            $cateVendors = [];
            $uniqVendors = [];
            $user = Auth::user();
            $langId = $user->language;

            // Get vendors
            $ses_vendors = $this->getServiceAreaVendors($user->latitude, $user->longitude);

            // Get all data by category id
            $category = Category::with([
                'tags', 'type'  => function($q) {
                    $q->select('id', 'title as redirect_to');
                }, 
                'categoryMobileBanner' => function($q) {
                    $q->where('link', '=', 'category')->where('status', 1);
                },
                'products'  => function ($q) use ($langId) {
                        $q->with(['media' => function($q){
                            $q->groupBy('product_id');
                        }, 'media.image',
                        'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId){
                            $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode', 'compare_at_price');
                            $q->groupBy('product_id');
                        },
                    ]);
                },
                'vendorCategory.vendor'  => function ($q) use ($ses_vendors) {
                    $q->whereIn('id', $ses_vendors);
                },
                'translation' => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                },'cateBrands'  
            ])->select('id', 'status', 'icon', 'image', 'slug', 'type_id', 'can_add_products')->where('status', 1)->where('parent_id', $cid)->get();

            // Collection of refrence_ids for promocode
            if(!empty($category)){
                foreach($category as $cst){
                    $p_ids = array_merge($p_ids, $cst->products->pluck('id')->toArray());
                    $v_ids = array_merge($v_ids, $cst->vendorCategory->pluck('vendor_id')->toArray());
                    $cateVendors[] = $cst->vendorCategory->toArray();
                }
            }

            // Remove duplicate ids and get promocode
                $p_ids = array_unique($p_ids);
                $v_ids = array_unique($v_ids);
                $promo = $this->getRefrenceWisePromoCodes($v_ids, $p_ids);
   

            // Get popular & top_rated products
            $popularProducts = $this->vendorProducts($v_ids, $langId, '', 'popular_products');
            $top_ratedProducts = $this->vendorProducts($v_ids, $langId, '', 'top_rated_products');

            $popular_products[] = array_map(function($v){ 
                $v->path = get_file_path($v->path,'FILL_URL','260','260'); 
                return $v;
            }, $popularProducts->toArray());
            $top_rated_products[] = array_map(function($v){ 
                $v->path = get_file_path($v->path,'FILL_URL','260','260'); 
                return $v;
            }, $top_ratedProducts->toArray());
            
            // Remove duplicate vendore
            foreach($cateVendors as $key => $value) {
                foreach($value as $vel){
                    if(in_array($vel['vendor_id'], $v_ids)){
                        $index = array_search($vel['vendor_id'], $v_ids);
                        unset($v_ids[$index]);
                        $uniqVendors[] = $vel;
                    }
                }
            }

            $data = [
                'top_rated_products' => $top_rated_products,
                'popular_products' => $popular_products,
                'uniqVendors' => $uniqVendors,
                'category' => $category,
                'promo' => $promo
            ];
            return $this->successResponse($data);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
