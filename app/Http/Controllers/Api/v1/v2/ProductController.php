<?php

namespace App\Http\Controllers\Api\v1\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Front\FrontController;
use Session;
use App\Models\{Product, ClientCurrency, ProductVariant, ProductVariantSet};
use DB;

class ProductController  extends FrontController
{
    /**
     * Display product variant data
     *
     * @return \Illuminate\Http\Response
     */
    public function getVariantData(Request $request, $sku){
        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role', 'is_token_currency_enable']);

        $customerCurrency = Session::get('customerCurrency');
        if(isset($customerCurrency) && !empty($customerCurrency)){
        }
        else{
            $primaryCurrency = ClientCurrency::where('is_primary','=', 1)->first();
            Session::put('customerCurrency', $primaryCurrency->currency_id);
        }
        $data = array();
        $is_available = true;
        $vendors = $this->getServiceAreaVendors();
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
        $product = Product::select('id', 'vendor_id')->where('sku', $sku)->firstOrFail();
        if(!in_array($product->vendor_id, $vendors)){
            $is_available = false;
        }
        $data['is_available'] = $is_available;

        $pv_ids = array();
        $product_variant = [];
        if ($request->has('options') && !empty($request->options)) {
            foreach ($request->options as $key => $value) {
                if ($product_variant) {
                    $pv_ids = array();
                    foreach ($product_variant as $k => $variant) {
                        if($request->options[$key]){
                            $variantSet = ProductVariantSet::whereIn('variant_type_id', $request->variants)
                            ->whereIn('variant_option_id', $request->options)
                            ->where('product_variant_id', $variant->product_variant_id)
                            ->whereHas('productVariants', function($q){
                                $q->where('status', '=', 1);
                                $q->where('quantity', '>', 0);

                            })->get();
                           // pr($variantSet->toArray());
                           // if(count($variantSet) == count($request->variants)){
                                // if(!in_array($variantSet->product_variant_id, $pv_ids)){
                                    $pv_ids[] = $variant->product_variant_id;
                                // }
                            //}
                        }
                    }
                }
                else{
                    $product_variant = ProductVariantSet::where('variant_type_id', $request->variants[$key])
                    ->where('variant_option_id', $request->options[$key])->where('product_variant_sets.product_id', $product->id)->get();
                    if($product_variant){
                        foreach ($product_variant as $k => $variant) {
                            if(!in_array($variant->product_variant_id, $pv_ids)){
                                $pv_ids[] = $variant->product_variant_id;
                            }
                        }
                    }
                }

            }
        }
        $sets = array();

        if ($request->has('variants') && $request->has('options')) {
            $selected_variant = DB::table('product_variant_sets')->join('product_variants', 'product_variants.id', '=', 'product_variant_sets.product_variant_id')->where('product_variant_sets.product_id', $product->id)
            ->whereIn('variant_option_id', $request->options)
            ->whereIn('variant_type_id', $request->variants)
            ->groupBy('product_variant_id')
            ->havingRaw("COUNT(DISTINCT variant_option_id) = ". count($request->options). " " )
            ->havingRaw("COUNT(DISTINCT variant_type_id) = ".count($request->variants)." ")
            ->select('product_variant_sets.*', 'product_variants.price', 'product_variants.price', 'product_variants.compare_at_price', 'product_variants.quantity')
            ->first();
        }

        //pr($pv_ids);
        $selected_variant_title = $request->selected_title;
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
        $availableSets = Product::with(['variantSet.variantDetail','variantSet.option2'=>function($q)use($product, $pv_ids){
            $q->where('product_variant_sets.product_id', $product->id)->whereIn('product_variant_id', $pv_ids);
        }])
        //return $product;
        ->select('id')
        ->where('products.id', $product->id)->first();
        // Assuming $availableSets is an array of objects with a 'title' property
        foreach ($availableSets->variantSet as $key => $sets) {
            if ($sets->variantDetail->title === $selected_variant_title) {
                unset($availableSets->variantSet[$key]);
            }
        }
        // Convert the object to an array
        $availableSets = json_decode(json_encode($availableSets->variantSet), true);

        usort($availableSets, function ($a, $b) {
            return $a['variant_detail']['position'] - $b['variant_detail']['position'];
        });

        $availableSets = json_decode(json_encode($availableSets), false);
        $data['availableSets'] = $availableSets;
        if($pv_ids){
            $variantData = ProductVariant::with(['product.media.image', 'product.addOn', 'media.pimage.image', 'checkIfInCart'])
            ->select('id', 'sku', 'quantity', 'price', 'compare_at_price', 'barcode', 'product_id')
            ->whereIn('id', $pv_ids)->get();

            if ($variantData) {
                foreach($variantData as $variant){

                    $variant->productPrice =  decimal_format(($variant->price * $clientCurrency->doller_compare));
                }
                if(count($variantData) <= 1){
                    $image_fit = "";
                    $image_path = "";
                    $variantData = $variantData->first()->toArray();
                    if(!empty($variantData['media'])){
                        $image_fit = $variantData['media'][0]['pimage']['image']['path']['image_fit'];
                        $image_path = $variantData['media'][0]['pimage']['image']['path']['image_path'];
                    }else if(!is_null($variantData['product']['media']) && !empty($variantData['product']['media']) && !is_null($variantData['product']['media'][0]['image'])){
                        $image_fit = $variantData['product']['media'][0]['image']['path']['image_fit'];
                        $image_path = $variantData['product']['media'][0]['image']['path']['image_path'];
                    }
                    if(empty($image_path)){
                        $image_fit = \Config::get('app.FIT_URl');
                        $image_path = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url('default/default_image.png').'@webp';
                    }
                    $variantData['image_fit'] = $image_fit;
                    $variantData['image_path'] = $image_path;
                    if(count($variantData['check_if_in_cart']) > 0){
                        $variantData['check_if_in_cart'] = $variantData['check_if_in_cart'][0];
                    }
                    $variantData['isAddonExist'] = 0;
                    if(count($variantData['product']['add_on']) > 0){
                        $variantData['isAddonExist'] = 1;
                    }

                    $variantData['variant_multiplier'] = $clientCurrency ? $clientCurrency->doller_compare : 1;
                    // dd($variantData);
                }else{
                    $variantData = array();
                }
                $tokenAmount = 1;
                $is_token_enable = $getAdditionalPreference['is_token_currency_enable'];
                if($is_token_enable){
                    $tokenAmount = getJsToken();
                }
                if(isset($selected_variant)){
                    $selected_variant->multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
                }
                

                $data['variant'] = $variantData;
                $data['tokenAmount'] = $tokenAmount;
                $data['is_token_enable'] = $is_token_enable;
                $data['selected_variant'] = $selected_variant;

                return response()->json(array('status' => 'Success', 'data' => $data));
            }

        }
        //pr($data['availableSets']->toArray());
        return response()->json(array('status' => 'Error', 'message' => 'This option is currenty not available', 'data' => $data));
    }
}
