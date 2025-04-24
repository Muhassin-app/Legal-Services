<?php

namespace App\Http\Controllers\Client;

use Image;
use Phumbor;
use Session;
use Redirect;
use DataTables;
use Carbon\Carbon;
use App\Models\UserVendor;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\VendorImport;
use App\Http\Traits\ApiResponser;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ToasterResponser;
use App\Http\Traits\VendorTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\{BaseController, VendorPayoutController};
use App\Http\Controllers\ShiprocketController;
use App\Http\Controllers\AhoyController;
use App\Models\{AddonOption, AddonOptionTranslation, CsvProductImport, Vendor, CsvVendorImport, VendorSlot, VendorDineinCategory, VendorBlockDate, Category, ServiceArea, ClientLanguage, ClientCurrency, AddonSet, AddonSetTranslation, ProductTranslation, Client, ClientPreference, EstimateAddonOption, EstimateProduct, Product, Type, VendorCategory,UserPermissions, VendorDocs, SubscriptionPlansVendor, SubscriptionInvoicesVendor, SubscriptionInvoiceFeaturesVendor, SubscriptionFeaturesListVendor, VendorDineinTable, Woocommerce,TaxCategory, PayoutOption, VendorConnectedAccount, OrderVendor, ProductAddon,ProductVariant, ProductCategory, ProductImage, ShippingOption, VendorPayout,VendorRegistrationSelectOption,TaxRate, VendorMedia,CsvQrcodeImport,VendorFacilty,Facilty, Role, VendorSection,VendorMultiBanner, VendorMinAmount};
use GuzzleHttp\Client as GCLIENT;
use App\Exports\VendorSimpelExport;
use App\Exports\VendorProductExport;
use DB,Log;
use App\Models\VendorRegistrationDocument;
use App\Services\InventoryService;
use App\Models\VendorSocialMediaUrls;
use Exception;
use Illuminate\Support\Facades\Http;

class SellerController extends BaseController
{
    use ToasterResponser;
    use ApiResponser;
    use VendorTrait;
    public $is_payout_enabled;
    private $folderName = '/vendor/extra_docs';

    public function __construct(){
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/vendor/extra_docs';
        $payoutOption = PayoutOption::where('status', 1)->first();
        if($payoutOption){
            $this->is_payout_enabled = 1;
        }else{
            $this->is_payout_enabled = 0;
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFilterData(Request $request){
        // $dinein_check = '';
        // $takeaway_check = '';
        // $delivery_check = '';
        $client_preference = (object)Session::get('preferences');
        // if($client_preference){
        //     $dinein_check = $client_preference->dinein_check;
        //     $takeaway_check = $client_preference->takeaway_check;
        //     $delivery_check = $client_preference->delivery_check;
        // }
        $vendors = Vendor::withCount(['products', 'orders', 'currentlyWorkingOrders'])->with('slot')->where('status', $request->status)->where('is_seller', 1)->orderBy('id', 'desc');
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendors = $vendors->get();
        foreach ($vendors as $vendor) {
            $offers = [];
            $vendor->show_url = route('seller.catalogs', $vendor->id);
            $vendor->destroy_url = route('seller.destroy', $vendor->id);
            $vendor->add_category_option = ($vendor->add_category == 0) ? __('No') : __('Yes');
            if($vendor->show_slot == 1){
                $vendor->show_slot_option ="Open";
                $vendor->show_slot_label ="success";
            }elseif ($vendor->slot->count() > 0) {
                $vendor->show_slot_option = "Open";
                $vendor->show_slot_label ="success";
            }else{
                $vendor->show_slot_label="danger";
                $vendor->show_slot_option = "Closed";
            }
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $clientVendorTypes = $vendor_typ_key.'_check';
                $NomenclitureName =  $vendor_typ_key == "dinein" ? 'Dine-In' : $vendor_typ_value;
                if($client_preference->$clientVendorTypes == 1 && $vendor->$VendorTypesName){
                    $vendor->$VendorTypesName = ($request->has($VendorTypesName) && $request->$VendorTypesName == 'on') ? 1 : 0;
                    $offers[]=  $vendor->$VendorTypesName == 1 ? getNomenclatureName($NomenclitureName) : $NomenclitureName;
                }
            }
            $vendor->offers = $offers;
        }
        return Datatables::of($vendors)
            ->addColumn('checkbox', function($row){
                $btn = '<input type="checkbox" class="single_vendor_check" name="vendor_id[]" id="single_vendor" value="'.$row->id.'"></a>';
                return $btn;
            })
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request){
                        if (Str::contains(Str::lower($row['name']), Str::lower($request->get('search')))){
                            return true;
                        }
                        return false;
                    });
                }
            })
            ->rawColumns(['checkbox'])
            ->make(true);
    }



    public function index(){
        $user = Auth::user();
        $csvVendors = CsvVendorImport::orderBy('id','desc')->get();

       // pr($csvVendors->toArray());
        $vendor_docs = collect(new VendorDocs);
        $client_preferences = ClientPreference::first();
        $vendors = Vendor::withCount(['products', 'orders', 'currentlyWorkingOrders'])->where('is_seller', 1)->with('slot')->orderBy('id', 'desc');
        if ($user->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $only_active_vendors = $vendors;
        $vendors = $vendors->get();
        $only_active_vendors = $only_active_vendors->where('status', 1)->get();
        $active_vendor_count = $vendors->where('status', 1)->count();
        $blocked_vendor_count = $vendors->where('status', 2)->count();
        $awaiting__Approval_vendor_count = $vendors->where('status', 0)->count();
        $available_vendors_count = 0;
        $vendors_product_count = 0;
        $vendors_active_order_count = 0;
        foreach ($only_active_vendors as $key => $vendor) {
            $vendors_product_count += $vendor->products->count();
            $vendors_active_order_count += $vendor->currentlyWorkingOrders->count();
            if($vendor->show_slot == 1){
                $available_vendors_count+=1;
            }elseif ($vendor->slot->count() > 0) {
                $available_vendors_count+=1;
            }
        }
        $total_vendor_count = $vendors->count();
        $vendor_registration_documents = VendorRegistrationDocument::get();

        $vendor_for_pickup_delivery = null;
        $vendor_for_ondemand = null;
        if($vendors->isNotEmpty()){
            $vendor_for_pickup_delivery = VendorCategory::where('vendor_id',$vendors->first()->id)->whereHas('category',function($q){$q->where('type_id',7);})->count();
            $vendor_for_ondemand = VendorCategory::where('vendor_id',$vendors->first()->id)->whereHas('category',function($q){$q->where('type_id',8);})->count();
        }

        if(count($vendors) == 1 && $user->is_superadmin == 0){
            return Redirect::route('seller.catalogs', $vendors->first()->id);
        }else{
            $build = array();

            $categories = Category::with('translation_one')->select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
            ->where('id', '>', '1')
            // ->where('is_core', 1)
            ->whereNotIn('type_id', [4, 5])
            ->where(function ($q) {
                $q->whereNull('vendor_id');
            })->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->where('status', 1)
            ->orderBy('parent_id', 'asc')->get();
            if ($categories) {
                $build = $this->buildTree($categories->toArray());
            }
            $templetes = \DB::table('vendor_templetes')->where('status', 1)->get();


            return view('backend/seller/index')->with([
                'vendors' => $vendors,
                'vendor_for_pickup_delivery' => $vendor_for_pickup_delivery,
                'vendor_for_ondemand' => $vendor_for_ondemand,
                'vendor_docs' => $vendor_docs,
                'csvVendors' => $csvVendors,

                'builds' => $build,
                'VendorCategory' => array(),
                'templetes' => $templetes,

                'total_vendor_count' => $total_vendor_count,
                'client_preferences' => $client_preferences,
                'active_vendor_count' => $active_vendor_count,
                'blocked_vendor_count' => $blocked_vendor_count,
                'available_vendors_count' => $available_vendors_count,
                'awaiting__Approval_vendor_count' => $awaiting__Approval_vendor_count,
                'vendor_registration_documents' => $vendor_registration_documents,
                'vendors_product_count' => $vendors_product_count, 'vendors_active_order_count' => $vendors_active_order_count]);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
        $rules = array(
            // 'name' => 'required|string|max:150|unique:vendors',
            'name' => 'required|string|max:150',
            'address' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'state' => 'required',
            'country' => 'required',
        );
        foreach ($vendor_registration_documents as $vendor_registration_document) {
            if($vendor_registration_document->is_required == 1){
                if(isset($vendor_registration_document->primary) && !empty($vendor_registration_document->primary))
                {
                    $rules[$vendor_registration_document->primary->slug] = 'required';
                }
            }
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $new_model   = $request->has('new_model') && $request->new_model ? $request->new_model : null;
        $vendor      = new Vendor();
        $saveVendor = $this->save($request, $vendor, 'false');
        if($new_model){
            $this->addDataSaveVendor($request, $saveVendor  , 'false');
        }
       // dd('a');
        if ($saveVendor > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Vendor created Successfully!',
                'data' => $saveVendor
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, Vendor $vendor, $update = 'false'){
        $checks = array();
        foreach ($request->only('name', 'address', 'latitude', 'longitude', 'desc','short_desc') as $key => $value) {
            $vendor->{$key} = $value;
        }
        $client_preference = (object)Session::get('preferences');
        $single_vendor_type = "delivery";
        $count = 0;
        if($client_preference){
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $clientVendorTypes = $vendor_typ_key.'_check';
                if($client_preference->$clientVendorTypes == 1){
                    if($count == 0){
                        $single_vendor_type = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
                    }
                    $count++;
                }
            }
        }

        if($count > 1){
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $vendor->$VendorTypesName = ($request->has($VendorTypesName) && $request->$VendorTypesName == 'on') ? 1 : 0;
            }
        }
        else{
            $vendor->$single_vendor_type = 1;
        }

        if($count > 1){
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $vendor->$VendorTypesName = ($request->has($VendorTypesName) && $request->$VendorTypesName == 'on') ? 1 : 0;
            }
        }
        else{
            $vendor->$single_vendor_type = 1;
        }

        if ($update == 'false') {
            $vendor->logo = 'default/default_logo.png';
            $vendor->banner = 'default/default_image.png';
        }
        if ($request->hasFile('logo')) {    /* upload logo file */
            $file = $request->file('logo');
            $vendor->logo = Storage::disk('s3')->put('/vendor', $file, 'public');
        }
        if ($request->hasFile('banner')) {    /* upload logo file */
            $file = $request->file('banner');
            $vendor->banner = Storage::disk('s3')->put('/vendor', $file, 'public');
        }
        $vendor->email = $request->email;
        $vendor->website = $request->website;
        $vendor->dial_code = $request->vendor_dial_code;
        $vendor->phone_no = $request->phone_no;
        $vendor->pincode = $request->pincode;
        $vendor->city = $request->city;
        $vendor->state = $request->state;
        $vendor->country = $request->country;

        $vendor->slug = Str::slug($request->name, "-");
        if(Vendor::where('slug',$vendor->slug)->count() > 0)
        $vendor->slug = Str::slug($request->name, "-").rand(10,100);
        $vendor->save();

        $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
        if ($vendor_registration_documents->count() > 0) {
            foreach ($vendor_registration_documents as $vendor_registration_document) {
                $doc_name = str_replace(" ", "_", $vendor_registration_document->primary->slug);
                if ($vendor_registration_document->file_type != "Text" && $vendor_registration_document->file_type != "selector" ) {
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
        return $vendor->id;
    }


    public function addDataSaveVendor(Request $request, $vendor_id){
        $vendor = Vendor::where('id', $vendor_id)->firstOrFail();
        $VendorController = new VendorController();
        if($request->has('userIDs')){
            // permissionsForUserViaVendor
            foreach($request->userIDs as $userId){
                $UserViaVendorRequest = new Request();
                $UserViaVendorRequest->setMethod('POST');
                $UserViaVendorRequest->request->add(['vendor_id' => $vendor_id,'ids' => $userId]);
                $UserViaVendorRequestResponse = $VendorController->permissionsForUserViaVendor($UserViaVendorRequest)->getData();
            }
        }
        $request->merge(["return_json"=>1]);
        $VendorConfigrespons = $VendorController->updateConfig($request,'',$vendor_id)->getData();//$this->updateConfig($vendor_id);

        if($request->has('can_add_category')){
            $vendor->add_category = $request->can_add_category == 'on' ? 1 : 0;
        }
        if ($request->has('assignTo')) {
            $vendor->vendor_templete_id = $request->assignTo;
        }

        $vendor->save();
        if($request->has('category_ids')){
            foreach($request->category_ids as $category_id){
                VendorCategory::create(['vendor_id' => $vendor_id, 'category_id' => $category_id, 'status' => '1']);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Vendor created Successfully!',
            'data' => $VendorConfigrespons
        ]);
        // pr($VendorConfigrespons);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id){
        $vendor = Vendor::where('id', $id)->first();
        $client_preferences = ClientPreference::first();
        $vendor_docs = VendorDocs::where('vendor_id', $id)->get();
        $vendor_registration_documents = VendorRegistrationDocument::get();
        $returnHTML = view('backend.vendor.form')->with(['client_preferences' => $client_preferences, 'vendor' => $vendor, 'vendor_docs' => $vendor_docs, 'vendor_registration_documents' => $vendor_registration_documents])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {

        $rules = array(
            'address' => 'required',
        //    'name' => 'required|string|max:150|unique:vendors,name,' . $id,
            'name' => 'required|string|max:150',
            'phone_no' => 'nullable|min:7|max:14',
            'email' => 'nullable|email',
        );
        //dd($request->all());
        $validation  = Validator::make($request->all(), $rules)->validate();
        $vendor = Vendor::where('id', $id)->first();
        $saveVendor = $this->save($request, $vendor, 'true');
        $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
        if ($vendor_registration_documents->count() > 0) {
            foreach ($vendor_registration_documents as $vendor_registration_document) {
                $doc_name = str_replace(" ", "_", $vendor_registration_document->primary->slug);
                if ($vendor_registration_document->file_type != "Text") {
                    if ($request->hasFile($doc_name)) {
                        $filePath = $this->folderName . '/' . Str::random(40);
                        $file = $request->file($doc_name);
                        $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                        VendorDocs::updateOrCreate(['vendor_id' => $id, 'vendor_registration_document_id' => $vendor_registration_document->id],['file_name' => $file_name]);
                    }
                } else {
                    VendorDocs::updateOrCreate(['vendor_id' => $id, 'vendor_registration_document_id' => $vendor_registration_document->id],['file_name' => $request->$doc_name]);
                }
            }
        }
        if ($saveVendor > 0) {
            return response()->json([
                'status' => 'success',
                'message' => __('Vendor updated Successfully!'),
                'data' => $saveVendor
            ]);
        }
    }
    public function postUpdateStatus(Request $request, $domain = ''){
        Vendor::where('id', $request->vendor_id)->update(['status' => $request->status]);
        return response()->json([
            'status' => 'success',
            'message' => __('Vendor Status Updated Successfully!'),
        ]);
    }
    /*  /**   show vendor page - config tab      */
    public function show($domain = '', $id)
    {
        $active = array();
        $categoryToggle = array();
        $user = Auth::user();
        $vendor = Vendor::findOrFail($id);



        $client_preferences = ClientPreference::first();
        $dinein_categories = VendorDineinCategory::where('vendor_id', $id)->get();
        $vendor_tables = VendorDineinTable::where('vendor_id', $id)->with('category')->get();
        foreach ($vendor_tables as $vendor_table) {
            $vendor_table->qr_url = url('/vendor/'.$vendor->slug.'/?id='.$vendor->id.'&name='.$vendor->name.'&table='.$vendor_table->id);
        }
        $co_ordinates = $all_coordinates = array();
        $areas = ServiceArea::where('vendor_id', $id)->orderBy('created_at', 'DESC')->get();
        $VendorCategory = VendorCategory::where('vendor_id', $id)->where('status', 1)->pluck('category_id')->toArray();
        $zz = 1;
        $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
                    ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
                    ->where('client_languages.client_code', Auth::user()->code)
                    ->where('client_languages.is_active', 1)
                    ->orderBy('client_languages.is_primary', 'desc')->get();
        foreach ($areas as $k => $v) {
            $all_coordinates[] = [
                'name' => $k . '-a',
                'coordinates' => $v->geo_coordinates
            ];
        }
        $preferences = Session::get('preferences');
        $defaultLatitude = 30.0612323;
        $defaultLongitude = 76.1239239;
        if($preferences){
            $defaultLatitude = $preferences['Default_latitude'];
            $defaultLongitude = $preferences['Default_longitude'];
            $defaultAddress = $preferences['Default_location_name'];
        }
        $center = [
            'lat' => $defaultLatitude,
            'lng' => $defaultLongitude
        ];
        if (!empty($all_coordinates)) {
            $center['lat'] = $all_coordinates[0]['coordinates'][0]['lat'];
            $center['lng'] = $all_coordinates[0]['coordinates'][0]['lng'];
        }
        $area1 = ServiceArea::where('vendor_id', $id)->orderBy('created_at', 'DESC')->first();
        if (isset($area1)) {
            $co_ordinates = $area1->geo_coordinates[0];
        } else {
            $co_ordinates = [
                'lat' => $defaultLatitude, //33.5362475,
                'lng' => $defaultLongitude //-111.9267386
            ];
        }
        $categories = Category::select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
            ->with('translation_one')->where('id', '>', '1')
            ->where(function ($q) use ($id) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $id);
            })->orderBy('position', 'asc')->orderBy('id', 'asc')->orderBy('parent_id', 'asc')->get();

        /* get active category list also with parent */
        foreach ($categories as $category) {
            if (in_array($category->id, $VendorCategory) && $category->parent_id == 1) {
                $active[] = $category->id;
            }
            if (in_array($category->id, $VendorCategory) && in_array($category->parent_id, $VendorCategory)) {
                $active[] = $category->id;
            }
        }
        if ($categories) {
            $build = $this->buildTree($categories->toArray());
            $categoryToggle = $this->printTreeToggle($build, $active);
        }
        $vendorSection = VendorSection::with(['primary','SectionTranslation'=>function($q){
            $q->join('client_languages as cl', 'cl.language_id', 'vendor_section_translations.language_id')->where('cl.is_primary', 1)->count();
        }])->where('vendor_id', $vendor->id)->get();
        $vendorSection = $vendorSection->map(function($da) {
            $count = count($da->SectionTranslation);
            $da->section_count = $count;
            unset($da->SectionTranslation);
            return $da;
        });
        $vendorMultiBanner = $this->getMultiBanner($vendor->id);
        $socialMediaUrls = VendorSocialMediaUrls::where('vendor_id', $vendor->id)->get();
        //pr($vendorMultiBanner);
        $templetes = \DB::table('vendor_templetes')->where('status', 1)->get();
        $returnData = array();
        $returnData['client_preferences'] = $client_preferences;
        $returnData['hour12'] = ($client_preferences->time_format == '12') ? true : false;
        $returnData['vendor'] = $vendor;
        $returnData['center'] = $center;
        $returnData['tab'] = 'configuration';
        $returnData['co_ordinates'] = $co_ordinates;
        $returnData['all_coordinates'] = $all_coordinates;
        $returnData['areas'] = $areas;
        $returnData['dinein_categories'] = $dinein_categories;
        $returnData['vendor_tables'] = $vendor_tables;
        $returnData['languages'] = $langs;
        $returnData['categoryToggle'] = $categoryToggle;
        $returnData['VendorCategory'] = $VendorCategory;
        $returnData['templetes'] = $templetes;
        $returnData['builds'] = $build;
        $returnData['vendorSection'] = $vendorSection;
        $returnData['vendorMultiBanner'] = $vendorMultiBanner;
        $returnData['is_payout_enabled'] = $this->is_payout_enabled;
        $returnData['socialMediaUrls'] = $socialMediaUrls;
        if((isset($preferences['subscription_mode'])) && ($preferences['subscription_mode'] == 1)){
            $subscriptions_data = $this->getSubscriptionPlans($id);
            $returnData['subscription_plans'] = $subscriptions_data['sub_plans'];
            $returnData['subscription'] = $subscriptions_data['active_sub'];
        }
        $vendor_for_pickup_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',7);})->count();
        $vendor_for_ondemand = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',8);})->count();
        $vendor_for_appointment_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',12);})->count();
        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        $facilties = Facilty::with(['primary'])->get();
        $vendor_facilty_ids = VendorFacilty::where('vendor_id',$vendor->id)->pluck('facilty_id')->toArray();

        return view('backend/vendor/show')->with($returnData)
                ->with(['vendor_for_pickup_delivery' => $vendor_for_pickup_delivery,
                    'clientCurrency'=>$clientCurrency,'vendor_for_ondemand' => $vendor_for_ondemand,
                    'vendor_for_appointment_delivery' => $vendor_for_appointment_delivery,
                    'facilties'=>$facilties,'vendor_facilty_ids'=> $vendor_facilty_ids
                ]);
    }

    /**   show vendor page - category tab      */
    public function vendorCategory($domain = '', $id){
        $csvVendors = [];
        $vendor = Vendor::findOrFail($id);
        $VendorCategory = VendorCategory::where('vendor_id', $id)->where('status', 1)->pluck('category_id')->toArray();
        $categories = Category::with('translation_one')->select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
            ->where('id', '>', '1')
            ->where(function ($q) use ($id) {
                $q->whereNull('vendor_id')
                    ->orWhere('vendor_id', $id);
            })->orderBy('position', 'asc')->orderBy('id', 'asc')->orderBy('parent_id', 'asc')->get();
        $categoryToggle = array();
        $active = array();
        /* get active category list also with parent */
        foreach ($categories as $category) {
            if (in_array($category->id, $VendorCategory) && $category->parent_id == 1) {
                $active[] = $category->id;
            }
            if (in_array($category->id, $VendorCategory) && in_array($category->parent_id, $VendorCategory)) {
                $active[] = $category->id;
            }
            if($category->vendor_id == $id)
            {
                $active[] = $category->id;
            }
        }
       // pr($categories);
        if ($categories) {
            $build = $this->buildTree($categories->toArray());
            $tree = $this->printTree($build, 'vendor', $active);
            $categoryToggle = $this->printTreeToggle($build, $active);
        }
        $addons = AddonSet::with('option')->select('id', 'title', 'min_select', 'max_select', 'position')
            ->where('status', '!=', 2)
            ->where('vendor_id', $id)
            ->orderBy('position', 'asc')->get();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
            ->where('is_active', 1)
            ->orderBy('is_primary', 'desc')->get();
        $client_preferences = ClientPreference::first();
        $templetes = \DB::table('vendor_templetes')->where('status', 1)->get();
        $vendor_registration_documents = VendorRegistrationDocument::get();
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        $vendor_for_pickup_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',7);})->count();
        $vendor_for_appointment_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',12);})->count();
        $vendor_for_ondemand = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',8);})->count();
        $facilties = Facilty::with(['primary'])->get();
        $vendor_facilty_ids = VendorFacilty::where('vendor_id',$vendor->id)->pluck('facilty_id')->toArray();
        $vendorMultiBanner = $this->getMultiBanner($vendor->id);
        $socialMediaUrls = VendorSocialMediaUrls::where('vendor_id', $vendor->id)->get();

        $roles = Role::where('status',1)->get();

        return view('backend.vendor.vendorCategory')->with(['vendor_for_pickup_delivery' => $vendor_for_pickup_delivery,'vendor_for_appointment_delivery' => $vendor_for_appointment_delivery,'vendor_for_ondemand' => $vendor_for_ondemand,'client_preferences' => $client_preferences, 'vendor' => $vendor, 'tab' => 'category', 'html' => $tree, 'languages' => $langs, 'addon_sets' => $addons, 'VendorCategory' => $VendorCategory, 'categoryToggle' => $categoryToggle, 'templetes' => $templetes, 'builds' => $build,'csvVendors'=> $csvVendors, 'is_payout_enabled'=>$this->is_payout_enabled, 'vendor_registration_documents' => $vendor_registration_documents,'clientCurrency'=>$clientCurrency,'facilties'=>$facilties,'vendor_facilty_ids'=> $vendor_facilty_ids,'vendorMultiBanner'=>$vendorMultiBanner,'socialMediaUrls'=>$socialMediaUrls,'roles' => $roles]);
    }

    /**   show vendor page - catalog tab      */
    public function sellerCatalog($domain = '', $id){
        $product_categories = [];
        $active = array();
        $type = Type::all();
        $categoryToggle = array();
        $vendor = Vendor::where('id',$id);
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $vendor_registration_documents = VendorRegistrationDocument::get();
        if (Auth::user()->is_superadmin == 0) {
            $vendor = $vendor->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendor  =  $vendor->first();
        if(empty($vendor))
        abort(404);
        $vendor->fixedFeeNomenclatures = $this->fixedFee($langId);
        $VendorCategory = VendorCategory::where('vendor_id', $id)->where('status', 1)->pluck('category_id')->toArray();

        $check_pickup_delivery_service = Category::whereIn('id',$VendorCategory)->where('type_id',7)->count();
        $check_on_demand_service = Category::whereIn('id',$VendorCategory)->where('type_id',8)->count();
        $categories = Category::with('primary')->select('id', 'slug')
                        ->where('id', '>', '1')->where('status', '!=', '2')->where('type_id', '1')
                        ->where('can_add_products', 1)->orderBy('parent_id', 'asc')->where('status', 1)->orderBy('position', 'asc')->get();
        $products = Product::with(['media.image', 'primary', 'category.cat', 'vendor','brand','variant' => function($v){
                            $v->select('id','product_id', 'quantity', 'price', 'barcode')->groupBy('product_id');
                    }])->select('id', 'sku','vendor_id', 'is_live', 'is_new', 'is_featured', 'has_inventory', 'has_variant', 'sell_when_out_of_stock', 'Requires_last_mile', 'averageRating', 'brand_id','minimum_order_count','batch_count')
                    ->where('vendor_id', $id)->get();
        $product_count = $products->count();
        $published_products = $products->where('is_live', 1)->count();
        $last_mile_delivery = $products->where('Requires_last_mile', 1)->count();
        $new_products = $products->where('is_new', 1)->count();
        $featured_products = $products->where('is_featured', 1)->count();
        $categories = Category::select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
                        ->where('id', '>', '1')
                        ->where(function($q) use($id){
                              $q->whereNull('vendor_id')->orWhere('vendor_id', $id);
                        })->where('status', 1)->orderBy('position', 'asc')
                        ->orderBy('id', 'asc')
                        ->orderBy('parent_id', 'asc')->get();
        $products = Product::with(['media.image', 'primary', 'category.cat', 'vendor', 'brand', 'variant' => function ($v) {
            $v->select('id', 'product_id', 'quantity', 'price')->groupBy('product_id');
        }])->select('id', 'sku', 'vendor_id', 'is_live', 'is_new', 'is_featured', 'has_inventory', 'has_variant', 'sell_when_out_of_stock', 'Requires_last_mile', 'averageRating', 'brand_id','minimum_order_count','batch_count', 'title')
            ->where('vendor_id', $id)->get()->sortBy('primary.title', SORT_REGULAR, false);
        $categories = Category::with('translation_one')->select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
            ->where('id', '>', '1')
            // ->where('is_core', 1)
            ->whereNotIn('type_id', [4, 5])
            ->where(function ($q) use ($id) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $id);
            })->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->where('status', 1)
            ->orderBy('parent_id', 'asc')->get();
        $csvProducts = CsvProductImport::where('vendor_id', $id)->orderBy('id','DESC')->get();
        //pr($csvProducts->toArray());
        $csvVendors = CsvVendorImport::all();
        /*    get active category list also with parent     */
        foreach ($categories as $category) {
            if (in_array($category->id, $VendorCategory) && $category->parent_id == 1) {
                $active[] = $category->id;
            }
            if (in_array($category->id, $VendorCategory) && in_array($category->parent_id, $VendorCategory)) {
                $active[] = $category->id;
            }
        }

        if ($categories) {
            $build = $this->buildTree($categories->toArray());
            $categoryToggle = $this->printTreeToggle($build, $active);
        }
       // pr($build);
        $product_categories = VendorCategory::with(['category', 'category.translation' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        }])
        ->whereHas('category', function($q) use($langId){
            $q->whereNull('deleted_at')->orWhere('deleted_at', '');
        })
        ->where('status', 1)->where('vendor_id', $id)->groupBy('category_id')->get();


        $p_categories = collect();
        $product_categories_hierarchy = '';
        if ($product_categories) {
            foreach($product_categories as $pc){
                $p_categories->push($pc->category);
            }
            $product_categories_build = $this->buildTree($p_categories->toArray());
            $product_categories_hierarchy = $this->getCategoryOptionsHeirarchy($product_categories_build, $langId);
            foreach($product_categories_hierarchy as $k => $cat){
                $myArr = array(1,3,7,8,9);
                if (isset($cat['type_id']) && !in_array($cat['type_id'], $myArr)) {
                    unset($product_categories_hierarchy[$k]);
                }

            }
        }
        $templetes = \DB::table('vendor_templetes')->where('status', 1)->get();
        $client_preferences = ClientPreference::first();
        $woocommerce_detail = Woocommerce::first();

        $client = Client::orderBy('id','asc')->first();
        if(isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain)
        $sku_url =  ($client->custom_domain);
        else
        $sku_url =  ($client->sub_domain.env('SUBMAINDOMAIN'));

        $sku_url = array_reverse(explode('.',$sku_url));
        $sku_url = implode(".",$sku_url);
        $vendor_name = $vendor->name;
        $vendor_name = preg_replace('/\s+/', '', $vendor_name);
        if(isset($vendor_name) && !empty($vendor_name))
        $sku_url = $sku_url.".".$vendor_name;
        $taxCate = TaxCategory::all();
        $live_status=([0=>'Draft',1=>'Published',2=>'Blocked']);
        $vendor_for_pickup_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',7);})->count();
        $vendor_for_ondemand = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',8);})->count();
        $vendor_for_appointment_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',12);})->count();
        $ship_creds = ShippingOption::select('status', 'test_mode')->where('code', 'shiprocket')->where('status', 1)->first();
        $ahoys = ShippingOption::select('status', 'test_mode')->where('code', 'ahoy')->where('status', 1)->first();
        $checkShip = ($ship_creds->status) ?? 0;
        $checkAhoyShip = ($ahoys->status) ?? 0;
        $taxRates=TaxRate::all();
        $files = CsvQrcodeImport::latest()->get();
        $facilties = Facilty::with(['primary'])->get();
        $vendor_facilty_ids = VendorFacilty::where('vendor_id',$vendor->id)->pluck('facilty_id')->toArray();
        $vendorMultiBanner = $this->getMultiBanner($vendor->id);

        $client_languages = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
                    ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
                    ->where('client_languages.client_code', Auth::user()->code)
                    ->where('client_languages.is_active', 1)
                    ->orderBy('client_languages.is_primary', 'desc')->get();

        $socialMediaUrls = VendorSocialMediaUrls::where('vendor_id', $vendor->id)->get();

        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);

        $roles = Role::get();
        if($getAdditionalPreference['is_price_by_role'] == 1){
            if($roles){
                foreach($roles as $role){
                    $role->order_min_amount = VendorMinAmount::where('role_id', $role->id)->where('vendor_id', $id)->value('order_min_amount');
                }
            }
        }
        


        return view('backend.seller.vendorCatalog')->with(['vendor_for_pickup_delivery' => $vendor_for_pickup_delivery,'vendor_for_appointment_delivery' => $vendor_for_appointment_delivery,'vendor_for_ondemand' => $vendor_for_ondemand,'taxCate' => $taxCate,'sku_url' => $sku_url, 'new_products' => $new_products, 'featured_products' => $featured_products, 'last_mile_delivery' => $last_mile_delivery, 'published_products' => $published_products, 'product_count' => $product_count, 'client_preferences' => $client_preferences, 'vendor' => $vendor, 'VendorCategory' => $VendorCategory,'csvProducts' => $csvProducts, 'csvVendors' => $csvVendors, 'products' => $products, 'tab' => 'catalog', 'typeArray' => $type, 'categories' => $categories, 'categoryToggle' => $categoryToggle, 'templetes' => $templetes, 'product_categories' => $product_categories_hierarchy, 'builds' => $build, 'woocommerce_detail' => $woocommerce_detail, 'is_payout_enabled'=>$this->is_payout_enabled, 'vendor_registration_documents' => $vendor_registration_documents,'check_pickup_delivery_service' => $check_pickup_delivery_service, 'check_on_demand_service'=>$check_on_demand_service,'checkShip'=>$checkShip,'checkAhoyShip'=>$checkAhoyShip,'live_status'=>$live_status,'taxRates'=>$taxRates,'files'=>$files,'facilties'=>$facilties,'vendor_facilty_ids'=> $vendor_facilty_ids,'vendorMultiBanner'=>$vendorMultiBanner,'socialMediaUrls'=>$socialMediaUrls,'client_languages'=>$client_languages, 'roles' => $roles, 'getAdditionalPreference' => $getAdditionalPreference]);
    }
    // vendor product datatable
    public function SellerProductFilter(Request $request,$domain='',$vendor_id)
    {
        $ordring = 'asc';
        if(!empty($request->order)){
            $ordring = $request->order[0]['dir'] ?? 'asc';
        }
        $status = $request->get('status_filter');
        $client_preference_detail =ClientPreference::select('id','business_type')->first();
        $product = Product::with(['media.image', 'primary', 'category.cat','vendor', 'brand', 'variant' => function ($v) {
            $v->select('id', 'product_id', 'quantity', 'price', 'barcode', 'expiry_date')->groupBy('product_id');
        }])->select('products.id', 'products.sku', 'products.vendor_id', 'products.is_live', 'products.is_new', 'products.is_featured', 'products.has_inventory', 'products.has_variant', 'products.sell_when_out_of_stock', 'products.Requires_last_mile', 'products.averageRating', 'products.brand_id','products.minimum_order_count','products.batch_count', 'products.title','products.global_product_id')
        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
        ->orderBy('product_translations.title', $ordring)
        ->groupBy('products.id')
        ->where('vendor_id', $vendor_id); //->get()->sortBy('primary.title', SORT_REGULAR, false);

        if (!empty($request->get('status_filter'))) {
            $product = $product->where('is_live', $status);
        }

        $need_sync_with_order = Vendor::where('id', $vendor_id)->value('need_sync_with_order');
            // pr($product->get()->toArray());
        $datatable = Datatables::of($product)
            ->addIndexColumn()
            ->addColumn('single_product_check', function ($product) use ($request) {
                $action = '<input type="checkbox" class="single_product_check"
                                name="product_id[]" id="single_product"
                                value="'.$product->id.'">';
                return $action;
            })
            ->addColumn('product_image', function ($product) use ($request) {
                $image = '';
                if($product->media->first() && !empty($product->media->first()->image) ){
                    $image_path = $product->media->first()->image->path['proxy_url'] . '30/30' . $product->media[0]->image->path['image_path'];
                    $image = '<img  class="rounded-circle" src="'. $image_path.'">';
                }

                return $image;
            })->addColumn('product_is_live', function ($product) use ($request) {
                if($product->is_live == 0 ){
                    $live_status = __('Draft');
                }elseif($product->is_live == 1 ){
                    $live_status = __('Published');
                }else{
                    $live_status = __('Blocked');
                }
                return $live_status;
            })
            ->addColumn('action', function ($product) use ($request) {
                $edit_url = route('product.edit', $product->id);
                $delete_url = route('product.destroy', $product->id);
                $action = '<div class="form-ul" style="width: 60px;">
                <div class="inner-div" style="float: left;">
                    <a class="action-icon"
                        href="'.$edit_url.'"
                        userId="'.$product->id.'"><i
                            class="mdi mdi-square-edit-outline"></i></a>
                </div>
                <div class="inner-div">
                    <form id="deleteproduct_'.$product->id.'" method="POST"
                        action="'. $delete_url.'">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <input type="hidden" name="_method" value="DELETE">
                        <div class="form-group">
                            <button type="button" class="btn btn-primary-outline action-icon delete-product" data-destroy_url="'. $delete_url.'" data-rel="'.$product->id.'"><i class="mdi mdi-delete"></i></button>

                        </div>
                    </form>
                </div>
            </div>';


                return $action;
            })
            ->addColumn('expiry_date', function ($product) use ($request) {
                return $product->variant->first() ? $product->variant->first()->expiry_date : '-';
            }) 
            ->addColumn('bar_code', function ($product) use ($request) {
                return $product->variant->first() ? $product->variant->first()->barcode : '-';
            }); 


            $datatable->addColumn('product_name', function ($product) use ($request) {
                $edit_url = route('product.edit', $product->id);
                $action =  '<a href="'.$edit_url .'"
                target="_blank" title="'.(($product->global_product_id)?' Global Item':'').'">'.($product->primary->title??'N/A').(($product->global_product_id)?' (Global)':'').'</a>';
                // Str::limit(isset($product->primary->title) && !empty($product->primary->title) ? $product->primary->title : '', 30)
                return $action;
            })
            ->addColumn('product_category', function ($product) use ($request) {
                return $product->category && $product->category->cat && $product->category->cat->name ? $product->category->cat->name : 'N/A';
            });

            if ($client_preference_detail->business_type != 'taxi'){

                $datatable->addColumn('product_brand', function ($product) use ($request) {
                    return !empty($product->brand) ? $product->brand->title : 'N/A';
                })
                ->addColumn('product_quantity', function ($product) use ($request) {
                    return $product->variant->first() ? $product->variant->first()->quantity : 0;
                })
                ->addColumn('product_price', function ($product) use ($request) {
                    return $product->variant->first() ? decimal_format($product->variant->first()->price) : 0;
                })->addColumn('product_is_new', function ($product) use ($request) {

                    return $product->is_new == 0 ? __('No') : __('Yes');
                })
                ->addColumn('product_is_featured', function ($product) use ($request) {
                    return $product->is_featured == 0 ? __('No')  : __('Yes');
                })
                ->addColumn('product_last_mile', function ($product) use ($request) {
                    return $product->Requires_last_mile == 0 ? __('No')  : __('Yes');
                });
            }
            $datatable->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where(function($query) use($search) {
                        $query->whereHas('primary', function($q) use($search){
                            $q->join('client_languages as cl', 'cl.language_id', 'product_translations.language_id')->select('product_translations.product_id', 'product_translations.title', 'product_translations.language_id', 'product_translations.body_html', 'product_translations.meta_title', 'product_translations.meta_keyword', 'product_translations.meta_description')->where('cl.is_primary', 1)->where('title', 'LIKE', '%'.$search.'%');
                        })
                        ->orWhereHas('category.cat', function($q) use($search){
                            $q->where('name', 'LIKE', '%'.$search.'%');
                        });

                    });
                }
                $instance->where(function($query) use($request) {
                    $ordring = 'asc';
                    if(!empty($request->order)){
                        $ordring = $request->order[0]['dir'];
                    }
                    $query->orderBy('title',$ordring);
                });

            });

            $columg_arr = ['single_product_check', 'product_image', 'product_name' ];
            if($need_sync_with_order != 1){
                array_push($columg_arr, 'action');
            }
            
            return $datatable->rawColumns($columg_arr)->make(true);
            
    }


    /**   show vendor page - payout tab      */
    public function vendorPayout($domain = '', $id){
        $product_categories = [];
        $active = array();
        $type = Type::all();
        $categoryToggle = array();
        $vendor = Vendor::where('id',$id);
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $user = Auth::user();
        if ($user->is_superadmin == 0) {
            $vendor = $vendor->whereHas('permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $vendor  =  $vendor->first();
        if(empty($vendor)){
            abort(404);
        }

        $VendorCategory = VendorCategory::where('vendor_id', $id)->where('status', 1)->pluck('category_id')->toArray();

        $categories = Category::with('translation_one')->select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
            ->where('id', '>', '1')
            // ->where('is_core', 1)
            ->whereNotIn('type_id', [4, 5])
            ->where(function ($q) use ($id) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $id);
            })->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->where('status', 1)
            ->orderBy('parent_id', 'asc')->get();

        /*    get active category list also with parent     */
        foreach ($categories as $category) {
            if (in_array($category->id, $VendorCategory) && $category->parent_id == 1) {
                $active[] = $category->id;
            }
            if (in_array($category->id, $VendorCategory) && in_array($category->parent_id, $VendorCategory)) {
                $active[] = $category->id;
            }
        }
        if ($categories) {
            $build = $this->buildTree($categories->toArray());
            $categoryToggle = $this->printTreeToggle($build, $active);
        }

        $templetes = \DB::table('vendor_templetes')->where('status', 1)->get();
        $client_preferences = ClientPreference::first();
        $woocommerce_detail = Woocommerce::first();

        $client = Client::with('country')->orderBy('id','asc')->first();
        if(isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain)
        $sku_url =  ($client->custom_domain);
        else
        $sku_url =  ($client->sub_domain.env('SUBMAINDOMAIN'));

        $sku_url = array_reverse(explode('.',$sku_url));
        $sku_url = implode(".",$sku_url);
        $vendor_name = $vendor->name;
        $vendor_name = preg_replace('/\s+/', '', $vendor_name);
        if(isset($vendor_name) && !empty($vendor_name)){
            $sku_url = $sku_url.".".$vendor_name;
        }

        $total_delivery_fees = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
        if ($user->is_superadmin == 0) {
            $total_delivery_fees = $total_delivery_fees->whereHas('vendor.permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $total_delivery_fees = $total_delivery_fees->sum('delivery_fee');

        $total_promo_amount = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
        if ($user->is_superadmin == 0) {
            $total_promo_amount = $total_promo_amount->whereHas('vendor.permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $total_promo_amount = $total_promo_amount->where('coupon_paid_by', 0)->sum('discount_amount');

        $total_admin_commissions = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
        if ($user->is_superadmin == 0) {
            $total_admin_commissions = $total_admin_commissions->whereHas('vendor.permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $total_admin_commissions = $total_admin_commissions->sum(DB::raw('admin_commission_percentage_amount + admin_commission_fixed_amount'));

        $total_order_value = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
        if ($user->is_superadmin == 0) {
            $total_order_value = $total_order_value->whereHas('vendor.permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $total_order_value = $total_order_value->sum('payable_amount') - $total_delivery_fees;

        $vendor_payouts = VendorPayout::where('vendor_id', $id)->orderBy('id','desc');
        if($user->is_superadmin == 0){
            $vendor_payouts = $vendor_payouts->whereHas('vendor.permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $vendor_payouts = $vendor_payouts->where('status', 1)->sum('amount');

        $past_payout_value = $vendor_payouts;

        $available_funds = $total_order_value - $total_admin_commissions - $total_promo_amount - $past_payout_value;
        // $available_funds = number_format($available_funds, 2, '.', ',');
        $past_payout_value = decimal_format($past_payout_value, ',');

        // get vendor payout connect details
        $vendorPayoutController = new VendorPayoutController();
        $payout_options = $vendorPayoutController->payoutConnectDetails($id);

        $taxCate = TaxCategory::all();
        $vendor_for_pickup_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',7);})->count();
        $vendor_for_ondemand = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',8);})->count();
        $vendor_for_appointment_delivery = VendorCategory::where('vendor_id',$id)->whereHas('category',function($q){$q->where('type_id',12);})->count();
        $vendorMultiBanner = $this->getMultiBanner($vendor->id);
        $socialMediaUrls = VendorSocialMediaUrls::where('vendor_id', $vendor->id)->get();
        return view('backend.vendor.vendorPayout')->with(['vendor_for_pickup_delivery' => $vendor_for_pickup_delivery,'vendor_for_appointment_delivery' => $vendor_for_appointment_delivery,'vendor_for_ondemand' => $vendor_for_ondemand,'taxCate' => $taxCate,'sku_url' => $sku_url, 'client_preferences' => $client_preferences, 'vendor' => $vendor, 'VendorCategory' => $VendorCategory, 'tab' => 'payout', 'typeArray' => $type, 'categories' => $categories, 'categoryToggle' => $categoryToggle, 'templetes' => $templetes, 'builds' => $build, 'woocommerce_detail' => $woocommerce_detail, 'is_payout_enabled'=>$this->is_payout_enabled, 'total_order_value' => decimal_format($total_order_value), 'total_admin_commissions' => decimal_format($total_admin_commissions), 'total_promo_amount'=>$total_promo_amount, 'past_payout_value'=>$past_payout_value, 'available_funds'=>decimal_format($available_funds), 'payout_options' => $payout_options,'vendorMultiBanner'=>$vendorMultiBanner,'socialMediaUrls'=>$socialMediaUrls]);
    }

    public function vendorPayoutCreate(Request $request, $domain = '', $id){
        try{
            DB::beginTransaction();
            $vendor = Vendor::where('id',$id);
            $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
            $user = Auth::user();
            if ($user->is_superadmin == 0) {
                $vendor = $vendor->whereHas('permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $vendor = $vendor->first();
            if(empty($vendor)){
                abort(404);
            }

            $total_delivery_fees = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_delivery_fees = $total_delivery_fees->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_delivery_fees = $total_delivery_fees->sum('delivery_fee');

            $total_promo_amount = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_promo_amount = $total_promo_amount->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_promo_amount = $total_promo_amount->where('coupon_paid_by', 0)->sum('discount_amount');

            $total_admin_commissions = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_admin_commissions = $total_admin_commissions->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_admin_commissions = $total_admin_commissions->sum(DB::raw('admin_commission_percentage_amount + admin_commission_fixed_amount'));

            $total_order_value = OrderVendor::where('vendor_id', $id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_order_value = $total_order_value->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_order_value = $total_order_value->sum('payable_amount') - $total_delivery_fees;

            $vendor_payouts = VendorPayout::where('vendor_id', $id)->orderBy('id','desc');
            if($user->is_superadmin == 0){
                $vendor_payouts = $vendor_payouts->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $vendor_payouts = $vendor_payouts->sum('amount');

            $past_payout_value = $vendor_payouts;
            $available_funds = $total_order_value - $total_admin_commissions - $total_promo_amount - $past_payout_value;

            if($request->amount > $available_funds){
                $toaster = $this->errorToaster('Error', __('Payout amount is greater than available funds'));
                return Redirect()->back()->with('toaster', $toaster);
            }

            $client_currency = ClientCurrency::select('currency_id')->where('is_primary', 1)->first();

            $pay_option = $request->payment_option_id;

            $payout = new VendorPayout();
            $payout->vendor_id = $id;
            $payout->payout_option_id = $request->payout_option_id;
            $payout->transaction_id = ($pay_option != 1) ? $request->transaction_id : '';
            $payout->amount = $request->amount;
            $payout->currency = $client_currency->currency_id;
            $payout->requested_by = $user->id;
            $payout->status = $request->status;
            $payout->save();
            DB::commit();
            $toaster = $this->successToaster(__('Success'), __('Payout is created successfully'));
        }
        catch(Exception $ex){
            DB::rollback();
            $toaster = $this->errorToaster(__('Errors'), $ex->message());
        }
        return Redirect()->back()->with('toaster', $toaster);
    }

    public function payoutFilter(Request $request, $domain='', $id){
        $from_date = "";
        $to_date = "";
        $user = Auth::user();
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
            $from_date = $date_date_filter[0];
        }
        $vendor_payouts = VendorPayout::with(['vendor', 'user', 'payoutOption'])->where('vendor_id', $id)->orderBy('id','desc');
        if($user->is_superadmin == 0){
            $vendor_payouts = $vendor_payouts->whereHas('vendor.permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }

        $vendor_payouts = $vendor_payouts->get();
        foreach ($vendor_payouts as $payout) {
            $payout->date = dateTimeInUserTimeZone($payout->created_at, $user->timezone);
            $payout->amount = $payout->amount;
            $payout->type = $payout->payoutOption->title;
        }
        return Datatables::of($vendor_payouts)
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                // if (!empty($request->get('search'))) {
                //     $instance->collection = $instance->collection->filter(function ($row) use ($request){
                //         if (Str::contains(Str::lower($row['name']), Str::lower($request->get('search')))){
                //             return true;
                //         }
                //         return false;
                //     });
                // }
            })->make(true);
    }

    /**       delete vendor       */
    public function destroy($domain = '', $id){
        $vendor = Vendor::where('id', $id)->first();
        $vendor->status = 2;
        $vendor->save();
        return $this->successResponse($vendor, 'Vendor deleted successfully!');
    }

    public function updateVendorConfigProfile(Request $request, $domain = '',  $id){
        $vendor = Vendor::where('id', $id)->first();
        $msg = 'Order configuration';
        $vendor->is_show_vendor_details = ($request->has('is_show_vendor_details') && $request->is_show_vendor_details == 'on') ? 1 : 0;
        $vendor->save();
        return redirect()->back()->with('success', $msg . ' updated successfully!');
    }

    /**     update vendor configuration data     */
    public function updateConfig(Request $request, $domain = '',  $id)
    {
        $vendor = Vendor::where('id', $id)->first();
        $msg = 'Order configuration';

        if (!$request->has('commission_percent')) {

            $vendor->show_slot = ($request->has('show_slot') && $request->show_slot == 'on') ? 1 : 0;
            $vendor->auto_accept_order = ($request->has('auto_accept_order') && $request->auto_accept_order == 'on') ? 1 : 0;
            $vendor->need_container_charges = ($request->has('need_container_charges') && $request->need_container_charges == 'on') ? 1 : 0;
            $vendor->return_request = ($request->has('return_request') && $request->return_request == 'on') ? 1 : 0;
            $vendor->cancel_order_in_processing = ($request->has('cancel_order_in_processing') && $request->cancel_order_in_processing == 'on') ? 1 : 0;
            $vendor->return_auto_approve = ($request->has('return_auto_approve') && $request->return_auto_approve == 'on') ? 1 : 0;
            // $vendor->cron_for_service_area = ($request->has('cron_for_service_area') && $request->cron_for_service_area == 'on') ? 1 : 0;
            if($request->has('slot_minutes')){
                $vendor->slot_minutes   = ($request->slot_minutes>0)?$request->slot_minutes:0;
            }
            $vendor->closed_store_order_scheduled = (($request->has('show_slot')) ? 0 : ($request->closed_store_order_scheduled == 'on')) ? 1 : 0;
            $vendor->fixed_fee = ($request->has('fixed_fee') && $request->fixed_fee == 'on') ? 1 : 0;
            $vendor->price_bifurcation = ($request->has('price_bifurcation') && $request->price_bifurcation == 'on') ? 1 : 0;
            // $vendor->fixed_fee_amount = $request->has('fixed_fee_amount') ? $request->fixed_fee_amount : 0.00;

            $vendor->fixed_fee_amount = $request->has('fixed_fee') ? $request->fixed_fee_amount : 0.00;

        }else{

            //Commission & Taxes (Visible For Admin)
            $vendor->commission_percent         = $request->commission_percent;
            $vendor->commission_fixed_per_order = $request->commission_fixed_per_order;
            $vendor->commission_monthly         = $request->commission_monthly;
            $vendor->service_fee_percent        = $request->service_fee_percent;
       
            //$vendor->add_category = ($request->has('add_category') && $request->add_category == 'on') ? 1 : 0;
            $msg = 'commission configuration';

            $vendor->service_charges_tax = ($request->has('service_charges_tax') && $request->service_charges_tax == 'on') ? 1 : 0;
            $vendor->service_charges_tax_id=$request->service_charges_tax_id != 0 && $vendor->service_charges_tax !=0 ? $request->service_charges_tax_id:0;
            $vendor->add_markup_price = ($request->has('add_markup_price') && $request->add_markup_price == 'on') ? 1 : 0;
            $vendor->markup_price_tax_id=$request->markup_price_tax_id != 0 && $vendor->add_markup_price !=0 ? $request->markup_price_tax_id:0;

            $vendor->delivery_charges_tax = ($request->has('delivery_charges_tax') && $request->delivery_charges_tax == 'on') ? 1 : 0;
            $vendor->delivery_charges_tax_id=$request->delivery_charges_tax_id != 0 && $vendor->delivery_charges_tax !=0 ? $request->delivery_charges_tax_id:0;

            $vendor->container_charges_tax = $request->container_charges_tax == 'on' ? 1 : 0;
            $vendor->container_charges_tax_id=$request->container_charges_tax_id != 0 && $vendor->container_charges_tax !=0 ? $request->container_charges_tax_id:0;

            $vendor->fixed_fee_tax = $request->fixed_fee_tax == 'on' ? 1 : 0;
            $vendor->fixed_fee_tax_id=$request->fixed_fee_tax_id != 0 && $vendor->fixed_fee_tax !=0 ? $request->fixed_fee_tax_id:0;

            }



        // Set order limit - By Ovi
        if($request->has('orders_per_slot')){
            $vendor->orders_per_slot   = $request->orders_per_slot;
        }

        if ($request->has('order_min_amount')) {
            $vendor->order_min_amount   = $request->order_min_amount;
        }
        if ($request->has('order_pre_time')) {
            $vendor->order_pre_time     = $request->order_pre_time;
        }
        if (empty($vendor->auto_accept_order) && $request->has('auto_reject_time')) {
            $vendor->auto_reject_time = $request->auto_reject_time;
        } else {
            $vendor->auto_reject_time = "";
        }
        if ($request->has('order_amount_for_delivery_fee')) {
            $vendor->order_amount_for_delivery_fee   = $request->order_amount_for_delivery_fee;
        }
        if ($request->has('delivery_fee_minimum')) {
            $vendor->delivery_fee_minimum   = $request->delivery_fee_minimum;
        }
        if ($request->has('delivery_fee_maximum')) {
            $vendor->delivery_fee_maximum   = $request->delivery_fee_maximum;
        }

        if($request->has('rescheduling_charges')){
            $vendor->rescheduling_charges   = $request->rescheduling_charges;
        }
        if($request->has('pickup_cancelling_charges')){
            $vendor->pickup_cancelling_charges   = $request->pickup_cancelling_charges;
        }
        // if ($request->has('service_fee_percent')) {
        //     $vendor->service_fee_percent         = $request->service_fee_percent;
        //     $msg = 'commission configuration';
        // }
        if ($request->has('instagram_url')) {
            $vendor->instagram_url = $request->has('instagram_url') ? $request->instagram_url : NULL;
        }
        if ($request->has('easebuzz_sub_merchent_id')) {
            $vendor->easebuzz_sub_merchent_id = $request->has('easebuzz_sub_merchent_id') ? $request->easebuzz_sub_merchent_id : NULL;
        }


       // $vendor->dynamic_html =  $request->has('dynamic_html') ? $request->dynamic_html : NULL;
        $vendor->save();

        if ($request->has('facilty_ids')) {
            foreach($request->facilty_ids as $facilty_id){
                $VendorFacilty =  VendorFacilty::where(['vendor_id' =>   $vendor->id, 'facilty_id'=> $facilty_id])->first();
                if(!$VendorFacilty){
                    $vendor_facilty = new VendorFacilty();
                    $vendor_facilty->vendor_id  = $vendor->id;
                    $vendor_facilty->facilty_id  = $facilty_id;
                    $vendor_facilty->save();
                }
            }
        }

        // vendor min amount by role
        if(isset($request->order_min_amount_arr)){
            $min_amounts = $request->order_min_amount_arr;
            foreach($min_amounts  as $key => $min_amount ){
                $where = ['vendor_id' => $id, 'role_id' => $key];
                $create = ['vendor_id' => $id, 'role_id' => $key, 'order_min_amount' => $min_amount ];
                VendorMinAmount::updateOrCreate($where, $create);
            }
        }
        
        $return_json   = $request->has('return_json') && $request->return_json ? $request->return_json : 0;
        if($return_json ==  1 ){
            return $this->successResponse($vendor,__("Vendor update successfully!"));
        }
        return redirect()->back()->with('success', $msg . ' updated successfully!');
    }

    /**
     * Update vendor social media urls.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function updateVendorSocialMediaUrls(Request $request, $domain = ''){
        try{

            $rules = array(
                'vendor_id' => 'required',
                'icon' => 'required',
                'url' => 'required'
            );
            //dd($request->all());
            $validation  = Validator::make($request->all(), $rules)->validate();

            $data['vendor_id'] = $request->vendor_id;
            $data['icon']      = $request->icon;
            $data['url']       = $request->url;
            $result = VendorSocialMediaUrls::updateOrCreate(['vendor_id' => $request->vendor_id, 'icon' => $request->icon], $data);
            if(!empty($result->id)){
                $data = ['icon'=>$request->icon, 'url'=>$request->url, 'media'=>$result->id];
                return $this->successResponse(__("Vendor Social Media Url Updated!"), $data);
            }else{
                return $this->errorResponse(__("Somthing Went Wrong."), 422);
            }

        }catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(), 422);
        }
    }

    /**
     * Update vendor cron job status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function updateCronStatusForServiceArea(Request $request, $domain = '', $id){
        try{
            $rules = array(
                'status' => 'required'
            );
            $messages = array(
                'status.required' => 'Status is required'
            );
            $validation  = Validator::make($request->all(), $rules, $messages);

            if ($validation->fails()) {
                foreach ($validation->errors()->toArray() as $error_key => $error_value) {
                    return $this->errorResponse(__($error_value[0]), 422);
                }
            }
            $vendor = Vendor::where('id', $id)->firstOrFail();
            $vendor->cron_for_service_area = $request->status;
            $vendor->save();
            return $this->successResponse('', __('Vendor updated successfully!'));
        }
        catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(), 422);
        }
    }

    public function updateAhoyLocation(Request $request, $domain = '',  $id)
    {
        $vendor = Vendor::where('id', $id)->first();
        $msg = 'Ahoy delivery location name added.';

        if ($request->has('location_name')) {
            $ship = new AhoyController();
            $save = (object)$ship->createLocation($vendor,$request);
            //dd($save);
             if(isset($save) && $save->code=='200'){
                $vendor->ahoy_location  = json_encode($save->response);
                $vendor->save();
                return redirect()->back()->with('success', $msg . ' successfully!');
                }elseif(isset($save) && $save->code=='401'){
                    return redirect()->back()->with('success',$save->response->message);
                }else{
                    return redirect()->back()->with('error_delete',$save->response->error);
                }
        }

    }

    public function updateLocation(Request $request, $domain = '',  $id)
    {
        $vendor = Vendor::where('id', $id)->first();
        $msg = 'Shiprocket Pickup Location added';

        if ($request->has('shiprocket_pickup_name')) {
            $ship = new ShiprocketController();
            $save = $ship->addShiprocketPickup($vendor,$request->shiprocket_pickup_name);
             if(isset($save->success) && $save->success){
                $vendor->shiprocket_pickup_name  = $save->address->pickup_code;
                $vendor->save();
                return redirect()->back()->with('success', $msg . ' successfully!');
             }
            // dd($save);
             return redirect()->back()->with('error_delete',$save->message);
        }

    }

    /**     Activate Category for vendor     */
    public function activeCategory(Request $request, $domain = '', $vendor_id){
        $product_categories = [];
        if($request->has('can_add_category')){
            $vendor = Vendor::where('id', $request->vendor_id)->firstOrFail();
            $vendor->add_category = $request->can_add_category == 'true' ? 1 : 0;
            $vendor->save();
        } elseif ($request->has('assignTo')) {
            // dd($request->all());
            $vendor = Vendor::where('id', $request->vendor_id)->firstOrFail();
            $vendor->vendor_templete_id = $request->assignTo;
            $vendor->save();
        } else {
            $status = $request->status == 'true' ? 1 : 0;
            $vendor_category = VendorCategory::where('vendor_id', $request->vendor_id)->where('category_id', $request->category_id)->first();
            if ($vendor_category) {
                VendorCategory::where(['vendor_id' => $request->vendor_id, 'category_id' => $request->category_id])->update(['status' => $status]);
            } else {
                VendorCategory::create(['vendor_id' => $request->vendor_id, 'category_id' => $request->category_id, 'status' => $status]);
            }
        }
        $product_categories = VendorCategory::with('category')->where('status', 1)->where('vendor_id', $request->vendor_id)->get();
        $check_pickup_delivery_service = 0;
        $check_on_demand_service = 0;
        $check_appointment_service = 0;
        foreach ($product_categories as $product_category) {
            if(isset($product_category->category) && !empty($product_category->category->translation_one))
            $product_category->category->title = $product_category->category ? $product_category->category->translation_one->name : '';

            if(isset($product_category->category) && !empty($product_category->category)) {
                    if($product_category->category->type_id == 7 || $product_category->category->type_id == "7")
                    {
                        $check_pickup_delivery_service = 1;
                    }
                    if($product_category->category->type_id == 8|| $product_category->category->type_id == "8")
                    {
                        $check_on_demand_service = 1;
                    }
                    if($product_category->category->type_id == 12 || $product_category->category->type_id == "12")
                    {
                        $check_appointment_service = 1;
                    }
            }

        }
        $data['product_categories'] = $product_categories;
        $data['check_pickup_delivery_service'] = $check_pickup_delivery_service;
        $data['check_on_demand_service'] = $check_on_demand_service;
        $data['check_appointment_service'] = $check_appointment_service;

        return $this->successResponse($data, 'Category setting saved successfully.');
    }

    /**     Check parent category enable status - true if all parent, false if any parent disable     */
    public function checkParentStatus(Request $request, $domain = '', $id)
    {
        $blockedCategory = VendorCategory::where('vendor_id', $id)->where('status', 0)->pluck('category_id')->toArray();
        $is_parent_disabled = $exit = 0;
        $category = Category::where('id', $request->category_id)->select('id', 'parent_id')->first();
        $parent_id = $category->parent_id;
        while ($exit == 0) {
            if ($parent_id == 1) {
                $exit = 1;
                break;
            } elseif (in_array($parent_id, $blockedCategory)) {
                $is_parent_disabled = 1;
                $exit = 1;
            } else {
                $category = Category::where('id', $parent_id)->select('id', 'parent_id')->first();
                $parent_id = $category->parent_id;
            }
        }
        if ($is_parent_disabled == 1) {
            return $this->errorResponse('Parent category is disabled. First enable parent category to enable this category.', 422);
        } else {
            return $this->successResponse(null, 'Parent is enabled.');
        }
    }

    /**
     * Import Excel file for vendors
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importCsv(Request $request)
    {
        if($request->has('vendor_csv')){
            $csv_vendor_import = new CsvVendorImport;
            if($request->file('vendor_csv')) {
                $fileName = time().'_'.$request->file('vendor_csv')->getClientOriginalName();
                $filePath = $request->file('vendor_csv')->storeAs('csv_vendors', $fileName, 'public');
                $csv_vendor_import->name = $fileName;
                $csv_vendor_import->path = '/storage/' . $filePath;
                $csv_vendor_import->status = 1;
                $csv_vendor_import->save();
            }
            $data = Excel::import(new VendorImport($csv_vendor_import->id), $request->file('vendor_csv'));
            //pr($data);
            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'File Successfully Uploaded!'
            // ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'File Upload Pending!'
        ]);
    }

     /**
     *update Create Vendor In Dispatch
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCreateVendorInDispatch(Request $request)
    {
        DB::beginTransaction();
        try {
                    $dispatch_domain = $this->checkIfPickupDeliveryOnCommon();
                    if ($dispatch_domain && $dispatch_domain != false) {
                        $dispatch_domain['vendor_id'] = $request->id;
                        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
                        $dispatch_domain['token'] = $token;
                        $data = [];
                        $request_from_dispatch = $this->checkUpdateVendorToDispatch($dispatch_domain);
                        if ($request_from_dispatch && isset($request_from_dispatch['status']) && $request_from_dispatch['status'] == 200) {
                            DB::commit();
                            $request_from_dispatch['url'] = $request_from_dispatch['url']."?set_unique_order_login=".$token;
                            return $request_from_dispatch;
                        } else {
                            DB::rollback();
                            return $request_from_dispatch;
                        }
                    } else {
                        return response()->json([
                        'status' => 'error',
                        'message' => 'Pickup & Delivery service in not available.'
                    ]);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            }
    }


     // check and update in dispatcher panel
     public function checkUpdateVendorToDispatch($dispatch_domain){
        try {

                $vendor = Vendor::find($dispatch_domain->vendor_id);
                $unique = Auth::user()->code;
                $postdata =  ['vendor_id' => $dispatch_domain->vendor_id ?? 0,
                'name' => $vendor->name ?? "Manager".$dispatch_domain->vendor_id,
                'phone_number' =>  $vendor->phone_no ?? rand('11111','458965'),
                'email' => $unique.$vendor->id."_royodispatch@dispatch.com",
                'team_tag' => $unique."_".$vendor->id,
                'public_session' => $dispatch_domain->token];

                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                                                    'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                                                    'content-type' => 'application/json']
                                                        ]);

                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->post(
                    $url.'/api/update-create-vendor-order',
                    ['form_params' => (
                            $postdata
                        )]
                );
                $response = json_decode($res->getBody(), true);
                if ($response) {
                   return $response;
                }
                return $response;

            }catch(\Exception $e)
                    {
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  $e->getMessage();
                        return $data;

                    }

        }



        // serach customer for vendor permission

        public function searchUserForPermission(Request $request)
            {
                $user_id_array = new \Illuminate\Database\Eloquent\Collection;
                $search = $request->get('query')??'';
                $vendor_id = $request->get('vendor_id')??0;
                $alreadyids = UserVendor::where('vendor_id', $vendor_id)->pluck('user_id') ?? array();

                $userIDs = $request->has('user_ids') ? $request->user_ids : array();
                  //pr($userIDs);
                if($alreadyids){ //$user_id_array->merge()
                    $user_id_array = $alreadyids->toArray();
                }
                if($userIDs){
                     $user_id_array = array_merge($user_id_array,$userIDs);
                }

                if (isset($search)) {

                    if ($search == '') {
                        $employees = User::orderby('name', 'asc')->select('id', 'name','email','phone_number','image')->where('is_superadmin','!=',1)->whereNotIn('id',$user_id_array)->limit(10)->get();
                    } else {
                        $employees = User::orderby('name', 'asc')->select('id', 'name','email','phone_number','image')->where('is_superadmin','!=',1)->whereNotIn('id',$user_id_array)->where('name', 'LIKE', "%{$search}%")->limit(10)->get();
                    }
                    $output = '<ul class="dropdown-menu" id="sujesion_user_id" style="display:block; position:relative">';
                        foreach($employees as $row)
                        {
                         $image =   $row->image['image_fit'].'100/100'.$row->image['image_path'];
                         $output .= '
                        <li data-id="'.$row->id.'" data-email="'.$row->email.'" data-name="'.$row->name.'"  data-image="'.$image.'"  ><a href="#">'.$row->name.' ('.$row->email.')</a></li>
                        ';
                        }
                        $output .= '</ul>';
                        echo $output;

                }
            }

      /**
     * submit permissions for user via vendor
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function permissionsForUserViaVendor(Request $request, $domain = ''){
        DB::beginTransaction();
        try {
        $rules = array(
             'ids' => 'required',
        );
        //dd($request->all());
        $validation  = Validator::make($request->all(), $rules)->validate();

        $id = $request->ids;
        $data = [
            'status' => 1,
            'is_admin' => 1,
            'is_superadmin' => 0
        ];
        $client = User::where('id', $id)->update($data);

        if(UserPermissions::where('user_id', $id)->count() == 0){
            //for updating permissions
            $request->permissions = [1,2,3,12,17,18,19,20,21];
            $removepermissions = UserPermissions::where('user_id', $id)->delete();
            if ($request->permissions) {
                $userpermissions = $request->permissions;
                $addpermission = [];
                for ($i=0;$i<count($userpermissions);$i++) {
                    $addpermission[] =  array('user_id' => $id,'permission_id' => $userpermissions[$i]);
                }
                UserPermissions::insert($addpermission);
            }
        }

         //for updating vendor permissions

            $addvendorpermissions = UserVendor::updateOrCreate(['user_id' =>  $id,'vendor_id' => $request->vendor_id]);
            DB::commit();
            return $this->successResponse($client,'Updated.');
        }catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), 400);
        }

    }



    /**
     *update Create Vendor In Dispatch On demand
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCreateVendorInDispatchOnDemand(Request $request)
    {
        DB::beginTransaction();
        try {
                    $dispatch_domain = $this->checkIfOnDemandOnCommon();
                    if ($dispatch_domain && $dispatch_domain != false) {
                        $dispatch_domain['vendor_id'] = $request->id;
                        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
                        $dispatch_domain['token'] = $token;
                        $data = [];
                        $request_from_dispatch = $this->checkUpdateVendorToDispatchOnDemand($dispatch_domain);
                        if ($request_from_dispatch && isset($request_from_dispatch['status']) && $request_from_dispatch['status'] == 200) {
                            DB::commit();
                            $request_from_dispatch['url'] = $request_from_dispatch['url']."?set_unique_order_login=".$token;
                            return $request_from_dispatch;
                        } else {
                            DB::rollback();
                            return $request_from_dispatch;
                        }
                    } else {
                        return response()->json([
                        'status' => 'error',
                        'message' => 'Pickup & Delivery service in not available.'
                    ]);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            }
    }



    // check and update in dispatcher panel on demand
    public function checkUpdateVendorToDispatchOnDemand($dispatch_domain){
        try {

                $vendor = Vendor::find($dispatch_domain->vendor_id);
                $unique = Auth::user()->code;
                $postdata =  ['vendor_id' => $dispatch_domain->vendor_id ?? 0,
                'name' => $vendor->name ?? "Manager".$dispatch_domain->vendor_id,
                'phone_number' =>  $vendor->phone_no ?? rand('11111','458965'),
                'email' => $unique.$vendor->id."_royodispatch@dispatch.com",
                'team_tag' => $unique."_".$vendor->id,
                'public_session' => $dispatch_domain->token];

                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->dispacher_home_other_service_key,
                                                    'shortcode' => $dispatch_domain->dispacher_home_other_service_key_code,
                                                    'content-type' => 'application/json']
                                                        ]);

                $url = $dispatch_domain->dispacher_home_other_service_key_url;
                $res = $client->post(
                    $url.'/api/update-create-vendor-order',
                    ['form_params' => (
                            $postdata
                        )]
                );
                $response = json_decode($res->getBody(), true);
                if ($response) {
                   return $response;
                }
                return $response;

            }catch(\Exception $e)
                    {
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  $e->getMessage();
                        return $data;

                    }

        }




    /**
     * Remove the specified user fro vendor permission
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userVendorPermissionDestroy($domain = '', $id)
    {
        $del_price_rule = UserVendor::where('id', $id);
         $del_price_rule = $del_price_rule->delete();

        return redirect()->back()->with('success', 'Permission deleted successfully!');
    }

    /**
     * get vendor subscriptions.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubscriptionPlans($id)
    {
        $sub_plans = SubscriptionPlansVendor::with('features.feature')->where('status', '1')->orderBy('sort_order', 'asc')->get();
        $featuresList = SubscriptionFeaturesListVendor::where('status', 1)->get();
        $active_subscription = SubscriptionInvoicesVendor::with(['plan', 'features.feature', 'status'])
                            ->where('vendor_id', $id)
                            ->where('status_id', '!=', 4)
                            ->orderBy('end_date', 'desc')
                            ->orderBy('id', 'desc')->first();

        if($sub_plans){
            foreach($sub_plans as $sub){
                $subFeaturesList = array();
                if($sub->features->isNotEmpty()){
                    foreach($sub->features as $feature){
                        $subFeaturesList[] = $feature->feature->title;
                    }
                    unset($sub->features);
                }
                $sub->features = $subFeaturesList;
            }
        }
        $data['sub_plans'] = $sub_plans;
        $data['active_sub'] = $active_subscription;
        return $data;
    }

    public function vendor_specific_categories($domain = '', $id){
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $product_categories = VendorCategory::with(['category', 'category.translation' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        }])
        ->whereHas('category', function($q) use($langId){
            $q->whereNull('deleted_at')->orWhere('deleted_at', '');
        })
        ->where('status', 1)->where('vendor_id', $id)->groupBy('category_id')->get();

        $p_categories = collect();
        $product_categories_hierarchy = '';
        if ($product_categories) {
            foreach($product_categories as $pc){
                $p_categories->push($pc->category);
            }
            $product_categories_build = $this->buildTree($p_categories->toArray());
            $product_categories_hierarchy = $this->getCategoryOptionsHeirarchy($product_categories_build, $langId);
            foreach($product_categories_hierarchy as $k => $cat){
                $myArr = array(1,3,7,8,9,10,12);
                if (isset($cat['type_id']) && !in_array($cat['type_id'], $myArr)) {
                    unset($product_categories_hierarchy[$k]);
                }
            }
        }
        $options = [];
        foreach($product_categories_hierarchy as $key => $product_category){
            $options[] = "<option value=".$product_category['id'].">".$product_category['hierarchy']."</option>";
        }
        return response()->json(['status' => 1, 'message' => 'Product Categories', 'product_categories' => $product_categories_hierarchy, 'options' => $options]);
    }


        /**
     *update Create Vendor In Dispatch On demand
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCreateVendorInDispatchLaundry(Request $request)
    {
        DB::beginTransaction();
        try {
                    $dispatch_domain = $this->checkIfLaundryOnCommon();
                    if ($dispatch_domain && $dispatch_domain != false) {
                        $dispatch_domain['vendor_id'] = $request->id;
                        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
                        $dispatch_domain['token'] = $token;
                        $data = [];
                        $request_from_dispatch = $this->checkUpdateVendorToDispatchLaundry($dispatch_domain);
                        if ($request_from_dispatch && isset($request_from_dispatch['status']) && $request_from_dispatch['status'] == 200) {
                            DB::commit();
                            $request_from_dispatch['url'] = $request_from_dispatch['url']."?set_unique_order_login=".$token;
                            return $request_from_dispatch;
                        } else {
                            DB::rollback();
                            return $request_from_dispatch;
                        }
                    } else {
                        return response()->json([
                        'status' => 'error',
                        'message' => 'Laundry service in not available.'
                    ]);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            }
    }

    // check and update in dispatcher panel laundry
    public function checkUpdateVendorToDispatchLaundry($dispatch_domain){
        try {

                $vendor = Vendor::find($dispatch_domain->vendor_id);
                $unique = Auth::user()->code;
                $postdata =  ['vendor_id' => $dispatch_domain->vendor_id ?? 0,
                'name' => $vendor->name ?? "Manager".$dispatch_domain->vendor_id,
                'phone_number' =>  $vendor->phone_no ?? rand('11111','458965'),
                'email' => $unique.$vendor->id."_royodispatch@dispatch.com",
                'team_tag' => $unique."_".$vendor->id,
                'public_session' => $dispatch_domain->token];

                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->laundry_service_key,
                                                    'shortcode' => $dispatch_domain->laundry_service_key_code,
                                                    'content-type' => 'application/json']
                                                        ]);

                $url = $dispatch_domain->laundry_service_key_url;
                $res = $client->post(
                    $url.'/api/update-create-vendor-order',
                    ['form_params' => (
                            $postdata
                        )]
                );
                $response = json_decode($res->getBody(), true);
                if ($response) {
                   return $response;
                }
                return $response;

        }catch(\Exception $e)
        {
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage();
            return $data;

        }
    }

    public function updateCreateVendorInDispatchAppointment(Request $request)
    {

        DB::beginTransaction();
        try {
                    $dispatch_domain = $this->checkIfAppointmentOnCommon();
                    if ($dispatch_domain && $dispatch_domain != false) {
                        $dispatch_domain['vendor_id'] = $request->id;
                        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
                        $dispatch_domain['token'] = $token;
                        $data = [];
                        $request_from_dispatch = $this->checkUpdateVendorToDispatchAppointment($dispatch_domain);
                        if ($request_from_dispatch && isset($request_from_dispatch['status']) && $request_from_dispatch['status'] == 200) {
                            DB::commit();
                            $request_from_dispatch['url'] = $request_from_dispatch['url']."?set_unique_order_login=".$token;
                            return $request_from_dispatch;
                        } else {
                            DB::rollback();
                            return $request_from_dispatch;
                        }
                    } else {
                        return response()->json([
                        'status' => 'error',
                        'message' => 'Appointmen service in not available.'
                    ]);
                    }
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
    }
    /**
     *
     *
     */

    public function checkUpdateVendorToDispatchAppointment($dispatch_domain){
        try {

                $vendor = Vendor::find($dispatch_domain->vendor_id);
                $unique = Auth::user()->code;
                $postdata =  ['vendor_id' => $dispatch_domain->vendor_id ?? 0,
                'name' => $vendor->name ?? "Manager".$dispatch_domain->vendor_id,
                'phone_number' =>  $vendor->phone_no ?? rand('11111','458965'),
                'email' => $unique.$vendor->id."_royodispatch@dispatch.com",
                'team_tag' => $unique."_".$vendor->id,
                'public_session' => $dispatch_domain->token];

                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->appointment_service_key,
                                                    'shortcode' => $dispatch_domain->appointment_service_key_code,
                                                    'content-type' => 'application/json']
                                                        ]);

                $url = $dispatch_domain->appointment_service_key_url;
                $res = $client->post(
                    $url.'/api/update-create-vendor-order',
                    ['form_params' => (
                            $postdata
                        )]
                );
                $response = json_decode($res->getBody(), true);
                if ($response) {
                   return $response;
                }
                return $response;

        }catch(\Exception $e)
        {
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage();
            return $data;

        }
    }


        // this vendio export ony for get simel vendor ewport
        public function export() {
            return Excel::download(new VendorSimpelExport, 'vendor_simpel.xlsx');
        }

        # update all vendor action
    public function updateActions(Request $request){
        $vendor_ids = $request->vendor_id;
        if($request->action == "delete"){
             Vendor::whereIn('id', $vendor_ids)->update(['status'=>2]);
        }
        return response()->json([
            'status' => 'success',
            'message' => __('Vendor action Submitted successfully!')
        ]);
    }

    # get listing of inventory vendor
    public function getInventoryImport($domain = '', $slug){

        $store_list_data = [];
        $client_preferences = [];
        $store_list = $this->getAllStoreListFromInventory();
        if($store_list['status'] == 200){
            $store_list_data = $store_list['data'];
            $client_preferences = $store_list['client_preferences'];
        }


        $vendor = Vendor::where('slug',$slug)->first();


        return view('backend.vendor.inventory-import')->with([
            'store_list_data' => $store_list_data,'vendor' => $vendor,'client_preferences' => $client_preferences]);
    }


    # get Inventory Store Products
    public function getInventoryStoreProducts(Request $request) {

        $store_product = [];

        $store_product_list = $this->getAllProductListFromInventory($request);
        if($store_product_list['status'] == 200){
           $store_product = $store_product_list['data'];

        }

        $returnHTML = view('backend.vendor.inventory-product-list')->with(['store_product' => $store_product,'vendor_slug' => $request->vendor_slug,'vendor_id' => $request->vendor_id])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }


    # get Inventory Store Products
    public function postInventoryStoreProducts(Request $request) {
        $store_product = [];
        $client_lang = [];
        $productids = $request->productids;
        $store_product_list = $this->getAllProductListFromInventoryByIds($productids);

        if($store_product_list['status'] == 200){
           $store_product = $store_product_list['data'];
        }

        if($store_product_list['status'] == 200){
            $client_lang = $store_product_list['client_lang'];
         }

         try {
                DB::beginTransaction();

                # update or insert all selected products from inventory
                foreach($store_product as $key => $product)
                {
                    $product_translation = $product['translation'];
                    $variant_data = $product['variant_data'];
                    $variant_set_data = $product['variant_set_data'];
                    unset($product['category']);
                    unset($product['primary']);
                    unset($product['translation']);
                    unset($product['variant_data']);
                    unset($product['variant_set_data']);

                    foreach($request->order_category as $key => $cat_ids){

                        $cat = explode('_',$cat_ids);

                        if($product['category_id'] == $cat[0])
                        $product['category_id'] = $cat[1];
                    }

                    $product['vendor_id'] = $request->vendor_id;
                    $product['import_from_inventory'] = 1;

                    $product_import = Product::updateOrCreate(['sku' => $product['sku']],$product);

                    foreach($product_translation as  $key => $pro_translation) {     # import product translation
                        unset($pro_translation['id']);
                        unset($pro_translation['product_id']);
                        unset($pro_translation['created_at']);
                        unset($pro_translation['updated_at']);

                        $product_translation_import = ProductTranslation::updateOrCreate(['product_id' => $product_import->id],$pro_translation);
                    }

                    foreach($variant_data as $key => $variant) {     # import product variant

                        unset($variant['id']);
                        unset($variant['product_id']);
                        unset($variant['created_at']);
                        unset($variant['updated_at']);

                        $variant['product_id'] = $product_import->id;
                        $variant['barcode'] = $this->generateBarcodeNumber();

                        $product_variant_import = ProductVariant::updateOrCreate(['sku' => $variant['sku']],$variant);
                    }




                //    ProductCategory::updateOrCreate(['product_id' => $product_import->id],['category_id' => $product['category_id']]);


                }

                    # update or insert client languages from inventory
                foreach($client_lang as $key => $lang)
                {
                        $code = Auth::user()->code;
                        $already_lang = ClientLanguage::updateOrCreate(['language_id' => $lang],['client_code' => $code,'is_active' => 1]);

                }

                DB::commit();

            } catch (\PDOException $e) {
               // Log::info($e->getMessage());
                DB::rollBack();

            }


       return Redirect::route('vendor.catalogs',$request->vendor_slug);

    }

    private function generateBarcodeNumber()
    {
        $random_string = substr(md5(microtime()), 0, 14);
        while (ProductVariant::where('barcode', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 14);
        }
        return $random_string;
    }




    # get category list of selected products
    public function getInventoryCategoryListProducts(Request $request) {

            $inventory_category = [];
            $productids = $request->productids;
            $inventory_category_list = $this->getAllCategoryListFromInventoryByIds($productids);
            if($inventory_category_list['status'] == 200){
               $inventory_category = $inventory_category_list['data'];
            }

            $order_category = Category::select('id')->where('slug','!=','Root')->whereHas('translation_one',function ($q){
                $q->where('name','!=',null);
            })->with('translation_one')->get();

        $returnHTML = view('backend.vendor.inventory-category-list')->with(['inventory_category' => $inventory_category,'order_category' => $order_category])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }




    public function VendorGlobalProductFilter(Request $request,$domain='')
    {
        $ordring = 'asc';
        if(!empty($request->order)){
            $ordring = $request->order[0]['dir'] ?? 'asc';
        }
        $product = EstimateProduct::with(['primary' => function ($q)use($ordring){
            $q->orderBy('name', $ordring);
        }])->orderBy('id',$ordring);
        $client_preference_detail =ClientPreference::select('id','business_type')->first();
        $datatable = Datatables::of($product)
            ->addIndexColumn()
            ->addColumn('global_product_check', function ($product) use ($request) {
                $action = '<input type="checkbox" class="global_product_check"
                                name="product_id[]" id="global_product"
                                value="'.$product->id.'">';
                return $action;
            })
            ->addColumn('product_image', function ($product) use ($request) {
                $image = '';
                if($product->icon){
                    $image_path = $product->icon['proxy_url'] . '30/30' . $product->icon['image_path'];
                    $image = '<img  class="rounded-circle" src="'. $image_path.'">';
                }
                return $image;
            });
            $datatable->addColumn('product_name', function ($product) use ($request) {
                $action =  Str::limit(isset($product->primary) && !empty($product->primary) ? $product->primary->name : '', 30);
                return $action;
            })
            ->addColumn('product_category', function ($product) use ($request) {
                return $product->category->translation ? $product->category->translation[0]->name : 'N/A';
            });

            return $datatable->rawColumns(['global_product_check','product_image', 'product_name','product_category'])->make(true);
    }


     # import get estimation Global Products
     public function importGlobalProducts(Request $request){

       if(!isset($request->product_id)){
        return response()->json(array('success' => true,'message'=>'Try again somthing went wrong.'));
       }

       try{
        DB::beginTransaction();
         $estimate_products = EstimateProduct::with(['primary','category','estimate_product_addons'])->whereIn('id',$request->product_id)->get();

            foreach($estimate_products as $k => $product)
            {
                    //Product added
                    $productId = Product::updateOrCreate(
                    [
                        'title'=>$product->primary->name,
                        'global_product_id'=>$product->id,
                        'vendor_id'=>$request->vid
                    ],
                    [
                        'title'=>$product->primary->name,
                        'global_product_id'=>$product->id,
                        'sku'=>str_replace(' ','.',$product->primary->name).'.'.time().'.'.str_replace(' ','.',$product->category->slug),
                        'url_slug'=>str_replace(' ','.',$product->primary->name).'.'.rand(9,100),
                        'vendor_id'=>$request->vid,
                        'category_id'=>$product->category_id,
                        'type_id'=>'1'
                    ]);

                     //Product added
                     $productVar = ProductVariant::updateOrCreate(
                        [
                            'title'=>$product->primary->name,
                            'product_id'=>$productId->id,
                            'sku'=>str_replace(' ','.',$product->primary->name).'.'.time().'.'.str_replace(' ','.',$product->category->slug),
                        ],
                        [
                            'title'=>$product->primary->name,
                            'product_id'=>$productId->id,
                            'sku'=>str_replace(' ','.',$product->primary->name).'.'.time().'.'.str_replace(' ','.',$product->category->slug),
                            'price'=>$product->primary->price,
                            'barcode'=>time().$productId->id
                        ]);

                    //Product media image added
                    $mediaId = VendorMedia::updateOrCreate(
                    [
                        'path'=>$product->icon['original'],
                        'vendor_id'=>$request->vid,
                    ],
                    [
                        'path'=>$product->icon['original'],
                        'media'=>'1',
                        'vendor_id'=>$request->vid
                    ]);


                     //Product image added
                     $imageId = ProductImage::updateOrCreate(
                        [
                            'product_id'=>$productId->id,
                            'media_id'=>$mediaId->id,
                        ],
                        [
                            'product_id'=>$productId->id,
                            'media_id'=>$mediaId->id
                        ]);



                    //Product Translation added
                $productTrans = ProductTranslation::updateOrCreate(
                    [
                        'title'=>$product->primary->name,
                        'product_id'=>$productId->id,
                    ],
                    [
                        'title'=>$product->primary->name,
                        'product_id'=>$productId->id,
                        'language_id'=>'1'
                    ]);


                    //Product Category added
                    ProductCategory::updateOrCreate([
                        'product_id'=>$productId->id,
                        'category_id'=>$product->category_id
                    ],
                    [
                        'product_id'=>$productId->id,
                        'category_id'=>$product->category_id
                    ]);



                    foreach($product->estimate_product_addons as $addon)
                    {
                        $set = $addon->estimate_addon_set;

                         //Product Addon set added
                        $addonID = AddonSet::updateOrCreate([
                            'title'=>$set->title,
                            'vendor_id'=>$request->vid
                        ],
                        [
                            'title'=>$set->title,
                            'vendor_id'=>$request->vid,
                            'min_select'=>$set->min_select,
                            'max_select'=>$set->max_select,
                            'position'=>$set->position,
                            'status'=>$set->status
                        ]);


                          //ProductAddon added
                          AddonSetTranslation::updateOrCreate([
                            'title'=>$set->title,
                            'addon_id'=>$addonID->id
                        ],
                        [
                            'title'=>$set->title,
                            'addon_id'=>$addonID->id,
                            'language_id'=>'1'
                        ]);



                         //ProductAddon added
                         ProductAddon::updateOrCreate([
                            'product_id'=>$productId->id,
                            'addon_id'=>$addonID->id
                        ],
                        [
                            'product_id'=>$productId->id,
                            'addon_id'=>$addonID->id
                        ]);


                        $estimate_addon_id = $addon->estimate_addon_id;
                        $optionAddon = EstimateAddonOption::where('estimate_addon_id',$estimate_addon_id)->get();
                        foreach($optionAddon as $addonOpt)
                        {
                            //ProductAddon set added
                              $optId =  AddonOption::updateOrCreate([
                                    'title'=>$addonOpt->title,
                                    'addon_id'=>$addonID->id
                                ],
                                [
                                    'title'=>$addonOpt->title,
                                    'addon_id'=>$addonID->id,
                                    'position'=>$addonOpt->position,
                                    'price'=>$addonOpt->price
                                ]);


                                //Addon option Translation added
                                $addonTrans = AddonOptionTranslation::updateOrCreate(
                                    [
                                        'title'=>$addonOpt->title,
                                        'addon_opt_id'=>$optId->id,
                                    ],
                                    [
                                        'title'=>$addonOpt->title,
                                        'addon_opt_id'=>$optId->id,
                                        'language_id'=>'1'
                                    ]);

                        }

                    }

            }
            DB::commit();
            return response()->json(array('success' => true,'message'=>'Global Product import successfuly.'));

        }catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }

    }




    public function vendorProductExport(Request $request) {
        return Excel::download(new VendorProductExport($request->id), 'vendor_products.xlsx');
    }

    public function getInvetoryToken()
    {
// dd("asdf");
        $preference = InventoryService::checkIfInventoryOn();
        if($preference){
            $email = Auth::user()->email;
           
            $response = Http::get($preference->inventory_service_key_url."/admin/generate_inventory_login_token", [
                'email' => $email
            ]);
            // $response->body();
           $token =  $response->json();
           
            return response()->json(['success' => true,'data' => $token['data']?? null]);
        }else{
            return $this->errorResponse(['success' => false,'message'=>'Not Found'], 401);
        }
        
    }



}
