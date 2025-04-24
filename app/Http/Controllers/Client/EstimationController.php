<?php

namespace App\Http\Controllers\Client;
use DB;
use Auth;
use Session;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ClientCurrency;
use App\Models\VendorCategory;
use App\Models\EstimateProduct;
use App\Models\ClientPreference;
use App\Http\Traits\ApiResponser;
use App\Models\CategoryTranslation;
use App\Models\EstimateProductAddon;
use Illuminate\Support\Facades\Storage;
use App\Models\EstimateProductTranslation;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Client,ClientLanguage, CsvQrcodeImport, EstimateAddonSet,EstimateAddonOption,EstimateAddonOptionTranslation,EstimateAddonSetTranslation, Product, ProductTranslation, QrcodeImport};

class EstimationController extends BaseController{
    use ApiResponser;

    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/estimate_products';
    }

    
    public function index(Request $request)
    {
        // Get Product Caregories - By Ovi
        $product_categories = $this->getCategories();
        $clientPreference = ClientPreference::first();

        $estimate_products = EstimateProduct::with('primary')->get();

        $client_languages = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
        ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
        ->where('client_languages.client_code', Auth::user()->code)
        ->where('client_languages.is_active', 1)
        ->orderBy('client_languages.is_primary', 'desc')->get();

        $addons = EstimateAddonSet::with('option')->select('id', 'title', 'min_select', 'max_select', 'position')
        ->where('status', '!=', 2)
        ->orderBy('position', 'asc')->get(); 

        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();

        $getAllEstimateAddonSets = EstimateAddonSet::all();
       
        return view('backend/setting/estimate_product')->with([
            'estimate_products'       => $estimate_products,
            'client_languages'        => $client_languages,
            'languages'               => $client_languages,
            'addon_sets'              => $addons, 
            'clientCurrency'          => $clientCurrency,
            'getAllEstimateAddonSets' => $getAllEstimateAddonSets,
            'product_categories'      => $product_categories,
            'clientPreference'        => $clientPreference
        ]);
  
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
              'name.0' => 'required|string|max:255',
              'icon' => 'image'
             ],['name.0' => 'The default language name field is required.']);
            DB::beginTransaction();

            $tag = new EstimateProduct();

            if ($request->hasFile('icon')) {    /* upload icon file */
                $file = $request->file('icon');
                $tag->icon = Storage::disk('s3')->put($this->folderName, $file, 'public');
                $tag->category_id = $request->get('product_category');
            }

            $tag->save();
            $language_id = $request->language_id;
            foreach ($request->name as $k => $name) {
                if($name){
                    $TagTranslation = new EstimateProductTranslation();
                    $TagTranslation->name = $name;
                    $TagTranslation->slug = Str::slug($name, '-');
                    $TagTranslation->language_id = $language_id[$k];
                    $TagTranslation->estimate_product_id = $tag->id;
                    $TagTranslation->price = $request->get('price');
                    $TagTranslation->save();
                }
            }
            DB::commit();

            foreach($request->product_addons as $key=>$value){
                $estimateProductAddon = new EstimateProductAddon();
                $estimateProductAddon->estimate_product_id = $tag->id;
                $estimateProductAddon->estimate_addon_id = $request->product_addons[$key];
                $estimateProductAddon->save();
            }
            
            
            return $this->successResponse($tag, 'Product Added Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        try {
            $tag = EstimateProduct::with(['translations'])->where(['id' => $request->estimate_product_id])->first();
            // Get Estimated Product Addon with Estimate Addon Sets - By Ovi
            $estimateProductAddons = EstimateProductAddon::with(['estimate_addon_set'])->where('estimate_product_id', $tag->id)->get();
            return $this->successResponse([$tag, $estimateProductAddons], '');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EstimateProduct $tag){
         try {
            $this->validate($request, [
              'name.0' => 'required|string|max:255',
              'icon' => 'image'
            ],['name.0' => 'The default language name field is required.']);
            DB::beginTransaction();
            $estimate_product_id = $request->estimate_product_id;
            $tag = EstimateProduct::where('id', $estimate_product_id)->first();

            if ($request->hasFile('icon')) {    /* upload icon file */
                $file = $request->file('icon');
                $tag->icon = Storage::disk('s3')->put($this->folderName, $file, 'public');
                $tag->category_id = $request->get('product_category');
            }
            $pids = Product::where('global_product_id',$estimate_product_id)->get();
            Product::where('global_product_id',$estimate_product_id)->update(['title'=>$request->name[0],'url_slug'=>Str::slug($request->name[0], '-')]);
            //dd($pid);
            if(isset($pids)){
                foreach($pids as $pid)
                {
                    ProductTranslation::where('product_id',$pid->id)->update(['title'=>$request->name[0]]);
                }
            }

            $tag->save();
            $language_id = $request->language_id;
            EstimateProductTranslation::where('estimate_product_id', $estimate_product_id)->delete();
            foreach ($request->name as $k => $name) {
                if($name){
                    $TagTranslation = new EstimateProductTranslation();
                    $TagTranslation->name = $name;
                    $TagTranslation->slug = Str::slug($name, '-');
                    $TagTranslation->language_id = $language_id[$k];
                    $TagTranslation->estimate_product_id = $tag->id;
                    $TagTranslation->price = $request->get('price');
                    $TagTranslation->save();
                }
            }
            DB::commit();

            foreach($request->product_addons as $key=>$value){

                // Check Existing Coulmns in DB - By Ovi
                $checkColumnsFromDB = EstimateProductAddon::where('estimate_product_id', $tag->id)->get();

                // Compare Columns with Request Id's - By Ovi
                foreach($checkColumnsFromDB as $check)
                {
                    // Check If Column and Request Id matches - By Ovi
                   if(!in_array($check->estimate_addon_id, $request->product_addons)){
                       // Delete Row
                       $check->delete();
                   }
                }

                // Update Existing Estimate Product Addon - By Ovi
                $estimateProductAddon = EstimateProductAddon::where('estimate_product_id',$tag->id)->where('estimate_addon_id', $request->product_addons[$key])->first();
                // Check If Row Exists - By Ovi
                if(!$estimateProductAddon){
                    // Create New If Row is Not Found in the DB - By Ovi
                    $estimateProductAddon = new EstimateProductAddon();
                }
                $estimateProductAddon->estimate_product_id = $tag->id;
                $estimateProductAddon->estimate_addon_id = $request->product_addons[$key];
                $estimateProductAddon->save();
            }
            // Send Response with Estimate Products
            return $this->successResponse($tag, 'Product Updated Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request){
        try {
            EstimateProduct::where('id', $request->estimate_product_id)->delete();
            EstimateProductTranslation::where('estimate_product_id', $request->estimate_product_id)->delete();
            return $this->successResponse([], 'Product Deleted Successfully.');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }

    // Get Product Caregories - By Ovi
    public function getCategories()
    {
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;

        $product_categories = Category::with(['translation' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        }])->where('status', 1)->whereNotIn('type_id', [7])->get();

        $p_categories = collect();
        $product_categories_hierarchy = '';
        if ($product_categories) {
            foreach($product_categories as $pc){
                $p_categories->push($pc);
            }
            $product_categories_build = $this->buildTree($p_categories->toArray());
            $product_categories_hierarchy = $this->printCategoryOptionsHeirarchy($product_categories_build);
            foreach($product_categories_hierarchy as $k => $cat){
                $myArr = array(1,3,7,8,9);
                if (isset($cat['type_id']) && !in_array($cat['type_id'], $myArr)) {
                    unset($product_categories_hierarchy[$k]);
                }
            }
        }

        return $product_categories_hierarchy;
    }

    public function updateEstimationMatchingLogic(Request $request)
    {
        $client = ClientPreference::first();
        $client->estimation_matching_logic = $request->get('matching_logic');
        $client->save();

        Session::flash('success', 'Estimation logic updated.');
        return redirect()->back();
    }

    public function barcode(Request $request,$vendor = null)
    {
        try {
                $codes = QrcodeImport::with('vendorDetail')->latest();
                if($vendor){
                    $codes = $codes->where('vendor_id',$request->vendor);   
                }
                $codes  = $codes->paginate(25);
            $files = CsvQrcodeImport::get();
            return view('backend.qrcode.index')->with(['codes' => $codes,'files'=>$files]);

        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }

    public function deleteBarcode(Request $request)
    {
        try {

            $codes = QrcodeImport::find($request->qrCode);
            // $files = CsvQrcodeImport::get();
            return view('backend.qrcode.index')->with(['codes' => $codes,'files'=>$files]);

        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }

}
