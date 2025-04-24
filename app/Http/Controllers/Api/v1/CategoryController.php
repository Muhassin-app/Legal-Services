<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Validation;
use Carbon\Carbon;
// use Client;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,VendorTrait};
use App\Http\Traits\HomePage\HomePageTrait;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{User,Cart, Product,ClientLanguage, Category, ProductVariantSet, ProductVariant, ProductAddon, ProductRelated, ProductUpSell, ProductCrossSell, ClientCurrency, Vendor, Brand, VendorCategory, ProductCategory, Client, ClientPreference,CategoryKycDocuments,CaregoryKycDoc};

class CategoryController extends BaseController
{
    private $field_status = 2;
    use ApiResponser,VendorTrait;
    use HomePageTrait;
    /**     * Get Company ShortCode     *     */
    public function categoryData(Request $request, $cid = 0)
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 12;
            $page = $request->has('page') ? $request->page : 1;
            $product_list = $request->has('product_list') ? $request->product_list : 'false';
            $mod_type = $request->has('type') ? $request->type : 'pick_drop';
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
            ])
                ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products')
                ->where('id', $cid)->first();
                // pr($category->toArray());
            $mode_of_service = "";
            // if (!empty($category)) {
            //     if (!empty($category->products) && count($category->products) > 0) {
            //         $mode_of_service = $category->products->first()->mode_of_service;
            //     }
            // }
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
            //return $this->listData($langId, $cid, strtolower($category->type->redirect_to), $userid, $product_list, $mod_type, $mode_of_service, $limit, $page);

            $response['category'] = $category;
            $response['filterData'] = $variantSets;
            $response['listData'] = $this->listData($langId, $cid, strtolower($category->type->redirect_to), $userid, $product_list, $mod_type, $mode_of_service=null, $limit, $page, $request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function listData($langId, $category_id, $type = '', $userid, $product_list, $mod_type, $mode_of_service = null, $limit = 12, $page = 1, $request)
    {
        $type = strtolower($type);
        $user = Auth::user();
        $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'pickup_delivery_service_area','subscription_mode')->where('id', '>', 0)->first();

        $latitude = !empty($request->latitude) ? $request->latitude : $preferences->Default_latitude;
        $longitude = !empty($request->longitude) ? $request->longitude : $preferences->Default_longitude;

        if ($type == 'vendor' && $product_list == 'false') {

            $vendor_ids = [];
            $vendor_categories = VendorCategory::where('category_id', $category_id)->where('status', 1)->get();

            foreach ($vendor_categories as $vendor_category) {
                if (!in_array($vendor_category->vendor_id, $vendor_ids)) {
                    $vendor_ids[] = $vendor_category->vendor_id;
                }
            }

            //return $vendor_categories;

            $vendorData = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->with('vendor_promo')->select('id', 'slug', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'latitude', 'longitude');

            $ses_vendors = $this->getServiceAreaVendors($user->latitude, $user->longitude, $mod_type);

            // if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            //     $latitude = $user->latitude;
            //     $longitude = $user->longitude;
            //     if ((empty($latitude)) && (empty($longitude))) {
            //         $latitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_latitude) : 0;
            //         $longitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_longitude) : 0;
            //     }
            //     $vendorData = $vendorData->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
            //         $query->select('vendor_id')
            //             ->whereRaw("ST_Contains(polygon, ST_GeomFromText('POINT(" . $latitude . " " . $longitude . ")'))");
            //     });
            // }

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $latitude = ($user->latitude) ? $user->latitude : $preferences->Default_latitude;
                $longitude = ($user->longitude) ? $user->longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $distance_to_time_multiplier = $preferences->distance_to_time_multiplier;
                $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'),DB::Raw("6371 * acos(cos(radians(" . $latitude . "))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(" . $longitude . "))
                        + sin(radians(" .$latitude. "))
                        * sin(radians(latitude))) * ".$distance_to_time_multiplier." as timeTaken"))->orderBy('vendorToUserDistance', 'ASC');
                $vendorData = $vendorData->whereIn('id', $ses_vendors);
            }
            $vendorData = $vendorData->where($mod_type, 1)->with('slot')->where('status', 1)->whereIn('id', $vendor_ids)->withAvg('product', 'averageRating')->paginate($limit, $page);


            //$vendorData = $vendorData->where($mod_type, 1)->where('status', 1)->whereIn('id', $vendor_ids)->with('slot')->withAvg('product', 'averageRating')->paginate($limit, $page);
            foreach ($vendorData as $vendor) {

               $vendor->vendorOffer = $vendor->vendor_promo->max('amount');
               $vendor->vendorNoOfRatings = $this->vendorNoOfRatings($vendor->products);
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

                // if (($preferences) && ($preferences->is_hyperlocal == 1) && ($user->latitude) && ($user->longitude)) {
                //     $vendor = $this->getVendorDistanceWithTime($user->latitude, $user->longitude, $vendor, $preferences);
                // }
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
            $latitude = !empty($request->latitude) ? $request->latitude : $preferences->Default_latitude;
            $longitude = !empty($request->longitude) ? $request->longitude : $preferences->Default_longitude;
            $vendor_ids = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->where('status', 1);
            $vendor_ids =  $vendor_ids->pluck('id')->toArray();
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
                    $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                    // $q->groupBy('product_id');
                }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                'tags.tag.translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }
            ])->select('products.category_id', 'products.id', 'products.mode_of_service', 'products.sku', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count', 'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.tags','products.is_recurring_booking')
                ->where('products.category_id', $category_id)->where('products.is_live', 1)->whereIn('products.vendor_id', $vendor_ids)->paginate($limit, $page);
                //->where('mode_of_service', $mode_of_service)
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
            $vendorDistance[] = [];
            if (!empty($products)) {
                 foreach ($products as $key => $product) {
                    if(empty($vendorDistance[$product->vendor_id])){
                        $vendorDistance[$product->vendor->id] = $this->getVendorDistanceWithTime($latitude, $longitude, $product->vendor, $preferences, $request->type);
                    }
                        if($vendorDistance[$product->vendor_id])
                        {
                            $product->lineOfSightDistance =$vendorDistance[$product->vendor_id]->lineOfSightDistance??0;
                            $product->timeofLineOfSightDistance =$vendorDistance[$product->vendor_id]->timeofLineOfSightDistance??0;
                        }

                }
            }

            return $products;
        } elseif ($type == 'pickup/delivery') {
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
            $vendorData = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->select('id', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id');

            if(isset($preferences->pickup_delivery_service_area) && ($preferences->pickup_delivery_service_area == 1)){

                if (!empty($pickup_latitude) && !empty($pickup_longitude)) {
                    $vendorData = $vendorData->whereHas('serviceArea', function ($query) use ($pickup_latitude, $pickup_longitude) {
                        $query->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$pickup_latitude." ".$pickup_longitude.")'))");
                    });
                }
            }
            $vendorData = $vendorData->where('status', 1)->whereIn('id', $vendor_ids)->with('slot', 'products')->paginate($limit, $page);
            // $avgRating = $vendorData->products->avg('averageRating');
            // $vendorData->avgRating = "fmwjkenf";
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
            ])->where('parent_id', $category_id)->orderBy('position', 'ASC')->get();
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
        } elseif ($type == 'product' || $type == 'on demand service' || $type == 'laundry') {

            $latitude = !empty($request->latitude) ? $request->latitude : $preferences->Default_latitude;
            $longitude = !empty($request->longitude) ? $request->longitude : $preferences->Default_longitude;


            $vendor_ids = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->where('status', 1);

            $vendor_ids =  $vendor_ids->pluck('id')->toArray();
            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
            if(empty($clientCurrency)){
                $clientCurrency = ClientCurrency::where('is_primary',1)->first();
            }
            $dollorCompare = $clientCurrency->doller_compare??1;
            $products = Product::has('vendor')->with([
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
                    $q1->where('ast.language_id', $langId);
                },
                'addOn.setoptions' => function ($q2) use ($langId) {
                    $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                    $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                    $q2->where('apt.language_id', $langId);
                },
                'media.image', 'translation' => function ($q) use ($langId) {
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                },
                'variant' => function ($q) use ($langId) {
                    $q->select('id', 'sku', 'product_id', 'title', 'quantity', 'price', 'barcode');
                    // $q->groupBy('product_id');
                }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                'tags.tag.translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }
            ])->select('products.category_id', 'mode_of_service', 'products.id', 'products.sku', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count', 'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.tags','products.is_recurring_booking')
            ->where('products.category_id', $category_id)->where('products.is_live', 1)
            ->where('mode_of_service', $mode_of_service)
            ->whereIn('products.vendor_id', $vendor_ids)
            ->paginate($limit, $page);


            if (!empty($products)) {
                foreach ($products as $key => $product) {
                    foreach ($product->addOn as $key => $value) {
                        foreach ($value->setoptions as $k => $v) {
                            if ($v->price == 0) {
                                $v->is_free = true;
                            } else {
                                $v->is_free = false;
                            }
                            $v->multiplier = $dollorCompare;
                        }
                    }

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
                    $variantData = $product->with(['variantSet' => function ($z) use ($langId, $p_id) {
                        $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                        $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                        $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                        $z->where('vt.language_id', $langId);
                        $z->where('product_variant_sets.product_id', $p_id)->orderBy('product_variant_sets.variant_type_id', 'asc');
                    }, 'variantSet.options' => function ($zx) use ($langId, $p_id) {
                        $zx->join('variant_option_translations as vt', 'vt.variant_option_id', 'variant_options.id')
                            ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id')
                            ->where('pvs.product_id', $p_id)
                            ->where('vt.language_id', $langId);
                    }])->where('id', $p_id)->first();
                    $product->variantSet = $variantData->variantSet;
                    $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                    $product->product_image = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                    $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;
                    $product->translation_description = ($product->translation->isNotEmpty()) ? html_entity_decode(strip_tags($product->translation->first()->body_html)) : '';
                    $product->translation_description = !empty($product->translation_description) ? mb_substr($product->translation_description, 0, 70) . '...' : '';
                    $product->variant_multiplier = $dollorCompare;
                    $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
                    $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
                    $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;
                    if ($product->variant->count() > 0) {
                        foreach ($product->variant as $k => $v) {
                            $product->variant[$k]->multiplier = $dollorCompare;
                        }
                    } else {
                        $product->variant =  $product;
                    }
                }
            }

            $vendorDistance[] = [];
            if (!empty($products)) {
                 foreach ($products as $key => $product) {
                    if(empty($vendorDistance[$product->vendor_id])){
                        $vendorDistance[$product->vendor->id] = $this->getVendorDistanceWithTime($latitude, $longitude, $product->vendor, $preferences, $request->type);
                    }
                        if($vendorDistance[$product->vendor_id])
                        {
                            $product->lineOfSightDistance =$vendorDistance[$product->vendor_id]->lineOfSightDistance??0;
                            $product->timeofLineOfSightDistance =$vendorDistance[$product->vendor_id]->timeofLineOfSightDistance??0;
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
            $type = $request->has('type') ? $request->type : 'delivery';
            $setArray = $optionArray = array();
            $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
            if ($request->has('variants') && !empty($request->variants)) {
                $setArray = array_unique($request->variants);
            }
            $startRange = 0;
            $endRange = 20000;
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
            $products = Product::byProductCategoryServiceType($type)->with([
                'category.categoryDetail', 'media.image',
                'translation' => function ($q) use ($langId) {
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    $q->groupBy('language_id','product_id');
                },
                'variant' => function ($q) use ($langId, $variantIds,$order_type) {
                    $q->select('sku', 'product_id', 'quantity', 'price', 'barcode','price');
                    if (!empty($variantIds)) {
                        $q->whereIn('id', $variantIds);
                    }
                    // if (!empty($order_type) && $order_type == 'low_to_high') {
                    //     $q->orderBy('price', 'asc');
                    // }
                    // if (!empty($order_type) && $order_type == 'high_to_low') {
                    //     $q->orderBy('price', 'desc');
                    // }

                    $q->groupBy('product_id');
                },
            ])->select('products.id', 'products.sku', 'products.url_slug','products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.sell_when_out_of_stock', 'products.requires_shipping', 'products.Requires_last_mile', 'products.averageRating','products.minimum_order_count','products.batch_count', 'products.is_show_dispatcher_agent', 'products.is_slot_from_dispatch', 'products.tags','products.is_recurring_booking')
                ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
                ->join('product_translations', 'product_translations.product_id', '=', 'products.id') // Or whatever the join logic is
                ->withCount('OrderProduct')
                ->where('products.category_id', $cid)
                ->where('products.is_live', 1)
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

            $paginate = $request->has('limit') ? $request->limit : 12;
            $products = $products->groupBy('id');
            $products = $products->paginate($paginate);

            if (!empty($products)) {
                foreach ($products as $key => $product) {
                    $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                    foreach ($product->variant as $k => $v) {
                        $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
                    }
                }
            }
            return $this->successResponse($products);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
     # get product faq
     public function getcategoryKycDocument(Request $request,$domain = ''){
        $user = Auth::user();
        if (!$user->id) {
            $cart = Cart::where('unique_identifier', $user->system_user);
        } else {
            $cart = Cart::where('user_id', $user->id);
        }
        $cart = $cart->first();
        $is_alrady_submit = [];
        if ($cart) {
            $is_alrady_submit = CaregoryKycDoc::where('cart_id',$cart->id)->pluck('category_kyc_document_id');
            $is_alrady_submit = $is_alrady_submit->isNotEmpty() ? $is_alrady_submit->toArray() : [];
        }

        $category_ids = explode(",",$request->category_ids);

        $langId = Auth::user()->language;

        if(empty($langId))
        $langId = ClientLanguage::orderBy('is_primary','desc')->value('language_id');
        $product_faqs=[];

        $category_kyc_documents = CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_ids){
            $q->whereIn('category_id',$category_ids);
           })->with(['translations' => function ($qs) use($langId){
                $qs->where('language_id',$langId);
            },'primary'])
            ->whereNotIn('id',$is_alrady_submit)->get();

            if(!$category_kyc_documents){
                return response()->json(['error' => 'No record found.'], 404);
            }
            return response()->json([
                'data' => $category_kyc_documents,
            ]);

    }
    public function getHourlyBasePrice(Request $request)

    {

        if($request->has('cat_id'))
        {
            $category_id = $request->get('cat_id');
            $product = ProductVariant::whereHas('product', function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })->orderBy('price','asc')->first();

             return $this->successResponse($product,null,200);

        }
        return $this->errorResponse('No Product Found ', 404);

    }


}
