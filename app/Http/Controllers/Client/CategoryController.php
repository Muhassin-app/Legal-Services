<?php

namespace App\Http\Controllers\Client;

use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{AddonSet, Client, ClientPreference, ProductVariant, MapProvider, Category, Category_translation, ClientLanguage, Variant, Brand, CategoryHistory, Type, CategoryTag, Vendor, DispatcherWarningPage, DispatcherTemplateTypeOption, Product,CategoryTranslation,CategoryKycDocumentMapping,CategoryKycDocuments,CategoryKycDocumentTranslation, Tag,Facilty, RoleOld, CategoryRole, Attribute, ClientCurrency};
use GuzzleHttp\Client as GCLIENT;
use App\Models\AdditionalAttributeOptionTranslation;
use App\Models\AdditionalAttributeOption;
use App\Models\AdditionalAttributeTranslation;
use App\Models\AdditionalAttribute;
use Illuminate\Support\Str;
use App\Models\AttributeType;
class CategoryController extends BaseController
{
    private $blocking = '2';
    private $folderName = 'category/icon';
    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/category/icon';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


       
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $celebrity_check = ClientPreference::first()->value('celebrity_check');

        // $brands = Brand::with('bc.cate.primary')->with('translation_one')->where('status', '!=', 2)->orderBy('position', 'asc')->with('bc')->get();
        $brands = Brand::with(['bc.categoryDetail', 'bc.categoryDetail.translation' =>  function ($q) use ($langId) {
            $q->select('category_translations.name', 'category_translations.category_id', 'category_translations.language_id')->where('category_translations.language_id', $langId);
        }, 'translation' => function ($q) use ($langId) {
            $q->select('title', 'brand_id', 'language_id')->where('language_id', $langId);
        }])->where('status', 1)->orderBy('position', 'asc')->get();

        $variants = Variant::with('option', 'varcategory.cate.primary','translation_one')->where('status', '!=', 2)->orderBy('position', 'asc')->get();
        $attributes = [];
        if( checkTableExists('product_attributes') ) {
            $attributes = Attribute::with('option', 'varcategory.cate.primary','translation_one')->where('status', '!=', 2)->orderBy('position', 'asc');
            if(Auth::user()->is_superadmin) {
                $attributes = $attributes->get();
            }
            else {
                $attributes = $attributes->where('user_id', Auth::id())->get();
            }
        }

        $categories = Category::with('translation_one','type')->where('id', '>', '1')->where('is_core', 1)->orderBy('parent_id', 'asc')->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1);
      
        if ($celebrity_check == 0)
            $categories = $categories->where('type_id', '!=', 5);   # if celebrity mod off .
          
        $categories = $categories->get();
        if ($categories) {
            $build = $this->buildTree($categories->toArray());;
             
            $tree = $this->printTree($build);
        }
        $tags = Tag::with('primary')->latest()->get();
        $facilties = Facilty::with('primary')->get();
        // $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
        //     ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
        //     // ->where('client_languages.client_code', Auth::user()->code)
        //     ->where('client_languages.is_active', 1)
        //     ->orderBy('client_languages.is_primary', 'desc')->get();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
            ->where('is_active', 1)
            ->orderBy('is_primary', 'desc')->get();
        $addon_sets = AddonSet::with('option')->orderBy('id', 'desc')->get();
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        
       
        return view('backend.catalog.index')->with(['clientCurrency' => $clientCurrency, 'categories' => $categories, 'addon_sets' => $addon_sets ,'html' => $tree,  'languages' => $langs, 'variants' => $variants, 'brands' => $brands, 'build' => $build, 'tags'=>$tags,'facilties'=>$facilties,'client_languages'=>$langs, 'attributes'=>$attributes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $is_vendor = ($request->has('is_vendor')) ? $request->is_vendor : 0;
        $vendors = array();
        $category = new Category();
        $preference = ClientPreference::first();
        $type_service =  getCategoryTypesServices();

        $type = Type::whereIn('service_type',$type_service)->orderBY('sequence', 'ASC')->get();
        // pr( $type);
        // switch($preference->business_type){
        //     case "taxi":
        //     $type =Type::where('title','Pickup/Delivery')->orderBY('sequence', 'ASC')->get();
        //     break;
        //     case "food_grocery_ecommerce":
        //     $type =Type::whereNotIn('title',['Pickup/Delivery','On Demand Service','Pickup/Parent','laundry'])->orderBY('sequence', 'ASC')->get();
        //     break;
        //     case "home_service":
        //     $type =Type::whereNotIn('title',['Pickup/Delivery','Pickup/Parent'])->orderBY('sequence', 'ASC')->get();
        //     break;
        //     case "laundry":
        //     $type =Type::whereNotIn('title',['Pickup/Delivery','Pickup/Parent','On Demand Service'])->orderBY('sequence', 'ASC')->get();
        //     break;
        //     default:
        //     $type = Type::where('title', '!=', 'Pickup/Parent')->orderBY('sequence', 'ASC')->get();
        // }



        $parCategory = Category::with('translation_one')->select('id', 'slug')->where('deleted_at', NULL)->whereIn('type_id', ['1', '3', '6', '8','9','11','14'])->where('is_core', 1)->where('status', 1)->get();
        $vendor_list = Vendor::vendorOnline()->select('id', 'name')->where('status', '!=', $this->blocking)->get();
        $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
            ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
            ->where('client_languages.client_code', Auth::user()->code)
            ->where('client_languages.is_active', 1)
            ->orderBy('client_languages.is_primary', 'desc')->get();
        $dispatcher_warning_page_options = DispatcherWarningPage::where('status', 1)->get();
        $dispatcher_template_type_options = DispatcherTemplateTypeOption::where('status', 1)->get();
        $returnHTML = view('backend.catalog.add-category')->with(['category' => $category, 'is_vendor' => $is_vendor, 'languages' => $langs, 'parCategory' => $parCategory, 'typeArray' => $type, 'vendor_list' => $vendor_list, 'dispatcher_template_type_options' => $dispatcher_template_type_options, 'dispatcher_warning_page_options' => $dispatcher_warning_page_options, 'preference' => $preference])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'cat_lang.name' => 'required|string|max:60',
            'slug' => 'required|string|max:30|unique:categories',
        );
        if ($request->type == 'Vendor') {
            $rules['vendor_ids'] = "required";
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $cate = new Category();
        $save = $this->save($request, $cate, 'false');
        if ($save > 0) {
            $languageId = $request->cat_lang['lang_id'];
            Category_translation::updateOrCreate(
                ['category_id' => $save,
                'language_id' => $languageId],
                ['category_id' => $save,
                    'language_id' => $languageId,
                    'name' => $request->cat_lang['name'],
                    'meta_title' => $request->cat_lang['meta_title'],
                    'meta_description' => $request->cat_lang['meta_description'],
                    'meta_keywords' => $request->cat_lang['meta_keywords']
                ]
            );

            $hs = new CategoryHistory();
            $hs->category_id = $save;
            $hs->action = 'Add';
            $hs->updater_role = 'Admin';
            $hs->update_id = Auth::user()->id;
            $hs->client_code = Auth::user()->code;
            $hs->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Category created Successfully!',
                'data' => $save
            ]);
        }
        if ($save == 'bad parent') {
            return response()->json([
                'status' => 'error1',

            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category_translation  $category_translation
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $domain = '', $id)
    {
        $is_vendor = ($request->has('is_vendor')) ? $request->is_vendor : 0;
        $vendors = array();
        $tagList = array();
        $preference = ClientPreference::first();
        switch($preference->business_type){
            case "taxi":
            $type =Type::where('title','Pickup/Delivery')->orderBY('sequence', 'ASC')->get();
            break;
            case "food_grocery_ecommerce":
            $type =Type::whereNotIn('title',['Pickup/Delivery','On Demand Service','Pickup/Parent','laundry'])->orderBY('sequence', 'ASC')->get();
            break;
            case "laundry":
            $type =Type::whereNotIn('title',['Pickup/Delivery','Pickup/Parent','On Demand Service'])->orderBY('sequence', 'ASC')->get();
            break;
            case "home_service":
            $type =Type::whereNotIn('title',['Pickup/Delivery','Pickup/Parent'])->orderBY('sequence', 'ASC')->get();
            break;
            default:
            $type = Type::where('title', '!=', 'Pickup/Parent')->orderBY('sequence', 'ASC')->get();
        }


     //   $get_multi_cat = CategoryTranslation::where('category_id',$id)->groupBy('language_id')->orderBY('updated_at','desc')->pluck('id');

     //   $del = CategoryTranslation::where('category_id',$id)->whereNotIn('id',$get_multi_cat)->delete();

        $category = Category::with('translationSetUnique', 'tags','primary')->where('id', $id)->first();
        $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
            ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
            ->where('client_languages.client_code', Auth::user()->code)
            ->where('client_languages.is_active', 1)
            ->orderBy('client_languages.is_primary', 'desc')->get();
        $existlangs = $langIds = array();
        foreach ($langs as $key => $value) {
            $langIds[] = $langs[$key]->langId;
        }
        foreach ($category->translationSetUnique as $key => $value) {
            $existlangs[] = $value->language_id;
        }
        $parCategory = Category::with('translation_one')->select('id', 'slug')->where('categories.id', '!=', $id)->where('status', '!=', $this->blocking)->whereIn('type_id', ['1', '3', '6', '8','9','11','10'])->where('deleted_at', NULL)->get();
        $dispatcher_warning_page_options = DispatcherWarningPage::where('status', 1)->get();
        $dispatcher_template_type_options = DispatcherTemplateTypeOption::where('status', 1)->get();

        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);

        if($getAdditionalPreference['is_price_by_role'] == 1){
            $roles = RoleOld::get();
            if($roles != null){
                foreach($roles as $role){
                    $category_role = CategoryRole::where('category_id', $id)->where('role_id', $role->id)->first();
                    if($category_role != null){
                        $role->is_added = true;
                    }else{
                        $role->is_added = false;
                    }
                }
            }
        }


        $returnHTML = view('backend.catalog.edit-category')->with(['typeArray' => $type, 'category' => $category,  'languages' => $langs, 'is_vendor' => $is_vendor, 'parCategory' => $parCategory, 'langIds' => $langIds, 'existlangs' => $existlangs, 'tagList' => $tagList, 'dispatcher_warning_page_options' => $dispatcher_warning_page_options, 'dispatcher_template_type_options' => $dispatcher_template_type_options, 'preference' => $preference])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    public function getCategoryTranslation(Request $request){
        $trans = [];
        if(!empty($request->categoryId) && !empty($request->languageId)){
            $trans = Category_translation::where('category_id', $request->categoryId)->where('language_id', $request->languageId)->latest()->first();
            if(!$trans){
                $trans = new Category_translation();
                $trans->category_id = $request->categoryId;
                $trans->language_id = $request->languageId;
                $trans->save();
            }
            return response()->json(array('status' => 'success', 'data' => $trans));
        }
        return response()->json(array('status' => 'error', 'data' => $trans));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category_translation  $category_translation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {

        $rules = array(
            'slug' => 'required|string|max:30|unique:categories,slug,' . $id,
            'cat_lang.name' => 'required|string|max:60',
        );
        $validation  = Validator::make($request->all(), $rules)->validate();
        $category = Category::where('id', $id)->first();
        $save = $this->save($request, $category, 'true');

        $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
            ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
            ->where('client_languages.client_code', Auth::user()->code)
            ->where('client_languages.is_active', 1)
            ->orderBy('client_languages.is_primary', 'desc')->get();
        $languageId = $request->cat_lang['language_id']??$langs->first()->langId;
        if ($save > 0) {
            if (!empty($languageId)) {
                // $languageId = $request->cat_lang['language_id'];
                $trans = Category_translation::where('category_id', $save)->where('language_id', $languageId)->latest()->first();

                if (!$trans) {
                    $trans = new Category_translation();
                    $trans->category_id = $save;
                    $trans->language_id = $languageId;
                }
                $trans->name = $request->cat_lang['name'];
                $trans->meta_title = $request->cat_lang['meta_title'];
                $trans->meta_description = $request->cat_lang['meta_description'];
                $trans->meta_keywords = $request->cat_lang['meta_keywords'];
                $trans->save();
            }
            $hs = new CategoryHistory();
            $hs->action = 'Update';
            $hs->category_id = $save;
            $hs->updater_role = 'Admin';
            $hs->update_id = Auth::user()->id;
            $hs->client_code = Auth::user()->code;
            $hs->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Category created Successfully!',
                'data' => $save
            ]);
        }
        if ($save == 'bad parent') {
            return response()->json([
                'status' => 'error1',

            ]);
        }
        if ($save == 'bad type') {
            return response()->json([
                'status' => 'error2',

            ]);
        }
        // return response()->json('error', 'Cannot create a sub-category of product type of category!!!');
    }

    /**
     * save and update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, Category $cate, $update = 'false')
    {
        try {
            $cate->slug = $request->slug;
            if ($request->type_id == 1 && $cate->childs->first() != null) {
                return 'bad type';
            }
            $cate->type_id = $request->type_id;
            $cate->display_mode = $request->display_mode;
            $cate->warning_page_id = $request->warning_page_id;
            $cate->template_type_id = $request->template_type_id;
            $cate->warning_page_design = $request->has('warning_page_design') ? $request->warning_page_design : 0;
            $cate->is_visible = ($request->has('is_visible') && $request->is_visible == 'on') ? 1 : 0;
            $cate->show_wishlist = ($request->has('show_wishlist') && $request->show_wishlist == 'on') ? 1 : 0;
            $cate->can_add_products = ($request->has('can_add_products') && $request->can_add_products == 'on' && ($request->type_id == 1 || $request->type_id == 3)) ? 1 : 0;
            if ($request->has('parent_cate') && $request->parent_cate > 0) {
                $cat = Category::find($request->parent_cate);
                if ($request->parent_cate != 1) {
                    if (($update == 'false' || $update == 'true') && $cat->type->title == 'Product') {
                        return 'bad parent';
                    }
                }
                $cate->parent_id = $request->parent_cate;
            } else {
                $cate->parent_id = 1;
            }
            if ($update == 'false') {
                if ($request->has('vendor_id')) {
                    $cate->is_core = 0;
                    $cate->vendor_id = $request->vendor_id;
                } else {
                    $cate->is_core = 1;
                }
                $cate->status = 1;
                $cate->position = 1;
                $cate->client_code = (!empty(Auth::user()->code)) ? Auth::user()->code : '';
            }
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $cate->icon = Storage::disk('s3')->put($this->folderName, $file, 'public');
            }
            if ($request->hasFile('icon_two')) {
                $file = $request->file('icon_two');
                $cate->icon_two = Storage::disk('s3')->put($this->folderName, $file, 'public');
            }
            if(@$request->remove_image && $request->remove_image == 1){
                Storage::disk('s3')->delete($cate->image);
                $cate->image = null;
            }
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $cate->image = Storage::disk('s3')->put('/category/image', $file, 'public');
            }
            if ($request->hasFile('cat_banner')) {
                $catBannerImages = [];
                if(!empty($request->cat_banner)){
                    foreach($request->cat_banner as $catBanner){
                        $catBannerImages[] = Storage::disk('s3')->put('/category/image', $catBanner, 'public');
                    }
                }
                $cate->sub_cat_banners = implode(',',$catBannerImages);
            }
            $cate->save();
            $tagDelete = CategoryTag::where('category_id', $cate->id)->delete();
            if ($request->has('tags') && !empty($request->tags)) {
                $tagArray = array();
                $tags = explode(',', $request->tags);
                foreach ($tags as $k => $v) {
                    $tagArray[] = [
                        'category_id' => $cate->id,
                        'tag' => $v
                    ];
                }
                CategoryTag::insert($tagArray);
            }

            // category role
            $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);

            if($getAdditionalPreference['is_price_by_role'] == 1){
                if($request->has('role')){
                    $roles = $request->role;

                    $role_array = [];
                    foreach($roles as $key => $role){
                        array_push($role_array, $key);

                        $category_role = CategoryRole::where('category_id', $cate->id)->where('role_id', $key)->first();
                        if($category_role == null){
                            $category_role = new CategoryRole();
                        }
                        $category_role->category_id = $cate->id;
                        $category_role->role_id = $key;
                        $category_role->save();
                    }

                    // delete those role which are not there in array
                    CategoryRole::where('category_id', $cate->id)->whereNotIn('role_id', $role_array)->delete();
                }
            }

            return $cate->id;
        } catch (Exception $e) {
            pr($e->getMessage());
            die;
        }
    }

    /**
     * Update the order of categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category_translation  $category_translation
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request)
    {
        $data = json_decode($request->orderDta);
        $arr = $this->buildArray($data);
        if ($arr > 0) {
            return redirect('client/category')->with('success', 'Category order updated successfully!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category_translation  $category_translation
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id)
    {
        $user = Auth::user();
        $parent = Category::where('id', $id)->first();
        $array_of_ids = $this->getChildren($parent);
        array_push($array_of_ids, $id);


        $dynamic = time().substr(md5(mt_rand()), 0, 7);

        $tot_var  = Product::whereIn('category_id', $array_of_ids)->select('id','sku')->get();
        foreach($tot_var as $varr)
        {
            $dynamic = time().substr(md5(mt_rand()), 0, 7);
            Product::where('id', $varr->id)->update(['sku' => $varr->sku.$dynamic]);
            ProductVariant::where('product_id', $varr->product_id)->update(['sku' => $varr->sku.$dynamic]);
        }

        foreach($array_of_ids as $varr)
        {
            $dynamic = time().substr(md5(mt_rand()), 0, 7);
            Category::where('id', $varr)->update(['slug' => $dynamic]);

        }



        Product::whereIn('category_id', $array_of_ids)->delete();
        Category::destroy($array_of_ids);

        // category kyc document delete
        CategoryKycDocumentMapping::where('category_id',$id)->delete();
        // CategoryKycDocuments::whereIn(['id', $category_kyc_document_ids])->delete();
        // CategoryKycDocumentTranslation::whereIn(['category_kyc_document_id',  $category_kyc_document_ids])->delete();
        // CategoryKycDocumentMapping::whereIn('category_kyc_document_id',$category_kyc_document_ids)->delete();
        //end kyc document

        CategoryHistory::insert([
            'category_id' => $id,
            'action' => 'deleted',
            'update_id' => $user->id,
            'updater_role' => 'Admin',
            'client_code' => $user->code,
        ]);

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    private function getChildren($category)
    {
        $ids = [];
        if ($category->childs) {
            foreach ($category->childs as $cat) {
                $ids[] = $cat->id;
                $ids = array_merge($ids, $this->getChildren($cat));
            }
        }
        return $ids;
    }

    # get dispatcher tags from dispatcher panel
    public function getDispatcherTags()
    {
        try {
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $client = new GCLIENT([
                    'headers' => [
                        'personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                        'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                        'content-type' => 'application/json'
                    ]
                ]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->get($url . '/api/get-agent-tags');
                $response = json_decode($res->getBody(), true);
                if ($response && $response['message'] == 'success') {
                    return $response['tags'];
                }
            }
        } catch (\Exception $e) {
        }
    }
    # check if last mile delivery on
    public function checkIfPickupDeliveryOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
            return $preference;
        else
            return false;
    }

    public function manageAttribute(Request $request){
        $attributes = [];
        if( checkTableExists('product_attributes') ) {
            $attributes = AdditionalAttribute::with('option','translation_one')->where('status', '!=', 2)->orderBy('position', 'asc');
            if(Auth::user()->is_superadmin) {
                $attributes = $attributes->get();
            }
            else {
                $attributes = $attributes->where('user_id', Auth::id())->get();
            }
        }

        return view('backend.attributes.manageAttribute')->with(['attributes' => $attributes]);
    }

    public function getAddAttributeForm(Request $request)
    {
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
        ->where('is_active', 1)
        ->orderBy('is_primary', 'desc')->get();

        $attributeType = AttributeType::select('id', 'title')
        ->get()
        ->pluck('title', 'id')
        ->toArray();
        $fieldType = AdditionalAttribute::fieldType();
        $returnHTML = view('backend.attributes.add-attribute')->with(['languages' => $langs, 'attributeType' => $attributeType, 'fieldType' => $fieldType])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    public function getEditAttributeForm($domain = '', $id)
    {
        if(
            checkTableExists('additional_attributes') &&
            checkTableExists('additional_attributes_option_translations') &&
            checkTableExists('additional_attributes_options') &&
            checkTableExists('additional_attributes_translations')
            ){
                $variant = AdditionalAttribute::select('id', 'title', 'type_id', 'position', 'service_type', 'field_type')
                ->with('translation', 'option.translation')
                ->where('id', $id)->firstOrFail();

                $langs = ClientLanguage::with(['language', 'variantTrans' => function($query) use ($id) {
                    $query->where('variant_id', $id);
                }])
                ->select('language_id', 'is_primary', 'is_active')
                ->where('is_active', 1)
                ->orderBy('is_primary', 'desc')->get();
                $submitUrl = route('manage.attribute.update', $id);

                $attributeType = AttributeType::select('id', 'title')
                ->get()
                ->pluck('title', 'id')
                ->toArray();
                $fieldType = AdditionalAttribute::fieldType();
                $returnHTML = view('backend.attributes.edit-attribute')->with(['languages' => $langs, 'variant' => $variant, 'attributeType' => $attributeType, 'fieldType' => $fieldType])->render();
                return response()->json(array('success' => true, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
        }
        else {
            return response()->json(array('success' => false));
        }
    }

    public function storeAttributeForm(Request $request)
    {
        if(
            checkTableExists('additional_attributes') &&
            checkTableExists('additional_attributes_option_translations') &&
            checkTableExists('additional_attributes_options') &&
            checkTableExists('additional_attributes_translations')
            ){
                \DB::beginTransaction();
                $v_pos = AdditionalAttribute::select('id','position')->where('position', \DB::raw("(select max(`position`) from variants)"))->first();
                $variant = new AdditionalAttribute();
                $variant->title = (!empty($request->title[0])) ? $request->title[0] : '';
                $variant->user_id = Auth::id();
                $variant->field_type = $request->field_type;
                $variant->type_id = $request->type_id;
                $variant->service_type = $request->service_type;
                $variant->is_required = $request->is_required;
                $variant->position = 1;
                if($v_pos){
                    $variant->position = $v_pos->position + 1;
                }
                $variant->save();
                $data = [];
                if($variant->id > 0){
                    foreach ($request->title as $key => $value) {
                        $varTrans = new AdditionalAttributeTranslation();
                        $varTrans->title = $request->title[$key];
                        $varTrans->additional_attribute_id = $variant->id;
                        $varTrans->language_id = $request->language_id[$key];
                        $varTrans->slug = Str::slug($request->title[$key], "_");
                        $varTrans->save();
                    }

                    foreach ($request->hexacode as $key => $value) {

                        $varOpt = new AdditionalAttributeOption();
                        $varOpt->title = $request->opt_color[0][$key];
                        $varOpt->additional_attribute_id = $variant->id;
                        $varOpt->hexcode = ($value == '') ? '' : $value;
                        $varOpt->save();

                        foreach($request->language_id as $k => $v) {
                            $data[] = [
                                'title' => $request->opt_color[$k][$key],
                                'additional_attribute_option_id' => $varOpt->id,
                                'language_id' => $v
                            ];
                        }
                    }
                    AdditionalAttributeOptionTranslation::insert($data);
                }
                \DB::commit();
                return redirect()->back()->with('success', 'Attribute added successfully!');
        }
        else {
            return redirect()->back()->with(array('error_delete' => 'Attribute not added !'));
        }
    }

    public function updateAttributeForm(Request $request, $domain = '', $id)
    {
        if(
            checkTableExists('additional_attributes') &&
            checkTableExists('additional_attributes_option_translations') &&
            checkTableExists('additional_attributes_options') &&
            checkTableExists('additional_attributes_translations')
            ){
                \DB::beginTransaction();
                $variant = AdditionalAttribute::where('id', $id)->firstOrFail();
                $variant->title = (!empty($request->title[0])) ? $request->title[0] : '';
                $variant->user_id = Auth::id();
                $variant->field_type = $request->field_type;
                $variant->type_id = $request->type_id;
                $variant->service_type = $request->service_type;
                $variant->is_required = $request->is_required;
                $variant->save();

                foreach ($request->language_id as $key => $value) {

                    $varTrans = AdditionalAttributeTranslation::where('language_id', $value)->where('additional_attribute_id', $variant->id)->first();
                    if(!$varTrans){
                        $varTrans = new AdditionalAttributeTranslation();
                        $varTrans->additional_attribute_id = $variant->id;
                        $varTrans->language_id = $value;
                    }
                    $varTrans->title = $request->title[$key];
                    $varTrans->slug = str::slug($request->title[$key], "_");
                    $varTrans->save();
                }

                $exist_options = [];
                foreach ($request->option_id as $key => $value) {

                    $curLangId = $request->language_id[0];

                    if(!empty($value)){
                        $varOpt = AdditionalAttributeOption::where('id', $value)->first();

                        if(!$varOpt){
                            $varOpt = new AdditionalAttributeOption();
                            $varOpt->additional_attribute_id = $variant->id;
                        }

                        $varOpt->title = $request->opt_title[$curLangId][$key];
                        $varOpt->hexcode = ($request->hexacode[$key] == '') ? '' : $request->hexacode[$key];
                        $varOpt->save();
                        $exist_options[$key] = $varOpt->id;
                    }else{

                        $varOpt = new AdditionalAttributeOption();
                        $varOpt->additional_attribute_id = $variant->id;
                        $varOpt->title = $request->opt_title[$curLangId][$key];
                        $varOpt->hexcode = ($request->hexacode[$key] == '') ? '' : $request->hexacode[$key];
                        $varOpt->save();
                        $exist_options[$key] = $varOpt->id;

                    }
                }

                foreach($request->opt_id as $lid => $options) {

                    foreach($options as $key => $value) {

                        if(!empty($value)){
                            $varOptTrans = AdditionalAttributeOptionTranslation::where('language_id', $lid)->where('attribute_option_id', $value)->first();
                            if(!$varOptTrans){
                                $varOptTrans = new AdditionalAttributeOptionTranslation();
                                $varOptTrans->additional_attribute_option_id =$exist_options[$key];
                                $varOptTrans->language_id = $lid;
                            }
                            $varOptTrans->title = $request->opt_title[$lid][$key];
                            $varOptTrans->save();

                        }else{
                            $varOptTrans = new AdditionalAttributeOptionTranslation();
                            $varOptTrans->additional_attribute_option_id =$exist_options[$key];
                            $varOptTrans->language_id = $lid;
                            $varOptTrans->title = $request->opt_title[$lid][$key];
                            $varOptTrans->save();
                        }
                    }
                }
                \DB::commit();
                return redirect()->back()->with('success', 'Attribute updated successfully!');
        }
        else {
            return redirect()->back()->with('error_delete', 'Attribute not updated successfully!');
        }
    }

    public function destroyAttribute($domain = '', $id)
    {
        if(checkTableExists('additional_attributes')) {
            $var = AdditionalAttribute::where('id', $id)->first();
            $var->status = 2;
            $var->save();
            return redirect()->back()->with('success', 'Attribute deleted successfully!');
        }
        else {
            return redirect()->back()->with('error_delete', 'Attribute not deleted successfully!');
        }
    }
}
