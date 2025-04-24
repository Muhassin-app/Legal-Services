<?php

namespace App\Http\Controllers\Api\v1\v2;

use DB;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{User, Product, Category, ProductVariantSet, ProductVariant, ProductAddon, ProductRelated, ProductUpSell, ProductCrossSell, ClientCurrency, Vendor, Brand, VendorCategory, ProductCategory, Client, ClientPreference, UserVendor};

class P2PController extends BaseController
{
    use ApiResponser;
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
            ])->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products')
                ->where('id', $cid)->first();
            $mode_of_service = "";

            if (!empty($category)) {

                $mode_of_service_data = Product::where('category_id',$cid)->first();
           

                $mode_of_service = $mode_of_service_data->mode_of_service ?? null;
               
            }

            $variantSets = ProductVariantSet::with(['options' => function ($zx) use ($langId) {
                $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id');
                $zx->select('variant_options.*', 'vt.title');
                $zx->where('vt.language_id', $langId);
            }])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                ->join('variant_translations as vt', 'vt.variant_id', 'vr.id')
                ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                ->where('vt.language_id', $langId)
                ->whereIn('product_variant_sets.product_id', function ($qry) use ($cid) {
                    $qry->select('product_id')->from('product_categories')->where('category_id', $cid);
                })->groupBy('product_variant_sets.variant_type_id')->get();
            if (!$category) {
                return response()->json(['error' => 'No record found.'], 200);
            }
            $code = $request->header('code');
            $client = Client::where('code', $code)->first();
            $category->share_link = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/category/" . $category->slug;
            $response['category'] = $category;
            $response['filterData'] = $variantSets;
           
            $response['listData'] = $this->listData($langId, $cid, strtolower($category->type->redirect_to), $userid, $product_list, $mod_type, $mode_of_service, $limit, $page, $request);
                 
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function listData($langId, $category_id, $type = '', $userid, $product_list, $mod_type, $mode_of_service = null, $limit = 12, $page = 1, $request)
    { 
        $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'pickup_delivery_service_area')->where('id', '>', 0)->first();

        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            $latitude = ($request->latitude) ? $request->latitude : $preferences->Default_latitude;
            $longitude = ($request->longitude) ? $request->longitude : $preferences->Default_longitude;
            $servicearea = $this->getServiceArea($request->latitude, $request->longitude, $mod_type);
      
        }
       
     
        if ($type == 'vendor' && $product_list == 'false') {
           
            $user = Auth::user();
            $vendor_ids = [];
            $vendor_categories = VendorCategory::where('category_id', $category_id)->where('status', 1)->get();
            foreach ($vendor_categories as $vendor_category) {
                if (!in_array($vendor_category->vendor_id, $vendor_ids)) {
                    $vendor_ids[] = $vendor_category->vendor_id;
                }
            }
            $vendorData = Vendor::select('id', 'slug', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'latitude', 'longitude');
            $ses_vendors = $this->getServiceAreaVendors($user->latitude, $user->longitude, $mod_type);
         
           

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
         
            $vendor_ids = Vendor::where('status', 1)->pluck('id')->toArray();
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
                    $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode');
                    // $q->groupBy('product_id');
                }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                'tags.tag.translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }
            ])->select('products.category_id', 'products.id', 'mode_of_service', 'products.sku', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count')
                ->where('products.category_id', $category_id)->where('products.is_live', 1)->where('mode_of_service', $mode_of_service)->whereIn('products.vendor_id', $vendor_ids)->paginate($limit, $page);
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
                    $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : $this->loadDefaultImage();
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
        } elseif ($type == 'product' || $type == 'appointment' || $type == 'on demand service' || strtolower($type) == 'laundry' || $type == 'rental service' || $type == 'p2p') {
           
            $vendor_ids = Vendor::where('status', 1)->pluck('id')->toArray();
           
            if (!empty($request->latitude) && !empty($request->longitude)) {
            
                $latitude = $request->latitude;
                $longitude = $request->longitude ;
               
                $categoryTypes = getServiceTypesCategory($request->type);
                   
               
                $vendorData = Vendor::whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                    $q->whereIn('type_id',$categoryTypes);
                })->select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude','id as is_vendor_closed' ,'closed_store_order_scheduled')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($request->type, 1);
               
                if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                   
                    $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
                    $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;
                    $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                    //3961 for miles and 6371 for kilometers
                    $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                    $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                            cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                            sin( radians(' . $latitude . ') ) *
                            sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->withAvg('product', 'averageRating');
                            
                    $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $request->type);
                    $Service_area = $this->getServiceArea($latitude, $longitude, $request->type);
              
                    $vendorData = $vendorData->whereIn('id', $ses_vendors);
                    //if($venderFilternear && ($venderFilternear == 1) ){
                        //->orderBy('vendorToUserDistance', 'ASC')
                        $vendorData =   $vendorData->orderBy('vendorToUserDistance', 'ASC');
                    //}
                }
                
                $vendorIds  = $vendorData->where('status', 1)->pluck('id');
            }else{
                // $vendorIds = UserVendor::where('user_id', $userid)->pluck('vendor_id')->toArray();
                $vendorIds = $vendor_ids;

            }
            
          
            
            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency ?? 1)->first();
            $multipli = $clientCurrency ? $clientCurrency->doller_compare : 1;
            $now = Carbon::now();
            $products = Product::has('vendor')->with(['ProductAttribute',
                'category.categoryDetail', 'media.image',
                'translation' => function ($q) use ($langId) {
                    $q->select('id','product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    $q->groupBy('language_id','product_id');
                },
                'variant' => function ($q) use ($langId) {
                    $q->select('id','sku', 'product_id', 'quantity', 'price','markup_price', 'barcode');
                },
                'variant.checkIfInCartApp', 'checkIfInCartApp',
                'tags.tag.translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }, 'inwishlist' => function ($qry) use ($userid) {
                    $qry->where('user_id', $userid);
                }
            ])->where(function($q)use($now, $vendorIds,$type){
                if ($type != 'p2p') {
                $q->whereHas('product_availability', function ($q) use ($now, $vendorIds) {
                    // $q->where(function($qq) use ($now, $vendorIds){
                    //     $qq->where('date_time', '>', $now);
                    //     $qq->where('not_available', 0);
                    // });
                    $q->orWhereIn('vendor_id', $vendorIds);
                });
                }
            })->where('products.category_id', $category_id)
                ->where('products.is_live', 1); 

            if(!empty($vendorIds))
            {

                $products = $products->whereIn('vendor_id',$vendorIds);
            }
               
            if( clientPrefrenceModuleStatus('p2p_check') && $request->has('attributes') && count($request['attributes']) > 0) {

             
                $attributes = $request['attributes'];
                
                $products = $products->whereHas('ProductAttribute', function($q) use($attributes){
                    foreach($attributes as $key=>$attribute){
                        foreach($attribute['options'] as $key=>$option){
                            $q->where('attribute_id', $attribute['attribute_id'])->where('attribute_option_id' , $option)->orWhere('key_value', $option);
                        }
                    }
                });
            }

       
            $products = $products->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count', DB::raw("'$multipli' as variant_multiplier"))
                ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                ->join('product_translations', 'product_translations.product_id', '=', 'products.id') // Or whatever the join logic is
                ->withCount('OrderProduct');
            $products = $products->orderBy('product_translations.title', 'asc');
            
            $products = $products->withCount(['variantSet','addOn'])->groupBy('id');
            
            $products = $products->paginate($limit, $page);

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
                    $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : $this->loadDefaultImage();
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

    public function getP2pCategories()
    {

        $celebrity_check = ClientPreference::first()->value('celebrity_check');
        $categories = Category::with('translation_one','type')->where('id', '>', '1')
        ->whereHas('type', function($q){
            $q->where('service_type', 'p2p');
        })
        ->where('is_core', 1)->orderBy('parent_id', 'asc')->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1);

        if ($celebrity_check == 0)
            $categories = $categories->where('type_id', '!=', 5);   # if celebrity mod off .

        $categories = $categories->get();

        return $this->successResponse($categories);
    }

    public function getRentalCategories()
    {

        $celebrity_check = ClientPreference::first()->value('celebrity_check');
        $categories = Category::with('translation_one','type')->where('id', '>', '1')
        ->whereHas('type', function($q){
            $q->where('service_type', 'rental_service');
        })
        ->where('is_core', 1)->orderBy('parent_id', 'asc')->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1);

        if ($celebrity_check == 0)
            $categories = $categories->where('type_id', '!=', 5);   # if celebrity mod off .

        $categories = $categories->paginate();


        return $this->successResponse($categories);
    }
}
