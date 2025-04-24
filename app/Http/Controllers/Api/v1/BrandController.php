<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\BaseController;
use Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\{User, Product, Category, ProductVariantSet, ProductVariant, ProductAddon, ProductRelated, ProductUpSell, ProductCrossSell, ClientCurrency, Vendor, Brand};
use Validation;
use DB;
use App\Http\Traits\ApiResponser;

class BrandController extends BaseController
{
    use ApiResponser;
    private $field_status = 2;
    /**
     * Get Company ShortCode
     *
     */
    public function productsByBrand(Request $request, $brandId = 0){
        try {
            if($brandId == 0 || $brandId < 0){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $userid = Auth::user()->id;
            $langId = Auth::user()->language;
            $paginate = $request->has('limit') ? $request->limit : 12;
            $brand = Brand::with(['translation' => function($q) use($langId){
                            $q->select('title', 'brand_id');
                            $q->where('language_id', $langId);
                        }])->select('id', 'image')
                        ->where('status', '!=', 2)
                        ->where('id', $brandId)->first();
            if(!$brand){
                return response()->json(['error' => 'No record found.'], 200);
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
                        ->groupBy('product_variant_sets.variant_type_id')->get();
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
                        $q->select('id', 'sku', 'product_id', 'quantity', 'price', 'barcode','compare_at_price');
                        // $q->groupBy('product_id');
                    }, 'variant.checkIfInCartApp', 'checkIfInCartApp',
                    'tags.tag.translations' => function ($q) use ($langId) {
                        $q->where('language_id', $langId);
                    }
                ])
                ->select('id', 'sku', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'brand_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'category_id','minimum_order_count','batch_count')
                ->where('brand_id', $brandId)
                ->where('is_live', 1)->paginate($paginate);
            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
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
                        $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
                    }
                }
            }

            // if(!empty($products)){
            //     foreach ($products as $product) {
            //         $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
            //         foreach ($product->variant as $k => $v) {
            //             $product->variant[$k]->multiplier = $clientCurrency->doller_compare;
            //         }
            //     }
            // }
            $response['brand'] = $brand;
            $response['products'] = $products;
            $response['filterVariant'] = $variantSets;
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Product filters on category Page
     * @return \Illuminate\Http\Response
     */
    public function brandFilters(Request $request, $brandId = 0){
        try{
            if($brandId == 0 || $brandId < 0){
            return response()->json(['error' => 'No record found.'], 404);
        }
        $userid = Auth::user()->id;
        $langId = Auth::user()->language;
        $curId = Auth::user()->currency;
        $paginate = $request->has('limit') ? $request->limit : 12;
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
                $vResult = ProductVariantSet::join('product_categories as pc', 'product_variant_sets.product_id', 'pc.product_id')->select('product_variant_sets.product_variant_id', 'product_variant_sets.product_id')
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
        $products = Product::with(['category.categoryDetail','media.image',
            'translation' => function($q) use($langId){
            $q->select('product_id', 'title', 'body_html', 'meta_title','language_id', 'meta_keyword', 'meta_description')->where('language_id', $langId);
            $q->groupBy('language_id','product_id');
            },
            'variant' => function($q) use($langId, $variantIds, $order_type){
            $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
            if(!empty($variantIds)){
                $q->whereIn('id', $variantIds);
            }
            $q->groupBy('product_id');
            }])->select('products.id', 'products.sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating','minimum_order_count','batch_count')
            ->join('product_variants', 'product_variants.product_id', '=', 'products.id') // Or whatever the join logic is
            ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
            ->where('brand_id', $brandId)
            ->where('is_live', 1)
            ->whereIn('products.id', function($qr) use($startRange, $endRange){
                $qr->select('product_id')->from('product_variants')->where('price',  '>=', $startRange)->where('price',  '<=', $endRange);
            })->withCount(['orderProduct','variantSet','addOn']) ;
            if(!empty($productIds)){
                $products = $products->whereIn('products.id', $productIds);
            }
            if(!empty($order_type) && $request->order_type == 'rating'){
                $products = $products->orderBy('averageRating', 'desc');
            }
            if (!empty($order_type) && $order_type == 'low_to_high') {
                $products = $products->orderBy('product_variants.price', 'asc');
            }
            if (!empty($order_type) && $order_type == 'high_to_low') {
                $products = $products->orderBy('product_variants.price', 'desc');
            }
            if (!empty($order_type) && $order_type == 'newly_added') {
                $products = $products->orderBy('products.id', 'desc');
            }
            if (!empty($order_type) && $order_type == 'most_purchased') {

                $products = $products->orderBy('order_product_count', 'desc');
            }
            $products = $products->groupBy('products.id')->paginate($paginate);
        if(!empty($products)){
            foreach ($products as $product) {
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
}
