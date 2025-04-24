<?php

namespace App\Http\Controllers\Front;

use Auth;
use Session;
use Exception;
use Timezonelist;
use Carbon\Carbon;
use App\Models\Tag;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ClientCurrency;
use App\Models\VendorCategory;
use App\Models\EstimateProduct;
use App\Models\EstimatedProduct;
use App\Models\ProductVariantSet;
use Illuminate\Support\Facades\DB;
use App\Models\EstimateAddonOption;
use App\Http\Controllers\Controller;
use App\Models\EstimatedProductCart;
use App\Models\EstimatedProductAddons;
use App\Models\EstimateProductTranslation;
use App\Http\Controllers\Front\FrontController;



class EstimationController extends FrontController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $preferences = Session::get('preferences');      
        $langId = Session::get('customerLanguage');
        $curId  = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();
        $navCategories = $this->categoryNav($langId);
        $user = Auth::user();
        $estimateProductsWithAddons = EstimateProduct::with(['estimate_product_addons.estimate_addon_set.option','category.primary' , 'estimate_product_translation' => function($q) use($langId) {
            $q->where('language_id', '=', $langId);
        }])->groupBy('category_id')->orderBy('id', 'ASC')->get();  
       
        if ($user) {
            $estimatedProductCart = EstimatedProductCart::where('user_id', $user->id)->first();
        }else{
            $user_token = session()->get('_token');
            $estimatedProductCart = EstimatedProductCart::where('unique_identifier', $user_token)->first();
        }

        if($estimatedProductCart){
            $estimatedProducts = EstimatedProduct::where('estimated_cart_id', $estimatedProductCart->id)->get();
        }else{
            $estimatedProducts = [];
        }
        return view('frontend.estimation.index')->with(['navCategories' => $navCategories, 'products' => $estimateProductsWithAddons, 'estimatedProducts' => $estimatedProducts]);
    }


    public function estimateProductAddons(Request $request)
    {
        // dd(getClientPreferenceDetail());
        $langId = Session::get('customerLanguage')??'1';
        $product_id = $request->slug;
        $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
        $variant_id = ($request->has('variant')) ? $request->variant : 0;

           $AddonData = EstimateProduct::with(['estimate_product_addons.estimate_addon_set.option','category.primary' , 'estimate_product_translation' => function($q) use($langId) {
                $q->where('language_id', '=', $langId);
            }])->where('id', $product_id)->first(); 
        if(!empty($AddonData)){
            $AddonData->product_image =  $AddonData->icon['image_fit'].'300/300'.$AddonData->icon['image_path'];
            $AddonData->translation_title = $AddonData->estimate_product_translation->name;
            $AddonData->translation_description = $AddonData->estimate_product_translation->name;
            $AddonData->variant_multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
            $variant_price = 0;
            $AddonData->variant_price = 22;
        }
     
        return response()->json(array('status' => 'Success', 'data' => $AddonData));
    }


    public function addToEstimateCart(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();

            // $quantity = $request->get('quantity');
            $quantity = 1;

            // Query To Check if the Cart is Already Exists
            if($user){
                $estimatedProductCart = EstimatedProductCart::where('user_id', $user->id)->first();
            }else{
                $user_token = session()->get('_token');
                $estimatedProductCart = EstimatedProductCart::where('unique_identifier', $user_token)->first();
            }
            // If Cart Not Exists Then Generate New Cart Else Just Increment the item_count Column by 1.
            if(!$estimatedProductCart){
                $estimatedProductCart = new EstimatedProductCart();
                $estimatedProductCart->unique_identifier = $user_token??null;
                $estimatedProductCart->user_id           = (($user)?$user->id:null);
                $estimatedProductCart->item_count        = ($quantity != '') ? $quantity : 1;
                $estimatedProductCart->currency_id       = Session::get('customerCurrency');
                $estimatedProductCart->save();
                
            }else{
                $estimatedProductCart->item_count = $estimatedProductCart->item_count+$quantity;
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
                $estimatedProduct->quantity          = $quantity;
                $estimatedProduct->save();
            }else{
                $estimatedProduct->quantity          = $estimatedProduct->quantity+$quantity;
                $estimatedProduct->save();
            }

             // Make Array from (estimate_option_id) string
            // $estimate_option_ids = explode(',',$request->get('estimate_option_id'));
            //\Log::info($request->estimate_option_id);
            //Delete previous added addons
            EstimatedProductAddons::where('estimated_product_id', $estimatedProduct->id)->delete();
            // Loop through the (estimate_option_ids)
            foreach($request->estimate_option_id as $estimate_option_id){
               
                $checkAddonExists = EstimatedProductAddons::where('estimated_product_id', $estimatedProduct->id)->where('estimated_addon_option_id', $estimate_option_id)->first();
                if(!$checkAddonExists){
                    $estimatedProductAddon = new EstimatedProductAddons();
                    $estimatedProductAddon->estimated_product_id      = $estimatedProduct->id;
                    // Get Estimate Addon ID Frome Estimate Option ID
                    $estimateAddonOption = EstimateAddonOption::find($estimate_option_id);
                    $estimatedProductAddon->estimated_addon_id        = $estimateAddonOption->estimate_addon_id;
                    $estimatedProductAddon->estimated_addon_option_id = $estimate_option_id;
                    $estimatedProductAddon->save();
                }
            }            

            $success['message'] = 'Product has been added to cart.';
            $success['estimatedProductCart'] = $estimatedProductCart;
            return response()->json($success, 200);
           
          } catch (Exception $e) {
            DB::rollback();
            return $e->getMessage();
          }
    }


    public function estimationList(Request $request)
    {
        // Get language ID from Request Header - By Ovi
        $langId  = Session::get('customerLanguage')??'1';
        $user = Auth::user();
        $currency = Session::get('customerCurrency');
        // Get Cart from Estimated Product Cart based on user_id - By Ovi
        if($user){
            $userCart = EstimatedProductCart::where('user_id', $user->id)->first();
        }else{
            $user_token = session()->get('_token');
            $userCart = EstimatedProductCart::where('unique_identifier', $user_token)->first();
        }
        if(!$userCart){
            $message = "Your Cart is Empty";
            return redirect()->back()->withErrors(['message', $message]);
        }
        // Get Products from added Estimated Cart using cart id - By Ovi
        $userProducts = EstimatedProduct::where('estimated_cart_id', $userCart->id )->get();
        //dd($userProducts);
        $clientCurrency = ClientCurrency::where('currency_id', $currency)->first();
        $doller_compare = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
        // Search for similar products and addons. - By Ovi
        $searchResult = $this->searchProducts($userProducts, $langId);
        $navCategories = $this->categoryNav($langId);
       

        $returnHTML = view('frontend.estimation.list')->with(['vendor_count'=> count($searchResult).' Vendors Found','vendors' => $searchResult,'navCategories' => $navCategories])->render();
        return response()->json(array('status' => 'Success', 'html' => $returnHTML));

    }

    public function searchProducts($userProducts, $langId)
    {
            $dcnt = 0;
            // Make empty array for vendors, product keywords and adoon keywords
            $all_vendors = array();
            $keywords = array();
            $addonKeywords = array();

            // Loop through cart products
            foreach($userProducts as $i=> $product)
            {
                // Get Specific ($this) Product Translation
                $estimateProductTranslation = EstimateProductTranslation::where('estimate_product_id', $product->product_id)->where('language_id', $langId)->first();
                //dd($estimateProductTranslation);
                // Save Product Name in $keywords, so that we can run search later
                $keywords[] =  ($estimateProductTranslation) ? $estimateProductTranslation->name : '';

                // Get All Addons of ($this) Specific Product
                $estimatedProductAddons = EstimatedProductAddons::where('estimated_product_id', $product->id)->get();

                // Loop through these addons and save the ($title) for later search
                foreach($estimatedProductAddons as $k=> $estimatedProductAddon){
                    $addonKeywords[$estimateProductTranslation->name][] = $estimatedProductAddon->estimated_product_addon_option->title;
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
            $vendorsgb = DB::select("SELECT v.id as vid,v.address,v.name as vname,v.logo,ps.title as ptitle,p.id as pid,pv.price as pprice from vendors as v join products as p on v.id=p.vendor_id join product_translations as ps on p.id=ps.product_id join product_variants as pv  on p.id=pv.product_id where v.status='1' and p.deleted_at is null and ps.title IN (?) and is_live='1' group by v.id", [$pkeys]);
            foreach($vendorsgb as $vpg)
            {

                $products = array();
                $vendors = DB::select("SELECT v.id as vid,v.address,v.name as vname,v.logo,ps.title as ptitle,p.id as pid,pv.price as pprice,p.deleted_at from vendors as v join products as p on v.id=p.vendor_id join product_translations as ps on p.id=ps.product_id join product_variants as pv  on p.id=pv.product_id where v.status='1' and ps.title IN (?) and is_live='1' and v.id=? and p.deleted_at is null  group by ps.title ", [$pkeys, $vpg->vid]);
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
                        //pr($addonKeywords[$vp->ptitle]);
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
                            }

                            $addons[] = array(
                                'addonId' => $vpa->aid,
                                'addonName' => $vpa->title,
                                'option' => $addoption,
                            );

                        }

                    $products[] = array(
                        'title' => $vp->ptitle,    
                        'pid' => $vp->pid,
                        'price' => $vp->pprice,
                        'needCnt'   => $pkeyCnt,
                        'addon'=> $addons
                        
                    );

                }

            $data[] = array(
                'vid' => $vp->vid,
                'address' => $vp->address,
                'logo' => $this->getImage($vp->logo),    
                'name' => $vp->vname,
                'product' => $products
            );

        }
           //dd($data);
            return $data;
    }


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

    public function destroy(Request $request)
    {
        $estimated_cart_id = $request->estimated_cart_id;
        $product_id = $request->product_id;

        $estimatedProduct = EstimatedProduct::where('product_id', $product_id )->where('estimated_cart_id', $estimated_cart_id )->first();
        $estimatedProductAddons = EstimatedProductAddons::where('estimated_product_id', $estimatedProduct->id )->delete();
        $estimatedProduct->delete();

        $product_count = EstimatedProduct::where('estimated_cart_id', $estimated_cart_id )->count();
        
        $estimatedProductCart = EstimatedProductCart::find($estimated_cart_id)->first();
        $estimatedProductCart->item_count = $product_count;
        $estimatedProductCart->save();

        $success['message'] = 'Product has been removed from cart.';
        return response()->json($success, 200);
                
    }


    // public function searchEstimatedProducts(Request $request)
    // {
    //     $response = [];
    //     $tagId = $request->input('tag_id');
    //     $keyword = $request->input('keyword');
    //     $langId = Session::get('customerLanguage');
    //     $preferences = Session::get('preferences');

    //     $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();

    //     $estimateProductsWithAddons = EstimateProduct::with(['estimate_product_addons.estimate_addon_set.option','category.primary' , 'estimate_product_translation' => function($q) use($langId, $keyword) {
    //         $q->where('language_id', '=', $langId)->where('name', 'LIKE', '%' . $keyword . '%');
    //     }])->groupBy('category_id')->orderBy('id', 'ASC')->get();  

    //     $returnHTML = view('frontend.get-estimation-search-products')->with(['products'=> $estimateProductsWithAddons])->render();
    //     return response()->json(array('status' => 'Success', 'html' => $returnHTML));
    // }
}
