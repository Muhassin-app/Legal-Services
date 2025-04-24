<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\BaseController;
use App\Models\{ClientPreference, PaymentMethod,HomePageLabel,ClientLanguage, HomePageLabelTranslation,CabBookingLayout,CabBookingLayoutTranslation,Category,CabBookingLayoutCategory, ClientPreferenceAdditional,OrderDeliveryStatusIcon, WebStyling,WebStylingOption, HomeProduct, Product, CabBookingLayoutBanner};
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use DB,Log;
use Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\HomePage\WebStylingTrait;

class WebStylingController extends BaseController{
    use WebStylingTrait;
    //
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client_preferences = ClientPreference::first();
        switch($client_preferences->business_type){
            case "taxi":
            $home_page_labels = HomePageLabel::whereIn('slug',['dynamic_page','pickup_delivery'])->with('translations')->orderBy('order_by');
            $cab_booking_layouts = CabBookingLayout::whereIn('slug',['dynamic_page','pickup_delivery'])->with('translations');
            break;
            case "laundry":
            $home_page_labels = HomePageLabel::whereNotin('slug',['pickup_delivery'])->with('translations')->orderBy('order_by');
            $cab_booking_layouts = CabBookingLayout::whereNotin('slug',['pickup_delivery'])->with('translations');
            break;
            default:
            $home_page_labels = HomePageLabel::with('translations')->orderBy('order_by');
            $cab_booking_layouts = CabBookingLayout::with('translations');
        }
            $cab_booking_layouts = $cab_booking_layouts->web();

        $all_pickup_category = Category::with('translation_one')->where('type_id',7)->get();
        if(count($all_pickup_category) == 0){
            $cab_booking_layouts = $cab_booking_layouts->where('slug','!=','pickup_delivery')->orderBy('order_by')->get();
            $home_page_labels = $home_page_labels->where('slug','!=','pickup_delivery')->orderBy('order_by')->get();

        }
        else{
            $cab_booking_layouts = $cab_booking_layouts->orderBy('order_by')->get();
            $home_page_labels = $home_page_labels->orderBy('order_by')->get();

        }


        $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
                    ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
                    ->where('client_languages.client_code', Auth::user()->code)
                    ->where('client_languages.is_active', 1)
                    ->orderBy('client_languages.is_primary', 'desc')->get();
        $homepage_style = WebStyling::where('name', 'Home Page Style')->first();
        $homepage_style_options = [];
        if ($homepage_style) {
            $homepage_style_options = WebStylingOption::where('web_styling_id', $homepage_style->id)->get();
        $themeId = WebStylingOption::where(['web_styling_id'=> $homepage_style->id,'is_selected'=>'1'])->first('id');
        $themeId = $themeId->id??1;
        }
        $user = Auth::user();
        $client = Client::where('code', $user->code)->first();
        $payment_methods = PaymentMethod::get();
        $orderDeliveryIcons = OrderDeliveryStatusIcon::get();
       // pr( $payment_methods->toArray());

       $slug = 'single_category_products';
       $single_category_products = $this->getCategories($slug); // get categories listing for single cat products  section 
       $selected_single_category_products = $this->getSingleCategoryProducts($slug); // get categories listing for single cat products  section 

       $categories =  $this->getCategoryListing();
       $selectedProducts =  $this->getSelectedProducts();
       $products = $this->getProducts(['products' => $selectedProducts]);
       //type = 0 for Web products
       $selected_ids= $this->getHomePageSelectedProducts(0);
       $select_products= $this->getProducts([],'all');
        $bottom_name = ClientPreferenceAdditional::where('key_name','bottom_name')->first();
        
        return view('backend/web_styling/index')->with(['products' => $products, 'selectedProducts' => $selectedProducts, 'categories' => $categories, 'clientContact'=>$client,'homepage_style_options' => $homepage_style_options,'all_pickup_category'=> $all_pickup_category,'client_preferences' => $client_preferences,'home_page_labels' => $home_page_labels,'cab_booking_layouts' => $cab_booking_layouts, 'langs' => $langs,'payment_methods' => $payment_methods,'themeId'=>$themeId,'orderDeliveryIcons'=>$orderDeliveryIcons, 'single_category_products'=> $single_category_products, 'selected_single_category_products' => $selected_single_category_products,'selected_ids'=>$selected_ids,'select_products'=> $select_products,'bottom_name'=>($bottom_name->key_value??'')]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateWebStyles(Request $request){
        //  dd($request->all());
        if($request->has('home_labels')){
            foreach ($request->home_labels as $key => $value) {
                $home_translation = HomePageLabelTranslation::where('language_id', $request->languages[$key])->where('home_page_label_id', $request->home_labels[$key])->first();
                if (!$home_translation) {
                    $home_translation = new HomePageLabelTranslation();
                }
                $home_translation->title = $request->names[$key];
                $home_translation->home_page_label_id = $request->home_labels[$key];
                $home_translation->language_id = $request->languages[$key];
                $home_translation->save();
            }
        }
        // $featured_products = HomePageLabel::where('slug', 'featured_products')->first();
        // if($featured_products){
        //     $featured_products->is_active = $request->has('featured_products') && $request->featured_products == "on" ? 1 : 0;
        //     $featured_products->save();
        // }
        // $vendors = HomePageLabel::where('slug', 'vendors')->first();
        // if($vendors){
        //     $vendors->is_active = $request->has('vendors') && $request->vendors == "on" ? 1 : 0;
        //     $vendors->save();
        // }
        // $new_products = HomePageLabel::where('slug', 'new_products')->first();
        // if($new_products){
        //     $new_products->is_active = $request->has('new_products') && $request->new_products == "on" ? 1 : 0;
        //     $new_products->save();
        // }
        // $on_sale = HomePageLabel::where('slug', 'on_sale')->first();
        // if($on_sale){
        //     $on_sale->is_active = $request->has('on_sale') && $request->on_sale == "on" ? 1 : 0;
        //     $on_sale->save();
        // }
        // $brands = HomePageLabel::where('slug', 'brands')->first();
        // if($brands){
        //     $brands->is_active = $request->has('brands') && $request->brands == "on" ? 1 : 0;
        //     $brands->save();
        // }
        // $best_sellers = HomePageLabel::where('slug', 'best_sellers')->first();
        // if($best_sellers){
        //     $best_sellers->is_active = $request->has('best_sellers') && $request->best_sellers == "on" ? 1 : 0;
        //     $best_sellers->save();
        // }
        // $pickup_delivery = HomePageLabel::where('slug', 'pickup_delivery')->first();
        // if($pickup_delivery){
        //     $pickup_delivery->is_active = $request->has('pickup_delivery') && $request->pickup_delivery == "on" ? 1 : 0;
        //     $pickup_delivery->save();
        // }
        $client_preferences = ClientPreference::first();
        if($client_preferences){
            if($request->has('favicon')){
                $client_preferences->favicon = Storage::disk('s3')->put('favicon', $request->favicon, 'public');
            }
            if($request->has('sign_up_image')){
                $client_preferences->signup_image = Storage::disk('s3')->put('favicon', $request->sign_up_image, 'public');
            }

            if($request->has('admin_sign_in_image')){
                $admin_sign_in_image = Storage::disk('s3')->put('admin_sign_in_image', $request->admin_sign_in_image, 'public');
                $user = Client::first();
                ClientPreferenceAdditional::updateOrCreate(
                ['key_name' => 'admin_signin_image'],['key_name' => 'admin_signin_image', 'key_value' => $admin_sign_in_image,'client_code'=>$user->code]
                );
            }
            
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $iconFiledName     = config('constants.VendorTypesIcon.'.$vendor_typ_key);
                if($request->has($iconFiledName)){
                    $client_preferences->$iconFiledName = Storage::disk('s3')->put('VTI', $request->$iconFiledName, 'public');
                }
            }

        
            $client_preferences->web_color = $request->primary_color;
            $client_preferences->cart_enable = $request->cart_enable == 'on' ? 1 : 0;
            $client_preferences->age_restriction = $request->age_restriction == 'on' ? 1 : 0;
            $client_preferences->rating_check = $request->rating_enable == 'on' ? 1 : 0;
            $client_preferences->show_contact_us = $request->show_contact_us == 'on' ? 1 : 0;
            $client_preferences->show_icons = $request->show_icons == 'on' ? 1 : 0;
            $client_preferences->show_wishlist = $request->show_wishlist == 'on' ? 1 : 0;
           // $client_preferences->show_payment_icons = $request->show_payment_icons == 'on' ? 1 : 0;
            $client_preferences->hide_nav_bar = $request->hide_nav_bar == 'on' ? 1 : 0;
            $client_preferences->header_quick_link = $request->header_quick_link == 'on' ? 1 : 0;
            $client_preferences->show_qr_on_footer = $request->show_qr_on_footer == 'on' ? 1 : 0;
            $client_preferences->age_restriction_title = $request->age_restriction_title;
            $client_preferences->site_top_header_color = $request->site_top_header_color;
            $client_preferences->dashboard_theme_color = $request->dashboard_theme_color;

            $client_preferences->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Web Styling Updated Successfully!'
        ]);
    }
    public function updateOrderStatusIcons(Request $request){
        try{
                $orderIcons = OrderDeliveryStatusIcon::get();
                foreach($orderIcons as  $k => $value){
                    $nmm = 'image_'.$value->id;
                    if($request->has($nmm)){
                        $orderValue = OrderDeliveryStatusIcon::where('id',$value->id)->first();
                        $orderVal = Storage::disk('s3')->put('ODSI', $request->$nmm, 'public');
                        $orderValue->image_url = $orderVal;
                        $orderValue->save();
                    }
                }
                
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery Icon Updated Successfully!'
            ]);

        }catch(\Exception $e)
        {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }

    }

    public function updatePaymentIcons(Request $request){
        $client_preferences = ClientPreference::first();
        $client_preferences->show_payment_icons = $request->show_payment_icons == 'on' ? 1 : 0;
        $client_preferences->save();
        return back()->with('success',__('Payment Method Updated Successfully!'));

    }

    public function updatePaymentMethods(Request $request){
        $status = $request->has('state') ? $request->state : null;
        $is_show  = ($status == 'true') ? 1 : 0;

        $Payment_method =  PaymentMethod::where('id',$request->id)->first();

        if($Payment_method){
            $Payment_method->is_show = $is_show;
            $Payment_method->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Payment Method Updated Successfully!')
        ]);

    }

    /**
     * save the order of banner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function saveOrder(Request $request)
    {
        foreach ($request->order as $key => $value) {
            $home_page = HomePageLabel::where('id', $value)->first();
            $home_page->order_by = $key + 1;
            $home_page->save();
        }
        return response()->json([
            'status'=>'success',
            'message' => 'Home Page Labels order updated Successfully!',
        ]);
    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateDarkMode(Request $request){
        $client_preferences = ClientPreference::first();
        if($client_preferences){
            $client_preferences->show_dark_mode = $request->show_dark_mode;
            $client_preferences->save();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Web Styling Updated Successfully!'
        ]);
    }


    # add new pickup delivery section
    public function addNewPickupSection(Request $request){

        DB::beginTransaction();
        try{
        $featured_products = new CabBookingLayout();
        $featured_products->title = $request->names[0]??null;
        $featured_products->slug = preg_replace('/\s+/', '', $request->names[0])??null;
        $featured_products->is_active = $request->has('is_active') && $request->is_active == "on" ? 1 : 0;
        $featured_products->save();
        foreach ($request->languages as $key => $value) {
            $home_translation = new CabBookingLayoutTranslation();
            $home_translation->title = $request->names[$key];
            $home_translation->body_html = $request->description_[$key] ??null;
            $home_translation->cab_booking_layout_id  = $featured_products->id;
            $home_translation->language_id = $request->languages[$key];
            $home_translation->save();
        }
        foreach ($request->categories as $key => $value) {
            $cate = new CabBookingLayoutCategory();
            $cate->cab_booking_layout_id  = $featured_products->id;
            $cate->category_id  = $value;
            $cate->save();
        }



        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Web Styling Updated Successfully!'
        ]);
        }
        catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage()
            ]);

        }
    }

    /**
     * save the order of banner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function saveOrderPickup(Request $request)
    {
        foreach ($request->order as $key => $value) {
            $home_page = CabBookingLayout::where('id', $value)->first();
            $home_page->order_by = $key + 1;
            $home_page->save();
        }
        return response()->json([
            'status'=>'success',
            'message' => 'Home Page Labels order updated Successfully!',
        ]);
    }


     # delete  pickup delivery section
     public function deletePickupSection($domain = '', $id){
        DB::beginTransaction();
        try{
        $featured_products =  CabBookingLayout::where('id',$id)->delete();
        if($featured_products){
            HomeProduct::where('layout_id',$id)->delete();
        }
        DB::commit();
        return redirect()->back()->with('success', 'Pickup Styling Deleted Successfully!');
        }
        catch(\Exception $ex){
            DB::rollback();
            return redirect()->back()->with('success', $ex->getMessage());

        }
    }


    # apend new section
    public function appendPickupSection(Request $request){
        DB::beginTransaction();
        try{
        $home_page = HomePageLabel::where('id', $request->row_id)->first();

        $order_no = CabBookingLayout::orderBy('order_by','desc')->value('order_by');
        if(isset($order_no) && !empty($order_no))
        $order_no += 1;
        else
        $order_no = 1;

        $featured_products = new CabBookingLayout();
        $featured_products->title = $home_page->title??null;
        $featured_products->slug = $home_page->slug??null;
        $featured_products->is_active = 1;
        $featured_products->type = 1;
        $featured_products->order_by = $order_no??1;
        $featured_products->save();

        if($home_page->slug == 'pickup_delivery')
        {
            $all_pickup_category = Category::with('translation_one')->where('type_id',7)->first();
            if( $all_pickup_category){
            $cate = new CabBookingLayoutCategory();
            $cate->cab_booking_layout_id  = $featured_products->id;
            $cate->category_id  = $all_pickup_category->id;
            $cate->save();
            }

        }

        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Web Styling Updated Successfully!'
        ]);
        }
        catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage()
            ]);

        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateWebStylesNew(Request $request){
        if($request->has('home_labels')){
            foreach (@$request->home_labels as $key => $value) {
                $home_translation = CabBookingLayoutTranslation::where('language_id', $request->languages[$key])->where('cab_booking_layout_id', $request->home_labels[$key])->first();
                if (!$home_translation) {
                    $home_translation = new CabBookingLayoutTranslation();
                }
                $home_translation->title = $request->names[$key];
                $home_translation->cab_booking_layout_id  = $request->home_labels[$key];
                $home_translation->language_id = $request->languages[$key];
                $home_translation->save();


            }
        }

        if(@$request->product_category){
            $this->updateSingleCategoryProductsToDb($request);
        }

        if(@$request->selected_products)
        {
            //For webside type = 0
            $this->updateSelectedProductstoDb($home_translation->cab_booking_layout_id,$request,'0');
        }

        foreach ($request->pickup_labels as $key => $value) {

            if(isset($request->is_active[$key]) && !empty($request->is_active[$key]))
            $is_cc =  1;
            else
            $is_cc =  0;

            if(isset($request->for_no_product_found_html[$key]) && !empty($request->for_no_product_found_html[$key]))
            $for_no_product_found_html =  1;
            else
            $for_no_product_found_html =  0;

            $is_active = CabBookingLayout::where('id', $request->pickup_labels[$key])->first();
            if($is_active){
                $is_active->is_active = $is_cc;
                $is_active->for_no_product_found_html = $for_no_product_found_html;
                $is_active->save();
            }

            if(isset($request->categories[$key]) && !empty($request->categories[$key])){
                $is_cat =  $request->categories[$key]['check'];
            //   // Log::info($is_cat);
            }
            else{
                $is_cat =  0;
            //   // Log::info($is_cat);
            }

            if(isset($request->banner_image[$key]) && !empty($request->banner_image[$key])){
                $is_img =$request->banner_image[$key]['check'];
            } else{
                $is_img =  0;
            }



            if($is_cat != 0)
            {
                $del = CabBookingLayoutCategory::where('cab_booking_layout_id',$request->pickup_labels[$key])->delete();
                $cate = new CabBookingLayoutCategory();
                $cate->cab_booking_layout_id  = $request->pickup_labels[$key];
                $cate->category_id  = $is_cat;
                $cate->save();
            }

            if(isset($request->banner_image[$value]) && !empty($request->banner_image[$value])){
                $is_img =$request->banner_image[$value]['check'];
                $del = CabBookingLayoutBanner::where('cab_booking_layout_id', $value)->delete();
                $folderName='banner';
                $filePath = $folderName . '/' . Str::random(40);
                $file = $is_img;
                
                $orignal_name = $is_img->getClientOriginalName();
                $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                
                $url = Storage::disk('s3')->url($file_name);
                
                $cate = new CabBookingLayoutBanner();
                $cate->cab_booking_layout_id  =  $value;
                $cate->banner_image_url  = $url;
                $cate->type  = 1;
                $cate->save();
            }

            if(isset($request->banner_url[$value]) && !empty($request->banner_url[$value])){
                CabBookingLayoutBanner::updateOrCreate(
                    ['cab_booking_layout_id' => $value],
                    [
                        'banner_url' => $request->banner_url[$value],
                        'type' => 1
                    ]
                );
            }
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Web Styling Updated Successfully!'
        ]);
    }



     /**
     * get Html Data in Modal
    */
    public function getHtmlDatainModal(Request $request){
        try {
            $html_data = CabBookingLayoutTranslation::where('cab_booking_layout_id',$request->id)->get();
            $langs = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
                    ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
                    ->where('client_languages.client_code', Auth::user()->code)
                    ->where('client_languages.is_active', 1)
                    ->orderBy('client_languages.is_primary', 'desc')->get();

                if ($request->ajax()) {
                 return \Response::json(\View::make('backend.web_styling.html-edit-modal', array('html_data'=>  $html_data,'langs' => $langs))->render());
                }


            return $this->errorResponse('Invalid Layout', 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


      /**
     * get Layout background image in Modal
    */
    public function getImageDatainModal(Request $request){
        try {
            $banner = CabBookingLayout::where('id',$request->id)->first();

            $returnHTML = view('backend.web_styling.image-edit-modal')->with(['banner' => $banner])->render();
            return response()->json(array('success' => true, 'html'=>$returnHTML));

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * get Products for selected product home section
    */


    public function getProductDatainModal(Request $request){
        try {
            $products = [];
            if(@$request->category_id){
                $products = $this->getProducts($request);
            }
            $returnHTML = view('backend.web_styling.product-modal')->with(['products' => $products])->render();
            return response()->json(array('success' => true, 'html'=>$returnHTML));

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * updateProduct Data in Modal
    */
    public function updateProductsDatainModal(Request $request){

        if ($request->has('product_ids')) {    /* upload logo file */
            $rules['product_ids'] =  'required';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        if (checkTableExists('home_products')) {
            $insert = ['slug' => 'selected_products', 'products' => json_encode($request->product_ids)];
            HomeProduct::updateOrCreate(
                ['slug' => $insert['slug']],
                ['products' => $insert['products']]
            );
        }
        return response()->json([
            'status'=>'success',
            'message' => __('Products updated Successfully!')
        ]);
    }

      /**
     * update Image Data in Modal
    */
    public function updateImageDatainModal(Request $request){

        if ($request->hasFile('image')) {    /* upload logo file */
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $id = $request->id;
        $banner = CabBookingLayout::find($id);
        if ($request->hasFile('image')) {    /* upload logo file */
            $file = $request->file('image');
            $banner->image = Storage::disk('s3')->put('/banner', $file,'public');
        }
        $banner = $banner->save();

        if($banner){
            return response()->json([
                'status'=>'success',
                'message' => 'Banner updated Successfully!',
                'data' => $banner
            ]);
        }
    }




       # edit Dynamic Html Section
       public function editDynamicHtmlSection(Request $request){

        DB::beginTransaction();
        try{
            CabBookingLayoutTranslation::where('cab_booking_layout_id',$request->layout_id)->delete();
         foreach ($request->languages as $key => $value) {
            $home_translation = new CabBookingLayoutTranslation();
            $home_translation->body_html = $request->description_[$key] ??null;
            $home_translation->cab_booking_layout_id   = $request->layout_id;
            $home_translation->language_id = $request->languages[$key];
            $home_translation->save();
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Web Styling Updated Successfully!'
        ]);
        }
        catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage()
            ]);

        }
    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateHomePageStyle(Request $request)
    {
        $font = WebStylingOption::where('id', $request->home_styles)->first();
        $option_change = WebStylingOption::where('web_styling_id', '=', $font->web_styling_id)->update(array('is_selected' => 0));
        $font->is_selected = 1;
        $font->save();
        return response()->json([
            'status' => 'success',
            'theme'  => $font->id,
            'message' => 'Updated successfully!'
        ]);
    }
    public function updateContactUs(Request $request){
        $rules = array(
            'contact_phone_number' => 'required|min:7|max:15'
        );
        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $user = Auth::user();
        $client = Client::where('code', $user->code)->first();
        $client->contact_address =  $request->contact_address ;
        $client->contact_phone_number =  $request->contact_phone_number ;
        $client->contact_email =  $request->contact_email ;
        if($request->has('whatsapp_url'))
        $client->whatsapp_url =  $request->whatsapp_url;
        if($request->has('bottom_value')){
            ClientPreferenceAdditional::updateOrCreate(['key_name'=>$request->bottom_name,'client_code'=>$user->code],[
                'key_name'=>$request->bottom_name,
                'key_value'=>$request->bottom_value
            ]);
        }
        $client->save();
        return redirect()->back()->with('success', 'Contact Us Updated successfully!');
    }

    // public function updateSingleCategoryProducts(Request $request){
        
        
    //     return redirect()->back()->with('success', 'Category Updated successfully!');
    // }
}
