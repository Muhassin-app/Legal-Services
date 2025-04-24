<?php

namespace App\Http\Controllers\Client;

use Session;
use Carbon\Carbon;
use App\Models\Measurements;
use Illuminate\Http\Request;
use App\Imports\QrcodesImport;
use App\Imports\ProductsImport;

use App\Http\Traits\ApiResponser;

use App\Jobs\ProductImportCsvJob;
use GuzzleHttp\Client as GCLIENT;
use App\Models\ProductMeasurement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\ToasterResponser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\SquareInventoryManager;
use App\Http\Controllers\Client\BaseController;
use App\Models\{CsvProductImport, Product, Category, ProductTranslation, Nomenclature, NomenclatureTranslation, Vendor, AddonSet, ProductRelated, ProductCrossSell, ProductAddon, ProductCategory, ClientLanguage, ProductVariant, ProductImage, TaxCategory, ProductVariantSet, Country, Variant, VendorMedia, ProductVariantImage, Brand, Celebrity, ClientPreference, ProductCelebrity, Type, ProductUpSell, CartProduct, CartAddon, UserWishlist,Client, CsvQrcodeImport, Tag,ProductTag,ProductFaq, ProductVariantByRole, RoleOld as Role, TaxRate, ProductByRole, ProductDeliveryFeeByRole, TollPassOrigin, TravelMode, VehicleEmissionType, Attribute, BookingOption, BundleupProduct, ProductAttribute,LongTermServiceProductAddons, ProcessorProduct, OrderProduct,DeliverySlot, MargProduct, Pincode, ProductBookingOption, ProductRentalProtection, RentalProtection};

class ProductController extends BaseController
{
    use ApiResponser, SquareInventoryManager;
    private $folderName = 'prods';
    private $slugIsUnique = true;
    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/prods';
    }
    use ToasterResponser;
    /**   Display   List of products  */
    public function index()
    {
    }

    /**   Add new product view - currently not working add product show in modal  */
    public function create($domain = '', $id)
    {
        $vendor = Vendor::findOrFail($id);
        $type = Type::all();
        $countries = Country::all();
        $addons = AddonSet::with('option')->select('id', 'title')
            ->where('status', '!=', 2)
            ->where('vendor_id', $id)
            ->orderBy('position', 'asc')->get();

        $categories = Category::with('english')->select('id', 'slug')
            ->where('id', '>', '1')->where('status', '!=', '2')
            ->where('can_add_products', 1)->orderBy('parent_id', 'asc')
            ->orderBy('position', 'asc')->get();

        $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
            ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code')
            ->where('client_languages.client_code', Auth::user()->code)->get();

        $taxCate = TaxCategory::all();
        return view('backend/product/create', ['typeArray' => $type, 'categories' => $categories, 'vendor_id' => $vendor->id, 'addons' => $addons, 'languages' => $langs, 'taxCate' => $taxCate, 'countries' => $countries]);
    }

    /**
     * Validate add prodect fields
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateData(Request $request)
    {
        //checking if slug is unique in particular vendor
        if(!empty(Product::where(['vendor_id'=>$request->vendor_id,'url_slug'=>$request->url_slug])->first()->id)){
        $this->slugIsUnique=false;
        }
        $rules = array(
            'sku' => 'required|unique:products',
            // 'url_slug' => 'required|unique:products',
            'category' => 'required',
            'product_name' => 'required',
                'url_slug' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if ($this->slugIsUnique== false) {
                            $fail('The '.$attribute.' exists already.');
                        }
                    },
                ],
        );
        $validation = Validator::make($request->all(), $rules)->validate();

        if ($validation) {
            return response()->json([
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
            ]);
        }
    }

    /**
     * Validate product sku
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateSku(Request $request){
        $sku = $request->sku;
        $product = Product::where('sku', $sku)->first();
        if($product){
            return $this->errorResponse(__('Sku is not available'), 422);
        }else{
            return $this->successResponse('', __('Sku is available'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $rule = array(
            'sku' => 'required|unique:products',
            'url_slug' => 'required',
            'category' => 'required',
            'product_name' => 'required',
        );
        $validation  = Validator::make($request->all(), $rule);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        try {
            DB::beginTransaction();
            $product = new Product();
            $product->sku = $request->sku;
            $product->url_slug = empty($request->url_slug) ? $request->sku : $request->url_slug;
            $product->title = empty($request->product_name) ? $request->sku : $request->product_name;
            $product->type_id = $request->type_id;
            $product->category_id = $request->category;
            $product->vendor_id = $request->vendor_id;
            $product->captain_name = $request->captain_name ?? '';
            $product->captain_description = $request->captain_description ?? '';
            if($request->hasFile('captain_profile')){
                $filePath = 'profile/' . \Str::random(40);
                $file = $request->file('captain_profile');
                $orignal_name = $request->file('captain_profile')->getClientOriginalName();
                $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                $url = Storage::disk('s3')->url($file_name);
                $product->captain_profile = $url;
            }
            $client_lang = ClientLanguage::where('is_primary', 1)->first();
            if (!$client_lang) {
                $client_lang = ClientLanguage::where('is_active', 1)->first();
            }
            $product->save();
            if ($product->id > 0) {
                $datatrans[] = [
                    'title' => $request->product_name??null,
                    'body_html' => '',
                    'meta_title' => '',
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'product_id' => $product->id,
                    'language_id' => $client_lang->language_id
                ];
                $product_category = new ProductCategory();
                $product_category->product_id = $product->id;
                $product_category->category_id = $request->category;
                $product_category->save();
                $proVariant = new ProductVariant();
                $proVariant->sku = $request->sku;
                $proVariant->title = $request->sku . '-' .  empty($request->product_name) ? $request->sku : $request->product_name;
                $proVariant->product_id = $product->id;
                $proVariant->barcode = $this->generateBarcodeNumber();
                $proVariant->save();
                ProductTranslation::insert($datatrans);
                DB::commit();
                return redirect('client/product/' . $product->id . '/edit')->with('success', __('Product added successfully!') );
            }

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withError($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id)
    {
        $measurements="";
        $productMeasurementData = [];
        // $this->testfun1();
        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role', 'is_free_delivery_by_roles', 'is_seller_module', 'is_cab_pooling', 'is_one_push_book_enable','is_service_product_price_from_dispatch']);
        // $this->searchCatalogObjects();

        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role', 'is_free_delivery_by_roles', 'is_seller_module', 'is_cab_pooling', 'is_one_push_book_enable','is_service_product_price_from_dispatch', 'is_same_day_delivery', 'is_next_day_delivery', 'is_hyper_local_delivery', 'is_attribute','is_product_measurement_in_cm_kg','product_measurment']);

        $with_array = ['brand', 'variant.set','vendor', 'variant.vimage.pimage.image', 'primary', 'category.cat', 'variantSets', 'vatoptions', 'addOn', 'media.image', 'related', 'upSell', 'crossSell', 'celebrities','productVariantByRoles', 'bookingOptions', 'rentalProtections'];

        if( checkTableExists('product_attributes') ) {
            $with_array[] = 'ProductAttribute';
        }

        if( checkTableExists('delivery_slots_product') ) {
            $with_array[] = 'syncProductDeliverySlot';
        }

        $product = Product::with($with_array)->where('id', $id)->firstOrFail();

        $type = Type::all();
        $countries = Country::all();
        $addons = AddonSet::with('option')->select('id', 'title')
            ->where('status', '!=', 2)
            // ->where('vendor_id', $product->vendor_id)
            ->orderBy('position', 'asc')->get();
        $brands = Brand::join('brand_categories as bc', 'bc.brand_id', 'brands.id')
            ->select('brands.id', 'brands.title', 'brands.image')
            ->where('bc.category_id', $product->category_id)->where('status',1)->get();
        $clientLanguages = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
            ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.is_primary')
            ->where('client_languages.client_code', Auth::user()->code)
            ->where('client_languages.is_active', 1)
            ->orderBy('client_languages.is_primary', 'desc')->get();


        $productVariants = Variant::with('option', 'varcategory.cate.primary')
            ->select('variants.*')
            ->join('variant_categories', 'variant_categories.variant_id', 'variants.id')
            ->where('variant_categories.category_id', $product->category_id)
            ->where('variants.status', '!=', 2)
            ->orderBy('position', 'asc')->get();


        $taxCate = TaxCategory::all();

        $celeb_ids = $related_ids = $upSell_ids = $crossSell_ids = $existOptions = $addOn_ids = $attribute_value = $attribute_key_value = $attribute_latitude = $attribute_longitude = array();

        foreach ($product->addOn as $key => $value) {
            $addOn_ids[] = $value->addon_id;
        }

        foreach ($product->related as $key => $value) {
            $related_ids[] = $value->related_product_id;
        }
        foreach ($product->upSell as $key => $value) {
            $upSell_ids[] = $value->upsell_product_id;
        }
        foreach ($product->crossSell as $key => $value) {
            $crossSell_ids[] = $value->cross_product_id;
        }

        foreach ($product->crossSell as $key => $value) {
            $crossSell_ids[] = $value->cross_product_id;
        }
        foreach($product->variantSets as $key=>$value){
            $existOptions[] = $value->variant_option_id;
        }

        foreach ($product->celebrities as $key => $value) {
            if (!in_array($value->celebrity_id, $celeb_ids)) {
                $celeb_ids[] = $value->celebrity_id;
            }
        }
        $productAttributes = [];
        if( checkTableExists('attributes') ) {
            $productAttributes = Attribute::with('option', 'varcategory.cate.primary')
                ->select('attributes.*')
                ->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
                ->where('attribute_categories.category_id', $product->category_id)
                ->where('attributes.status', '!=', 2)
                ->orderBy('position', 'asc')->get();

            if( !empty($product->ProductAttribute) ) {
                foreach($product->ProductAttribute as $key => $val) {
                    $attribute_value[] = $val->attribute_option_id;
                    $attribute_key_value[$val->attribute_option_id] = $val->key_value;
                    if(!empty($val->latitude)){
                        $attribute_latitude[$val->attribute_option_id] = $val->latitude;
                    }
                    if (!empty($val->longitude)) {
                        $attribute_longitude[$val->attribute_option_id] = $val->longitude;
                    }
                }
            }
        }



        $otherProducts                      = Product::with('primary')->select('id', 'sku','title')->where('is_live', 1)->where('id', '!=', $product->id)->where('vendor_id', $product->vendor_id)->get();
        $bundle_product_ids = BundleupProduct::where('product_id',$id)->get()->pluck('related_product_id')->toArray();
        $configData                         = ClientPreference::select('celebrity_check', 'pharmacy_check', 'need_dispacher_ride', 'need_delivery_service', 'enquire_mode','need_dispacher_home_other_service','delay_order','product_order_form','business_type','minimum_order_batch','age_restriction_on_product_mode','need_appointment_service')->first();

        $celebrities                        = Celebrity::select('id', 'name')->where('status', '!=', 3)->get();
        $configData->is_cab_pooling         = $getAdditionalPreference['is_cab_pooling'];
        $configData->is_one_push_book_enable= $getAdditionalPreference['is_one_push_book_enable'];
        $celebrities                        = Celebrity::select('id', 'name')->where('status', '!=', 3)->get();
        $tollPassOrigin                     = TollPassOrigin::select('id', 'toll_pass', 'desc')->get();
        $travelMode                         = TravelMode::select('id', 'travelmode', 'desc')->get();

        $vehicleEmissionType                = VehicleEmissionType::select('id', 'emission_type', 'desc')->get();
       if($getAdditionalPreference['product_measurment'] == 1){
            $measurements                       =Measurements::with('category')->where('category_id',$product->category_id)->where('vendor_id',$product->vendor_id)->get();
            $productMeasurements                =ProductMeasurement::where('product_id',$product->id)->get();
       }

        $agent_dispatcher_tags = [];
        $agent_dispatcher_on_demand_tags = [];
        $pro_tags = [];

        if(isset($product->category->categoryDetail)){
            switch($product->category->categoryDetail->type_id){
                case 7:
                    $vendor_id = $product->vendor_id;
                    $agent_dispatcher_tags = $this->getDispatcherTags($vendor_id);
                    break;
                case 1:
                    $vendor_id = $product->vendor_id;
                    $agent_dispatcher_tags = $this->getDeliveryDispatcherTags($vendor_id);
                    break;
                case 8:
                    $vendor_id = $product->vendor_id;
                    $onDemandRes = $this->getDispatcherOnDemandTags($vendor_id);
                    if(  $getAdditionalPreference['is_service_product_price_from_dispatch'] ==1){
                        $product->is_onDemand_on = isset($onDemandRes['is_onDemand_enable']) ?  $onDemandRes['is_onDemand_enable'] : 0;
                    }
                    $agent_dispatcher_on_demand_tags = isset($onDemandRes['tags']) ?  $onDemandRes['tags'] : '';// $this->getDispatcherOnDemandTags($vendor_id);
                    break;
                case 12:
                    $vendor_id = $product->vendor_id;
                    $agent_dispatcher_on_demand_tags = $this->getDispatcherAppointmentTags($vendor_id);
                    break;
            }
        }

        $pro_tags = Tag::with('primary')->whereHas('primary')->get();
        $product_faqs = ProductFaq::with('primary')->where('product_id',$product->id)->get();


        $set_product_tags = ProductTag::where('product_id',$product->id)->pluck('tag_id')->toArray();

        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;

        $nomenclature = Nomenclature::where('label','Product Order Form')->first();
        $nomenclatureProductOrderForm = "Product Order Form";
        if(!empty($nomenclature)){
            $nomenclatureTranslation = NomenclatureTranslation::where(['nomenclature_id'=>$nomenclature->id,'language_id'=>$langId])->first();
            if($nomenclatureTranslation){
                $nomenclatureProductOrderForm = $nomenclatureTranslation->name ?? null;
            }
        }

        $allRoles = Role::where('status',1);
        $roles = $allRoles->where('is_enable_pricing',1)->get();
        $allRoles = $allRoles->get();


        $selectedRoles = [];
        if($getAdditionalPreference['is_free_delivery_by_roles'] == 1){
            $querySelectedRoles = ProductDeliveryFeeByRole::where('product_id', $id)->where('is_free_delivery', 1)->get();
            foreach($querySelectedRoles as $querySelectedRole){
                $selectedRoles[] = $querySelectedRole->role_id;
            }
        }

        $delivery_slots = [];
        if(checkTableExists('delivery_slots')){
            $delivery_slots = DeliverySlot::where(['status' => 0, 'parent_id' => 0])->get();
        }

        $pincodes = [];
        if(checkTableExists('pincodes')){
            $pincodes = Pincode::where('is_disabled', 0)->get();
        }
        //mohit sir branch code added by sohail
        $processorProduct = ProcessorProduct::where('product_id', $product->id)->first();
        $margProduct = MargProduct::where('product_id', $product->id)->first();
        if(@$margProduct && isset($margProduct))
        {
            $margProduct = $margProduct->toArray()??[];
        }
        $rentalProtection = RentalProtection::get();
        $bookingOption = BookingOption::get();
        $productBookingOption = $product->bookingOptions()->pluck('booking_option_id')->toArray();
        $productRentalProtection = $product->rentalProtections()->where('type_id', 2)->pluck('rental_proctection_id')->toArray();
        $inlcudedProductRentalProtection = $product->rentalProtections()->where('type_id', 1)->pluck('rental_proctection_id')->toArray();
        if($getAdditionalPreference['product_measurment'] == 1){
            foreach ($productMeasurements as $measurement) {
                $productMeasurementData[$measurement->key_id][$measurement->product_variant_id] = $measurement->key_value;
            }
        }
        return view('backend/product/edit', ['bundle_product_ids'=>$bundle_product_ids,'measurements'=>$measurements,'productMeasurementData'=>$productMeasurementData,'delivery_slots'=> $delivery_slots, 'product_faqs' => $product_faqs ,'set_product_tags' => $set_product_tags, 'nomenclatureProductOrderForm'=>$nomenclatureProductOrderForm, 'pro_tags' => $pro_tags,'agent_dispatcher_on_demand_tags' => $agent_dispatcher_on_demand_tags,'agent_dispatcher_tags' => $agent_dispatcher_tags,'processorProduct' => $processorProduct,'typeArray' => $type, 'addons' => $addons, 'productVariants' => $productVariants, 'languages' => $clientLanguages, 'taxCate' => $taxCate, 'countries' => $countries, 'product' => $product, 'addOn_ids' => $addOn_ids, 'existOptions' => $existOptions, 'brands' => $brands, 'otherProducts' => $otherProducts, 'related_ids' => $related_ids, 'upSell_ids' => $upSell_ids, 'crossSell_ids' => $crossSell_ids, 'celebrities' => $celebrities, 'configData' => $configData, 'celeb_ids' => $celeb_ids ,'roles' => $roles, 'getAdditionalPreference' => $getAdditionalPreference, 'allRoles' => $allRoles, 'selectedRoles' => $selectedRoles, 'tollPassOrigin' => $tollPassOrigin, 'travelMode' => $travelMode, 'vehicleEmissionType' => $vehicleEmissionType, 'productAttributes' => $productAttributes, 'attribute_value' => $attribute_value, 'attribute_key_value' => $attribute_key_value, 'attribute_latitude' => $attribute_latitude, 'attribute_longitude' => $attribute_longitude,'margProduct' => $margProduct??[], 'productAttributes' => $productAttributes, 'rentalProtection' => $rentalProtection, 'bookingOption' => $bookingOption, 'productBookingOption' => $productBookingOption, 'productRentalProtection' => $productRentalProtection, 'inlcudedProductRentalProtection' => $inlcudedProductRentalProtection]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {
        DB::beginTransaction();
        try {
            //ProductVariant::where('product_id',$id)->update(['status'=>0]);
            $product = Product::where('id', $id)->firstOrFail();
            $rule = [
                'product_name' => 'required|string',
                'sku' => 'required|unique:products,sku,' . $product->id,
                'url_slug' => 'required',
                'minimum_order_count' => 'required|numeric|min:1',
                // 'batch_count' => 'required|numeric|min:1'
            ];
            $getAdditionalPreference = getAdditionalPreference(['is_price_by_role','product_measurment']);
            if ($product->has_variant) {
                if ($getAdditionalPreference['product_measurment'] == 1) {
                    $additionalRules = [
                        'product_id' => 'required',
                        'key_id' => 'required|array',
                        'key_id.*' => 'required|exists:measurements,id',
                        'key_value' => 'required|array',
                    ];

                    foreach ($request->input('key_value', []) as $variantId => $keys) {
                        foreach ($keys as $keyId => $values) {
                            foreach ($values as $index => $value) {
                                $additionalRules["key_value.$variantId.$keyId.$index"] = 'required';
                            }
                        }
                    }

                    $rule = array_merge($rule, $additionalRules);
                }
            }

            // else{
            //     if ($getAdditionalPreference['product_measurment'] == 1) {
            //         $additionalRules = [
            //             'product_id' => 'required',
            //             'key_id' => 'required|array',
            //             'key_id.*' => 'required|exists:measurements,id',
            //             'key_value' => 'required|array',
            //             'key_value.*' => 'required'
            //         ];
            //         $rule = array_merge($rule, $additionalRules);
            //     }
            // }
            $validation = Validator::make($request->all(), $rule);

            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation);
            }


            $check_url_slug = Product::where('id','!=',$id)->where('vendor_id',$request->vendor_id)->where('url_slug',$request->url_slug)->first();
            if(!is_null($check_url_slug))
            {
                return redirect()->back()->with('url_slug_error','The url slug has already been taken.');
            }
            $product_category = ProductCategory::where('product_id', $id)->where('category_id', $request->category_id)->first();
            if(!$product_category){
                $product_category = new ProductCategory();
                $product_category->product_id = $id;
                $product_category->category_id = $request->category_id;
                $product_category->save();
            }
            if ($product->is_live == 0) {
                $product->publish_at = ($request->is_live == 1) ? date('Y-m-d H:i:s') : '';
            }
            // foreach ($request->only('country_origin_id', 'weight', 'weight_unit', 'is_live', 'brand_id', 'length', 'breadth', 'height', 'packaging_weight', 'packaging_weight_unit', 'packaging_length', 'packaging_breadth', 'packaging_height') as $k => $val) {
            //     $product->{$k} = $val;
            // }
            foreach ($request->only('country_origin_id', 'weight', 'weight_unit', 'is_live', 'brand_id', 'length', 'breadth', 'height', 'packaging_weight', 'packaging_weight_unit', 'packaging_length', 'packaging_breadth', 'packaging_height') as $k => $val) {
                $product->{$k} = $val;
            }
            if( clientPrefrenceModuleStatus('p2p_check') || is_attribute_enabled() ) {
                if( !empty($request->attribute) ) {
                    if( checkTableExists('product_attributes') ) {
                        $insert_arr = [];
                        $insert_count = 0;
                        foreach($request->attribute as $key => $value) {
                            if( !empty($value) && !empty($value['option'] && is_array($value) )) {

                                if(!empty($value['type']) && $value['type'] == 1 ) { // dropdown
                                    $value_arr = @$value['value'];
                                    foreach( $value['option'] as $key1 => $val1 ) {
                                        if( @in_array($val1['option_id'], $value_arr) ) {

                                            $insert_arr[$insert_count]['product_id'] = $id;
                                            $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                            $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                            $insert_arr[$insert_count]['attribute_option_id'] = $val1['option_id'];
                                            $insert_arr[$insert_count]['key_value'] = $val1['option_id'];
                                            $insert_arr[$insert_count]['latitude'] = null;
                                            $insert_arr[$insert_count]['longitude'] = null;
                                            $insert_arr[$insert_count]['is_active'] = 1;
                                        }
                                        $insert_count++;
                                    }
                                }
                                else {
                                    foreach($value['option'] as $option_key => $option) {
                                        if(@$option['value']){
                                            $insert_arr[$insert_count]['product_id'] = $id;
                                            $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                            $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                            $insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
                                            $insert_arr[$insert_count]['key_value'] = $option['value'] ?? $option['option_title'];
                                            $insert_arr[$insert_count]['latitude'] = $option['latitude'] ?? null;
                                            $insert_arr[$insert_count]['longitude'] = $option['longitude'] ?? null;
                                            $insert_arr[$insert_count]['is_active'] = 1;

                                        }
                                        $insert_count++;
                                    }
                                }
                            }


                        }
                        if( !empty($insert_arr) ) {
                            ProductAttribute::where('product_id',$id)->delete();
                            ProductAttribute::insert($insert_arr);
                        }
                    }
                }

            }

            $product->sku = $request->sku;
            $product->cashback = $request->cashback;
            $product->markup_price = $request->markup_price;
            $product->url_slug = $request->url_slug;
            $product->tags        = $request->tags??null;
            $product->category_id = $request->category_id;
            $product->inquiry_only = ($request->has('inquiry_only') && $request->inquiry_only == 'on') ? 1 : 0;
            $product->tax_category_id = $request->tax_category;
            $product->is_new                    = ($request->has('is_new') && $request->is_new == 'on') ? 1 : 0;
            $product->is_featured               = ($request->has('is_featured') && $request->is_featured == 'on') ? 1 : 0;
            $product->is_physical               = ($request->has('is_physical') && $request->is_physical == 'on') ? 1 : 0;
            $product->pharmacy_check            = ($request->has('pharmacy_check') && $request->pharmacy_check == 'on') ? 1 : 0;
            $product->validate_pharmacy_check            = ($request->has('validate_prescription_check') && $request->validate_prescription_check == 'on') ? 1 : 0;
            $product->individual_delivery_fee   = ($request->has('individual_delivery_fee') && $request->individual_delivery_fee == 'on') ? 1 : 0;
            $product->returnable        = ($request->has('returnable') && $request->returnable == 'on') ? 1 : 0;
            $product->spotlight_deals        = ($request->has('spotlight_deals') && $request->spotlight_deals == 'on') ? 1 : 0;
            $product->replaceable        = ($request->has('replaceable') && $request->replaceable == 'on') ? 1 : 0;
            $product->has_inventory             = ($request->has('has_inventory') && $request->has_inventory == 'on') ? 1 : 0;
            $product->sell_when_out_of_stock    = ($request->has('sell_stock_out') && $request->sell_stock_out == 'on') ? 1 : 0;
            $product->requires_shipping         = ($request->has('require_ship') && $request->require_ship == 'on') ? 1 : 0;
            $product->Requires_last_mile        = ($request->has('last_mile') && $request->last_mile == 'on') ? 1 : 0;
            $product->need_price_from_dispatcher = ($request->has('need_price_from_dispatcher') && $request->need_price_from_dispatcher == 'on') ? 1 : 0;
            $product->age_restriction = ($request->has('age_restriction') && $request->age_restriction == 'on') ? 1 : 0;
            $product->mode_of_service        = $request->mode_of_service??null;
            $product->delay_order_hrs        = $request->delay_order_hrs??0;
            $product->delay_order_min        = $request->delay_order_min??0;
            $product->delay_order_hrs_for_dine_in = $request->delay_order_hrs_for_dine_in??0;
            $product->delay_order_min_for_dine_in = $request->delay_order_min_for_dine_in??0;
            $product->delay_order_hrs_for_takeway = $request->delay_order_hrs_for_takeway??0;
            $product->delay_order_min_for_takeway = $request->delay_order_min_for_takeway??0;
            $product->pickup_delay_order_hrs        = $request->pickup_delay_order_hrs??0;
            $product->pickup_delay_order_min        = $request->pickup_delay_order_min??0;
            $product->dropoff_delay_order_hrs        = $request->dropoff_delay_order_hrs??0;
            $product->dropoff_delay_order_min        = $request->dropoff_delay_order_min??0;
            $product->minimum_order_count        = $request->minimum_order_count??0;
            $product->batch_count        = $request->batch_count??1;
            $product->return_days        = $request->return_days??0;
            $product->calories        = $request->calories;

            // product pickup date by vendor vendor FramMeat priyal by sohail
            $product->product_pickup_date  = isset($request->product_pickup_date) ? $request->product_pickup_date : '';

            $product->is_product_instant_booking   = ($request->has('is_product_instant_booking') && $request->is_product_instant_booking == 'on') ? 1 : 0;

            $product->service_charges_tax = ($request->has('service_charges_tax') && $request->service_charges_tax == 'on') ? 1 : 0;
            $product->service_charges_tax_id=$request->service_charges_tax_id != 0 && $product->service_charges_tax !=0 ? $request->service_charges_tax_id:0;


            $product->delivery_charges_tax = ($request->has('delivery_charges_tax') && $request->delivery_charges_tax == 'on') ? 1 : 0;
            $product->delivery_charges_tax_id=$request->delivery_charges_tax_id != 0 && $product->delivery_charges_tax !=0 ? $request->delivery_charges_tax_id:0;

            $product->container_charges_tax = $request->container_charges_tax == 'on' ? 1 : 0;
            $product->container_charges_tax_id=$request->container_charges_tax_id != 0 && $product->container_charges_tax !=0 ? $request->container_charges_tax_id:0;

            $product->fixed_fee_tax = $request->fixed_fee_tax == 'on' ? 1 : 0;
            $product->fixed_fee_tax_id=$request->fixed_fee_tax_id != 0 && $product->fixed_fee_tax !=0 ? $request->fixed_fee_tax_id:0;

            if ($request->is_live == 0) {
                CartProduct::where(['product_id' => $id])->delete();
            }

            if (empty($product->publish_at)) {
                $product->publish_at = ($request->is_live == 1) ? date('Y-m-d H:i:s') : '';
            }
            $product->has_variant = ($request->has('variant_ids') && count($request->variant_ids) > 0) ? 1 : 0;
            if($product){
                if(isset($product->category) && in_array($product->category->categoryDetail->type_id,[8,9]))
                $product->sell_when_out_of_stock = 1;
            }
            $product->minimum_duration = str_pad($request->minimum_duration, 2, '0', STR_PAD_LEFT)??null;
            $product->additional_increments = str_pad($request->additional_increments, 2, '0', STR_PAD_LEFT)??null;
            $product->buffer_time_duration  = str_pad($request->buffer_time_duration, 2, '0', STR_PAD_LEFT)??null;
            $product->check_in_time         = str_pad($request->check_in_time, 2, '0', STR_PAD_LEFT)??null;
            $product->minimum_duration_min = str_pad($request->minimum_duration_min, 2, '0', STR_PAD_LEFT)??null;
            $product->additional_increments_min = str_pad($request->additional_increments_min, 2, '0', STR_PAD_LEFT)??null;
            $product->buffer_time_duration_min = str_pad($request->buffer_time_duration_min, 2, '0', STR_PAD_LEFT)??null;
            $product->is_fix_check_in_time = ($request->has('is_fix_check_in_time') && $request->is_fix_check_in_time == 'on') ? 1 : 0;

            $product->available_for_pooling = ($request->has('available_for_pooling') && $request->available_for_pooling == 'on') ? 1 : 0;
            $product->seats = ($request->has('seats')) ? $request->seats : 0;
            $product->seats_for_booking = ($request->has('seats_for_booking')) ? $request->seats_for_booking : 0;

            $product->is_toll_tax = ($request->has('is_toll_tax') && $request->is_toll_tax == 'on') ? 1 : 0;
            $product->travel_mode_id = ($request->has('travel_mode')) ? $request->travel_mode : 0;
            $product->toll_pass_id = ($request->has('toll_passes')) ? $request->toll_passes : 0;
            $product->emission_type_id = ($request->has('emission_type')) ? $request->emission_type : 0;

            $product->security_amount = ($request->has('security_amount')) ? $request->security_amount : null;
            $product->is_recurring_booking        = $request->is_recurring_booking == 'on' ? 1 : 0;

            $product->same_day_delivery = ($request->has('same_day_delivery') && $request->same_day_delivery == 'on') ? 1 : 0;

            $product->next_day_delivery = ($request->has('next_day_delivery') && $request->next_day_delivery == 'on') ? 1 : 0;
            $product->hyper_local_delivery = ($request->has('hyper_local_delivery') && $request->hyper_local_delivery == 'on') ? 1 : 0;

            $product->is_slot_from_dispatch        = ($request->has('is_slot_from_dispatch') && $request->is_slot_from_dispatch == 'on') ? 1 : 0;
            $product->is_show_dispatcher_agent     = ($request->has('is_show_dispatcher_agent') && $request->is_show_dispatcher_agent == 'on') ? 1 : 0;

            $product->pickup_time = ($request->has('pickup_time')) ? $request->pickup_time : null;
            $product->drop_time = ($request->has('drop_time')) ? $request->drop_time : null;
            $product->extra_time = ($request->has('extra_time')) ? $request->extra_time : null;
            $product->captain_name = $request->captain_name ?? '';
            $product->captain_description = $request->captain_description ?? '';
            if($request->hasFile('captain_profile')){
                $filePath = 'profile/' . \Str::random(40);
                $file = $request->file('captain_profile');
                $orignal_name = $request->file('captain_profile')->getClientOriginalName();
                $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                $url = Storage::disk('s3')->url($file_name);
                $product->captain_profile = $url;
            }
            $product->save();
            if($request->has('slot_ids') && $request->slot_ids != ''){
                $product->syncProductDeliverySlot()->sync($request->slot_ids);
            }
            if ($product->id > 0) {
                $trans = ProductTranslation::where('product_id', $product->id)->where('language_id', $request->language_id)->first();
                if (!$trans) {
                    $trans = new ProductTranslation();
                    $trans->product_id = $product->id;
                    $trans->language_id = $request->language_id;
                }
                $trans->title               = $request->product_name;
                $trans->body_html           = $request->body_html;
                $trans->meta_title          = $request->meta_title;
                $trans->meta_keyword        = $request->meta_keyword;
                $trans->meta_description    = $request->meta_description;
                $trans->save();
                $varOptArray = $prodVarSet = $updateImage = array();
                $i = 0;
                $productImageSave = array();
                if ($request->has('fileIds')) {
                    foreach ($request->fileIds as $key => $value) {
                        $productImageSave[] = [
                            'product_id' => $product->id,
                            'media_id' => $value,
                            'is_default' => 1
                        ];
                    }
                }
                ProductImage::insert($productImageSave);
                $cat = $addonsArray = $upArray = $crossArray = $relatedArray = $tagSetArray = array();
                $delete = ProductAddon::where('product_id', $product->id)->delete();
                $delete = BundleupProduct::where('product_id', $product->id)->delete();
                $delete = ProductUpSell::where('product_id', $product->id)->delete();
                $delete = ProductCrossSell::where('product_id', $product->id)->delete();
                $delete = ProductCelebrity::where('product_id', $product->id)->delete();
                $delete = ProductTag::where('product_id', $product->id)->delete();
                $delete=ProductRelated::where('product_id',$product->id)->delete();
                if($request->has('bundleup')){
                    foreach($request->bundleup as $bundleup){
                        BundleupProduct::updateOrCreate([
                            'product_id' => $id,
                            'related_product_id' => $bundleup
                        ]);
                    }
                }
                if ($request->has('addon_sets') && count($request->addon_sets) > 0) {
                    foreach ($request->addon_sets as $key => $value) {
                        $addonsArray[] = [
                            'product_id' => $product->id,
                            'addon_id' => $value
                        ];
                    }
                    ProductAddon::insert($addonsArray);
                }

                if ($request->has('tag_sets') && count($request->tag_sets) > 0) {
                    foreach ($request->tag_sets as $key => $value) {
                        $tagSetArray[] = [
                            'product_id' => $product->id,
                            'tag_id' => $value
                        ];
                    }
                    ProductTag::insert($tagSetArray);
                }

                if ($request->has('celebrities') && count($request->celebrities) > 0) {
                    foreach ($request->celebrities as $key => $value) {
                        $celebArray[] = [
                            'celebrity_id' => $value,
                            'product_id' => $product->id
                        ];
                    }
                    ProductCelebrity::insert($celebArray);
                }

                if ($request->has('up_cell') && count($request->up_cell) > 0) {
                    foreach ($request->up_cell as $key => $value) {
                        $upArray[] = [
                            'product_id' => $product->id,
                            'upsell_product_id' => $value
                        ];
                    }
                    ProductUpSell::insert($upArray);
                }

                if ($request->has('cross_cell') && count($request->cross_cell) > 0) {
                    foreach ($request->cross_cell as $key => $value) {
                        $crossArray[] = [
                            'product_id' => $product->id,
                            'cross_product_id' => $value
                        ];
                    }
                    ProductCrossSell::insert($crossArray);
                }

                if ( $request->has('corporate_user_price') && $request->has('minimum_order_count_corporate_user')) {
                    $corporate_user_price   = $request->corporate_user_price;
                    $minimum_order_count    = $request->minimum_order_count_corporate_user;

                    $product_variant_by_roles = [];
                    foreach($corporate_user_price as $key => $val) {
                        if($val != ''){
                            $product_variant_by_roles[$key]['product_id'] = $product->id;
                            $product_variant_by_roles[$key]['role_id'] = $request->role_id['corporate_user'];
                            $product_variant_by_roles[$key]['amount'] = $val;
                            $product_variant_by_roles[$key]['quantity'] = $minimum_order_count[$key];
                        }
                    }

                    $delete = ProductVariantByRole::where('product_id', $product->id)->where('role_id', 3)->delete();
                    foreach($product_variant_by_roles as $key => $data){
                        ProductVariantByRole::create($data);
                    }

                }

                if ($request->has('releted_product') && count($request->releted_product) > 0) {
                    foreach ($request->releted_product as $key => $value) {
                        $relatedArray[] = [
                            'product_id' => $product->id,
                            'related_product_id' => $value
                        ];
                    }
                    ProductRelated::insert($relatedArray);
                }
                $existv = array();

                if ($request->has('variant_ids')) {
                    // pr($request->variant_minimum_duration);
                    foreach ($request->variant_ids as $key => $value) {
                        $variantData = ProductVariant::where('id', $value)->first();
                        $existv[] = $value;

                        if ($variantData) {
                            $per_min = 0;
                            if (isset($request->variant_incremental_price[$key])) {
                                if ($request->variant_incremental_price[$key] !='' && $request->variant_incremental_price[$key] > 0) {
                                    $per_min = (($request->additional_increments*60)+($request->additional_increments_min))/($request->variant_incremental_price[$key]);
                                }
                            }

                            // pr($request->all());
                            $variantData->title             = @$request->variant_titles[$key];
                            $variantData->price             = @$request->variant_price[$key];
                            $variantData->incremental_price             = @$request->variant_incremental_price[$key]??0;
                            $variantData->minimum_duration = @$request->variant_minimum_duration[$key] ?? 0;
                            $variantData->incremental_price_per_min             = @$per_min;
                            $variantData->markup_price      = @$request->markup_price[$key];
                            $variantData->compare_at_price  = @$request->variant_compare_price[$key];
                            $variantData->container_charges  = @$request->container_charges[$key]??"";
                            $variantData->cost_price        = @$request->variant_cost_price[$key];
                            $variantData->quantity          = @$request->variant_quantity[$key];
                            $variantData->tax_category_id   = @$request->tax_category;
                            $variantData->status   = 1;
                            $variantData->save();
                            //pr($variantData->toArray());
                        }
                        $delOpt = ProductVariant::whereNotIN('id', $existv)->where('product_id', $product->id)->whereNull('title')->delete();
                    }
                } else {
                    $variantData = ProductVariant::where('product_id', $product->id)->first();
                    if (!$variantData) {
                        $variantData = new ProductVariant();
                        $variantData->product_id    = $product->id;
                        $variantData->sku           = $product->sku;
                        $variantData->title         = $product->sku;
                        $variantData->barcode       = $this->generateBarcodeNumber();
                    }
                    $variantData->price             = $request->price;
                    $variantData->markup_price      = $request->markup_price;
                    $variantData->compare_at_price  = $request->compare_at_price;
                    $variantData->container_charges  = $request->container_charges;
                    $variantData->cost_price        = $request->cost_price;
                    $variantData->quantity          = $request->quantity;
                    $variantData->tax_category_id   = $request->tax_category;
                    $variantData->minimum_duration = @$request->minimum_duration ?? 0;
                    $variantData->save();

                    // Save Product Variant By Roles without product_variant_id and amount
                    // Product Variant By Roles (START)

                    if( $request->has('role_id') && !$request->has('corporate_user_price') && !$request->has('minimum_order_count_corporate_user')){
                        foreach ($request->role_id as $key => $value) {
                            $productVariantByRole = ProductVariantByRole::where('product_id', $product->id)->where('role_id',$value)->first();
                            if (!$productVariantByRole) {
                                $productVariantByRole          = new ProductVariantByRole();
                            }
                            $productVariantByRole->product_id         = $product->id;
                            $productVariantByRole->role_id            = $value;
                            $productVariantByRole->amount             = $request->role_price[$value];
                            $productVariantByRole->product_variant_id = $variantData->id;
                            $productVariantByRole->save();
                        }
                    }
                    // Product Variant By Roles (END)
                }

                //mohit sir branch code added by sohail
                $processorProductDetail = [
                    'product_id' => $product->id,
                    'is_processor_enable' => ($request->is_processor_enable == 1)? 1 : 0,
                    'name' => !empty($request->processor_title)? $request->processor_title : '',
                    'address' => !empty($request->processor_address)? $request->processor_address : '',
                    'latitude' => !empty($request->processor_latitude)? $request->processor_latitude : '',
                    'longitude' => !empty($request->processor_longitude)? $request->processor_longitude : '',
                    'date' => !empty($request->processor_date)? $request->processor_date : '',
                ];
                ProcessorProduct::updateOrCreate(['product_id' => $product->id], $processorProductDetail);
                //till here

                // min order count
                if(isset($getAdditionalPreference['is_price_by_role']) && $getAdditionalPreference['is_price_by_role'] == 1){
                    $minimum_order_count_arr = $request->minimum_order_count_arr;

                    if($minimum_order_count_arr){
                        foreach($minimum_order_count_arr as $key => $minimum_order_count){
                            $where = ['product_id' => $id, 'role_id' => $key];
                            $create = ['product_id' => $id, 'role_id' => $key, 'minimum_order_count' => $minimum_order_count ];
                            ProductByRole::updateOrCreate($where, $create);
                        }
                    }
                }

            }

            // update product delivery fees
            if($request->has('free_delivery_roles')){
                $allRoles = Role::where('status', 1)->get();
                $free_del_arr = [];
                // list size will be small. so it's okay to use loop here
                foreach($allRoles as $allRole){
                    $free_del_val = [
                        'product_id'=> $product->id,
                        'role_id' => $allRole->id,
                        'is_free_delivery' => (in_array( $allRole->id,  $request->free_delivery_roles ) ? 1 : 0)
                    ];
                    ProductDeliveryFeeByRole::updateOrCreate(['product_id' =>$product->id, 'role_id' => $allRole->id ], $free_del_val);
                }

            }


            if(!empty($request->rental_protection)){
                foreach($request->rental_protection as $rentalId){
                    $rentalProtection = [
                        'product_id' => $product->id,
                        'rental_proctection_id' => $rentalId,
                        'type_id' => 2
                    ];
                    ProductRentalProtection::updateOrCreate($rentalProtection,$rentalProtection);
                }
            }

            if(!empty($request->included_rental_protection)){
                foreach($request->included_rental_protection as $rentalId){
                    $includedRentalProtection = [
                        'product_id' => $product->id,
                        'rental_proctection_id' => $rentalId,
                        'type_id' => 1
                    ];
                    ProductRentalProtection::updateOrCreate($includedRentalProtection,$includedRentalProtection);
                }
            }

            if(!empty($request->booking_option)){
                foreach($request->booking_option as $optionId){
                    $bookingOption = [
                        'product_id' => $product->id,
                        'booking_option_id' => $optionId
                    ];
                    ProductBookingOption::updateOrCreate($bookingOption,$bookingOption);
                }
            }

            DB::commit();
            $this->createOrUpdateProductInSquarePos($id);
            if ($getAdditionalPreference['product_measurment'] == 1) {
                $measuremenKeyExists=Measurements::where('vendor_id',$product->vendor_id)->where('category_id',$product->category_id)->exists();

                if ($measuremenKeyExists) {
                    $productId = $request->input('product_id');
                    $variantIds = $request->input('variant_ids');
                    $keyIds = $request->input('key_id');
                    $keyValues = $request->input('key_value');

                    if ($request->has('variant_ids')) {
                        foreach ($variantIds as $variantId) {
                            foreach ($keyIds as $keyId) {
                                if (isset($keyValues[$keyId][$variantId])) {
                                    $keyValue = $keyValues[$keyId][$variantId];

                                    ProductMeasurement::where([
                                        'product_id' => $productId,
                                        'product_variant_id' => $variantId,
                                        'key_id' => $keyId
                                    ])->delete();

                                    ProductMeasurement::create([
                                        'product_id' => $productId,
                                        'product_variant_id' => $variantId,
                                        'key_id' => $keyId,
                                        'key_value' => $keyValue[0]
                                    ]);
                                }
                            }
                        }
                    } else {
                        foreach ($keyIds as $keyIndex => $keyId) {
                            ProductMeasurement::where([
                                'product_id' => $productId,
                                'key_id' => $keyId
                            ])->delete();

                            ProductMeasurement::create([
                                'product_id' => $productId,
                                'key_id' => $keyId,
                                'key_value' => $keyValues[$keyIndex]
                            ]);
                        }
                    }
                } else {
                    return redirect()->back()->withInput()->withErrors(['error' => 'Please Add Measurement Keys for this Category in Category Add-Ons']);
                }
            }

                $toaster = $this->successToaster(__('Success'),__('Product updated successfully') );

            // return redirect('client/vendor/catalogs/' . $product->vendor_id)->with('toaster', $toaster);
            return redirect()->back()->with('toaster', $toaster);
        } catch (\Exception $e) {
            DB::rollback();
            $toaster = $this->errorToaster(__('ERROR'),$e->getMessage() );
            return redirect()->back()->with('toaster', $toaster);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id)
    {
        try{

            DB::beginTransaction();
            $product = Product::find($id);
            if(!empty($product) && isset($product->square_item_id) && !empty($product->square_item_id)){
                $this->deleteBatchInSquarePos([$product->square_item_id]);
            }

            $productde = Product::productDelete($id);
            // $product = Product::find($id);

            // $dynamic = time();

            // Product::where('id', $id)->update(['sku' => $product->sku.$dynamic ,'url_slug' => $product->url_slug.$dynamic]);

            // $tot_var  = ProductVariant::where('product_id', $id)->get();
            // foreach($tot_var as $varr)
            // {
            //     $dynamic = time().substr(md5(mt_rand()), 0, 7);
            //     ProductVariant::where('id', $varr->id)->update(['sku' => $product->sku.$dynamic]);
            // }

            // Product::where('id', $id)->delete();

            // CartProduct::where('product_id', $id)->delete();
            // UserWishlist::where('product_id', $id)->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Product deleted successfully!');
        }
        catch(\Exception $ex){
            DB::rollback();
            redirect()->back()->with(__('Errors'), $ex->getMessage());
        }
    }

    /**      Make variant rows          */
    public function makeVariantRows(Request $request)
    {

        // dd($request->all());
        //return $request->all();
        $multiArray = array();
        $variantNames = array();
        $product = Product::where('id', $request->pid)->with(['variant'])->firstOrFail();
        $msgRes = 'Please check variants to create variant set.';
        if (!$request->has('optionIds') || !$request->has('variantIds')) {
            return response()->json(array('success' => 'false', 'msg' => $msgRes));
        }
        foreach ($request->optionIds as $key => $value) {
            $name = explode(';', $request->variantIds[$key]);
            if (!in_array($name[1], $variantNames)) {
                $variantNames[] = $name[1];
            }
            $multiArray[$request->variantIds[$key]][] = $value;
        }

        $combination = $this->array_combinations($multiArray);
        $new_combination = array();
        $edit = 0;

        if ($request->has('existing') && !empty($request->existing)) {
            $existingComb = $request->existing;
            $edit = 1;
            foreach ($combination as $key => $value) {
                $comb = $arrayVal = '';
                foreach ($value as $k => $v) {
                    $arrayVal = explode(';', $v);
                    $comb .= $arrayVal[0] . '*';
                }

                $comb = rtrim($comb, '*');

                if (!in_array($comb, $existingComb)) {
                    $new_combination[$key] = $value;
                }
            }
            $combination = $new_combination;
            $msgRes = 'No new variant set found.';
        }

        if (count($combination) < 1) {
            return response()->json(array('success' => 'false', 'msg' => $msgRes));
        }

        if(!empty($product->variant)){
            if(count($product->variant) == 1){
                $ordercount = OrderProduct::where('product_id', $product->id)->where('variant_id', $product->variant[0]->id)->count();
                if($ordercount == 0){
                    ProductVariant::where('product_id', $product->id)->whereNull('price')->where('title', $request->sku)->delete();
                }
            }
        }

        $makeHtml = $this->combinationHtml($combination, $multiArray, $variantNames, $product->id, $request->sku, $edit);
        return response()->json(array('success' => true, 'html' => $makeHtml));
    }

    function combinationHtml($combination, $multiArray, $variantNames, $product_id, $sku = '',  $edit = 0)
    {
        $arrVal = array();
        foreach ($multiArray as $key => $value) {
            $varStr = $optStr = array();
            $vv = explode(';', $key);

            foreach ($value as $k => $v) {
                $ov = explode(';', $v);
                $optStr[] = $ov[0];
            }

            $arrVal[$vv[0]] = $optStr;
        }
        $name1 = '';

        $all_variant_sets = array();

        $html = '';
        if ($edit == 1) {
            $html .= '<h5 >New Variants Set</h5>';
        }
        $html .= '<table class="table table-centered table-nowrap table-striped">
            <thead>
                <th>Image</th>
                <th>Name</th>
                <th>'.getNomenclatureName('Variant').'</th>
                <th>Price</th>
                <th>Compare at price</th>
                <th>Cost Price</th>
                <th>Quantity</th>
                <th> </th>
                </thead>';
        $inc = 0;
        foreach ($combination as $key => $value) {
            $names = array();
            $ids = array();
            foreach ($value as $k => $v) {
                $variant = explode(';', $v);
                $ids[] = $variant[0];
                $names[] = $variant[1];
            }
            $proSku = $sku . '-' . implode('*', $ids);
            $proVariant = ProductVariant::where('sku', $proSku)->first();
            if (!$proVariant) {
                $proVariant = new ProductVariant();
                $proVariant->sku = $proSku;
                $proVariant->title = $sku . '-' . implode('-', $names);
                $proVariant->product_id = $product_id;
                $proVariant->barcode = $this->generateBarcodeNumber();
                $proVariant->save();

                foreach ($ids as $id1) {
                    $all_variant_sets[$inc] = [
                        'product_id' => $product_id,
                        'product_variant_id' => $proVariant->id,
                        'variant_option_id' => $id1,
                    ];

                    foreach ($arrVal as $key => $value) {

                        if (in_array($id1, $value)) {
                            $all_variant_sets[$inc]['variant_type_id'] = $key;
                        }
                    }
                    $inc++;
                }
            }

            $html .= '<tr>';
            $html .= '<td><div class="image-upload">
                      <label class="file-input" for="file-input_' . $proVariant->id . '"><img src="' . asset("assets/images/default_image.png") . '" width="30" height="30" class="uploadImages" for="' . $proVariant->id . '"/> </label>
                    </div>
                    <div class="imageCountDiv' . $proVariant->id . '"></div>
                    </td>';
            $html .= '<td> <input type="hidden" name="variant_ids[]" value="' . $proVariant->id . '">';

            $html .= '<input type="text" name="variant_titles[]" value="' . $proVariant->title . '"></td>';
            $html .= '<td>' . implode(", ", $names) . '</td>';
            $html .= '<td> <input type="text" style="width: 70px;" name="variant_price[]" value="0" onkeypress="return isNumberKey(event)"> </td>';
            $html .= '<td> <input type="text" style="width: 100px;" name="variant_compare_price[]" value="0" onkeypress="return isNumberKey(event)"> </td>';
            $html .= '<td> <input type="text" style="width: 70px;" name="variant_cost_price[]" value="0" onkeypress="return isNumberKey(event)"> </td>';
            $html .= '<td> <input type="text" style="width: 70px;" name="variant_quantity[]" value="0" onkeypress="return isNumberKey(event)"> </td><td>
            <a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a></td>';

            $html .= '</tr>';
        }
        ProductVariantSet::insert($all_variant_sets);
        $html .= '</table>';
        return $html;
    }

    private function array_combinations($arrays)
    {
        $result = array();
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i++) {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn - 1); $j >= 0; $j--) {
                if (next($arrays[$j]))
                    break;
                elseif (isset($arrays[$j]))
                    reset($arrays[$j]);
            }
        }
        return $result;
    }

    private function generateBarcodeNumber()
    {
        $random_string = substr(md5(microtime()), 0, 14);
        while (ProductVariant::where('barcode', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 14);
        }
        return $random_string;
    }

    public function deleteVariant(Request $request)
    {
        $product_variant = ProductVariant::where('id', $request->product_variant_id)->where('product_id', $request->product_id)->first();
        $product_variant->status = 0;
        if(isset($product_variant->square_variant_id) && !empty($product_variant->square_variant_id)){
            $this->deleteBatchInSquarePos([$product_variant->square_variant_id]);
        }
        $product_variant->save();
        if ($request->is_product_delete > 0) {
            $product = Product::find($request->product_id);
            if(!empty($product) && isset($product->square_item_id) && !empty($product->square_item_id)){
                $this->deleteBatchInSquarePos([$product->square_item_id]);
            }
            Product::where('id', $request->product_id)->delete();
        }
        return response()->json(array('success' => true, 'msg' => 'Product variant deleted successfully.'));
    }

    public function translation(Request $request){
        $data = ProductTranslation::where('product_id', $request->prod_id)->where('language_id', $request->lang_id)->first();
        $response = array('title' => '', 'body_html' => '', 'meta_title' => '', 'meta_keyword' => '', 'meta_description' => '');
        if ($data) {
            $response['title']              = $data->title;
            $response['body_html']          = $data->body_html;
            $response['meta_title']         = $data->meta_title;
            $response['meta_keyword']       = $data->meta_keyword;
            $response['meta_description']   = $data->meta_description;
        }
        return response()->json(array('success' => true, 'data' => $response));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function images(Request $request){
        $resp = '';
        $product = Product::findOrFail($request->prodId);
        if ($request->has('file')) {
            $imageId = '';
            $files = $request->file('file');
            if (is_array($files)) {
                foreach ($files as $file) {
                    $img = new VendorMedia();
                    $img->media_type = 1;
                    $img->vendor_id = $product->vendor_id;
                    $img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
                    $img->save();
                    $path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
                    if ($img->id > 0) {
                        $imageId = $img->id;
                        $image = new ProductImage();
                        $image->product_id = $product->id;
                        $image->is_default = 1;
                        $image->media_id = $img->id;
                        $image->save();
                        if ($request->has('variantId')) {
                            $resp .= '<div class="col-md-3 col-sm-4 col-12 mb-3">
                                        <div class="product-img-box">
                                            <div class="form-group checkbox checkbox-success">
                                                <input type="checkbox" id="image' . $image->id . '" class="imgChecks" imgId="' . $image->id . '" checked variant_id="' . $request->variantId . '">
                                                <label for="image' . $image->id . '">
                                                <img src="' . $path1 . '" alt="">
                                                </label>
                                            </div>
                                        </div>
                                    </div>';
                        }
                    }
                }
                return response()->json(['htmlData' => $resp]);
            } else {
                $img = new VendorMedia();
                $img->media_type = 1;
                $img->vendor_id = $product->vendor_id;
                $img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
                $img->save();
                $imageId = $img->id;
            }
            if($request->has('retunId') && $request->retunId  == 1){
                return $imageId;
            }
            return response()->json(['imageId' => $imageId]);
        } else {
            return response()->json(['error' => 'No file']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cms  $cms
     * @return \Illuminate\Http\Response
     */
    public function getImages(Request $request){
        $product = Product::where('id', $request->prod_id)->firstOrFail();
        $variId = ($request->has('variant_id') && $request->variant_id > 0) ? $request->variant_id : 0;
        $images = ProductImage::with('image')->where('product_images.product_id', $product->id)->get();
        $variantImages = array();
        if ($variId > 0) {
            $varImages = ProductVariantImage::where('product_variant_id', $variId)->get();
            if ($varImages) {
                foreach ($varImages as $key => $value) {
                    $variantImages[] = $value->product_image_id;
                }
            }
        }
        $returnHTML = view('backend.product.imageUpload')->with(['images' => $images, 'variant_id' => $variId, 'productId' => $product->id, 'variantImages' => $variantImages])->render();
        return response()->json(array('success' => true, 'htmlData' => $returnHTML));
    }

    public function updateVariantImage(Request $request){
        $product = Product::where('id', $request->prod_id)->firstOrFail();
        $saveImage = array();
        if ($request->has('image_id')) {
            $deleteVarImg = ProductVariantImage::where('product_variant_id', $request->variant_id)->delete();
            foreach ($request->image_id as $key => $value) {

                $saveImage[] = [
                    'product_variant_id' => $request->variant_id,
                    'product_image_id' => $value
                ];
            }
            ProductVariantImage::insert($saveImage);
            return response()->json(array('success' => true, 'msg' => 'Image added successfully!'));
        }
        return response()->json(array('success' => 'false', 'msg' => 'Something went wrong!'));
    }

    public function deleteImage(Request $request, $domain = '', $pid = 0, $imgId = 0){
        $product = Product::findOrfail($pid);
//      /   $img = VendorMedia::findOrfail($imgId);
        $prodImage =  ProductImage::findOrfail($imgId);
       // $img->delete();
        if(!empty($prodImage)){
            if(isset( $prodImage->image))
                $prodImage->image->delete();
            $prodImage->delete();
        }
        return redirect()->back()->with('success', 'Product image deleted successfully!');
    }

    /**
     * Import Excel file for products
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importCsv(Request $request){
        $validated = $request->validate([
            'product_excel' => 'required|mimes:csv,txt'
        ]);
        $vendor_id = $request->vendor_id;
        $fileModel = new CsvProductImport;
        if($request->file('product_excel')) {
            $fileName = time().'_'.$request->file('product_excel')->getClientOriginalName();
            $filePath = $request->file('product_excel')->storeAs('csv_products', $fileName, 'public');
            $fileModel->vendor_id = $request->vendor_id;
            $fileModel->name = $fileName;
            $fileModel->path = 'storage/' . $filePath;
            $fileModel->status = 1;
            $fileModel->save();
            if (File::exists($fileModel->storage_url)) {
                $csv = file($fileModel->storage_url);
                $chunks = array_chunk($csv, 2000);
                $header = [];
                $flag = 0;
                foreach ($chunks as $key => $chunk) {
                    $data = array_map('str_getcsv', $chunk);
                    if ($key == 0) {
                        $header = $data[0];
                        unset($data[0]);
                    }
                    $flag = ProductImportCsvJob::dispatch($fileModel->vendor_id, $fileModel->id, $data, $header)->onQueue('csv_import');
                }
                if ($flag) {
                    unlink($fileModel->storage_url);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Product import file uploaded successfully!'
            ]);
        }
    }

    public function importCsvOld(Request $request){
        $validated = $request->validate([
            'product_excel' => 'required|mimes:csv,txt'
        ]);
        $vendor_id = $request->vendor_id;
        $fileModel = new CsvProductImport;
        if($request->file('product_excel')) {
            $fileName = time().'_'.$request->file('product_excel')->getClientOriginalName();
            $filePath = $request->file('product_excel')->storeAs('csv_products', $fileName, 'public');
            $fileModel->vendor_id = $request->vendor_id;
            $fileModel->name = $fileName;
            $fileModel->path = '/storage/' . $filePath;
            $fileModel->status = 1;
            $fileModel->save();
            $data = Excel::import(new ProductsImport($vendor_id, $fileModel->id), $request->file('product_excel'));
            return response()->json([
                'status' => 'success',
                'message' => 'Product image deleted successfully!'
            ]);
        }
    }

    public function importCsvQrcode(Request $request){

        $vendor_id = $request->vendor_id??null;
        $fileModel = new CsvQrcodeImport;
        if($request->file('qrcode_excel')) {
            $fileName = time().'_'.$request->file('qrcode_excel')->getClientOriginalName();
            $filePath = $request->file('qrcode_excel')->storeAs('csv_qrcodes', $fileName, 'public');
            $fileModel->name = $fileName;
            $fileModel->vendor_id = $request->vendor_id??null;
            $fileModel->path = '/storage/' . $filePath;
            $fileModel->status = 1;
            $fileModel->save();

            $data = Excel::import(new QrcodesImport($vendor_id,$fileModel->id), $request->file('qrcode_excel'));

            return response()->json([
                'status' => 'success',
                'message' => 'Qrcode code import successfully!'
            ]);
        }
    }


      # get dispatcher tags from dispatcher panel
      public function getDispatcherTags($vendor_id){
        try {
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $unique = Auth::user()->code;
                $email =  $unique.$vendor_id."_royodispatch@dispatch.com";
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                                                    'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                                                    'content-type' => 'application/json']
                                                        ]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->get($url.'/api/get-agent-tags?email_set='.$email);
                $response = json_decode($res->getBody(), true);
                if($response && $response['message'] == 'success'){
                    return $response['tags'];
                }
            }
        }catch(\Exception $e){
        }
    }

    public function getDeliveryDispatcherTags($vendor_id)
    {
        try {
            $dispatch_domain = $this->checkIfDeliveryOn();
                if ($dispatch_domain && $dispatch_domain != false) {

                    $unique = Auth::user()->code;
                    $email =  $unique.$vendor_id."_royodispatch@dispatch.com";

                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->delivery_service_key,
                                                        'shortcode' => $dispatch_domain->delivery_service_key_code,
                                                        'content-type' => 'application/json']
                                                            ]);
                            $url = $dispatch_domain->delivery_service_key_url;
                            $res = $client->get($url.'/api/get-agent-tags?email_set='.$email);
                            $response = json_decode($res->getBody(), true);
                            if($response && $response['message'] == 'success'){
                                return $response['tags'];
                            }

                }
            }
            catch(\Exception $e){

            }
    }
    # check if last mile delivery on
    public function checkIfPickupDeliveryOn(){
        $preference = ClientPreference::first();
        if($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
            return $preference;
        else
            return false;
    }


      # get dispatcher on demand tags from dispatcher panel
      public function getDispatcherOnDemandTags($vendor_id){
        $retResponse = [
            'is_onDemand_enable' =>0,
            'tags' =>'',
        ];
        try {
            $dispatch_domain = $this->checkIfOnDemandOn();

                if ($dispatch_domain && $dispatch_domain != false) {
                    $retResponse['is_onDemand_enable'] = 1;
                    $unique = Auth::user()->code;
                    $email =  $unique.$vendor_id."_royodispatch@dispatch.com";

                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->dispacher_home_other_service_key,
                                                        'shortcode' => $dispatch_domain->dispacher_home_other_service_key_code,
                                                        'content-type' => 'application/json']
                                                            ]);
                            $url = $dispatch_domain->dispacher_home_other_service_key_url;
                            $res = $client->get($url.'/api/get-agent-tags?email_set='.$email);
                            $response = json_decode($res->getBody(), true);
                            if($response && $response['message'] == 'success'){

                                $retResponse['tags'] = $response['tags'];
                            }
                }
                return $retResponse;
            }
            catch(\Exception $e){
                return $retResponse;
            }
    }
    # get dispatcher Appointment tags from dispatcher panel
    public function getDispatcherAppointmentTags($vendor_id){

        try {
            $dispatch_domain = $this->checkIfAppointmentOnCommon();
                if ($dispatch_domain && $dispatch_domain != false) {

                    $unique = Auth::user()->code;
                    $email =  $unique.$vendor_id."_royodispatch@dispatch.com";

                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->appointment_service_key,
                                                        'shortcode' => $dispatch_domain->appointment_service_key_code,
                                                        'content-type' => 'application/json']
                                                            ]);
                            $url = $dispatch_domain->appointment_service_key_url;
                            $res = $client->get($url.'/api/get-agent-tags?email_set='.$email);
                            $response = json_decode($res->getBody(), true);

                            if($response && $response['message'] == 'success'){
                                return $response['tags'];
                            }
                }
            }
            catch(\Exception $e){
            }
    }
    # check if last mile delivery on
    public function checkIfOnDemandOn(){
        $preference = ClientPreference::first();
        if($preference->need_dispacher_home_other_service == 1 && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_url) && !empty($preference->dispacher_home_other_service_key_code))
            return $preference;
        else
            return false;
    }

    public function checkIfDeliveryOn(){
        $preference = ClientPreference::first();
        if(!empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_url) && !empty($preference->delivery_service_key_code))
            return $preference;
        else
            return false;
    }

    # update all products action
    public function updateActions(Request $request){
        if(isset($request->is_new) && $request->is_new == 'true')
        $is_new = 1;
        else
        $is_new = 0;

        if (isset($request->is_featured) && $request->is_featured == 'true') {
            $is_featured = 1;
        }
        else
        $is_featured = 0;

        if (isset($request->last_mile) && $request->last_mile == 'true') {
            $Requires_last_mile  = 1;
        }
        else
        $Requires_last_mile  = 0;

        if(isset($request->sell_when_out_of_stock) && $request->sell_when_out_of_stock == 'true')
        $sell_when_out_of_stock = 1;
        else
        $sell_when_out_of_stock = 0;

        if(isset($request->action_for) && !empty($request->action_for)){
            switch($request->action_for){
                case "for_new":
                $update_product = Product::whereIn('id',$request->product_id)->update(['is_new' => $is_new]);
                break;
                case "for_featured":
                $update_product = Product::whereIn('id',$request->product_id)->update(['is_featured' => $is_featured]);
                break;
                case "for_last_mile":
                    $update_product = Product::whereIn('id',$request->product_id)->update(['Requires_last_mile' => $Requires_last_mile]);
                break;
                case "for_live":
                    $update_product = Product::whereIn('id',$request->product_id)->update(['is_live' => $request->is_live]);
                break;
                case "for_tax":
                    $update_product = Product::whereIn('id',$request->product_id)->update(['tax_category_id' => $request->tax_category]);
                break;
                case "for_markup":

                    $update_product = Product::whereIn('id',$request->product_id)->update(['markup_price' => $request->markup_price]);

                    $update_product = ProductVariant::whereIn('product_id',$request->product_id)->update(['markup_price' => $request->markup_price]);

                break;
                case "is_recurring_booking":
                    $update_product = Product::whereIn('id',$request->product_id)->update(['is_recurring_booking' => 1]);
                break;

                case "for_sell_when_out_of_stock":
                    $update_product = Product::whereIn('id',$request->product_id)->update(['sell_when_out_of_stock' => $sell_when_out_of_stock]);
                break;
                case "sync_for_square_post":
                    foreach ($request->product_id as $key => $product_id) {
                        $this->createOrUpdateProductInSquarePos($product_id);
                    }
                break;

                case "delete":
                    // delete product harrry
                    $products = Product::whereIn('id',$request->product_id)->get();
                    $batch_square_ids = array();
                    foreach($products as $product){
                        DB::beginTransaction();
                        if(isset($product->square_item_id) && !empty($product->square_item_id)){
                            $batch_square_ids[] = $product->square_item_id;
                        }
                        Product::productDelete($product->id);
                        // $dynamic = time();

                        // Product::where('id', $product->id)->update(['sku' => $product->sku.$dynamic ,'url_slug' => $product->url_slug.$dynamic]);

                        // $tot_var  = ProductVariant::where('product_id', $product->id)->get();
                        // foreach($tot_var as $varr)
                        // {
                        //     $dynamic = time().substr(md5(mt_rand()), 0, 7);
                        //     ProductVariant::where('id', $varr->id)->update(['sku' => $product->sku.$dynamic]);
                        // }

                        // Product::where('id', $product->id)->delete();

                        // CartProduct::where('product_id', $product->id)->delete();
                        // UserWishlist::where('product_id', $product->id)->delete();

                        DB::commit();
                    }
                    $this->deleteBatchInSquarePos($batch_square_ids);
                break;
                default:
                '';
            }

        }


        return response()->json([
            'status' => 'success',
            'message' => __('Product action Submitted successfully!')
        ]);
    }

    # check if last mile delivery on
    public function getProductVariant(Request $request){

        // variant option
        $ProductVariants =   ProductVariant::where('product_id',$request->product_id)->get();
        $options = [];
        foreach($ProductVariants as $key => $variant){
            $options[] = "<option value=".$variant['id'].">".($variant['title'] ?? $variant['sku'])."</option>";
        }

        // addon selecter
        $selectedAddon =  LongTermServiceProductAddons::where('long_term_service_product_id',$request->service_product_id)->get();

        $ProductAddon  =  ProductAddon::with('addOnName','setoptions')->where('product_id',$request->product_id)->get();
        $addOnHtml = '';
        if(count( $ProductAddon)>0){
            $addOnHtml .= '<div class="addon_ser bg-light p-3" style="border-radius:15px;">
                                <h4 class="mt-0">'.__("Addons").'</h4>';
            foreach($ProductAddon as $key => $addons){
                $selected_option  = $selectedAddon->where('addon_id',$addons->addOnName->id)->values();
                $selected_option_id = (count($selected_option) > 0 ) ? $selected_option[0]['option_id'] : '';
                if($addons->setoptions->isNotEmpty()){
                    $addOnHtml .='<div class="col-12 p-0">
                                    <div class="form-group" id="service_product_variantInput">

                                        <label class="control-label">'.$addons->addOnName->title.'</label>
                                        <input name="add_on_id[]" type="hidden" value="'.$addons->addOnName->id.'">
                                        <select class="form-control selectizeInput" id="service_product_variant" name="add_on_set[]">';
                                        foreach($addons->setoptions as $setoptionskey => $setoptions){
                                            $vr =  $selected_option_id == $setoptions["id"]  ? 'selected' : '' ;
                                            $addOnHtml .='<option value="'.$setoptions["id"].'" '.$vr.' >'.($setoptions["title"]).'</option>';
                                        }
                                        $addOnHtml .='</select>
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>';
                }
            }
            $addOnHtml .=   '</div>';


        }
      $respons = [
        "variantOpt" => $options,
        "addOnHtml"  => $addOnHtml
      ];

        return $this->successResponse($respons, '');
    }

    // update Role's variant Price (START)
    public function updateRolePrice(Request $request)
    {
        try{
            if($request->has('role_id')){

                foreach ($request->role_id as $key => $value) {
                    $productVariantByRole = ProductVariantByRole::where('product_id', $request->product_id)->where('product_variant_id',$request->variant_id)->where('role_id',$value)->first();
                    if (!$productVariantByRole) {
                        $productVariantByRole          = new ProductVariantByRole();
                    }
                    $productVariantByRole->product_id         = $request->product_id;
                    $productVariantByRole->role_id            = $value;
                    $productVariantByRole->amount             = $request->role_price[$value];
                    $productVariantByRole->product_variant_id = $request->variant_id;
                    $productVariantByRole->save();
                }
            }
            return redirect()->back()->with('success', 'Amount Added Successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withError($e->getMessage());
        }
    }
    // Save Product Variant By Roles with variant_id and Amount (END)

    // Get the Amount based on product_id and product_variant_id (START)
    public function getRolePrice(Request $request)
    {
        try{
            if($request->has('product_id') && $request->has('variant_id')){

                $productVariantByRole = [];
                $roles = Role::where('status',1)->where('is_enable_pricing',1)->get();
                if($roles){
                    foreach($roles as $_role){
                        $data = ProductVariantByRole::where('product_id', $request->product_id)->where('product_variant_id',$request->variant_id)->where('role_id',$_role->id)->first();
                        $productVariantByRole[] = $data;
                    }
                }
                if(!$productVariantByRole){
                    return response()->json([
                        'status'  => 'error',
                        'result'   => false,
                        'message' => __('Products and its variants not found')
                    ]);
                }
                return response()->json([
                    'status'  => 'success',
                    'result'  => $productVariantByRole,
                    'message' => __('Successfully taken the amount!')
                ]);
            }
            return response()->json([
                'status'  => 'error',
                'result'  => false,
                'message' => __('Products and its variants not found')
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
    // Get the Amount based on product_id and product_variant_id (END)
}