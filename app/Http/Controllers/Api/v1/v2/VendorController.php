<?php

namespace App\Http\Controllers\Api\v1\v2;

use DB;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{Client, Type, User, Product, Category, ProductVariantSet, VendorSocialMediaUrls, ProductVariant, ProductAddon, ProductRelated, ProductUpSell, ProductCrossSell, ClientCurrency, ClientPreference, ClientLanguage, Vendor, Brand, VendorCategory, PermissionsOld, UserPermissions, UserVendor, VendorDocs, VendorRegistrationDocument, EmailTemplate, Country, OrderReturnRequest, Order, VendorOrderStatus, LuxuryOption,OrderVendor, OrderStatusOption};
use Log;
class VendorController extends BaseController{
    use ApiResponser;
    private $field_status = 2;
    private $folderName = '/vendor/extra_docs';

    public function postVendorCategoryList(Request $request){
        try {
            $vendor_ids = [];
            $category_details = [];
            $vendor_id = $request->vendor_id;
            $type = Type::where('title' ,'Vendor')->first();
            $vendor = Vendor::vendorOnline()->select('name', 'latitude', 'longitude')->where('id', $vendor_id)->first();
            $vendor_products = Product::with(['category.categoryDetail','category.categoryDetail.type'  => function ($q) {
                $q->select('id', 'title as redirect_to');
            }])->where('vendor_id', $vendor_id)->where('is_live', 1)->get(['id']);
            foreach ($vendor_products as $vendor_product) {
                if(!in_array($vendor_product->category->categoryDetail->id, $vendor_ids)){
                    if($vendor_product->category->categoryDetail->id != $type->id){
                        $vendor_ids[] = $vendor_product->category->categoryDetail->id;
                        $category_details[] = array(
                            'id' => $vendor_product->category->categoryDetail->id,
                            'slug' => $vendor_product->category->categoryDetail->slug,
                            'name' => $vendor_product->category->categoryDetail->translation_one ? $vendor_product->category->categoryDetail->translation_one->name :$vendor_product->category->categoryDetail->slug,
                            'icon' => $vendor_product->category->categoryDetail->icon,
                            'image' => $vendor_product->category->categoryDetail->image,
                            'type' => $vendor_product->category->categoryDetail->type??null
                        );
                    }
                }
            }
            return $this->successResponse($category_details, '', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function productsByVendor(Request $request, $vid = 0){
       // pr($vid);
        try {
            if($vid == 0){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $user = Auth::user();
            $userid = $user->id;
            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $limit = $request->has('limit') ? $request->limit : 12;
            $page = $request->has('page') ? $request->page : 12;
            $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
            $preferences = ClientPreference::select('distance_to_time_multiplier','distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude')->first();
            $langId = $user->language;
            $vendor = Vendor::vendorOnline()->select('id', 'name', 'desc', 'logo', 'banner', 'address', 'latitude', 'longitude', 'slug', 'show_slot',
                        'order_min_amount', 'vendor_templete_id', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery','closed_store_order_scheduled')
                        ->withAvg('product', 'averageRating');
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $latitude = (($latitude != '') && ($latitude != "undefined")) ? $latitude : $preferences->Default_latitude;
                $longitude = (($longitude != '') &&( $longitude != "undefined")) ? $longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendor = $vendor->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
            }
            $vendor = $vendor->where('id', $vid)->first();
            if(!$vendor){
                return response()->json(['error' => 'No record found.'], 200);
            }

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

            $slotsDate = 0;
            if($vendor->is_vendor_closed){
                $slotsDate = findSlot('',$vendor->id,'');
                $vendor->delaySlot = $slotsDate;
                $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);
            }else{
                $vendor->delaySlot = 0;
                $vendor->closed_store_order_scheduled = 0;
            }

            if($vendor->closed_store_order_scheduled == 1 && $vendor->is_vendor_closed == 1)
            {
                $vendor->scheduled_time = findSlot('',$vid);
            }

            $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4 ) ? 1 : 0;
            $vendor->is_show_products_with_category = ($vendor->vendor_templete_id == 5) ? 1 : 0;
            $categoriesList = '';

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $vendor = $this->getLineOfSightDistanceAndTime($vendor, $preferences);
            }

            $code = $request->header('code');
            $client = Client::where('code',$code)->first();
            $vendor->share_link = "https://".$client->sub_domain.env('SUBMAINDOMAIN')."/vendor/".$vendor->slug;
            $variantSets =  ProductVariantSet::with(['options' => function($zx) use($langId){
                                $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id');
                                $zx->select('variant_options.*', 'vt.title');
                                $zx->where('vt.language_id', $langId);
                            }])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                            ->join('variant_translations as vt','vt.variant_id','vr.id')
                            ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                            ->where('vt.language_id', $langId)
                            ->whereIn('product_id', function($qry) use($vid){
                            $qry->select('id')->from('products')
                                ->where('vendor_id', $vid);
                            })
                        ->groupBy('product_variant_sets.variant_type_id')->get();
                    // ->join('product_categories as pc', 'pc.product_id', 'products.id')
                    // ->whereNotIn('pc.category_id', function($qr) use($vid){
                    //             $qr->select('category_id')->from('vendor_categories')
                    //                 ->where('vendor_id', $vid)->where('status', 0);
                    // })

            if($vendor->vendor_templete_id == 5){
                $vendor_categories = VendorCategory::with(['category.translation' => function($q) use($langId){
                    $q->where('category_translations.language_id', $langId);
                }])->where('vendor_id', $vid);
                // if(isset($categorySlug) && ($categorySlug != '')){
                //     $vendor_categories = $vendor_categories->whereHas('category', function($query) use($categorySlug) {
                //         $query->where('slug', $categorySlug);
                //     });
                //     $vendor_categories = $vendor_categories->where('status', 1)->get();
                //     foreach($vendor_categories as $category){
                //         $childs = $this->getChildCategoriesForVendor($category->category_id, $langId, $vid);
                //         foreach($childs as $child){
                //             $vendor_categories->push($child);
                //         }
                //     }
                // }else{
                    // $vendor_categories = $vendor_categories->whereHas('category', function($query) {
                    //     $query->whereIn('type_id', [1]);
                    // });
                    $vendor_categories = $vendor_categories->where('status', 1)->get();
                // }

                foreach($vendor_categories as $ckey => $category) {
                    if ($category->category) {

                        $cckey = 0;
                        $categoriesList = $categoriesList . $category->category->translation_one->name ?? '';
                         if($cckey != $vendor_categories->count() - 1){
                            $categoriesList = $categoriesList . ', ';
                        }

                    }


                    $products = Product::with(['category.categoryDetail', 'category.categoryDetail.translation' => function($q) use($langId){
                                $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                                ->where('category_translations.language_id', $langId);
                            }, 'inwishlist' => function($qry) use($userid){
                                $qry->where('user_id', $userid);
                            },
                            'media.image',
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
                            },
                            'translation' => function($q) use($langId){
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                            },
                            'variant' => function($q) use($langId){
                                $q->select('id','sku', 'product_id', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price')->orderBy('quantity', 'desc');
                                // $q->groupBy('product_id');
                            },'variant.checkIfInCartApp', 'checkIfInCartApp',
                             'tags.tag.translations' => function ($q) use ($langId) {
                                $q->where('language_id', $langId);
                            }
                        ])->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only','minimum_order_count','batch_count','is_show_dispatcher_agent', 'is_slot_from_dispatch', 'mode_of_service','tags');
                    $products = $products->where('is_live', 1)->where('category_id', $category->category_id)->where('vendor_id', $vid)->get();

                    if(!empty($products)){
                        foreach ($products as $key => $value) {
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
                            $value->product_image = ($value->media->isNotEmpty()) ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : '';
                            $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                            $value->translation_description = ($value->translation->isNotEmpty()) ? html_entity_decode(strip_tags($value->translation->first()->body_html),ENT_QUOTES) : '';
                            $value->translation_description = !empty($value->translation_description) ? mb_substr($value->translation_description, 0, 70) . '...' : '';
                            $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                            $value->variant_id = ($value->variant->isNotEmpty()) ? $value->variant->first()->id : 0;
                            $value->variant_quantity = ($value->variant->isNotEmpty()) ? $value->variant->first()->quantity : 0;
                            $value->minimum_order_count = ($value->minimum_order_count) ? $value->minimum_order_count : 0;
                            foreach ($value->variant as $k => $v) {
                                $value->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            }
                        }
                    }
                    if($products->count() > 0){
                        $category->products = $products;
                        $category->products_count = $products->count();
                    }else{
                        // if(isset($categorySlug) && ($categorySlug != '')){
                        //     $category->products_count = $products->count();
                        // }
                        // else{
                            $vendor_categories->forget($ckey);
                        // }
                    }
                }
                $listData =  array_values($vendor_categories->toArray());
            }
            else{
                $vendorCategories = VendorCategory::with(['category.translation' => function($q) use($langId){
                    $q->where('category_translations.language_id', $langId);
                }])->where('vendor_id', $vendor->id)->where('status', 1)->get();

                foreach($vendorCategories as $key => $category){
                    if($category->category){
                        $categoriesList = $categoriesList . ($category->category->translation->first()->name ?? '');
                        if( $key !=  $vendorCategories->count()-1 ){
                            $categoriesList = $categoriesList . ', ';
                        }
                    }
                }

                $products = Product::with(['category.categoryDetail', 'category.categoryDetail.translation' => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                            ->where('category_translations.language_id', $langId);
                        }, 'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },
                        'media.image',
                        'addOn' => function($q1) use($langId){
                            $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                            $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                            $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                            $q1->where('set.status', 1)->where('ast.language_id', $langId);
                        },
                        'addOn.setoptions' => function($q2) use($langId){
                            $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                            $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                            $q2->where('apt.language_id', $langId);
                        },
                        'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId){
                            $q->select('id','sku', 'product_id', 'title', 'quantity', 'price', 'markup_price','barcode');
                            // $q->groupBy('product_id');
                        }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                        'tags.tag.translations' => function ($q) use ($langId) {
                            $q->where('language_id', $langId);
                        }
                    ])->select('products.id', 'products.sku', 'products.requires_shipping', 'products.sell_when_out_of_stock', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.Requires_last_mile', 'products.averageRating', 'products.category_id', 'products.minimum_order_count', 'products.batch_count')
                    ->where('products.vendor_id', $vid)
                    ->where('products.is_live', 1)->paginate($limit, $page);
                if(!empty($products)){
                    foreach ($products as $key => $product) {
                        foreach ($product->addOn as $key => $value) {
                            foreach ($value->setoptions as $k => $v) {
                                if($v->price == 0){
                                    $v->is_free = true;
                                }else{
                                    $v->is_free = false;
                                }
                                $v->multiplier = $clientCurrency->doller_compare;
                            }
                        }

                        $p_id = $product->id;
                        $variantData = $product->with(['variantSet' => function ($z) use ($langId, $p_id) {
                            $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                            $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                            $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                            $z->where('vt.language_id', $langId);
                            $z->where('product_variant_sets.product_id', $p_id)->orderBy('product_variant_sets.variant_type_id', 'asc');
                        },'variantSet.options'=> function($zx) use($langId, $p_id){
                            $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id')
                            ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id')
                            ->where('pvs.product_id', $p_id)
                            ->where('vt.language_id', $langId);
                        }])->where('id', $p_id)->first();
                        $product->variantSet = $variantData->variantSet;
                        // $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                        $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                        $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                        $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html),ENT_QUOTES) : '';
                        $product->translation_description = !empty($product->translation_description) ? mb_substr($product->translation_description, 0, 70) . '...' : '';
                        $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                        $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                        $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                        $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                        foreach ($product->variant as $k => $v) {
                            $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
                        }
                    }
                }
            }
            $vendor->categoriesList = $categoriesList;
            $response['vendor'] = $vendor;
            $response['products'] = ($vendor->vendor_templete_id != 5) ? $products : [];
            $response['categories'] = ($vendor->vendor_templete_id == 5) ? $listData : [];
            $response['filterData'] = $variantSets;
            return response()->json(['data' => $response]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }

    /**
     * Display product By Vendor Category
     * vendor -> category -> product
     * @return \Illuminate\Http\Response
     */
    public function vendorCategoryProducts(Request $request, $slug1 = 0, $slug2 = 0){
        try{
            $paginate = $request->has('limit') ? $request->limit : 12;
            // $preferences = Session::get('preferences');
            $vendor = Vendor::vendorOnline()->select('id', 'name', 'slug', 'desc', 'logo', 'show_slot', 'banner', 'address', 'latitude', 'longitude', 'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery', 'vendor_templete_id','closed_store_order_scheduled')
            ->withAvg('product', 'averageRating')->where('slug', $slug1)->where('status', 1)->first();
            if (!empty($vendor)) {
                if (!empty($vendor)) {
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
                    $slotsDate = 0;
                    if($vendor->is_vendor_closed){
                        $slotsDate = findSlot('',$vendor->id,'');
                        $vendor->delaySlot = $slotsDate;
                        $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);
                    }else{
                        $vendor->delaySlot = 0;
                        $vendor->closed_store_order_scheduled = 0;
                    }

                    $code = $request->header('code');
                    $client = Client::where('code', $code)->first();
                    $vendor->share_link = "https://".$client->sub_domain.env('SUBMAINDOMAIN')."/vendor/".$vendor->slug;
                }
                $user = Auth::user();
                $userid = $user->id;
                $langId = $user->language;
                $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
                $categoriesList = '';

                if ($vendor->vendor_templete_id == 5) {
                    $vid = $vendor->id;
                    $vendor_categories = VendorCategory::with(['category.translation' => function ($q) use ($langId) {
                        $q->where('category_translations.language_id', $langId);
                    }])->where('vendor_id', $vid);
                    if (isset($slug2) && ($slug2 != '')) {
                        $vendor_categories = $vendor_categories->whereHas('category', function ($query) use ($slug2) {
                            $query->where('slug', $slug2);
                        });
                        $vendor_categories = $vendor_categories->where('status', 1)->get();
                        foreach ($vendor_categories as $category) {
                            $childs = $this->getChildCategoriesForVendor($category->category_id, $langId, $vid);
                            foreach ($childs as $child) {
                                $vendor_categories->push($child);
                            }
                        }
                    } else {
                        // $vendor_categories = $vendor_categories->whereHas('category', function($query) {
                        //     $query->whereIn('type_id', [1]);
                        // });
                        $vendor_categories = $vendor_categories->where('status', 1)->get();
                    }

                    foreach ($vendor_categories as $ckey => $category) {
                        if ($category->category) {
                            $cckey = 0;
                            $categoriesList = $categoriesList . $category->category->translation_one->name ?? '';
                            if ($cckey != $vendor_categories->count() - 1) {
                                $categoriesList = $categoriesList . ', ';
                            }
                        }

                        $products = Product::with(['category.categoryDetail', 'category.categoryDetail.translation' => function ($q) use ($langId) {
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                                ->where('category_translations.language_id', $langId);
                        }, 'inwishlist' => function ($qry) use ($userid) {
                            $qry->where('user_id', $userid);
                        },
                            'media.image',
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
                            },
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                            },
                            'variant' => function ($q) use ($langId) {
                                $q->select('id', 'sku', 'product_id', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price')->orderBy('quantity', 'desc');
                            // $q->groupBy('product_id');
                            },'variant.checkIfInCartApp', 'checkIfInCartApp',
                        ])->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only','is_show_dispatcher_agent', 'is_slot_from_dispatch', 'mode_of_service','tags');
                        $products = $products->where('is_live', 1)->where('category_id', $category->category_id)->where('vendor_id', $vid)->get();

                        if (!empty($products)) {
                            foreach ($products as $key => $value) {
                                foreach ($value->addOn as $key => $val) {
                                    foreach ($val->setoptions as $k => $v) {
                                        if ($v->price == 0) {
                                            $v->is_free = true;
                                        } else {
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
                                $value->product_image = ($value->media->isNotEmpty()) ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : '';
                                $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                                $value->translation_description = ($value->translation->isNotEmpty()) ? html_entity_decode(strip_tags($value->translation->first()->body_html),ENT_QUOTES) : '';
                                $value->translation_description = !empty($value->translation_description) ? mb_substr($value->translation_description, 0, 70) . '...' : '';
                                $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                                $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                                $value->variant_id = ($value->variant->isNotEmpty()) ? $value->variant->first()->id : 0;
                                $value->variant_quantity = ($value->variant->isNotEmpty()) ? $value->variant->first()->quantity : 0;

                                foreach ($value->variant as $k => $v) {
                                    $value->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                                }
                            }
                        }
                        if ($products->count() > 0) {
                            $category->products = $products;
                            $category->products_count = $products->count();
                        } else {
                            // if(isset($slug2) && ($slug2 != '')){
                            //     $category->products_count = $products->count();
                            // }
                            // else{
                            $vendor_categories->forget($ckey);
                            // }
                        }
                    }
                    $listData =  array_values($vendor_categories->toArray());
                } else {
                    $vendorCategories = VendorCategory::with(['category.translation' => function ($q) use ($langId) {
                        $q->where('category_translations.language_id', $langId);
                    }])->where('vendor_id', $vendor->id)->where('status', 1)->get();

                    foreach ($vendorCategories as $key => $category) {
                        if ($category->category) {
                            $categoriesList = $categoriesList . ($category->category->translation->first()->name ?? '');
                            if ($key !=  $vendorCategories->count()-1) {
                                $categoriesList = $categoriesList . ', ';
                            }
                        }
                    }

                    $products = Product::with(['media.image',
                            'category.categoryDetail', 'category.categoryDetail.translation' => function ($q) use ($langId) {
                                $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                                ->where('category_translations.language_id', $langId);
                            }, 'inwishlist' => function ($qry) use ($userid) {
                                $qry->where('user_id', $userid);
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
                            },
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                            },
                            'variant' => function ($q) use ($langId) {
                                $q->select('id', 'sku', 'product_id', 'title', 'quantity', 'price','markup_price', 'barcode');
                            // $q->groupBy('product_id');
                            }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                        ])->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only','is_show_dispatcher_agent', 'is_slot_from_dispatch', 'mode_of_service','tags');
                    if (!empty($slug2)) {
                        $category = Category::select('id')->where('slug', $slug2)->first();
                        $products = $products->where('category_id', $category->id??0);
                    }
                    $products = $products->where('is_live', 1)->where('vendor_id', $vendor->id)->paginate($paginate);
                    if (!empty($products)) {
                        foreach ($products as $key => $product) {
                            foreach ($product->addOn as $key => $value) {
                                foreach ($value->setoptions as $k => $v) {
                                    if ($v->price == 0) {
                                        $v->is_free = true;
                                    } else {
                                        $v->is_free = false;
                                    }
                                    $v->multiplier = $clientCurrency->doller_compare;
                                }
                            }

                            $p_id = $product->id;
                            $variantData = $product->with(['variantSet' => function ($z) use ($langId, $p_id) {
                                $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                                $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                                $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                                $z->where('vt.language_id', $langId);
                                $z->where('product_variant_sets.product_id', $p_id)->orderBy('product_variant_sets.variant_type_id', 'asc');
                            },'variantSet.options'=> function ($zx) use ($langId, $p_id) {
                                $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id')
                            ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id')
                            ->where('pvs.product_id', $p_id)
                            ->where('vt.language_id', $langId);
                            }])->where('id', $p_id)->first();

                            $product->variantSet = $variantData->variantSet;
                            $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                            $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                            $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                            $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html),ENT_QUOTES) : '';
                            $product->translation_description = !empty($product->translation_description) ? mb_substr($product->translation_description, 0, 70) . '...' : '';
                            $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                            $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                            $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                            foreach ($product->variant as $k => $v) {
                                $product->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            }
                        }
                    }
                    $vid = $vendor->id;
                    $categoryId = $category->id;
                    $variantSets =  ProductVariantSet::with(['options' => function ($zx) use ($langId) {
                        $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id');
                        $zx->select('variant_options.*', 'vt.title');
                        $zx->where('vt.language_id', $langId);
                    }])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                ->join('variant_translations as vt', 'vt.variant_id', 'vr.id')
                ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                ->where('vt.language_id', $langId)
                ->whereIn('product_id', function ($qry) use ($vid, $categoryId) {
                    $qry->select('id')->from('products')->where('vendor_id', $vid)->where('category_id', $categoryId);
                })
                ->groupBy('product_variant_sets.variant_type_id')->get();
                }

                $vendor->categoriesList = $categoriesList;
                $response['vendor'] = $vendor;
                $response['products'] = ($vendor->vendor_templete_id != 5) ? $products : [];
                $response['categories'] = ($vendor->vendor_templete_id == 5) ? $listData : [];
                $response['filterData'] = $variantSets??[];
            }else{
                $response['vendor'] = [];
                $response['products'] = [];
                $response['categories'] =  [];
                $response['filterData'] = [];
            }
            return response()->json(['data' => $response]);
        }
        catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }

    public function vendorProductsFilter(Request $request){
        try{
            $vendor_id =  $request->has('vendor_id') && $request->vendor_id ? $request->vendor_id : null;
            $category_id =  $request->has('category_id') && $request->category_id ? $request->category_id : null;;
            if(!$vendor_id){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $paginate = $request->has('limit') ? $request->limit : 12;
            // $preferences = Session::get('preferences');
            $user = Auth::user();
            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $preferences = ClientPreference::select('distance_to_time_multiplier','distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude')->first();
            $vendor = Vendor::vendorOnline()->select('id', 'name', 'slug', 'desc', 'logo', 'show_slot', 'banner', 'address', 'latitude', 'longitude', 'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery', 'vendor_templete_id','closed_store_order_scheduled')
                        ->withAvg('product', 'averageRating')->where('id', $vendor_id)->where('status', 1);
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                // $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
                // $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;
                $latitude = (($latitude != '') && ($latitude != "undefined")) ? $latitude : $preferences->Default_latitude;
                $longitude = (($longitude != '') &&( $longitude != "undefined")) ? $longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendor = $vendor->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
            }
            $vendor = $vendor->first();
            if (!empty($vendor)) {
                if (!empty($vendor)) {
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
                    $slotsDate = 0;
                    if($vendor->is_vendor_closed){
                        $slotsDate = findSlot('',$vendor->id,'');
                        $vendor->delaySlot = $slotsDate;
                        $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);
                    }else{
                        $vendor->delaySlot = 0;
                        $vendor->closed_store_order_scheduled = 0;
                    }
                    $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4 ) ? 1 : 0;
                    $vendor->is_show_products_with_category = ($vendor->vendor_templete_id == 5) ? 1 : 0;
                    if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                        $vendor = $this->getLineOfSightDistanceAndTime($vendor, $preferences);
                    }
                    $code = $request->header('code');
                    $client = Client::where('code', $code)->first();
                    $vendor->share_link = "https://".$client->sub_domain.env('SUBMAINDOMAIN')."/vendor/".$vendor->slug;
                }
                $user = Auth::user();
                $userid = $user->id;
                $langId = $user->language;
                $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
                $categoriesList = '';
                //filter data
                $order_type = $request->has('order_type') ? $request->order_type : '';
                $setArray = $optionArray = array();
                if ($request->has('variants') && !empty($request->variants)) {
                    $setArray = array_unique($request->variants);
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

                $startRange = 0;
                $endRange = 20000;
                if ($request->has('range') && !empty($request->range)) {
                    $range = explode(';', $request->range);
                    $clientCurrency->doller_compare;
                    $startRange = $range[0] * $clientCurrency->doller_compare;
                    $endRange = $range[1] * $clientCurrency->doller_compare;
                }

                if ($vendor->vendor_templete_id == 5) {
                    $vid = $vendor->id;
                    $vendor_categories = VendorCategory::with(['category.translation' => function ($q) use ($langId) {
                        $q->where('category_translations.language_id', $langId);
                    }])->where('vendor_id', $vid);
                    if (isset($category_id) && ($category_id != '') && ($category_id)) {
                        $vendor_categories = $vendor_categories->whereHas('category', function ($query) use ($category_id) {
                            $query->where('id', $category_id);
                        });
                        $vendor_categories = $vendor_categories->where('status', 1)->get();
                        foreach ($vendor_categories as $category) {
                            $childs = $this->getChildCategoriesForVendor($category->category_id, $langId, $vid);
                            foreach ($childs as $child) {
                                $vendor_categories->push($child);
                            }
                        }
                    } else {
                        // $vendor_categories = $vendor_categories->whereHas('category', function($query) {
                        //     $query->whereIn('type_id', [1]);
                        // });
                        $vendor_categories = $vendor_categories->where('status', 1)->get();
                    }

                    foreach ($vendor_categories as $ckey => $category) {
                        if ($category->category) {
                            $cckey = 0;
                            $categoriesList = $categoriesList . $category->category->translation_one->name ?? '';
                            if ($cckey != $vendor_categories->count() - 1) {
                                $categoriesList = $categoriesList . ', ';
                            }
                        }

                        $products = Product::with(['category.categoryDetail', 'category.categoryDetail.translation' => function ($q) use ($langId) {
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                                ->where('category_translations.language_id', $langId);
                                $q->groupBy('category_translations.language_id');
                        }, 'inwishlist' => function ($qry) use ($userid) {
                            $qry->where('user_id', $userid);
                        },
                            'media.image',
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
                            },
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                            },
                            'variant' => function ($q) use ($langId, $variantIds) {
                                $q->select('id', 'sku', 'product_id', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price')->orderBy('quantity', 'desc');
                            // $q->groupBy('product_id');
                                if (!empty($variantIds)) {
                                    $q->whereIn('id', $variantIds);
                                }
                            },'variant.checkIfInCartApp', 'checkIfInCartApp',
                        ])->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock','products.inquiry_only', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count')
                            ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                            ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                            ->withCount('OrderProduct'); // Or whatever the join logic is

                        $products->where('products.category_id', $category->category_id);


                        $products =  $products->where('products.is_live', 1)
                                    ->where('vendor_id', $vid)
                                    ->whereIn('products.id', function ($qr) use ($startRange, $endRange) {
                                        $qr->select('product_id')->from('product_variants')
                                            ->where('price', '>=', $startRange)
                                            ->where('price', '<=', $endRange);
                                    });
                        if (!empty($productIds)) {
                            $products = $products->whereIn('id', $productIds);
                        }

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
                        //->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only');
                        // $products = $products->where('is_live', 1)->where('category_id', $category->category_id)->where('vendor_id', $vid)->get();
                        $products = $products->groupBy('products.id')->get();

                        if (!empty($products)) {
                            foreach ($products as $key => $value) {
                                foreach ($value->addOn as $key => $val) {
                                    foreach ($val->setoptions as $k => $v) {
                                        if ($v->price == 0) {
                                            $v->is_free = true;
                                        } else {
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
                                $value->product_image = ($value->media->isNotEmpty()) ? $value->media->first()->image->path['image_fit'] . '300/300' . $value->media->first()->image->path['image_path'] : '';
                                $value->translation_title = ($value->translation->isNotEmpty()) ? $value->translation->first()->title : $value->sku;
                                $value->translation_description = ($value->translation->isNotEmpty()) ? html_entity_decode(strip_tags($value->translation->first()->body_html),ENT_QUOTES) : '';
                                $value->translation_description = !empty($value->translation_description) ? mb_substr($value->translation_description, 0, 70) . '...' : '';
                                $value->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                                $value->variant_price = ($value->variant->isNotEmpty()) ? $value->variant->first()->price : 0;
                                $value->variant_id = ($value->variant->isNotEmpty()) ? $value->variant->first()->id : 0;
                                $value->variant_quantity = ($value->variant->isNotEmpty()) ? $value->variant->first()->quantity : 0;

                                foreach ($value->variant as $k => $v) {
                                    $value->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                                }
                            }
                        }
                        if ($products->count() > 0) {
                            $category->products = $products;
                            $category->products_count = $products->count();
                        } else {
                            // if(isset($slug2) && ($slug2 != '')){
                            //     $category->products_count = $products->count();
                            // }
                            // else{
                            $vendor_categories->forget($ckey);
                            // }
                        }
                    }
                    $listData =  array_values($vendor_categories->toArray());
                } else {
                    $vendorCategories = VendorCategory::with(['category.translation' => function ($q) use ($langId) {
                        $q->where('category_translations.language_id', $langId);
                    }])->where('vendor_id', $vendor->id)->where('status', 1)->get();

                    foreach ($vendorCategories as $key => $category) {
                        if ($category->category) {
                            $categoriesList = $categoriesList . ($category->category->translation->first()->name ?? '');
                            if ($key !=  $vendorCategories->count()-1) {
                                $categoriesList = $categoriesList . ', ';
                            }
                        }
                    }

                    $products = Product::with(['media.image',
                            'category.categoryDetail', 'category.categoryDetail.translation' => function ($q) use ($langId) {
                                $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                                ->where('category_translations.language_id', $langId);
                                $q->groupBy('category_translations.language_id');
                            }, 'inwishlist' => function ($qry) use ($userid) {
                                $qry->where('user_id', $userid);
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
                            },
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                            },
                            'variant' => function ($q) use ($langId,$variantIds) {
                                $q->select('id', 'sku', 'product_id', 'title', 'quantity', 'price', 'markup_price','barcode');
                                if (!empty($variantIds)) {
                                    $q->whereIn('id', $variantIds);
                                }
                               //$q->groupBy('product_id');
                            }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                        ])->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock','products.inquiry_only', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count')
                        ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')// Or whatever the join logic is
                        ->withCount('OrderProduct');


                        //->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only');
                        if (!empty($category_id)) {
                            $category = Category::select('id')->where('id', $category_id)->first();
                            $products = $products->where('category_id', $category->id??0);
                        }
                        if (!empty($productIds)) {
                            $products = $products->whereIn('products.id', $productIds);
                        }

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
                    $products = $products->where('is_live', 1)->groupBy('products.id')->where('vendor_id', $vendor->id)->paginate($paginate);

                    if (!empty($products)) {
                        foreach ($products as $key => $product) {
                            foreach ($product->addOn as $key => $value) {
                                foreach ($value->setoptions as $k => $v) {
                                    if ($v->price == 0) {
                                        $v->is_free = true;
                                    } else {
                                        $v->is_free = false;
                                    }
                                    $v->multiplier = $clientCurrency->doller_compare;
                                }
                            }

                            $p_id = $product->id;
                            $variantData = $product->with(['variantSet' => function ($z) use ($langId, $p_id) {
                                $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                                $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                                $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                                $z->where('vt.language_id', $langId);
                                $z->where('product_variant_sets.product_id', $p_id)->orderBy('product_variant_sets.variant_type_id', 'asc');
                            },'variantSet.options'=> function ($zx) use ($langId, $p_id) {
                                $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id')
                            ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id')
                            ->where('pvs.product_id', $p_id)
                            ->where('vt.language_id', $langId);
                            }])->where('id', $p_id)->first();

                            $product->variantSet = $variantData->variantSet;
                            $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                            $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                            $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                            $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html),ENT_QUOTES) : '';
                            $product->translation_description = !empty($product->translation_description) ? mb_substr($product->translation_description, 0, 70) . '...' : '';
                            $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                            $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                            $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                            foreach ($product->variant as $k => $v) {
                                $product->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            }
                        }
                    }
                    $vid = $vendor->id;
                    $categoryId = $category->id;
                    $variantSets =  ProductVariantSet::with(['options' => function ($zx) use ($langId) {
                        $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id');
                        $zx->select('variant_options.*', 'vt.title');
                        $zx->where('vt.language_id', $langId);
                    }])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                    ->join('variant_translations as vt', 'vt.variant_id', 'vr.id')
                    ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                    ->where('vt.language_id', $langId)
                    ->whereIn('product_id', function ($qry) use ($vid, $categoryId) {
                        $qry->select('id')->from('products')->where('vendor_id', $vid)->where('category_id', $categoryId);
                    })
                    ->groupBy('product_variant_sets.variant_type_id')->get();
                }

                $vendor->categoriesList = $categoriesList;
                $response['vendor'] = $vendor;
                $response['products'] = ($vendor->vendor_templete_id != 5) ? $products : [];
                $response['categories'] = ($vendor->vendor_templete_id == 5) ? $listData : [];
                $response['filterData'] = $variantSets??[];
            }else{
                $response['vendor'] = [];
                $response['products'] = [];
                $response['categories'] =  [];
                $response['filterData'] = [];
            }
            return response()->json(['data' => $response]);
        }
        catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }

    /**
     * Product filters on category Page
     * @return \Illuminate\Http\Response
     */
    public function vendorFilters(Request $request, $vid = 0){
        if($vid == 0 || $vid < 0){
            return response()->json(['error' => 'No record found.'], 404);
        }
        $langId = Auth::user()->language;
        $curId = Auth::user()->currency;
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
                $vResult = ProductVariantSet::select('product_variant_sets.product_variant_id', 'product_variant_sets.product_id')
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
        $products = Product::with(['category.categoryDetail', 'media.image', 'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                            $q->groupBy('language_id','product_id');
                        },'variant' => function($q) use($langId, $variantIds){
                            $q->select('sku', 'product_id', 'quantity', 'price', 'markup_price','barcode');
                            if(!empty($variantIds)){
                                $q->whereIn('id', $variantIds);
                            }
                            $q->groupBy('product_id');
                        },
                    ])->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count')
                        ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('vendor_id', $vid)
                        ->where('is_live', 1)
                        ->whereIn('products.id', function($qr) use($startRange, $endRange){
                            $qr->select('product_id')->from('product_variants')
                            ->where('price',  '>=', $startRange)
                            ->where('price',  '<=', $endRange);
                        });
        if(!empty($productIds)){
            $products = $products->whereIn('id', $productIds);
        }
        if($request->has('brands') && !empty($request->brands)){
            $products = $products->whereIn('brand_id', $request->brands);
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
        $paginate = $request->has('limit') ? $request->limit : 12;
        $products = $products->groupBy('products.id')->paginate($paginate);
        if(!empty($products)){
            foreach ($products as $key => $product) {
                $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                foreach ($product->variant as $k => $v) {
                    $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
                }
            }
        }
        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Product filters on category Page
     * @return \Illuminate\Http\Response
     */
    // public function vendorCategoryProductsFilters(Request $request, $vid = 0, $cid = 0){
    //     if( ($vid == 0 || $vid < 0) && ($cid == 0 || $cid < 0) ){
    //         return response()->json(['error' => 'No record found.'], 404);
    //     }
    //     $langId = Auth::user()->language;
    //     $curId = Auth::user()->currency;
    //     $setArray = $optionArray = array();
    //     $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
    //     if($request->has('variants') && !empty($request->variants)){
    //         $setArray = array_unique($request->variants);
    //     }
    //     $startRange = 0; $endRange = 20000;
    //     if($request->has('range') && !empty($request->range)){
    //         $range = explode(';', $request->range);
    //         $clientCurrency->doller_compare;
    //         $startRange = $range[0] * $clientCurrency->doller_compare;
    //         $endRange = $range[1] * $clientCurrency->doller_compare;
    //     }
    //     $multiArray = array();
    //     if($request->has('options') && !empty($request->options)){
    //         foreach ($request->options as $key => $value) {
    //             $multiArray[$request->variants[$key]][] = $value;
    //         }
    //     }
    //     $variantIds = $productIds = array();
    //     if(!empty($multiArray)){
    //         foreach ($multiArray as $key => $value) {
    //             $new_pIds = $new_vIds = array();
    //             $vResult = ProductVariantSet::select('product_variant_sets.product_variant_id', 'product_variant_sets.product_id')
    //                 ->where('product_variant_sets.variant_type_id', $key)
    //                 ->whereIn('product_variant_sets.variant_option_id', $value);
    //             if(!empty($variantIds)){
    //                 $vResult  = $vResult->whereIn('product_variant_sets.product_variant_id', $variantIds);
    //             }
    //             $vResult  = $vResult->groupBy('product_variant_sets.product_variant_id')->get();
    //             if($vResult){
    //                 foreach ($vResult as $key => $value) {
    //                     $new_vIds[] = $value->product_variant_id;
    //                     $new_pIds[] = $value->product_id;
    //                 }
    //             }
    //             $variantIds = $new_vIds;
    //             $productIds = $new_pIds;
    //         }
    //     }
    //     $order_type = $request->has('order_type') ? $request->order_type : '';
    //     $products = Product::with(['category.categoryDetail', 'media.image', 'translation' => function($q) use($langId){
    //                     $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
    //                     },'variant' => function($q) use($langId, $variantIds){
    //                         $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
    //                         if(!empty($variantIds)){
    //                             $q->whereIn('id', $variantIds);
    //                         }
    //                         if(!empty($order_type) && $order_type == 'low_to_high'){
    //                             $q->orderBy('price', 'asc');
    //                         }
    //                         if(!empty($order_type) && $order_type == 'high_to_low'){
    //                             $q->orderBy('price', 'desc');
    //                         }
    //                         $q->groupBy('product_id');
    //                     },
    //                 ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating')
    //                 ->where('vendor_id', $vid)
    //                 ->where('category_id', $cid)
    //                 ->where('is_live', 1)
    //                 ->whereIn('id', function($qr) use($startRange, $endRange){
    //                     $qr->select('product_id')->from('product_variants')->where('price',  '>=', $startRange)->where('price',  '<=', $endRange);
    //                 });
    //     if(!empty($productIds)){
    //         $products = $products->whereIn('id', $productIds);
    //     }
    //     if($request->has('brands') && !empty($request->brands)){
    //         $products = $products->whereIn('brand_id', $request->brands);
    //     }
    //     if(!empty($order_type) && $request->order_type == 'rating'){
    //         $products = $products->orderBy('averageRating', 'desc');
    //     }
    //     $paginate = $request->has('limit') ? $request->limit : 12;
    //     $products = $products->paginate($paginate);
    //     if(!empty($products)){
    //         foreach ($products as $key => $product) {
    //             $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
    //             foreach ($product->variant as $k => $v) {
    //                 $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
    //             }
    //         }
    //     }
    //     return response()->json([
    //         'data' => $products,
    //     ]);
    // }

    public function postVendorRegister(Request $request){
        try {
            DB::beginTransaction();
            $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
            if (empty($request->input('user_id'))) {
                if ($vendor_registration_documents->count() > 0) {
                    $rules_array = [
                        'full_name' => 'required',
                        'email' => 'required|email|unique:users',
                        'phone_number' => 'required|string|min:6|max:15|unique:users',
                        'dialCode' => 'required',
                        'password' => 'required|string|min:6|max:50',
                        'confirm_password' => 'required|same:password',
                        'name' => 'required|string|max:150|unique:vendors',
                        'address' => 'required',
                        // 'vendor_registration_document.*.did_visit' => 'required',
                        'check_conditions' => 'required',
                    ];
                    foreach ($vendor_registration_documents as $vendor_registration_document) {
                        if($vendor_registration_document->is_required == 1){
                            $rules_array[$vendor_registration_document->primary->slug] = 'required';
                        }
                    }

                    $validator = Validator::make($request->all(),
                        $rules_array,
                        ['check_conditions.required' => __('Please indicate that you have read and agree to the Terms and Conditions and Privacy Policy')]
                    );
                } else {
                    $validator = Validator::make($request->all(),
                        [
                            'full_name' => 'required',
                            'email' => 'required|email|unique:users',
                            'phone_number' => 'required|string|min:6|max:15|unique:users',
                            'dialCode' => 'required',
                            'password' => 'required|string|min:6|max:50',
                            'confirm_password' => 'required|same:password',
                            'name' => 'required|string|max:150|unique:vendors',
                            'address' => 'required',
                            'check_conditions' => 'required',
                        ],
                        ['check_conditions.required' => __('Please indicate that you have read and agree to the Terms and Conditions and Privacy Policy')]
                    );
                }
            } else {
                $rules_array = [
                    'name' => 'required|string|max:150|unique:vendors',
                    'address' => 'required',
                    'check_conditions' => 'required',
                ];
                foreach ($vendor_registration_documents as $vendor_registration_document) {
                    if($vendor_registration_document->is_required == 1){
                        $rules_array[$vendor_registration_document->primary->slug] = 'required';
                    }
                }
                $validator = Validator::make($request->all(),
                    $rules_array,
                    ['check_conditions.required' => __('Please indicate that you have read and agree to the Terms and Conditions and Privacy Policy')]
                );
            }
            if($validator->fails()){
                foreach($validator->errors()->toArray() as $error_key => $error_value){
                    $error = __($error_value[0]);
                    return $this->errorResponse($error, 422);
                }
            }
            $client_detail = Client::first();
            $client_preference = ClientPreference::first();
            if(!$request->user_id){
                $user = new User();
                $county = Country::where('code', strtoupper($request->countryData))->first();
                $sendTime = Carbon::now()->addMinutes(10)->toDateTimeString();
                $user->type = 1;
                $user->status = 1;
                $user->role_id = 1;
                $user->is_admin = 1;
                $user->is_email_verified = 0;
                $user->is_phone_verified = 0;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->title = $request->title;
                $user->country_id = $county->id;
                $user->dial_code = $request->dialCode;
                $user->phone_token_valid_till = $sendTime;
                $user->email_token_valid_till = $sendTime;
                $user->email_token = mt_rand(100000, 999999);
                $user->phone_token = mt_rand(100000, 999999);
                $user->phone_number = $request->phone_number;
                $user->password = Hash::make($request->password);
                $user->save();
                $wallet = $user->wallet;
            }else{
                $user = User::where('id', $request->user_id)->first();
                $user->title = $request->title;
                $user->save();
            }
            $vendor = new Vendor();
            $count = 0;
            if($client_preference){
                if($client_preference->dinein_check == 1){$count++;}
                if($client_preference->takeaway_check == 1){$count++;}
                if($client_preference->delivery_check == 1){$count++;}
            }
            if($count > 1){
                $vendor->dine_in = ($request->has('dine_in') && $request->dine_in == 'on') ? 1 : 0;
                $vendor->takeaway = ($request->has('takeaway') && $request->takeaway == 'on') ? 1 : 0;
                $vendor->delivery = ($request->has('delivery') && $request->delivery == 'on') ? 1 : 0;
            }
            else{
                $vendor->dine_in = $client_preference->dinein_check == 1 ? 1 : 0;
                $vendor->takeaway = $client_preference->takeaway_check == 1 ? 1 : 0;
                $vendor->delivery = $client_preference->delivery_check == 1 ? 1 : 0;
            }
            $vendor->logo = 'default/default_logo.png';
            $vendor->banner = 'default/default_image.png';
            if ($request->hasFile('upload_logo')) {
                $file = $request->file('upload_logo');
                $vendor->logo = Storage::disk('s3')->put('/vendor', $file, 'public');
            }
            if ($request->hasFile('upload_banner')) {
                $file = $request->file('upload_banner');
                $vendor->banner = Storage::disk('s3')->put('/vendor', $file, 'public');
            }
            $vendor->status = 0;
            $vendor->name = $request->name;
            $vendor->email = $request->email;
            $vendor->phone_no = $user->phone_no;
            $vendor->address = $request->address;
            $vendor->website = $request->website;
            $vendor->latitude = $request->latitude;
            $vendor->longitude = $request->longitude;
            $vendor->desc = $request->vendor_description;
            $vendor->slug = Str::slug($request->name, "-");
            $vendor->save();
            $permission_details = PermissionsOld::whereIn('id', [1,2,3,12,17,18,19,20,21])->get();
            if ($vendor_registration_documents->count() > 0) {
                foreach ($vendor_registration_documents as $vendor_registration_document) {
                    $doc_name = str_replace(" ", "_", $vendor_registration_document->primary->slug);
                    if ($vendor_registration_document->file_type != "Text" && $vendor_registration_document->file_type != "selector") {
                        if ($request->hasFile($doc_name)) {
                            $vendor_docs =  new VendorDocs();
                            $vendor_docs->vendor_id = $vendor->id;
                            $vendor_docs->vendor_registration_document_id = $vendor_registration_document->id;
                            $filePath = $this->folderName . '/' . Str::random(40);
                            $file = $request->file($doc_name);
                            $vendor_docs->file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                            $vendor_docs->save();
                        }
                    } else {
                        if (!empty($request->$doc_name)) {
                            $vendor_docs =  new VendorDocs();
                            $vendor_docs->vendor_id = $vendor->id;
                            $vendor_docs->vendor_registration_document_id = $vendor_registration_document->id;
                            $vendor_docs->file_name = $request->$doc_name;
                            $vendor_docs->save();
                        }
                    }
                }
            }
            UserVendor::create(['user_id' => $user->id, 'vendor_id' => $vendor->id]);
            foreach ($permission_details as $permission_detail) {
                UserPermissions::create(['user_id' => $user->id, 'permission_id' => $permission_detail->id]);
            }
            $content = '';
            $email_template = EmailTemplate::where('id', 1)->first();
            if($email_template){
                $content = $email_template->content;
                $content = str_ireplace("{title}", $user->title, $content);
                $content = str_ireplace("{email}", $user->email, $content);
                $content = str_ireplace("{address}", $vendor->address, $content);
                $content = str_ireplace("{website}", $vendor->website, $content);
                $content = str_ireplace("{description}", $vendor->desc, $content);
                $content = str_ireplace("{vendor_name}", $vendor->name, $content);
                $content = str_ireplace("{phone_no}", $user->phone_number, $content);
            }
            $email_data = [
                'title' => $user->title,
                'email' => $user->email,
                'powered_by' => url('/'),
                'banner' => $vendor->banner,
                'website' => $vendor->website,
                'address' => $vendor->address,
                'vendor_logo' => $vendor->logo,
                'vendor_name' => $vendor->name,
                'description' => $vendor->desc,
                'phone_no' => $user->phone_number,
                'email_template_content' => $content,
                'subject' => $email_template->subject,
                'client_name' => $client_detail->name,
                'customer_name' => ucwords($user->name),
                'logo' => $client_detail->logo['original'],
                'mail_from' => $client_preference->mail_from,
            ];
            $admin_email_data = [
                'title' => $user->title,
                'email' => $user->email,
                'powered_by' => url('/'),
                'banner' => $vendor->banner,
                'website' => $vendor->website,
                'address' => $vendor->address,
                'vendor_logo' => $vendor->logo,
                'vendor_name' => $vendor->name,
                'description' => $vendor->desc,
                'phone_no' => $user->phone_number,
                'email_template_content' => $content,
                'client_name' => $client_detail->name,
                'subject' => 'New Vendor Registration',
                'customer_name' => ucwords($user->name),
                'logo' => $client_detail->logo['original'],
                'mail_from' => $client_preference->mail_from,
            ];
            try{
                dispatch(new \App\Jobs\sendVendorRegistrationEmail($email_data))->onQueue('verify_email');
                dispatch(new \App\Jobs\sendVendorRegistrationEmail($admin_email_data))->onQueue('verify_email');
            }catch(Exception $e) {

            }
            DB::commit();
            return $this->successResponse('', 'Vendor Registration Created Successfully!', 200);
            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'Vendor Registration Created Successfully!',
            // ]);
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
            // return response()->json([
            //     'status' => 'error',
            //     'message' => $e->getMessage(),
            // ]);
        }
    }

    public function getVendorDetails(Request $request)
    {
        try {
			$validator = Validator::make($request->all(), [
				'vendor_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
            $vendordetail = Vendor::where('id',$request->vendor_id)->first();
            if($vendordetail)
            {
                if($vendordetail->name=="" || $vendordetail->desc=="" || $vendordetail->logo=="" || $vendordetail->address=="" || $vendordetail->email=="" || $vendordetail->phone_no=="")
                {
                    $vendordetail->profile_status = 0;
                }else{
                    $vendordetail->profile_status = 1;
                }
                return $this->successResponse($vendordetail);
            }else{
                return $this->errorResponse('Vendor not found', 422);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updateVendorDetails(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
				'vendor_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
            //return $request->all();
            $vendordetail = Vendor::where('id',$request->vendor_id)->first();
            if($vendordetail)
            {
                if($request->name)
                {
                    $vendordetail->name = $request->name;
                }
                if($request->desc)
                {
                    $vendordetail->desc = $request->desc;
                }
                if($request->email)
                {
                    $vendordetail->email = $request->email;
                }
                if($request->phone_no)
                {
                    $vendordetail->phone_no = $request->phone_no;
                }
                if($request->address)
                {
                    $vendordetail->address = $request->address;
                }
                if($request->latitude)
                {
                    $vendordetail->latitude = $request->latitude;
                }
                if($request->longitude)
                {
                    $vendordetail->longitude = $request->longitude;
                }
                if($request->website)
                {
                    $vendordetail->website = $request->website;
                }
                // if($request->slug)
                // {
                //     $vendordetail->slug = $request->slug;
                // }
                // if($request->order_min_amount)
                // {
                //     $vendordetail->order_min_amount = $request->order_min_amount;
                // }
                // if($request->order_pre_time)
                // {
                //     $vendordetail->order_pre_time = $request->order_pre_time;
                // }
                // if($request->auto_reject_time)
                // {
                //     $vendordetail->auto_reject_time = $request->auto_reject_time;
                // }
                // if($request->commission_percent)
                // {
                //     $vendordetail->commission_percent = $request->commission_percent;
                // }
                // if($request->commission_fixed_per_order)
                // {
                //     $vendordetail->commission_fixed_per_order = $request->commission_fixed_per_order;
                // }
                // if($request->commission_monthly)
                // {
                //     $vendordetail->commission_monthly = $request->commission_monthly;
                // }
                if($request->dine_in!="")
                {
                    $vendordetail->dine_in = $request->dine_in;
                }
                if($request->takeaway!="")
                {
                    $vendordetail->takeaway = $request->takeaway;
                }
                if($request->delivery!="")
                {
                    $vendordetail->delivery = $request->delivery;
                }
                // if($request->status)
                // {
                //     $vendordetail->status = $request->status;
                // }
                // if($request->commission_fixed_per_order)
                // {
                //     $vendordetail->commission_fixed_per_order = $request->commission_fixed_per_order;
                // }

                //images
                if ($request->hasFile('upload_logo')) {
                    $file = $request->file('upload_logo');
                    $vendordetail->logo = Storage::disk('s3')->put('/vendor', $file, 'public');
                }
                if ($request->hasFile('upload_banner')) {
                    $file = $request->file('upload_banner');
                    $vendordetail->banner = Storage::disk('s3')->put('/vendor', $file, 'public');
                }

                $vendordetail->save();
                $vendordetail = Vendor::where('id',$request->vendor_id)->first();
                return $this->successResponse($vendordetail);
            }else{
                return $this->errorResponse('Vendor not found', 422);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    // public function getVendorTransactions(Request $request)
    // {
    //     try {
	// 		$validator = Validator::make($request->all(), [
	// 			'vendor_id' => 'required',
	// 		]);

	// 		if ($validator->fails()) {
	// 			return $this->errorResponse($validator->errors()->first(), 422);
	// 		}
    //         $start_date = $request->start_date;
    //         $end_date = $request->end_date;
    //         $filter_order_status = $request->filter_order_status;

    //         $user = Auth::user();
    //         $langId = 1;
    //         $filter_order_status = $request->filter_order_status;
    //         $orders = Order::with(['vendors.products', 'vendors.status', 'orderStatusVendor'])->orderBy('id', 'DESC');
    //         if (Auth::user()->is_superadmin == 0) {
    //             $orders = $orders->whereHas('vendors.vendor.permissionToUser', function ($query) {
    //                 $query->where('user_id', Auth::user()->id);
    //             });
    //         }
    //         // if (!empty($request->search_keyword)) {
    //         //     $orders = $orders->where('order_number', 'like', '%' . $request->search_keyword . '%');
    //         // }



    //         $order_count = Order::with('vendors')->where(function ($q1) {
    //             $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1]);
    //             $q1->orWhere(function ($q2) {
    //                 $q2->where('payment_option_id', 1);
    //             });
    //         })->orderBy('id', 'asc');
    //         if (Auth::user()->is_superadmin == 0) {
    //             $order_count = $order_count->whereHas('vendors.vendor.permissionToUser', function ($query) {
    //                 $query->where('user_id', Auth::user()->id);
    //             });
    //         }
    //         //filer bitween date
    //         // if (!empty($request->get('date_filter'))) {
    //         //     $date_date_filter = explode(' to ', $request->get('date_filter'));
    //         //     $to_date = (!empty($date_date_filter[1])) ? $date_date_filter[1] : $date_date_filter[0];
    //         //     $from_date = $date_date_filter[0];

    //         //     $orders->between($from_date . " 00:00:00", $to_date . " 23:59:59");

    //         //     //order_count
    //         //     $order_count->between($from_date . " 00:00:00", $to_date . " 23:59:59");
    //         // }
    //         //get by vendor
    //         if (!empty($request->get('vendor_id'))) {
    //             $order_count->whereHas('vendors', function ($query)  use ($request) {
    //                 $query->where('vendor_id', $request->get('vendor_id'));
    //             });
    //         }
    //         $pending_orders = clone $order_count;
    //         $active_orders = clone $order_count;
    //         $orders_history = clone $order_count;

    //         if ($filter_order_status) {
    //             switch ($filter_order_status) {
    //                 case 'pending_orders':
    //                     $orders = $orders->with('vendors', function ($query) {
    //                         $query->where('order_status_option_id', 1);
    //                     })->whereHas('vendors', function ($query) use ($request) {
    //                         $query->where('order_status_option_id', 1);
    //                         if (!empty($request->get('vendor_id'))) {
    //                             $query->where('vendor_id', $request->get('vendor_id'));
    //                         }
    //                     });

    //                     break;
    //                 case 'active_orders':
    //                     $order_status_options = [2, 4, 5];
    //                     $orders = $orders->with('vendors', function ($query) use ($order_status_options) {
    //                         $query->whereIn('order_status_option_id', $order_status_options);
    //                     })->whereHas('vendors', function ($query) use ($order_status_options, $request) {
    //                         $query->whereIn('order_status_option_id', $order_status_options);
    //                         if (!empty($request->get('vendor_id'))) {
    //                             $query->where('vendor_id', $request->get('vendor_id'));
    //                         }
    //                     });

    //                     break;
    //                 case 'orders_history':
    //                     $order_status_options = [6, 3];
    //                     $orders = $orders->with('vendors', function ($query) use ($order_status_options) {
    //                         $query->whereIn('order_status_option_id', $order_status_options);
    //                     })->whereHas('vendors', function ($query) use ($order_status_options, $request) {
    //                         $query->whereIn('order_status_option_id', $order_status_options);
    //                         if (!empty($request->get('vendor_id'))) {
    //                             $query->where('vendor_id', $request->get('vendor_id'));
    //                         }
    //                     });

    //                     break;
    //             }
    //         }
    //         $orders = $orders->whereHas('vendors')->where(function ($q1) {
    //             $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1]);
    //             $q1->orWhere(function ($q2) {
    //                 $q2->where('payment_option_id', 1);
    //             });
    //         })->select('*', 'id as total_discount_calculate')->paginate(30);


    //         $pending_orders = $pending_orders->with('vendors', function ($query) {
    //             $query->where('order_status_option_id', 1);
    //         })->whereHas('vendors', function ($query) {
    //             $query->where('order_status_option_id', 1);
    //         })->count();

    //         $order_status_optionsa = [2, 4, 5];
    //         $active_orders = $active_orders->with('vendors', function ($query) use ($order_status_optionsa) {
    //             $query->whereIn('order_status_option_id', $order_status_optionsa);
    //         })->whereHas('vendors', function ($query) use ($order_status_optionsa) {
    //             $query->whereIn('order_status_option_id', $order_status_optionsa);
    //         })->count();

    //         $order_status_optionsd = [6, 3];
    //         $orders_history = $orders_history->with('vendors', function ($query) use ($order_status_optionsd) {
    //             $query->whereIn('order_status_option_id', $order_status_optionsd);
    //         })->whereHas('vendors', function ($query) use ($order_status_optionsd) {
    //             $query->whereIn('order_status_option_id', $order_status_optionsd);
    //         })->count();


    //         foreach ($orders as $key => $order) {
    //             // $order->created_date = convertDateTimeInTimeZone($order->created_at, $user->timezone, 'd-m-Y, h:i A');
    //             $order->created_date = dateTimeInUserTimeZone($order->created_at, $user->timezone);
    //             $order->scheduled_date_time = !empty($order->scheduled_date_time) ? dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone) : '';
    //             foreach ($order->vendors as $vendor) {
    //                 $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();
    //                 $vendor->order_status = $vendor_order_status ? __($vendor_order_status->OrderStatusOption->title) : '';
    //                 $vendor->order_vendor_id = $vendor_order_status ? $vendor_order_status->order_vendor_id : '';
    //                 $vendor->vendor_name = $vendor ? $vendor->vendor->name : '';
    //                 $product_total_count = 0;
    //                 foreach ($vendor->products as $product) {
    //                     $product_total_count += $product->quantity * $product->price;
    //                     $product->image_path  = $product->media->first() ? $product->media->first()->image->path : getDefaultImagePath();
    //                 }

    //                 if ($vendor->delivery_fee > 0) {
    //                     $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
    //                     $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
    //                     $ETA = $order_pre_time + $user_to_vendor_time;
    //                     // $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : convertDateTimeInTimeZone($vendor->created_at, $user->timezone, 'h:i A');
    //                     $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
    //                     //$order->converted_scheduled_date_time = $order->scheduled_date_time;
    //                     $order->converted_scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
    //                 }

    //                 $vendor->product_total_count = $product_total_count;
    //                 $vendor->final_amount = $vendor->taxable_amount + $product_total_count;
    //             }
    //             $luxury_option_name = '';
    //             if ($order->luxury_option_id > 0) {
    //                 $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
    //                 if ($luxury_option->title == 'takeaway') {
    //                     $luxury_option_name = $this->getNomenclatureName('Takeaway', $langId, false);
    //                 } elseif ($luxury_option->title == 'dine_in') {
    //                     $luxury_option_name = 'Dine-In';
    //                 } else {
    //                     $luxury_option_name = 'Delivery';
    //                 }
    //             }
    //             $order->luxury_option_name = __($luxury_option_name);
    //             if ($order->vendors->count() == 0) {
    //                 $orders->forget($key);
    //             }
    //         }

    //         return $data = array('orders' => $orders, 'pending_orders' => $pending_orders, 'active_orders' => $active_orders, 'orders_history' => $orders_history);


    //     } catch (Exception $e) {
    //         return $this->errorResponse($e->getMessage(), $e->getCode());
    //     }
    // }

    public function getVendorTransactions(Request $request)
    {
        try {
			$validator = Validator::make($request->all(), [
				'vendor_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
            // $start_date = $request->start_date;
            // $end_date = $request->end_date;
            //$filter_order_status = $request->filter_order_status;
            $user = Auth::user();
            $order_status_options = [];
            $type = $request->has('type') ? $request->type : 'active';
            // $orders = OrderVendor::where('user_id', $user->id)->orderBy('id', 'DESC');
            $total_amount = 0;
            $orders = OrderVendor::where('vendor_id', $request->vendor_id)->orderBy('id', 'DESC');
            $allorders = $orders->whereNotIn('order_status_option_id', [1,2,4,5,6])->sum('payable_amount');

            switch ($type) {
                case 'complete':
                    $orders->whereNotIn('order_status_option_id', [6]);
                    break;
                case 'pending':
                    $orders->whereIn('order_status_option_id', [1,2,4,5]);
                    break;
                case 'refund':
                    $order_status_options = [3];
                    $orders->whereHas('status', function ($query) use ($order_status_options) {
                        $query->whereIn('order_status_option_id', $order_status_options);
                    });
                    break;
            }
            $orders = $orders->with(['orderDetail'])
                ->whereHas('orderDetail', function ($q1) {
                    $q1->where('orders.payment_status', 1)->whereNotIn('orders.payment_option_id', [1]);
                    $q1->orWhere(function ($q2) {
                        $q2->where('orders.payment_option_id', 1);
                    });
                })
                ->get();
            foreach ($orders as $order) {
                $total_amount = $total_amount + $order->payable_amount;
                $order_item_count = 0;
                $order->user_name = $user->name;
                $order->user_image = $user->image;
                $order->date_time = dateTimeInUserTimeZone($order->orderDetail->created_at, $user->timezone);
                $order->payment_option_title = __($order->orderDetail->paymentOption->title ?? '');
                $order->order_number = $order->orderDetail->order_number;
                $product_details = [];
                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->orderDetail->id)->where('vendor_id', $order->vendor_id)->orderBy('id', 'DESC')->first();
                if ($vendor_order_status) {
                    $order_sts = OrderStatusOption::where('id',$order->order_status_option_id)->first();
                // $order->order_status =  ['current_status' => ['id' => $vendor_order_status->OrderStatusOption->id, 'title' => __($vendor_order_status->OrderStatusOption->title)]];
                    $order->order_status =  ['current_status' => ['id' => $order_sts->id, 'title' => __($order_sts->title)]];
                } else {
                    $order->current_status = null;
                }
                foreach ($order->products as $product) {
                    $order_item_count += $product->quantity;
                    $product_details[] = array(
                        'image_path' => $product->media->first() ? $product->media->first()->image->path : $product->image,
                        'price' => $product->price,
                        'qty' => $product->quantity,
                        'category_type' => $product->product->category->categoryDetail->type->title ?? '',
                        'product_id' => $product->product_id,
                        'title' => $product->product_name,
                    );
                }
                if ($order->delivery_fee > 0) {
                    $order_pre_time = ($order->order_pre_time > 0) ? $order->order_pre_time : 0;
                    $user_to_vendor_time = ($order->user_to_vendor_time > 0) ? $order->user_to_vendor_time : 0;
                    $ETA = $order_pre_time + $user_to_vendor_time;
                    $order->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $order->created_at, $order->orderDetail->scheduled_date_time) : dateTimeInUserTimeZone($order->created_at, $user->timezone);
                }
                if (!empty($order->orderDetail->scheduled_date_time)) {
                    $order->scheduled_date_time = dateTimeInUserTimeZone($order->orderDetail->scheduled_date_time, $user->timezone);
                }
                $luxury_option_name = '';
                if ($order->orderDetail->luxury_option_id > 0) {
                    $luxury_option = LuxuryOption::where('id', $order->orderDetail->luxury_option_id)->first();
                    if ($luxury_option->title == 'takeaway') {
                        $luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
                    } elseif ($luxury_option->title == 'dine_in') {
                        $luxury_option_name = __('Dine-In');
                    } else {
                        $luxury_option_name = __('Delivery');
                    }
                }
                $order->luxury_option_name = $luxury_option_name;
                $order->product_details = $product_details;
                $order->item_count = $order_item_count;
                unset($order->user);
                unset($order->products);
                unset($order->paymentOption);
                unset($order->payment_option_id);
                unset($order->orderDetail);
            }

            $data = array('totalEarning'=>$allorders, 'filter_total'=>$total_amount,'orders'=>$orders);
            return $this->successResponse($data, '', 201);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function viewAll(Request $request)
    {
        // return $request->all();
        $user = Auth::user();
        $langId = $user->language;
        $currency_id = $user->currency;
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
        $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude')->first();
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $limit = $request->has('limit') ? $request->limit : 12;
        $page = $request->has('page') ? $request->page : 1;

        //filter
        $venderFilterClose   = $request->has('close_vendor') && $request->close_vendor ? $request->close_vendor : null;
        $venderFilterOpen   = $request->has('open_vendor') && $request->open_vendor ? $request->open_vendor : null;
        $venderFilterbest   = $request->has('best_vendor') && $request->best_vendor ? $request->best_vendor : null;
        $venderFilternear   = $request->has('near_me') && $request->near_me ? $request->near_me : null;

        $type = 'delivery';
        if ($request->has('type')) {
            if (empty($request->type)) {
                $vendorData = Vendor::select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude')->withAvg('product', 'averageRating','closed_store_order_scheduled');
            } else {
                $vendorData = Vendor::select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($request->type, 1);
                $type = $request->type;
            }
        } else {
            $vendorData = Vendor::select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude','closed_store_order_scheduled')->withAvg('product', 'averageRating');
        }

        $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $type);

        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            // $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
            // $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;
            $latitude = (($latitude != '') && ($latitude != "undefined")) ? $latitude : $preferences->Default_latitude;
            $longitude = (($longitude != '') &&( $longitude != "undefined")) ? $longitude : $preferences->Default_longitude;
            $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
            //3961 for miles and 6371 for kilometers
            $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
            $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                    cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                    sin( radians(' . $latitude . ') ) *
                    sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->withAvg('product', 'averageRating');
            $vendorData = $vendorData->whereIn('id', $ses_vendors);
            //if($venderFilternear && ($venderFilternear == 1) ){
                //->orderBy('vendorToUserDistance', 'ASC')
                $vendorData =   $vendorData->orderBy('vendorToUserDistance', 'ASC');
            //}
        }

        //filter on ratings
        if($venderFilterbest && ($venderFilterbest == 1) ){
            $vendorData =   $vendorData->orderBy('product_avg_average_rating', 'desc');
        }
        if($venderFilterClose && ($venderFilterClose == 1) ){
            $vendorData =   $vendorData->where('show_slot', 0)->whereDoesntHave('slot')->whereDoesntHave('slotDate');
        }
        if($venderFilterOpen && ($venderFilterOpen == 1) ){
            $vendorData =   $vendorData->where('show_slot', 1)
            ->orWhere(function($q) {
                $q->where('show_slot', 0)
                ->where(function($q1){
                    $q1->whereHas('slot')->orWhereHas('slotDate');
                });
            });
        }
        $vendorData = $vendorData->with('slot', 'slotDate')->where('status', 1)->paginate($limit, $page);

        foreach ($vendorData as $vendor) {
            unset($vendor->products);

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
            $slotsDate = 0;
            if($vendor->is_vendor_closed){
                $slotsDate = findSlot('',$vendor->id,'');
                $vendor->delaySlot = $slotsDate;
                $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);
            }else{
                $vendor->delaySlot = 0;
                $vendor->closed_store_order_scheduled = 0;
            }


            $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4) ? 1 : 0;

            $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $vendor->id)->where('status', 1)->get();
            $categoriesList = '';
            foreach ($vendorCategories as $key => $category) {
                if ($category->category) {
                    $cat_name = isset($category->category->translation_one) ? $category->category->translation_one->name : $category->category->slug;
                    $categoriesList = $categoriesList . $cat_name ?? '';
                    if ($key !=  $vendorCategories->count() - 1) {
                        $categoriesList = $categoriesList . ', ';
                    }
                }
            }
            $vendor->categoriesList = $categoriesList;

            // $vends[] = $vendor->id;
            if (($preferences) && ($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
                $vendor = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor, $preferences, $type);
            }

        }
        //filter vendor
        // if($venderFilterClose && ($venderFilterClose == 1) ){
        //     $vendorData =   $vendorData->where('is_vendor_closed',1)->values();
        // }
        // if($venderFilterOpen && ($venderFilterOpen == 1) ){
        //     $vendorData =   $vendorData->where('is_vendor_closed',0)->values();
        // }

        return $this->successResponse($vendorData);
    }


    # optimize product by vendor

    public function productsByVendorOptimize(Request $request, $vid = 0){
        try {
            if($vid == 0){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $user = Auth::user();
            $userid = $user->id;
            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $limit = $request->has('limit') ? $request->limit : 15;
            $page = $request->has('page') ? $request->page : 1;
            $type = $request->has('type') ? $request->type : 'delivery';
            $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
            $preferences = ClientPreference::select('distance_to_time_multiplier','distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude')->first();
            $langId = $user->language;
            $vendor = Vendor::vendorOnline()->select('id', 'name', 'desc', 'logo', 'banner', 'address', 'latitude', 'longitude', 'slug', 'show_slot', 'instagram_url',
                        'order_min_amount', 'vendor_templete_id', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery','closed_store_order_scheduled','orders_per_slot')
                        ->withAvg('product', 'averageRating');
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {

                $latitude = (($latitude != '') && ($latitude != "undefined")) ? $latitude : $preferences->Default_latitude;
                $longitude = (($longitude != '') &&( $longitude != "undefined")) ? $longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendor = $vendor->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
            }
            $vendor = $vendor->where('id', $vid)->first();
            if(!$vendor){
                return response()->json(['error' => 'No record found.'], 200);
            }

            $socialMediaLinks = [];
            $vendorSocialMediaUrls = VendorSocialMediaUrls::where('vendor_id', $vendor->id)->select('icon', 'url')->get();
            if(!empty($vendorSocialMediaUrls)){
                foreach($vendorSocialMediaUrls as $key => $url){
                    if($url->icon == 'facebook'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/facebook.png');
                    }else if($url->icon == 'github'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/github.png');
                    }else if($url->icon == 'reddit'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/reddit.png');
                    }else if($url->icon == 'whatsapp'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/whatsapp-img.png');
                    }else if($url->icon == 'instagram'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/instagram.png');
                    }else if($url->icon == 'tumblr'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/tumblr.png');
                    }else if($url->icon == 'twitch'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/twitch.png');
                    }else if($url->icon == 'twitter'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/twitter.png');
                    }else if($url->icon == 'pinterest'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/pinterest.png');
                    }else if($url->icon == 'youtube'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/youtube.png');
                    }else if($url->icon == 'snapchat'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/snapchat.png');
                    }else if($url->icon == 'linkedin'){
                        $vendorSocialMediaUrls[$key]->icon_url = asset('assets/images/social-media/linkedin.png');
                    }
                }
                $vendor->social_media_links = $vendorSocialMediaUrls;
            }else{
                $vendor->social_media_links = $socialMediaLinks;
            }

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

            $slotsDate = 0;
            if($vendor->is_vendor_closed){
                $slotsDate = findSlot('',$vendor->id,'');
                $vendor->delaySlot = $slotsDate;
                $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);
            }else{
                $vendor->delaySlot = 0;
                $vendor->closed_store_order_scheduled = 0;
            }

            if($vendor->closed_store_order_scheduled == 1 && $vendor->is_vendor_closed == 1)
            {
                $vendor->scheduled_time = findSlot('',$vid);
            }

            $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4 ) ? 1 : 0;
            $vendor->is_show_products_with_category = ($vendor->vendor_templete_id == 5) ? 1 : 0;
            $categoriesList = '';

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $value = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor, $preferences);
            }

            $code = $request->header('code');
            $client = Client::where('code',$code)->first();

            $clientDomain = !empty($client->custom_domain)? $client->custom_domain : $client->sub_domain.env('SUBMAINDOMAIN');

            $vendor->share_link = $this->siteURL()."/vendor/".$vendor->slug;
            $multipli = $clientCurrency ? $clientCurrency->doller_compare : 1;

            $product_category_ids =  Product::byProductCategoryServiceType($type)->where('vendor_id', $vid)->pluck('category_id');
            $product_category_ids = $product_category_ids->isNotEmpty() ? $product_category_ids->toArray() : [];

            if($vendor->vendor_templete_id == 5){
                $vendor_categories = Category::select('categories.id','categories.type_id', 'types.title as redirect_to')->join('types', 'types.id', 'categories.type_id')->whereHas('vendorCategory',function ($q)use($vid){
                    $q->where('vendor_id',$vid)->where('status', 1);
                })->whereHas('data',function ($q)use($vid){
                    $q->where('is_live', 1)->where('vendor_id', $vid);
                })->withCount(['data' => function ($q)use($vid){
                    $q->where('is_live', 1)->where('vendor_id', $vid);
                }])->with(['translation' => function($q) use($langId){
                    $q->where('category_translations.language_id', $langId);
                }])->with(['data' => function ($q)use($langId,$userid, $multipli,$vid){
                    $q->where('is_live', 1)->where('vendor_id', $vid)->with([
                         'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },
                       'media.image',
                        'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description','language_id','body_html as translation_description')->where('language_id', $langId)->orderBy('updated_at','desc');
                        },
                        'variant' => function($q) use($langId, $multipli){
                            $q->select('id','sku', 'product_id', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price',DB::raw("'$multipli' as multiplier"),)->orderBy('quantity', 'desc');
                        },
                        'variant.checkIfInCartApp'
                        , 'checkIfInCartApp',
                         'tags.tag.translations' => function ($q) use ($langId) {
                            $q->where('language_id', $langId);
                        }
                    ])->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                    ->select('products.*',DB::raw("'$multipli' as variant_multiplier"))->withCount(['variantSet','addOn'])
                    ->orderBy('product_translations.title', 'asc')
                    ->groupBy('products.id');
                }]);

                if(isset($request->category_id))
                {
                    $vendor_categories = $vendor_categories->where(function($q) use ($request){
                        $q->where('categories.id',$request->category_id)->orWhere('categories.parent_id',$request->category_id);
                    });
                }

                $vendor_categories = $vendor_categories->get()->map(function ($query) {
                    $query->setRelation('data', $query->data->take(15));
                    return $query;
                });
                $listData =  array_values($vendor_categories->toArray());
            }else{
                $vendorCategories = VendorCategory::select(\DB::raw("group_concat(`category_translations`.`name`) as categoriesList"))
                ->join('category_translations', 'category_translations.category_id', '=', 'vendor_categories.category_id')
                ->where('vendor_id', $vendor->id)->where('status', 1)->where('category_translations.language_id',$langId)->groupBy('vendor_categories.vendor_id')->first();
                $categoriesList = !empty($vendorCategories)?$vendorCategories->categoriesList:$categoriesList;
                $cat_id = $request->category_id;
                $products = Product::byProductCategoryServiceType($type)->with([
                        'category.categoryDetail' => function($q) use($cat_id){
                            $q->where('categories.id', $cat_id)->orWhere('categories.parent_id', $cat_id);
                        },
                        'category.categoryDetail.translation' => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                            ->where('category_translations.language_id', $langId);
                        }, 'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },
                        'media.image',
                        'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description','language_id','body_html as translation_description')->where('language_id', $langId)->orderBy('id','desc');
                            $q->groupBy('language_id','product_id');
                        },
                        'variant' => function($q) use($langId,$multipli){
                        $q->select('id','sku', 'product_id', 'title', 'quantity', 'price', 'markup_price','barcode', 'compare_at_price',DB::raw("'$multipli' as multiplier"));
                            // $q->groupBy('product_id');
                        }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                        'tags.tag.translations' => function ($q) use ($langId) {
                            $q->where('language_id', $langId);
                        }
                ])->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                ->select('products.id', 'products.sku', 'products.requires_shipping', 'products.sell_when_out_of_stock',
                'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant',
                    'products.has_inventory', 'products.Requires_last_mile', 'products.averageRating', 'products.category_id', 'products.minimum_order_count',
                    'products.batch_count',DB::raw("'$multipli' as variant_multiplier"),'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.mode_of_service','products.tags','products.is_recurring_booking','products.calories','products.inquiry_only')->distinct()
                ->where('products.vendor_id', $vid)
                ->where('products.is_live', 1)->withCount(['variantSet','addOn'])->groupBy('products.id');

                $products = $products->orderBy('product_translations.title', 'asc')->paginate($limit, $page);
                if(!empty($products)){
                    foreach ($products as $key => $product) {
                        $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                        $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                        $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html),ENT_QUOTES) : '';
                        $product->translation_description = !empty($product->translation_description) ? mb_substr(strip_tags($product->translation_description), 0, 70) . '...' : '';
                        $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                        $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                        $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                        $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                    }
                }
            }
            $vendor->categoriesList = $categoriesList;
            $response['vendor'] = $vendor;
            $response['products'] = ($vendor->vendor_templete_id != 5) ? $products : [];
            $response['categories'] = ($vendor->vendor_templete_id == 5) ? $listData : [];
            return response()->json(['data' => $response]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    function siteURL() {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
          $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol.$domainName;
      }


     # optimize product by vendor filters

     public function productsByVendorOptimizeFilterList(Request $request, $vid = 0){
        try {
            if($vid == 0){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $user = Auth::user();
            $userid = $user->id;
             $limit = $request->has('limit') ? $request->limit : 12;
            $langId = $user->language;



            $variantSets =  ProductVariantSet::with(['options' => function($zx) use($langId){
                                $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id');
                                $zx->select('variant_options.*', 'vt.title');
                                $zx->where('vt.language_id', $langId);
                            }])->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id')
                            ->join('variant_translations as vt','vt.variant_id','vr.id')
                            ->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title')
                            ->where('vt.language_id', $langId)
                            ->where('vr.status', 1)
                            ->whereIn('product_id', function($qry) use($vid){
                            $qry->select('id')->from('products')
                                ->where('vendor_id', $vid);
                            })
                        ->groupBy('product_variant_sets.variant_type_id')->get();



            $response['filterData'] = $variantSets;
            return response()->json(['data' => $response]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }

    # filter data
    public function vendorProductsFilterOptimize(Request $request){
        try{
            $vendor_id =  $request->has('vendor_id') && $request->vendor_id ? $request->vendor_id : null;
            $category_id =  $request->has('category_id') && $request->category_id ? $request->category_id : null;
            if(!$vendor_id){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $paginate = $request->has('limit') ? $request->limit : 15;
            // $preferences = Session::get('preferences');
            $user = Auth::user();
            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $type = $request->has('type') ? $request->type : 'delivery';
            $preferences = ClientPreference::select('distance_to_time_multiplier','distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude')->first();
            $vendor = Vendor::vendorOnline()->select('id', 'name', 'slug', 'desc', 'logo', 'show_slot', 'banner', 'address', 'latitude', 'longitude', 'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery', 'vendor_templete_id','closed_store_order_scheduled')
                        ->withAvg('product', 'averageRating')->where('id', $vendor_id)->where('status', 1);
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                // $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
                // $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;
                $latitude = (($latitude != '') && ($latitude != "undefined")) ? $latitude : $preferences->Default_latitude;
                $longitude = (($longitude != '') &&( $longitude != "undefined")) ? $longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendor = $vendor->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->orderBy('vendorToUserDistance', 'ASC');
            }
            $vendor = $vendor->first();
            if (!empty($vendor)) {
                if (!empty($vendor)) {
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
                    $slotsDate = 0;
                    if($vendor->is_vendor_closed){
                        $slotsDate = findSlot('',$vendor->id,'');
                        $vendor->delaySlot = $slotsDate;
                        $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);
                    }else{
                        $vendor->delaySlot = 0;
                        $vendor->closed_store_order_scheduled = 0;
                    }
                    $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4 ) ? 1 : 0;
                    $vendor->is_show_products_with_category = ($vendor->vendor_templete_id == 5) ? 1 : 0;
                    if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                        $vendor = $this->getLineOfSightDistanceAndTime($vendor, $preferences);
                    }
                    $code = $request->header('code');
                    $client = Client::where('code', $code)->first();
                    $vendor->share_link = "https://".$client->sub_domain.env('SUBMAINDOMAIN')."/vendor/".$vendor->slug;
                }
                $user = Auth::user();
                $userid = $user->id;
                $langId = $user->language;
                $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();

                if(empty($clientCurrency)){
                    $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
                }
                $categoriesList = '';
                //filter data
                $order_type = $request->has('order_type') ? $request->order_type : '';
                $setArray = $optionArray = array();
                if ($request->has('variants') && !empty($request->variants)) {
                    $setArray = array_unique($request->variants);
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

                $startRange = 0;
                $endRange = 20000;
                if ($request->has('range') && !empty($request->range)) {
                    $range = explode(';', $request->range);
                    $startRange = $range[0] * $clientCurrency->doller_compare;
                    $endRange = $range[1] * $clientCurrency->doller_compare;
                }

                $multipli = $clientCurrency ? $clientCurrency->doller_compare : 1;

                if ($vendor->vendor_templete_id == 5)
                {
                    $vid = $vendor->id;

                    $vendor_categories = Category::select('id')->whereHas('vendorCategory',function ($q)use($vid){
                        $q->where('vendor_id',$vid)->where('status', 1);
                    })->whereHas('data',function ($q)use($vid){
                        $q->where('is_live', 1)->where('vendor_id', $vid);
                    })
                    ->withCount(['data' => function ($q)use($vid){
                        $q->where('is_live', 1)->where('vendor_id', $vid);
                    }])
                    ->with(['translation' => function($q) use($langId){
                        $q->where('category_translations.language_id', $langId);
                    }])->with(['data' => function ($products)use($order_type,$request,$langId,$userid, $multipli,$variantIds,$vid,$startRange, $endRange,$type){
                        //product query
                        $products->byProductCategoryServiceType($type)->where('is_live', 1)->select('products.*',DB::raw("'$multipli' as variant_multiplier"))->withCount('OrderProduct');

                        $products = $products->with([
                        'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },
                        'media.image',
                        'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description','language_id','body_html as translation_description')->where('language_id', $langId)->orderBy('id','desc');
                            $q->groupBy('language_id','product_id');
                        },
                        'variant' => function($q) use($langId, $multipli,$variantIds){
                            $q->select('id','sku', 'product_id', 'quantity', 'price','markup_price', 'barcode', 'compare_at_price',DB::raw("'$multipli' as multiplier"))->orderBy('quantity', 'desc');
                            if (!empty($variantIds)) {
                                $q->whereIn('id', $variantIds);
                            }

                        },'variant.checkIfInCartApp', 'checkIfInCartApp',
                         'tags.tag.translations' => function ($q) use ($langId) {
                            $q->where('language_id', $langId);
                        }
                    ])->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                    ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                    ->withCount('OrderProduct')->withCount(['variantSet','addOn']); // Or whatever the join logic is


                    $products =  $products->whereIn('products.id', function ($qr) use ($startRange, $endRange) {
                                    $qr->select('product_id')->from('product_variants')
                                        ->where('price', '>=', $startRange)
                                        ->where('price', '<=', $endRange);
                                });
                    if (!empty($productIds)) {
                        $products = $products->whereIn('id', $productIds);
                    }

                    if ($vid > 0) {
                        $products = $products->where('vendor_id', $vid);
                    }

                    if ($request->has('brands') && !empty($request->brands)) {
                        $products = $products->whereIn('products.brand_id', $request->brands);
                    }
                    if($request->has('tag_products') && !empty($request->tag_products)){
                        $products = $products->whereHas('tags',function($query) use ($request){
                            $query->whereIn('tag_id',$request->tag_products);
                        });
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
                    if (!empty($order_type) && ($order_type == 'cal_asc' || $order_type == 'cal_desc')) {
                        if ($order_type == 'cal_asc') {
                            $products = $products->orderByRaw('CAST(products.calories AS SIGNED) IS NULL')
                            ->orderByRaw('CAST(products.calories AS SIGNED) asc');
                        } elseif ($order_type == 'cal_desc') {
                            $products = $products->orderByRaw('CAST(products.calories AS SIGNED) IS NULL')
                            ->orderByRaw('CAST(products.calories AS SIGNED) desc');
                        }
                    }
                    $products = $products->groupBy('id');
                    }])
                    ->where('status', 1);

                    if (isset($category_id) && ($category_id != '') && ($category_id)) {
                        $vendor_categories = $vendor_categories->where('id', $category_id);
                    }

                    $data_count = 0;
                    $vendor_categories = $vendor_categories->get()->map(function ($query) {
                        $query->setRelation('data', $query->data->take(15));
                        return $query;
                    });
                    $listData =     ($vendor_categories->toArray());
                } else {

                    $vendorCategories = VendorCategory::with(['category.translation' => function ($q) use ($langId) {
                        $q->where('category_translations.language_id', $langId);
                    }])->where('vendor_id', $vendor->id)->where('status', 1)->get();

                    foreach ($vendorCategories as $key => $category) {
                        if ($category->category) {
                            $categoriesList = $categoriesList . ($category->category->translation->first()->name ?? '');
                            if ($key !=  $vendorCategories->count()-1) {
                                $categoriesList = $categoriesList . ', ';
                            }
                        }
                    }

                    $products = Product::byProductCategoryServiceType($type)->with(['media.image',
                            'category.categoryDetail', 'category.categoryDetail.translation' => function ($q) use ($langId) {
                                $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                                ->where('category_translations.language_id', $langId);
                                $q->groupBy('category_translations.language_id');
                            }, 'inwishlist' => function ($qry) use ($userid) {
                                $qry->where('user_id', $userid);
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
                            },
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                            },
                            'variant' => function ($q) use ($langId,$variantIds) {
                                $q->select('id', 'sku', 'product_id', 'title', 'quantity', 'price', 'markup_price','barcode');
                                if (!empty($variantIds)) {
                                    $q->whereIn('id', $variantIds);
                                }
                               //$q->groupBy('product_id');
                            },'tags.tag.translations' => function ($q) use ($langId) {
                                $q->where('language_id', $langId);
                            }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                        ])->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock','products.inquiry_only', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count','products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.mode_of_service','products.tags','products.is_recurring_booking','products.calories')
                        ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')// Or whatever the join logic is
                        ->withCount('OrderProduct');


                        //->select('id', 'sku', 'description', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only');
                        if (!empty($category_id)) {
                            $category = Category::select('id')->where('id', $category_id)->first();
                            $products = $products->where('category_id', $category->id??0);
                        }
                        if (!empty($productIds)) {
                            $products = $products->whereIn('products.id', $productIds);
                        }

                        if ($request->has('brands') && !empty($request->brands)) {
                            $products = $products->whereIn('products.brand_id', $request->brands);
                        }
                        if($request->has('tag_products') && !empty($request->tag_products)){
                            $products = $products->whereHas('tags',function($query) use ($request){
                                    $query->whereIn(['tag_id'=>$request->tag_products]);
                                });
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
                        if (!empty($order_type) && ($order_type == 'cal_asc' || $order_type == 'cal_desc')) {
                            if ($order_type == 'cal_asc') {
                                $products = $products->orderByRaw('CAST(products.calories AS SIGNED) IS NULL')
                                ->orderByRaw('CAST(products.calories AS SIGNED) asc');
                            } elseif ($order_type == 'cal_desc') {
                                $products = $products->orderByRaw('CAST(products.calories AS SIGNED) IS NULL')
                                ->orderByRaw('CAST(products.calories AS SIGNED) desc');
                            }
                        }
                    $products = $products->where('is_live', 1)->groupBy('products.id')->where('products.vendor_id', $vendor->id)->paginate($paginate);

                    if (!empty($products)) {
                        foreach ($products as $key => $product) {
                            foreach ($product->addOn as $key => $value) {
                                foreach ($value->setoptions as $k => $v) {
                                    if ($v->price == 0) {
                                        $v->is_free = true;
                                    } else {
                                        $v->is_free = false;
                                    }
                                    $v->multiplier = $clientCurrency->doller_compare;
                                }
                            }

                            $p_id = $product->id;
                            $variantData = $product->with(['variantSet' => function ($z) use ($langId, $p_id) {
                                $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                                $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                                $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                                $z->where('vt.language_id', $langId);
                                $z->where('product_variant_sets.product_id', $p_id)->orderBy('product_variant_sets.variant_type_id', 'asc');
                            },'variantSet.options'=> function ($zx) use ($langId, $p_id) {
                                $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id')
                            ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id')
                            ->where('pvs.product_id', $p_id)
                            ->where('vt.language_id', $langId);
                            }])->where('id', $p_id)->first();

                            $product->variantSet = $variantData->variantSet;
                            $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                            $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                            $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                            $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html),ENT_QUOTES) : '';
                            $product->translation_description = !empty($product->translation_description) ? mb_substr($product->translation_description, 0, 70) . '...' : '';
                            $product->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                            $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                            $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                            foreach ($product->variant as $k => $v) {
                                $product->variant[$k]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                            }
                        }
                    }
                    $vid = $vendor->id;
                    $categoryId = $category->id;

                }

                $vendor->categoriesList = $categoriesList;
                $response['vendor'] = $vendor;
                $response['products'] = ($vendor->vendor_templete_id != 5) ? $products : [];
                $response['categories'] = ($vendor->vendor_templete_id == 5) ? $listData : [];
                $response['filterData'] = [];

            }else{
                $response['vendor'] = [];
                $response['products'] = [];
                $response['categories'] =  [];
                $response['filterData'] = [];
            }
            return response()->json(['data' => $response]);
        }
        catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }



     # products By Vendor Category Optimize

     public function productsByVendorCategoryOptimize(Request $request, $vid = 0){
        try {
            if($vid == 0){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $user = Auth::user();
            $userid = $user->id;
            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $limit = $request->has('limit') ? $request->limit : 15;
            $page = $request->has('page') ? $request->page : 2;
            $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
            $langId = $user->language;
            $multipli = $clientCurrency ? $clientCurrency->doller_compare : 1;
            $type = $request->has('type') ? $request->type : 'delivery';
             //filter data
                $order_type = $request->has('order_type') ? $request->order_type : '';

            $products = Product::byProductCategoryServiceType($type)->where('is_live', 1)->where('vendor_id', $vid)->with([
                     'inwishlist' => function($qry) use($userid){
                        $qry->where('user_id', $userid);
                    },
                    'media.image',
                    'translation' => function($q) use($langId){
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description','language_id','body_html as translation_description')->where('language_id', $langId)->orderBy('id','desc');
                    },
                    'variant' => function($q) use($langId, $multipli){
                        $q->select('id','sku', 'product_id', 'quantity', 'price','markup_price', 'barcode', 'compare_at_price',DB::raw("'$multipli' as multiplier"),)->orderBy('quantity', 'desc');
                        // $q->groupBy('product_id');
                    },'variant.checkIfInCartApp', 'checkIfInCartApp',
                     'tags.tag.translations' => function ($q) use ($langId) {
                        $q->where('language_id', $langId);
                    }
                ])
                ->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock','products.inquiry_only', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count',DB::raw("'$multipli' as variant_multiplier"),'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.mode_of_service','products.tags','products.is_recurring_booking')
                ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                ->withCount(['orderProduct','variantSet','addOn']);

                if($request->has('tag_products') && !empty($request->tag_products)){
                   // pr($request->tag_products);
                    $products = $products->whereHas('tags',function($query) use ($request){
                        $query->whereIn('tag_id',$request->tag_products);
                    });
                }
                if(isset($request->category_id))
                $products = $products->where('category_id',$request->category_id);

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
                if(empty($order_type)){
                    $products = $products->orderBy('product_translations.title', 'asc');
                }
                $products = $products->groupBy('products.id')->paginate($limit, $page);
                $response['products'] = $products ?? [];

            return response()->json(['data' => $response]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }

}
