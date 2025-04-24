<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Front\PromoCodeController;
use Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\{Attribute, User,ClientLanguage,ProductFaq, Product, Category, ProductVariantSet, ProductVariant, ProductAddon, ProductRelated, ProductUpSell, ProductCrossSell, ClientCurrency, Vendor, Brand, BundleupProduct, ProductBooking, ProductFaqSelectOption, TagTranslation,Tag,DeliverySlotProduct, DeliverySlot, OrderProductRating, UserAddress};
use Validation;
use DB;
use Carbon\CarbonPeriod;
use App\Http\Traits\{ApiResponser,ProductTrait, ProductActionTrait};
use App\Models\ProductInquiry;

class ProductController extends BaseController
{
    private $field_status = 2;
    use ApiResponser,ProductTrait, ProductActionTrait;
    /**
     * Get Company ShortCode
     *
     */
    public function productById_old(Request $request, $pid){
        $pvIds = array();
        $userid = Auth::user()->id;
        $langId = Auth::user()->language;
        $proVariants = ProductVariant::select('id', 'product_id')->where('product_id', $pid)->get();
        if($proVariants){
            foreach ($proVariants as $key => $value) {
                $pvIds[] = $value->id;
            }
        }

        $products = Product::with(['inwishlist' => function($qry) use($userid){
                        $qry->where('user_id', $userid);
                    },
                    'variant' => function($v){
                        $v->select('id', 'sku', 'product_id', 'title', 'quantity','price','markup_price','barcode','tax_category_id');
                    },
                    'variant.vimage.pimage.image', 'vendor', 'media.image', 'related', 'upSell', 'crossSell',
                    'addOn' => function($q1) use($langId){
                        $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                        $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                        $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                        $q1->where('set.status', 1)->where('ast.language_id', $langId);
                    },
                    'variantSet' => function($z) use($langId){
                        $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                        $z->join('variant_translations as vt','vt.variant_id','vr.id');
                        $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                        $z->where('vt.language_id', $langId);
                    },
                    'variantSet.options' => function($zx) use($langId, $pvIds){
                        $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id')
                        ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id')
                        ->whereIn('pvs.product_variant_id', $pvIds)
                        ->where('vt.language_id', $langId);
                    },
                    'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                        $q->where('language_id', $langId);
                    },
                    'addOn.setoptions' => function($q2) use($langId){
                        $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                        $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                        $q2->where('apt.language_id', $langId);
                    },
                    'ProductAttribute' => function($q){
                        $q->whereIn('key_name', ['Transmission', 'Fuel Type', 'Seats']);
                    }, 'ProductAttribute.attributeOption'
                    ])->select('id', 'sku', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'is_new', 'is_featured', 'is_physical', 'has_inventory', 'has_variant', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating','minimum_order_count','batch_count')
                    ->where('id', $pid)
                    ->first();
        if(!$products){
            return response()->json(['error' => 'No record found.'], 404);
        }

        $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
        foreach ($products->variant as $key => $value) {
            $products->variant[$key]->multiplier = $clientCurrency->doller_compare ?? 1;
        }

        foreach ($products->addOn as $key => $value) {
            foreach ($value->setoptions as $k => $v) {
                $v->multiplier = $clientCurrency->doller_compare;
            }
        }
        foreach ($products->variant as $key => $value) {
            if($products->sell_when_out_of_stock == 1){
                $value->stock_check = '1';
            }elseif($value->quantity > 0){
                $value->stock_check = '1';
            }else{
                $value->stock_check = 0;
            }
        }

        $response['products'] = $products;
        $response['relatedProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'relate', $products->related);
        $response['upSellProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'upSell', $products->upSell);
        $response['crossProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'cross', $products->crossSell);
        unset($products->related);
        unset($products->upSell);
        unset($products->crossSell);
        $response['products'] = $products;
        return response()->json([
            'data' => $response,
        ]);
    }

    public function productById(Request $request, $pid)
    {
        try{
            $pvIds = array();
            $user = Auth::user();
            $langId = $user->language;
            $userid = $user->id;
            $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
            $limit = 6; // Number of frequently bought products to retrieve
            $product = Product::with(['variant','inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },'product_availability',
                        'measurements' 
                        => function($query) {
                            $query->with('productVariants:id,title');
                        }
                        ,
                        'category.categoryDetail', 'category.categoryDetail.translation' => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                            ->where('category_translations.language_id', $langId);
                        },
                        // 'variant' => function($v){
                        //     $v->select('id', 'sku', 'product_id', 'title', 'quantity','price','markup_price','cost_price','barcode','tax_category_id')
                        //     ->groupBy('product_id'); // return first variant
                        // },

                        'variant.media.pimage.image', 'vendor', 'media.image', 'related', 'upSell', 'crossSell', 'reviews.user' => function($rev) {
                            $rev->select('users.id', 'users.name', 'users.email', 'users.image');
                        }, 'reviews.reviewFiles',
                        'addOn' => function($q1) use($langId){
                            $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                            $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                            $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                            $q1->where('set.status', 1)->where('ast.language_id', $langId);
                        },
                        'variantSet' => function($z) use($langId){
                            $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                            $z->join('variant_translations as vt','vt.variant_id','vr.id');
                            $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                            $z->where('vt.language_id', $langId);
                            $z->orderBy('vr.position');
                        },
                        'variantSet.options' => function($zx) use($langId, $pvIds, $pid){
                            $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id')
                            ->join('product_variants','pvs.product_variant_id','product_variants.id')
                            ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id','product_variants.quantity')
                            ->where('pvs.product_id', $pid)
                            ->where('vt.language_id', $langId);
                        },
                        'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $langId);
                        },
                        'addOn.setoptions' => function($q2) use($langId){
                            $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                            $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                            $q2->where('apt.language_id', $langId)->groupBy(['addon_options.id', 'apt.language_id']);
                        },

                    ]);
                    $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price','product_measurment']);
                    if(@$getAdditionalPreference['is_rental_weekly_monthly_price']){
                        $product = $product->with(['OrderProduct' => function($q) {
                            $q->select('end_date_time', 'product_id', 'start_date_time');
                            $q->whereDate('end_date_time', '>', now());
                        }]);
                    }
                    $product = $product->select('id','is_live', 'sku', 'url_slug', 'description', 'weight', 'weight_unit', 'vendor_id', 'is_new', 'is_featured', 'is_physical', 'has_inventory', 'has_variant', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'minimum_order_count', 'batch_count', 'minimum_duration', 'minimum_duration_min', 'additional_increments', 'additional_increments_min', 'buffer_time_duration', 'buffer_time_duration_min', 'returnable', 'replaceable', 'return_days', 'is_long_term_service', 'service_duration', 'is_show_dispatcher_agent', 'is_slot_from_dispatch', 'tags', 'mode_of_service', 'is_recurring_booking', 'latitude', 'longitude', 'address', 'calories', 'inquiry_only', 'security_amount', 'captain_name', 'captain_profile', 'captain_description');


                    $product = $product->where('id', $pid)
                        ->first();

            if(!$product){
                return response()->json(['error' => 'No record found.'], 404);
            }

            // if(@$product->product_availability && @$product->OrderProduct){

            //     foreach($product->OrderProduct as $OrderProducts){
            //         // dd($OrderProducts);
            //         $dates = [];
            //         if(@$OrderProducts->start_date_time && @$OrderProducts->end_date_time){
            //             $period = CarbonPeriod::create(date('Y-m-d',strtotime($OrderProducts->start_date_time)), date('Y-m-d',strtotime($OrderProducts->end_date_time)));

            //             foreach ($period as $date) {
            //                 $dates[] =  $date->format('Y-m-d');
            //             }

            //             if(@$dates){
            //                 foreach($product->product_availability as $product_availability){
            //                     foreach($dates as $date){
            //                         if( date('Y-m-d',strtotime($product_availability->date_time)) == $date){
            //                             $product_availability->not_available = 1;
            //                         }
            //                     }

            //                 }
            //             }
            //     }
            // }

            // }

            $product->is_rented = 0;
            if(@$product->OrderProduct[0]->end_date_time){
                $product->is_rented = 1;
            }

            if ($this->checkTemplateForAction(8)) {
                $this->RecentView($pid);
            }
            $productBookingsCount = ProductBooking::whereHas('products', function ($q) use ($product) {
                $q->whereHas('product', function($q) use($product){
                    $q->where('vendor_id', $product->vendor_id);
                });
            })->count();
            $product->vendor->is_vendor_closed = 0;
            if($product->vendor->show_slot == 0){
                if( ($product->vendor->slotDate->isEmpty()) && ($product->vendor->slot->isEmpty()) ){
                    $product->vendor->is_vendor_closed = 1;
                }else{
                    $product->vendor->is_vendor_closed = 0;
                    if($product->vendor->slotDate->isNotEmpty()){
                        $product->vendor->opening_time = Carbon::parse($product->vendor->slotDate->first()->start_time)->format('g:i A');
                        $product->vendor->closing_time = Carbon::parse($product->vendor->slotDate->first()->end_time)->format('g:i A');
                    }elseif($product->vendor->slot->isNotEmpty()){
                        $product->vendor->opening_time = Carbon::parse($product->vendor->slot->first()->start_time)->format('g:i A');
                        $product->vendor->closing_time = Carbon::parse($product->vendor->slot->first()->end_time)->format('g:i A');
                    }
                }
            }
            $allReviews = array_column($product->vendor->products()->with('reviews')->get()->toArray(),'reviews');
            $rating = array_sum(array_column($allReviews,'rating'));
            $product->vendor_rating = $rating; 

            $slotsDate = 0;
            if($product->vendor->is_vendor_closed){
                $slotsDate = findSlot('',$product->vendor->id,'');
                $product->delaySlot = $slotsDate;
                $product->vendor->closed_store_order_scheduled = (($slotsDate)?$product->vendor->closed_store_order_scheduled:0);
            }else{
                $product->delaySlot  = 0;
                $product->vendor->closed_store_order_scheduled = 0;
            }


            $product->is_wishlist = @$product->inwishlist ? 1 : 0;
            $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
            foreach ($product->variant as $key => $value) {
                $product->variant[$key]->multiplier = $clientCurrency->doller_compare;
                $product->variant[$key]->price *= $product->variant[$key]->multiplier;
                $product->variant[$key]->compare_at_price *= $product->variant[$key]->multiplier;
                $product->variant[$key]->variant_title = $product->variant[$key]->optionData ?? '';
            }
            $addonList = array();
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
            $data_image = array();
            /*  if variant has image return variant images else product images  */
            $variant_id = 0;
            foreach ($product->variant as $key => $value) {
                $variant_id = $value->id;
                if($product->sell_when_out_of_stock == 1){
                    $value->stock_check = '1';
                }elseif($value->quantity > 0){
                    $value->stock_check = '1';
                }else{
                    $value->stock_check = 0;
                }
                if($value->media && count($value->media) > 0){
                    foreach ($value->media as $media_key => $media_value) {
                        $data_image[$media_key]['product_variant_id'] = $media_value->product_variant_id;
                        $data_image[$media_key]['media_id'] = $media_value->product_image_id;
                        $data_image[$media_key]['is_default'] = 0;
                        $data_image[$media_key]['image'] = $media_value->pimage->image;
                    }
                }else{
                    foreach ($product->media as $media_key => $media_value) {
                        $data_image[$media_key]['product_id'] = $media_value->product_id;
                        $data_image[$media_key]['media_id'] = $media_value->media_id;
                        $data_image[$media_key]['is_default'] = $media_value->is_default;
                        $data_image[$media_key]['image'] = $media_value->image;
                    }
                }
            }
            if($product->variantSet){
                foreach ($product->variantSet as $set_key => $set_value) {
                    foreach ($set_value->options as $opt_key => $opt_value) {
                        $opt_value->value = ($opt_key == 0 )? true : false;
                    }
                }
            }

            $product->product_media = $data_image;
            $product->share_link = getServerURL() . $product->vendor->slug . '/product/' . $product->url_slug;

            $promoCodeController = new PromoCodeController();
            $coupon_list = $promoCodeController->coupon_code_list($product->id, $product->vendor_id);


            $response['coupon_list'] = $coupon_list;
            if($product->is_long_term_service == 1){
                $product_id = $product->LongTermProducts->product_id;
                $url_slug   = $product->LongTermProducts->product->url_slug;
                $vendor_slug   = $product->vendor->slug;

                $LongTermProducts                    = $this->getProduct($product->LongTermProducts->product_id,$vendor_slug,$url_slug,$user,$langId);
                $LongTermProducts->long_term_product = $product->LongTermProducts;
                $addon =  $product->LongTermProducts->addons->pluck('option_id','addon_id')->toArray() ?? [];
                if($product->ServicePeriod){
                    $product->ServicePeriods = $product->ServicePeriod->pluck('service_period')->toArray();
                }
                $LongTermProducts->period =config('constants.Period');

                $LongTermProducts->product_addon     =  $addon;
                $product->longTermServiceProduct     = $LongTermProducts;
                $response['products'] = $product;
                return response()->json([
                    'data' => $response,
                ]);
            }
            // Product Attribute
            $product_attr = [];
            if( !empty($product->ProductAttribute) ) {
                foreach( $product->ProductAttribute as $key => $value ) {
                    if( !empty($value->attribute) && !empty($value->attribute->status) && $value->attribute->status == 1 ) {
                        $product_attr[$key]['title'] = optional($value->attribute)->title ?? '';
                        $product_attr[$key]['attribute_id'] = $value->attribute_id ?? '';
                        $attribute = $value->attribute;
                        $img = $attribute->icon ? $attribute->icon['proxy_url'] . '100/100' . $attribute->icon['image_path'] : '';
                        $product_attr[$key]['icon'] = $img;
                        
                        if( !empty($value->attribute) && $value->attribute->type != 4 && $value->attribute->type != 6) {
                            $product_attr[$key]['value'] = optional($value->attributeOption)->title ?? '';
                        }
                        else {
                            $product_attr[$key]['value'] = $value['key_value'] ?? '';
                        }
                    }
                }
            }

            $attr_id = '';
            $attr_array = [];
            foreach($product_attr as $pro_att_key => $pro_att_val) {

                if( empty($attr_id) || ($pro_att_val['attribute_id'] != $attr_id) ) {
                    $attr_id = $pro_att_val['attribute_id'];
                    $attr_array[$pro_att_val['title']][$pro_att_key]['title'] = $pro_att_val['title'];
                    $attr_array[$pro_att_val['title']][$pro_att_key]['attribute_id'] = $pro_att_val['attribute_id'];
                    $attr_array[$pro_att_val['title']][$pro_att_key]['value'] = $pro_att_val['value'];
                }
                else {
                    $attr_id = $pro_att_val['attribute_id'];
                    $attr_array[$pro_att_val['title']][$pro_att_key]['title'] = $pro_att_val['title'];
                    $attr_array[$pro_att_val['title']][$pro_att_key]['attribute_id'] = $pro_att_val['attribute_id'];
                    $attr_array[$pro_att_val['title']][$pro_att_key]['value'] = $pro_att_val['value'];
                }
            }
            $product->product_reviews = '';
            $product->product_reviews = OrderProductRating::with('userimage')->select('*','created_at as time_zone_created_at')->where(['product_id' => $product->id])->get();
            $product->review_count = $product->product_reviews->whereNotNull('review')->count();
            
            $product->average_rating = round($product->product_reviews->avg('rating') ?? 0, 1);
            // Calculate ratings distribution
            $totalReviews = $product->product_reviews->count();
            $ratingsDistribution = [
                ['stars' => 5, 'percentage' => 0],
                ['stars' => 4, 'percentage' => 0],
                ['stars' => 3, 'percentage' => 0],
                ['stars' => 2, 'percentage' => 0],
                ['stars' => 1, 'percentage' => 0],
                ['stars' => 0, 'percentage' => 0]
            ];

            if ($totalReviews > 0) {
                // Count reviews for each star rating
                $starCounts = [
                    5 => $product->product_reviews->where('rating', '5')->count(),
                    4 => $product->product_reviews->where('rating', '4')->count(),
                    3 => $product->product_reviews->where('rating', '3')->count(),
                    2 => $product->product_reviews->where('rating', '2')->count(),
                    1 => $product->product_reviews->where('rating', '1')->count(),
                    0 => $product->product_reviews->where('rating', '0')->count()
                ];

                // Calculate percentage for each star rating
                foreach ($starCounts as $stars => $count) {
                    // Find the correct index in ratingsDistribution array
                    $index = array_search($stars, array_column($ratingsDistribution, 'stars'));
                    if ($index !== false) {
                        $ratingsDistribution[$index]['percentage'] = round(($count / $totalReviews) * 100);
                    }
                }
            }

            $product->ratings_distribution = $ratingsDistribution;
            
            $frequentlyBoughtProducts = Product::with(['media.image', 'vendor', 'translation', 'variant', 'productVariantByRoles'])->join('order_vendor_products', 'products.id', '=', 'order_vendor_products.product_id')->join('orders', 'order_vendor_products.order_id', '=', 'orders.id')->where('products.vendor_id', $product->vendor->id)->select('products.*')
            ->groupBy('products.id')->orderByRaw('COUNT(products.id) DESC')->limit($limit)->get();

            $suggested_category_products = $suggested_brand_products = $suggested_vendor_products = [];

            $suggested_product = Product::with(['media.image','vendor', 'translation', 'variant', 'productVariantByRoles','categoryName']);
            if( !empty($product->category->category_id) ) {
                $suggested_category_products = $suggested_product->where('category_id', $product->category->category_id)->groupBy('id')->orderby('id', 'desc')->limit(20)->get();
            }


            foreach($suggested_category_products as $r_product){
                foreach ($r_product->variant as $key => $value) {
                    if(isset($r_product->variant[$key])){
                        $r_product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
                    }
                }
            }

            if( !empty($product->brand_id) ) {
                $suggested_product = Product::with(['media.image', 'vendor', 'translation', 'variant']);
                $suggested_brand_products = $suggested_product->where('brand_id', $product->brand_id)->orderby('id', 'desc')->limit(20)->get();
            }


                foreach($suggested_brand_products as $r_product){
                foreach ($r_product->variant as $key => $value) {
                    if(isset($r_product->variant[$key])){
                    $r_product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
                    }
                }
            }

            if( !empty($product->vendor_id) ) {
                $suggested_product = Product::with(['media.image', 'vendor', 'translation', 'variant','categoryName','category.categoryDetail.translation'])->whereNotIn('id',[$product->id]);
                $suggested_vendor_products = $suggested_product->where('vendor_id', $product->vendor_id)->orderby('id', 'desc')->limit(20)->get();
            }


                foreach($suggested_vendor_products as $r_product){
                foreach ($r_product->variant as $key => $value) {
                    if(isset($r_product->variant[$key])){
                    $r_product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
                    }
                }
            }
            

            $detail = [
            'Mileage',
            'Engine',
            'Transmission',
            'BHP',
            'Seats',
            'Boot Space',
            'Fuel Type'
            ];
            $additional = [];
            $desc = [];
            foreach ($product->ProductAttribute as $productAttribute) {
                $attribute = $productAttribute->attribute;
                $img = $attribute->icon['proxy_url'] . '100/100' . $attribute->icon['image_path'];
                if ($productAttribute->attributeOption()->exists()) {
                    $title = $productAttribute->attributeOption->title ?? $productAttribute->key_value;
                    if(!in_array($productAttribute->key_name, $detail)){
                        $additional[] = [
                            'title' => $productAttribute->key_name,
                            'value' => $title,
                            'img' => $img
                        ];
                    }
                    $desc[$productAttribute->key_name]['title'] = $title;
                    $desc[$productAttribute->key_name]['img'] = $img;
                }
            }
            $accordianData = [];
            $desc = array_search('Specification', $additional);
            $accordianData[] = [
                'title' => 'Specification',
                'value' => $desc['Specification']['title'] ?? ''
            ];
            $accordianData[] = [
                'title' => 'Cancellation',
                'value' => $product->returnable ? "Cancellable" : "Non-Cancelable"
            ];
            $accordianData[] = [
                'title' => 'Commercial Owner',
                'value' => $desc['Commercial Owner']['title'] ?? ''
            ];
            $accordianData[] = [
                'title' => 'Security Amount',
                'value' => $product->security_amount ? \Session::get('currencySymbol').$product->security_amount. ' need to be paid as security amount' : 'No Security Amount'
            ];
            $accordianData[] = [
                'title' => 'Captain Info',
                'value' => [
                    'name' => $product->captain_name,
                    'description' => $product->captain_description,
                    'profile' => $product->captain_profile
                ]
            ];
            if ($getAdditionalPreference['product_measurment'] == 1) {
                if ($product->has_variant == 1) {
                    $groupedData = [];
                    foreach ($product->measurements as $measurement) {
                        $filteredVariants = $measurement->productVariants->filter(function ($variant) use ($measurement) {
                            return $variant->id == $measurement->pivot->product_variant_id;
                        });
            
                        foreach ($filteredVariants as $variant) {
                            $parts = explode('-', $variant->title);
                            $letter = end($parts);
            
                            $productVariantId = $measurement->pivot->product_variant_id;
                            if (!isset($groupedData[$productVariantId])) {
                                $groupedData[$productVariantId] = [
                                    'variant_id' => $productVariantId,
                                    'title' => $letter,
                                    'data' => []
                                ];
                            }
            
                            $groupedData[$productVariantId]['data'][] = [
                                'measurement_key' => $measurement->key,
                                'measurement_key_id' => $measurement->id,
                                'key_value' => $measurement->pivot->key_value,
                                'product_variant_id' => $productVariantId,
                                'product_variant_title' => $letter,
                                'metric'=>'cm'
                            ];
                        }
                    }
                    unset($product->measurements);
                    $product->measurements = array_values($groupedData);
                } else {
                    $groupedData = [];
                    foreach ($product->measurements as $measurement) {
                        $groupedData[] = [
                            'measurement_key' => $measurement->key,
                            'measurement_key_id' => $measurement->id,
                            'key_value' => $measurement->pivot->key_value,
                            'product_id' => $measurement->pivot->product_id,
                            'metric'=>'cm'
                        ];
                    }
                    unset($product->measurements);
                    $product->measurements = $groupedData;
                }
            }
            
            
            
            $response['suggested_category_products'] =  $suggested_category_products;
            $response['suggested_brand_products'] =  $suggested_brand_products;
            $response['suggested_vendor_products'] =  $suggested_vendor_products;


            
            $response['products'] = $product;
            $response['frequently_bought'] = $frequentlyBoughtProducts;
            $response['relatedProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'relate', $product->related, $request->service);
            $response['upSellProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'upSell', $product->upSell, $request->service);
            $response['crossProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'cross', $product->crossSell, $request->service);
            $response['product_attribute'] = $product_attr;
            $response['additional_features'] = $additional;
            $response['productBookingsCount'] = $productBookingsCount;
            $response['accordianData'] = $accordianData;
            // $response['product_variant'] = ProductVariant::select('id', 'sku', 'product_id', 'title', 'quantity','price','markup_price','cost_price','barcode','tax_category_id')->where('product_id',$pid)->get();
            /* group by in query return data only for key - 0 so using 0 */
            $is_return_days = 0;
            if(((@$product->returnable && @$product->vendor->return_request) || $product->replaceable) && ($product->return_days > 0)){
                $is_return_days = 1;
                $product->is_return_days = $is_return_days;
            }
            if(isset($product->variant[0]->media) && !empty($product->variant[0]->media)){
                unset($product->variant[0]->media);
            }
            unset($product->related);
            unset($product->media);
            unset($product->upSell);
            unset($product->crossSell);
            $response['products'] = $product;
            $bundle_product_ids = BundleupProduct::where('product_id',$pid)->get()->pluck('related_product_id')->toArray();
            $bundeup_products=Product::with('variants')->whereIn('id',$bundle_product_ids)->get();
            $response['bundleup_products'] =$bundeup_products;
            $response['vendor_location'] = $product->vendor->address;
            return response()->json([
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }


    }

    public function checkProductAvailibility(Request $request)
    {
      try {
          $block_time = explode('-', $request->blocktime);
          $start_time = date("Y-m-d H:i:s",strtotime($request->selectedStartDate));
          $end_time = date("Y-m-d H:i:s",strtotime($request->selectEndDate));
          $product_variant_data = array();
          $product_variant_id =  ProductVariantSet::where(['variant_option_id'=>$request->variant_option_id,'product_id'=>$request->product_id])->pluck('product_variant_id');
          $product_variant_id = $product_variant_id->toArray();
          $ProductBooking  = ProductBooking::whereIn('variant_id',$product_variant_id)->where('product_id',$request->product_id)
                              ->where(function ($query) use ($start_time , $end_time ){
                                  $query->where('start_date_time', '<=', $end_time)
                                        ->where('end_date_time', '>=', $start_time);
                              })->pluck('variant_id')->toArray();
          $available_product_variant = array_values(array_diff($product_variant_id, $ProductBooking));
          if(isset($available_product_variant[0])){
            $product_variant_data =  ProductVariant::where('id',$available_product_variant[0])->with(['product','checkIfInCart'])->first();
          }
          $returnarr =  array();
          $returnarr['available_product_variant'] =  @$available_product_variant[0];
          $returnarr['product_variant_data'] = $product_variant_data;
          $returnarr['product_id'] =  $request->product_id;
          $returnarr['start_time'] =  $start_time;
          $returnarr['end_time'] =  $end_time;
          $returnarr['variant_option_id'] = $request->variant_option_id;
          return response()->json(array('success' => true, 'variant_data'=>$returnarr ,'message'=>'Available product data.'));
        } catch (\Exception $e) {
          return response()->json(array('error' => false, 'message'=>'Something went wrong.'));
        }

    }

    public function metaProduct($langId, $multiplier, $for = 'relate', $productArray = [], $service="",$product =null)
    {
        if(empty($productArray)){
            return $productArray;
        }

        $productIds = array();
        foreach ($productArray as $key => $value) {
            if($for == 'relate'){
                $productIds[] = $value->related_product_id;
            }
            if($for == 'upSell'){
                $productIds[] = $value->upsell_product_id;
            }
            if($for == 'cross'){
                $productIds[] = $value->cross_product_id;
            }
        }
        $products = Product::with(['media' => function($q){
                            $q->groupBy('product_id');
                        }, 'media.image','ProductAttribute','ProductAttribute.attributeOption','vendor',
                        'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId){
                            $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode');
                            $q->groupBy('product_id');
                        },
                    ])->select('id', 'sku', 'averageRating','calories')
                    ->whereIn('id', $productIds);



        if($for == 'similiar_product_user'){
            $products = $products->where('vendor_id',$product->vendor_id)->whereNotIn('id',[$product->id]);

        }
        if($for == 'similiar_product_category'){
            $products = $products->where('category_id',$product->category_id); 
        }


        $products = $products->get();

        if(!empty($products)){
            $fields = [];
            foreach ($products as $key => $value) {
                foreach ($value->variant as $k => $v) {
                    $value->variant[$k]->multiplier = $multiplier;
                }

                foreach ($value->ProductAttribute as $productAttribute) {
                    if ($productAttribute->attributeOption()->exists()) {
                        if(!empty($title = $productAttribute->attributeOption->title)){
                            $fields[$productAttribute->key_name] = $title;
                        }else{
                            $fields[$productAttribute->key_name] = $productAttribute->key_value;
                        }
                    }
                }
                if(!empty($fields)){
                    if($service == 'rental'){
                        $value->transmission = $fields['Transmission'] ?? '';
                        $value->fuel_type = $fields['Fuel Type'] ?? '';
                        $value->Seats = $fields['Seats'] .' Seats'?? '';
                    } else{
                        $value->cabins = $fields['Cabins'] ?? '0' . ' Cabins';
                        $value->baths = $fields['Baths'] ?? '0' . ' Baths';
                        $value->berths = $fields['Berths'] ?? '0' .' Berths';
                    }
                }
            }
        }
        return $products;
    }

    /**
     * Display product variant data
     *
     * @return \Illuminate\Http\Response
     */
    public function getVariantData(Request $request, $sku)
    {
        try{
            if(!$request->has('variants')){
                return $this->errorResponse('Variants should not be empty.', 422);
            }
            if(!$request->has('options')){
                return $this->errorResponse('Options should not be empty.', 422);
            }
            $product = Product::with('category.categoryDetail')->where('sku', $sku)->first();
            if(!$product){
                return $this->errorResponse('No record found.', 404);
            }

            $langId = Auth::user()->language;
            $userid = Auth::user()->id;
            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();

            $pv_ids = array();

            foreach ($request->options as $key => $value) {
                $newIds = array();

                $product_variant = ProductVariantSet::where('variant_type_id', $request->variants[$key])
                    ->where('variant_option_id', $request->options[$key]);

                if (!empty($pv_ids)) {
                    $product_variant = $product_variant->whereIn('product_variant_id', $pv_ids);
                }

                $product_variant = $product_variant->where('product_id', $product->id)->get();

                if ($product_variant) {
                    foreach ($product_variant as $key => $value) {
                        $newIds[] = $value->product_variant_id;
                    }
                }
                $pv_ids = $newIds;
            }

            if(empty($pv_ids)){
                return $this->errorResponse('Invalid product sets or product has been removed.', 404, ['variant_empty'=>true]);
            }


            $variantData = ProductVariant::join('products as pro', 'product_variants.product_id', 'pro.id')
                        ->with(['set','wishlist', 'product.media.image', 'media.pimage.image', 'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $langId);
                        },'wishlist' =>  function($q) use($userid){
                            $q->where('user_id', $userid);
                        }])->select('product_variants.id','product_variants.sku', 'product_variants.quantity', 'product_variants.price','product_variants.incremental_price',  'product_variants.barcode', 'product_variants.product_id', 'pro.sku', 'pro.url_slug', 'pro.weight', 'pro.weight_unit', 'pro.vendor_id', 'pro.is_new', 'pro.is_featured', 'pro.is_physical', 'pro.has_inventory', 'pro.has_variant', 'pro.sell_when_out_of_stock', 'pro.requires_shipping', 'pro.Requires_last_mile', 'pro.averageRating')->where('product_variants.id', $pv_ids[0])->first();
            if($variantData->sell_when_out_of_stock == 1){
                $variantData->stock_check = '1';
            }elseif($variantData->quantity > 0){
                $variantData->stock_check = '1';
            }else{
                $variantData->stock_check = 0;
            }


            $data_image = array();
            $variantData->inwishlist = $variantData->wishlist;
            $variantData->is_wishlist = $product->category->categoryDetail->show_wishlist;
            if($variantData->media && count($variantData->media) > 0){
                foreach ($variantData->media as $media_key => $media_value) {
                    $data_image[$media_key]['product_variant_id'] = $media_value->product_variant_id;
                    $data_image[$media_key]['media_id'] = $media_value->product_image_id;
                    $data_image[$media_key]['image'] = $media_value->pimage->image;
                }
            }else{
                foreach ($variantData->product->media as $media_key => $media_value) {
                    $data_image[$media_key]['product_id'] = $media_value->product_id;
                    $data_image[$media_key]['media_id'] = $media_value->media_id;
                    $data_image[$media_key]['image'] = $media_value->image;
                }
            }
            $variantData->product_media = $data_image;

            if ($variantData) {
                $variantData->multiplier = $clientCurrency->doller_compare;
                $variantData->productPrice = $variantData->price * $clientCurrency->doller_compare;
            }
            unset($variantData->media);
            unset($variantData->product->media);
            return $this->successResponse($variantData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    # get all product tags

    public function getAllProductTags(Request $request)
    {
        try{
            $langId = Auth::user()->language;
            $userid = Auth::user()->id;

            $get_all_tags = Tag::with(['translations' =>  function($q)use($langId){
                $q->where('language_id',$langId);
            }])->get();

            return $this->successResponse($get_all_tags);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
     # get product faq
     public function getProductFaq(Request $request, $product_id){
        $langId = Auth::user()->language;

        if(empty($langId))
        $langId = ClientLanguage::orderBy('is_primary','desc')->value('language_id');

        $product_faqs = ProductFaq::where('product_id',$product_id)->with(['translations' => function ($qs) use($langId){
            $qs->where('language_id',$langId);
        }])->get();
        foreach($product_faqs as $faq){
        if($faq->file_type == 'selector' || $faq->file_type == 'checkbox'){
            $faq->options = ProductFaqSelectOption::with(['translations'])
                                                        ->where(['product_faq_id' => $faq->id])
                                                        ->get();
           }
        }

        if(!$product_faqs){
            return response()->json(['error' => 'No record found.'], 404);
        }
        return response()->json([
            'data' => $product_faqs,
        ]);
    }
    public function getShippingProductDeliverySlots(Request $request){
        try {
            $request->validate(
                [
                    'delivery_date' => 'required',
                    'product_id' => 'required'
                ],
                [
                    'vendor_id.required' => 'Delivery date is required',
                    'pincode.required' => 'Product id is required'
                ]
            );
            $product_id = $request->product_id;
            $delivery_date = $request->delivery_date;
            $mytime = Carbon::now();
            $current_date = $mytime->format('Y-m-d');
            $current_time = $mytime->format('H:i');
            // $vendor_cut_off_time = Carbon::parse($request->vendor_cutoff_time)->format('H:i');
            $product_delivery_slots = DeliverySlotProduct::with('deliverySlot')->where('product_id', $product_id);
            if($current_date == $delivery_date){
                $product_delivery_slots = $product_delivery_slots->whereHas('deliverySlot' ,function ($q) use ($current_time) {
                    $q->whereTime('cutOff_time', '>=', $current_time);
                    // $q->whereTime('start_time', '>', $current_time)->whereTime('end_time', '<', $vendor_cut_off_time);
                })->get();
            }else{
                $product_delivery_slots = $product_delivery_slots->get();
            }
            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => $product_delivery_slots
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function getProductDeliverySlotsInterval(Request $request){
        try {
            $request->validate(
                [
                    'slot_id' => 'required'
                ],
                [
                    'slot_id.required' => 'Slot Id is required'
                ]
            );
            $product_delivery_slots_interval = [];
            if (checkTableExists('product_attributes')) {
                $product_delivery_slots_interval = DeliverySlot::where('parent_id', $request->slot_id)->get();
            }
            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => $product_delivery_slots_interval
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    /**
     * getFreeLincerFromDispatcher
     * @Author  Mr Harbans singh
     * @param  mixed $request
     * @return void
     */
    public function getFreeLincerFromDispatcher(Request $request){
        try {
            $selecterVariant = ProductVariant::where('id',$request->variant_id)->first();
            if($selecterVariant){
                $latitude = '';
                $longitud = '';
                $address = UserAddress::find($request->address_id);
                if($address){
                    $latitude = $address->latitude ;
                    $longitud = $address->longitude ;
                }

                $res = $this->getProductPriceFromDispatcher($request->bookingdateTime,$selecterVariant->sku, $latitude, $longitud,$request->slot);
                return response()->json(array('status' => 'Success', 'data' => $res['data']));
            }
            return response()->json([
                'status' => 200,
                'message' => 'Variant not Found!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Somthing Went wrong!'
            ]);
        }
    }

    public function storeProductInquiry(Request $request, $domain = '')
    {
        try {
            $request->validate([
                'agree' =>'accepted',
                'name' => 'required',
                'email' => 'required',
                'number1' => 'required',
                'message' => 'required',
            ], [
                'name.required' => __('The name field is required.'),
                'agree.accepted' => __('The agree must be accepted.'),
                'email.required' => __('The email field is required.'),
                'number1.required' => __('The number field is required.'),
                'message.required' => __('The message field is required.'),
            ]);
            ProductInquiry::create(['name' => $request->name, 'email' => $request->email, 'phone_number' => $request->number1, 'company_name' => $request->company_name, 'message' => $request->message, 'product_id' => $request->product_id, 'vendor_id' => $request->vendor_id, 'product_variant_id' => $request->variant_id]);
            return response()->json(array('status' => 'Success', 'message' => __('Inquiry Submitted Successfully')));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }
}