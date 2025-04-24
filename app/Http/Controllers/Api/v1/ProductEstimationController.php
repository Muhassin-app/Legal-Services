<?php

namespace App\Http\Controllers\Api\v1;

use Session;
use Exception;
use App\Models\Cart;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\AddonSet;
use App\Models\CartAddon;
use App\Models\CartProduct;
use Illuminate\Support\Str;
use App\Models\LuxuryOption;
use Illuminate\Http\Request;
use App\Models\ClientCurrency;
use App\Models\ProductVariant;
use App\Models\EstimateProduct;
use App\Models\ClientPreference;
use App\Models\EstimateAddonSet;
use App\Models\EstimatedProduct;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use App\Models\EstimateAddonOption;
use App\Http\Controllers\Controller;
use App\Models\AssignQrcodesToOrder;
use App\Models\EstimatedProductCart;
use Illuminate\Support\Facades\Auth;
use App\Models\EstimatedProductAddons;
use App\Models\EstimateProductAddon;
use App\Models\EstimateProductTranslation;
use App\Models\QrcodeImport;

class ProductEstimationController extends Controller
{
    use ApiResponser;

    // Get All Estimated Product with their Addons - By Ovi
    public function getProductEstimationWithAddons(Request $request)
    {
        try{
            $langID = $request->header('language')??"1";
            $estimateProductsWithAddons = EstimateProduct::with(['estimate_product_addons.estimate_addon_set.option','category.primary' , 'estimate_product_translation' => function($q) use($langID) {
                $q->where('language_id', '=', $langID);
            }])->where('category_id',$request->category_id)->get();
            // if($request->category_id){
            //     $estimateProductsWithAddons = $estimateProductsWithAddons->where('category_id',$request->category_id);
            // }
            // $estimateProductsWithAddons =$estimateProductsWithAddons->get();
            return $this->successResponse($estimateProductsWithAddons);
        }catch (\Exception $e)
        {
            dd($e->getMessage());
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    // Add Estimated Product In Cart To Get Estimations
    public function addEstimatedProductInCart(Request $request)
    {
        try {
            DB::beginTransaction();

            // Query To Check if the Cart is Already Exists
            $estimatedProductCart = EstimatedProductCart::where('user_id', Auth::user()->id)->first();

            // If Cart Not Exists Then Generate New Cart Else Just Increment the item_count Column by 1.
            if(!$estimatedProductCart){
                $estimatedProductCart = new EstimatedProductCart();
                $estimatedProductCart->unique_identifier = Str::random(18);
                $estimatedProductCart->user_id           = Auth::user()->id;
                $estimatedProductCart->item_count        = ($request->get('item_count') != '') ? $request->get('item_count') : 1;
                $estimatedProductCart->currency_id       = $request->get('currency_id');
                $estimatedProductCart->save();
            }else{
                $estimatedProductCart->item_count = $estimatedProductCart->item_count+$request->get('item_count');
                $estimatedProductCart->save();
            }
            DB::commit();

            // Query To Check if the Product is Already in Cart.
            $estimatedProduct = EstimatedProduct::where('estimated_cart_id', $estimatedProductCart->id)->where('product_id', $request->get('estimate_product_id'))->first();

            // Check $this in Products Table - If Found Just Increasing the Quantity else Creating Now One.
            if(!$estimatedProduct){
                // Insert Estimated Product
                $estimatedProduct = new EstimatedProduct();
                $estimatedProduct->estimated_cart_id = $estimatedProductCart->id;
                $estimatedProduct->product_id        = $request->get('estimate_product_id');
                $estimatedProduct->quantity          = $request->get('quantity');
                $estimatedProduct->save();
            }else{
                $estimatedProduct->quantity          = $estimatedProduct->quantity+$request->get('quantity');
                $estimatedProduct->save();
            }

             // Make Array from (estimate_option_id) string
            $estimate_option_ids = explode(',',$request->get('estimate_option_id'));
            
            // Loop through the (estimate_option_ids)
            foreach($estimate_option_ids as $estimate_option_id){
                $estimatedProductAddon = new EstimatedProductAddons();
                $estimatedProductAddon->estimated_product_id      = $estimatedProduct->id;
                // Get Estimate Addon ID Frome Estimate Option ID
                $estimateAddonOption = EstimateAddonOption::find($estimate_option_id);
                $estimatedProductAddon->estimated_addon_id        = $estimateAddonOption->estimate_addon_id;
                $estimatedProductAddon->estimated_addon_option_id = $estimate_option_id;
                $estimatedProductAddon->save();
            }            

            $success['message'] = 'Product has been added to cart.';
            $success['estimatedProductCart'] = $estimatedProductCart;
            return $this->successResponse($success);
          } catch (Exception $e) {
            DB::rollback();
            return $e->getMessage();
          }
    }

    public function getEstimation(Request $request)
    {
        // Get language ID from Request Header - By Ovi
        $langId   = $request->header('language')??'1';
        $currency = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $currency)->first();
        $doller_compare = ($clientCurrency) ? $clientCurrency->doller_compare : 1;

        // Get Cart from Estimated Product Cart based on user_id - By Ovi
        // $userCart = EstimatedProductCart::where('user_id', Auth::user()->id)->first();
        // if(!$userCart){
        //     $error['message'] = "Your Cart is Empty";
        //     return response()->json($error, 419);
        // }
        // Get Products from added Estimated Cart using cart id - By Ovi
         //$userProducts = EstimatedProduct::where('estimated_cart_id', $userCart->id )->get();
        // Search for similar products and addons. - By Ovi
        //foreach($request->product as $product){
            // $userProducts = $request->product;
            // //\Log::info($request->product);
            // return json_encode($request->product);
            $searchResult = $this->searchProductExpection($request->product, $langId);
        //}

        // $navCategories = $this->categoryNav($langId);
    
        // foreach($searchResult as $vendor){
           
        //     $addon_price = 0; 
        //     foreach ($vendor->productsLive as $prod){
        //         foreach($prod->sets as $set){
        //             // array_push($addon_id, $set->addon_id);
        //             $addon_price = $set->setoptions->sum('price');
        //         }
        //     }

        //   foreach($vendor->productsLive as $product){
            
        //     $p_id = $product->id;

        //     $variantData = $product->with(['variantSet' => function ($z) use ($langId, $p_id) {
        //         $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
        //         $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
        //         $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
        //         $z->where('vt.language_id', $langId);
        //         $z->where('product_variant_sets.product_id', $p_id)->where('vr.status', 1)->orderBy('product_variant_sets.variant_type_id', 'asc');
        //     },'variantSet.option2'=> function ($zx) use ($langId, $p_id) {
        //         $zx->where('vt.language_id', $langId)
        //         ->where('product_variant_sets.product_id', $p_id);
        //     }])->where('id', $p_id)->first();

        //     $product->variantSet = $variantData->variantSet;
        //     $product->variant_multiplier = 1;
        //     $product->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
        //     $vendor->variant_multiplier = $doller_compare;
        //     $vendor->variant_price = ($product->variant->isNotEmpty()) ? $product->variant->first()->price : 0;
        //     $vendor->product_price = ($product->variant->isNotEmpty()) ? (($product->variant->first()->price*$doller_compare)+$addon_price) : 0;

        //     $product->variant_id = ($product->variant->isNotEmpty()) ? $product->variant->first()->id : 0;
        //     $product->variant_quantity = ($product->variant->isNotEmpty()) ? $product->variant->first()->quantity : 0;

        //     // foreach($userProducts as $userProduct){
        //     //     //\Log::info($userProduct->count());
        //     //     //\Log::info($vendor->products->count());
        //     //     if($userProduct->count() <= $vendor->products->count()){
        //     //         $vendor->match = "Complete Match";
        //     //     }
        //     // }
        //   }
        // }
        return $this->successResponse($searchResult);
        // Return Vendor Count and Result.
        // if($searchResult->count()>0){
        //     return $this->successResponse($searchResult);
        // }else{
        //     return $this->errorResponse('Vendors Found','404');
        // }
       

    }

    public function assingQrcode(Request $request)
    {
        $searchCode = QrcodeImport::where('code',$request->code)->first();
        if($searchCode)
        {
            AssignQrcodesToOrder::create([
                'order_id'=>$request->order_id??null,
                'order_no'=>$request->order_no??null,
                'batch_no'=>$request->batch_no??null,
                'qrcode'=>$request->qrcode
            ]);
        }else{
            return $this->successResponse('Qrcode Assigned.');
        }
        return $this->errorResponse('Qrcode not found in System.','404');
    }

    // public function qrcodeList(Request $request)
    // {
    //     $searchCode = QrcodeImport::whereHas(['assignCode'=>function($q){
    //        return $q->whereNotIn('')
    //     }])->get();
    //     if($searchCode)
    //     {
    //         AssignQrcodesToOrder::create([
    //             'order_id'=>$request->order_id,
    //             'order_no'=>$request->order_no,
    //             'batch_no'=>$request->batch_no
    //         ]);
    //     }else{
    //         return $this->successResponse('Qrcode Assigned.');
    //     }
    //     return $this->errorResponse('Qrcode not found in System.','404');
    // }

    public function getImage($value){
        $values = array();
        $img = 'default/default_image.png';
        if(!empty($value)){
          $img = $value;
          $ex = checkImageExtension($img);
              $values['proxy_url'] = \Config::get('app.IMG_URL1');
              if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
                  $values['image_path'] = \Config::get('app.IMG_URL2').'/'.$img;
              } else {
                  $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
              }
              $values['image_fit'] = \Config::get('app.FIT_URl');
              $values['original'] = $img;
          return $values;
        }
        return $value;
  
      }

    public function searchProductExpection($userProducts, $langId)
    {
            // Make empty array for vendors, product keywords and adoon keywords
            $all_vendors = array();
            $keywords = array();
            $addonKeywords = array();
            // Loop through cart products
            foreach($userProducts as $i=> $product)
            {
                // Get Specific ($this) Product Translation
                $estimateProductTranslation = EstimateProductTranslation::where('estimate_product_id', $product['estimate_product_id'])->where('language_id', $langId)->first();
                //dd($estimateProductTranslation);
                // Save Product Name in $keywords, so that we can run search later
                $keywords[] =  ($estimateProductTranslation) ? $estimateProductTranslation->name : '';

                // Loop through these addons and save the ($title) for later search
                    foreach($product['estimate_products'] as $k=> $estimatedProductAddon){
                        $addonKeywords[$estimateProductTranslation->name][] = $estimatedProductAddon['title'];
                    }
            }
      
            $pkeyCnt = count($keywords);
            $pkeys = '';
            foreach($keywords as $no => $name)
            {
                $comma = '';
                if($no<$pkeyCnt-1)$comma = ',';

                $pkeys .= "'".$name."'".$comma;
            }
           

            $data = array();
            //FEtch Vendor with Products
            $vendorsgb = DB::select("SELECT v.id as vid,v.address,v.name as vname,v.logo,ps.title as ptitle,p.id as pid,pv.price as pprice from vendors as v join products as p on v.id=p.vendor_id join product_translations as ps on p.id=ps.product_id join product_variants as pv  on p.id=pv.product_id where v.status='1' and ps.title IN (?) and is_live='1' group by v.id", [$pkeys]);
            foreach($vendorsgb as $vpg)
            {
                $products = array();
                $vendors = DB::select("SELECT v.id as vid,p.sku,v.address,v.name as vname,v.logo,ps.title as ptitle,p.id as pid,pv.id  as variant_id,pv.price as pprice from vendors as v join products as p on v.id=p.vendor_id join product_translations as ps on p.id=ps.product_id join product_variants as pv  on p.id=pv.product_id where v.status='1' and ps.title IN (?) and is_live='1' and v.id=? group by ps.title ", [$pkeys, $vpg->vid]);
                foreach($vendors as $vp)
                {

                    $pkeyCnt = 0;
                    $pkeyCnt = count($addonKeywords[$vp->ptitle]);
                    $addonsKeys = '';
                    foreach($addonKeywords[$vp->ptitle] as $no => $name)
                    {
                        $comma = '';
                        if($no<$pkeyCnt-1)$comma = ',';

                        $addonsKeys .= "'".$name."'".$comma;
                    }

                        $addons =array();
                        $addon_id = array();
                        $option_id = array();
                        $addon_price = array(); 
                        //Fetch Products Addon set
                        $addon = DB::select("SELECT paj.addon_id as aid,sa.title,ado.id as aoid from product_addons as paj join addon_sets as sa on sa.id=paj.addon_id join addon_options as ado on paj.addon_id=ado.addon_id join addon_option_translations as adot on ado.id=adot.addon_opt_id where sa.status='1' and product_id=? and adot.title IN (?) group by paj.addon_id ", [$vp->pid, $addonsKeys]);
                        foreach($addon as $vpa)
                        {
                        
                            $addoption = array();
                            $addoptionP = array();
                            $addoptiont = array();

                            //Fetch Addon options
                            $addonSetOpt = DB::select("SELECT ao.id as aoid,ao.price,ao.title from addon_options as ao join addon_option_translations as aot on ao.id=aot.addon_opt_id where addon_id=? and  aot.title IN (?) group by ao.title", [$vpa->aid, $addonsKeys]);
                            foreach($addonSetOpt as $opts)
                            {
                                $addoption[] = array(
                                    'optId' => $opts->aoid,
                                    'title' => $opts->title,
                                    'price' => $opts->price
                                );
                                array_push($option_id, $opts->aoid);
                                array_push($addon_price, $opts->price??0);
                            }

                            $addons[] = array(
                                'addonId' => $vpa->aid,
                                'addonName' => $vpa->title,
                                'option' => $addoption,
                            );
                            array_push($addon_id, $vpa->aid);
                        }

                    $products[] = array(
                        'title' => $vp->ptitle,    
                        'product_id' => $vp->pid,
                        'product_sku' => $vp->sku,
                        'product_variant_id' => $vp->variant_id,
                        'price' => $vp->pprice + array_sum($addon_price),
                        'addon'=> $addons,
                        'addonIds'=> $addon_id,
                        'optionIds'=> $option_id,
                        'match'   => (($pkeyCnt==count($option_id))?'C':'P')          
                    );

                }

            $data[] = array(
                'id' => $vp->vid,
                'address' => $vp->address,
                'logo' => $this->getImage($vp->logo),    
                'name' => $vp->vname,
                'price' => $vp->pprice + array_sum($addon_price),
                'product' => $products
            );
        }

        // $data = usort($data, function ($a, $b) {
        //     return ($a['price'] < $b['price']) ? -1 : 1;
        //   });
        // //\Log::info(json_encode($data));
        return $data;
    }

    // public function searchProductExpection($userProducts, $langId)
    // {
    //         // Make empty array for vendors, product keywords and adoon keywords - By Ovi
    //         $all_vendors = array();
    //         $keywords = array();
    //         $addonKeywords = array();
    //         // Loop through cart products
    //         foreach($userProducts as $product)
    //         {
    //             // Get Specific ($this) Product Translation - By Ovi
    //             $estimateProductTranslation = EstimateProductTranslation::where('estimate_product_id', $product['estimate_product_id'])->where('language_id', $langId)->first();
        
    //             // Save Product Name in $keywords, so that we can run search later - By Ovi
    //             $keywords[] =  ($estimateProductTranslation) ? $estimateProductTranslation->name : '';

    //             // Get All Addons of ($this) Specific Product - By Ovi
    //             $estimatedProductAddons = EstimatedProductAddons::where('estimated_product_id', $product['estimate_product_id'])->get();

    //             // Loop through these addons and save the ($title) for later search - By Ovi
    //             foreach($estimatedProductAddons as $estimatedProductAddon){
    //                 $addonKeywords[] = $estimatedProductAddon->estimated_product_addon_option->title;
    //             }
    //         }

    //         // ***Start*** BY - OVI 
    //         // Query to get:
    //         // 1) All the Vendor list with similar product, string saved in ($keywords)
    //         // Product Addons of Specific Product with Translations
    //         // Product Media and Variants
    //         // Product Translations, comparing ($language_id)
    //         // By Checking Vendor (status) active, inactive, or pending.
    //         // ***End*** BY - OVI 
    //         $teststests = 0;
    //         $all_vendors = Vendor::OrderBy('id','desc')->with(['productsLive' => function($q) use($langId, $keywords, $addonKeywords){
    //             $q->whereHas('translation',function($q) use($langId, $keywords){
    //                 $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId)->whereIn('title', $keywords);
    //                 }
    //         )->with(['sets.setoptions' => function($ad) use($langId, $addonKeywords){
    //             $ad->whereHas('translation_one',function($ad) use($langId, $addonKeywords){
    //                 $ad->select('id', 'title')->where('language_id', $langId)->whereIn('title', $addonKeywords);
    //                 });
    //         }])->with('media.image','variant');
    //     }])->whereHas('productsLive.translation',function($q) use($langId, $keywords, $teststests){
    //             $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId)->whereIn('title', $keywords);
    //     })->where('status',1)->get();
    //     ////\Log::info($all_vendors);

    //         // Return All Vendors with Products, Addons - By Ovi 
    //         return $all_vendors;
    // }

    // Remove Product From Estimated Cart **Table: estimated_products**
    public function removeProductFromEstimatedCart(Request $request)
    {
        // Get Estimated Product Addons using (product_id) and delete all the addons.
        $estimatedProductAddons = EstimatedProductAddons::where('estimated_product_id', $request->product_id)->delete();
         // Get Estimated Product using (product_id) and delete the product
        $estimatedProduct   = EstimatedProduct::where('product_id', $request->product_id)->delete();
        $success['message'] = 'Product has been removed from cart.';
        return response()->json($success, 200);
    }

    // Remove Addons From Estimated Cart **Table: estimated_product_addons**
    public function removeAddonsFromEstimatedCart(Request $request)
    {  
        // Get Estimated Product Addon using (addon_id) and delete the addon.
        $estimatedProductAddons = EstimatedProductAddons::where('id', $request->addon_id)->delete();
        $success['message']     = 'Addon has been removed from cart.';
        // return response()->json($success, 200);
        return $this->successResponse($success);

    }

    //  Transfer Estimated Cart Products To Real Cart ***POST METHOD*** - By Ovi
    public function transferEstimatedCartProductsToRealCart(Request $request)
    {
        try {
            $preference = ClientPreference::first();
            $luxury_option = LuxuryOption::where('title', $request->type)->first();
            $user = Auth::user();
            $langId = $user->language;
            $user_id = $user->id;
            $unique_identifier = '';
            if (!$user_id) {
                if (empty($user->system_user)) {
                    return $this->errorResponse(__('System id should not be empty.'), 404);
                }
                $unique_identifier = $user->system_user;
            }

            $product = Product::where('sku', $request->sku)->first();
            if (!$product) {
                return $this->errorResponse(__('Invalid product.'), 404);
            }
            $productVariant = ProductVariant::where('product_id', $product->id)->where('id', $request->product_variant_id)->first();
            if (!$productVariant) {
                return $this->errorResponse(__('Invalid product variant.'), 404);
            }

            if ($product->category->categoryDetail->type_id == 8) {
            } else {
                if ( ($product->sell_when_out_of_stock == 0) && ($productVariant->quantity < $request->quantity) ) {
                    return $this->errorResponse('You Can not order more than ' . $productVariant->quantity . ' quantity.', 404);
                }
            }

        
            $addonSets = $addon_ids = $addon_options = array();
            if ($request->has('addon_ids')) {
                $addon_ids = $request->addon_ids;
            }
            if ($request->has('addon_options')) {
                $addon_options = $request->addon_options;
            }
      
            foreach ($addon_options as $key => $opt) {
                $addonSets[$addon_ids[$key]][] = $opt;
            }
          
            foreach ($addonSets as $key => $value) {
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                    ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                    ->where('ast.language_id', $langId)->where('addon_sets.status', '!=', '2')->where('addon_sets.id', $key)->first();
                if (!$addon) {
                    return $this->errorResponse(__('Invalid addon or delete by admin. Try again with remove some.'), 404);
                }
                if ($addon->min_select > count($value)) {
                    return response()->json([
                        "status" => "Error",
                        'message' => 'Select minimum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
                if ($addon->max_select < count($value)) {
                    return response()->json([
                        "status" => "Error",
                        'message' => 'You can select maximum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
            }

            $client_currency = ClientCurrency::where('is_primary', '=', 1)->first();
            $cart_detail = [
                'is_gift' => 0,
                'status' => '0',
                'item_count' => 0,
                'user_id' => $user->id,
                'created_by' => $user->id,
                'unique_identifier' => $unique_identifier,
                'currency_id' => $client_currency->currency_id,
            ];
            if (!empty($user_id)) {
                $cart_detail = Cart::updateOrCreate(['user_id' => $user->id], $cart_detail);
            } else {
                $cart_detail = Cart::updateOrCreate(['unique_identifier' => $unique_identifier], $cart_detail);
            }

            $checkVendorId = CartProduct::where('cart_id', $cart_detail->id)->where('vendor_id', '!=', $product->vendor_id)->first();

            if ($luxury_option) {
                $checkCartLuxuryOption = CartProduct::where('luxury_option_id', '!=', $luxury_option->id)->where('cart_id', $cart_detail->id)->first();
                if ($checkCartLuxuryOption) {
                    return $this->errorResponse(['error' => __('You are adding products in different mods'), 'alert' => '1'], 404);
                }
                if ($luxury_option->id == 2 || $luxury_option->id == 3) {
                    if ($checkVendorId) {
                        return $this->errorResponse(['error' => __('Your cart has existing items from another vendor'), 'alert' => '1'], 404);
                    }
                }
            }

            if ((isset($preference->isolate_single_vendor_order)) && ($preference->isolate_single_vendor_order == 1)) {
                if ($checkVendorId) {
                    CartProduct::where('cart_id', $cart_detail->id)->delete();
                }
            }

            if ($cart_detail->id > 0) {
                $oldquantity = $isnew = 0;
                $cart_product_detail = [
                    'status'  => '0',
                    'is_tax_applied'  => '1',
                    'created_by'  => $user_id,
                    'product_id' => $product->id,
                    'cart_id'  => $cart_detail->id,
                    'quantity'  => $request->quantity,
                    'vendor_id'  => $product->vendor_id,
                    'variant_id'  => $request->product_variant_id,
                    'currency_id' => $client_currency->currency_id,
                    'luxury_option_id' => $luxury_option ? $luxury_option->id : 1,
                ];
                $cartProduct = CartProduct::where('cart_id', $cart_detail->id)
                    ->where('product_id', $product->id)->where('variant_id', $productVariant->id)->first();
                if (!$cartProduct) {
                    $isnew = 1;
                } else {
                    $checkaddonCount = CartAddon::where('cart_product_id', $cartProduct->id)->count();
                    if (count($addon_ids) != $checkaddonCount) {
                        $isnew = 1;
                    } else {
                        foreach ($addon_options as $key => $opts) {
                            $cart_addon = CartAddon::where('cart_product_id', $cartProduct->id)
                                ->where('addon_id', $addon_ids[$key])
                                ->where('option_id', $opts)->first();
                            // if (!$cart_addon) {
                            //     $isnew = 1;
                            // }
                        }
                    }
                }
                if ($isnew == 1) {
                    $cartProduct = CartProduct::create($cart_product_detail);
                    if (!empty($addon_ids) && !empty($addon_options)) {
                        $saveAddons = array();
                        foreach ($addon_options as $key => $opts) {
                            $saveAddons[] = [
                                'option_id' => $opts,
                                'cart_id' => $cart_detail->id,
                                'addon_id' => $addon_ids[$key],
                                'cart_product_id' => $cartProduct->id,
                            ];
                        }
                        if (!empty($saveAddons)) {
                            CartAddon::insert($saveAddons);
                        }
                    }
                } else {
                    $cartProduct->quantity = $cartProduct->quantity + $request->quantity;
                    $cartProduct->save();
                }
            }

            // Delete Products and Empty Estimation Cart - By Ovi
            if($request->get('remove_cart_products') == "Yes"){
                $estimatedProductCart   = EstimatedProductCart::where('user_id', $user_id)->first();
                $estimatedProduct       = EstimatedProduct::where('estimated_cart_id', $estimatedProductCart->id)->first();
                $estimatedProductAddons = EstimatedProductAddons::where('estimated_product_id', $estimatedProduct->id)->delete();
                $estimatedProduct->delete();
                $estimatedProductCart->delete();
            }

           

            $success['message'] = 'Product has been added to cart.';
            return $this->successResponse($success);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }



}