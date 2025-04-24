<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Client\BaseController;
use App\Models\{AppStyling, AppStylingOption,ClientPreference,AppDynamicTutorial, CabBookingLayout, CabBookingLayoutBanner, CabBookingLayoutCategory, CabBookingLayoutTranslation, Category, Client, ClientLanguage, HomePageLabel, HomeProduct, Product};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\HomePage\WebStylingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppStylingController extends BaseController
{
    use WebStylingTrait;

    private $folderName = '/app_styling/tutorials';

    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/app_styling/tutorials';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $regular_font_options = [];
        $medium_font_options = [];
        $bold_font_options = [];
        $tab_style_options = [];
        $homepage_style_options = [];
        $primary_color_options = [];
        $secondary_color_options = [];
        $tertiary_color_options = [];
        $signup_tag_line_text = [];
        $regular_fonts = AppStyling::where('name', 'Regular Font')->first();
        if ($regular_fonts) {
            $regular_font_options = AppStylingOption::where('app_styling_id', $regular_fonts->id)->get();
        }
        $medium_fonts = AppStyling::where('name', 'Medium Font')->first();
        if ($medium_fonts) {
            $medium_font_options = AppStylingOption::where('app_styling_id', $medium_fonts->id)->get();
        }
        $bold_fonts = AppStyling::where('name', 'Bold Font')->first();
        if ($bold_fonts) {
            $bold_font_options = AppStylingOption::where('app_styling_id', $bold_fonts->id)->get();
        }
        $tab_style = AppStyling::where('name', 'Tab Bar Style')->first();
        if ($tab_style) {
            $tab_style_options = AppStylingOption::where('app_styling_id', $tab_style->id)->where('image','!=','bar_three.png')->get();
        }

        $selected_ids=[];
        //type = 1 for app products
        $selected_ids= $this->getHomePageSelectedProducts(1);

        $client_preferences = ClientPreference::first();
        $AppStylingOption = AppStylingOption::whereNotIn('template_id',[1,2]);
        
        switch($client_preferences->business_type){
            case "taxi":    # if business type is taxi
            $homepage_style = AppStyling::where('name', 'Home Page Style')->first();
            if ($homepage_style) {
                $homepage_style_options =  $AppStylingOption->where('image', 'home_six.png')->where('app_styling_id', $homepage_style->id)->get();
                $home_page_labels = HomePageLabel::whereIn('slug',['dynamic_page','pickup_delivery'])->with('translations')->orderBy('order_by');
                $cab_booking_layouts = CabBookingLayout::whereIn('slug',['dynamic_page','pickup_delivery'])->with('translations');
            }
            break;
            case "food_grocery_ecommerce":    # if business type is taxi
            $homepage_style = AppStyling::where('name', 'Home Page Style')->first();
            if ($homepage_style) {
                $homepage_style_options = $AppStylingOption->where('image','!=', 'home_six.png')->where('app_styling_id', $homepage_style->id)->get();
            }
            $home_page_labels = HomePageLabel::whereNotin('slug',['pickup_delivery'])->with('translations')->orderBy('order_by');
            $cab_booking_layouts = CabBookingLayout::whereNotin('slug',['pickup_delivery'])->with('translations');
            break;
            default:
            $homepage_style = AppStyling::where('name', 'Home Page Style')->first();
            if ($homepage_style) {
                $homepage_style_options = $AppStylingOption->where('app_styling_id', $homepage_style->id)->get();
            }
            $home_page_labels = HomePageLabel::with('translations')->orderBy('order_by');
            $cab_booking_layouts = CabBookingLayout::with('translations');
        }

        
            $cab_booking_layouts->app();
        

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

                    $slug = 'single_category_products';
                    $single_category_products = $this->getCategories($slug); // get categories listing for single cat products  section 
                    $selected_single_category_products = $this->getSingleCategoryProducts($slug); // get categories listing for single cat products  section 
                   
                    $select_products=Product::where(['is_live'=>'1']);
                    $select_products = $select_products->where('is_long_term_service',0);
                    $select_products= $select_products->get();
        //end home page

      
        $primary_color = AppStyling::where('name', 'Primary Color')->first();
        if ($primary_color) {
            $primary_color_options = AppStylingOption::where('app_styling_id', $primary_color->id)->first();
        }
        $secondary_color = AppStyling::where('name', 'Secondary Color')->first();
        if ($secondary_color) {
            $secondary_color_options = AppStylingOption::where('app_styling_id', $secondary_color->id)->first();
        }
        $tertiary_color = AppStyling::where('name', 'Tertiary Color')->first();
        if ($tertiary_color) {
            $tertiary_color_options = AppStylingOption::where('app_styling_id', $tertiary_color->id)->first();
        }
        $signup_tag_line = AppStyling::where('name', 'Home Tag Line')->first();
        if($signup_tag_line){
            $signup_tag_line_text = AppStylingOption::where('app_styling_id', $signup_tag_line->id)->first();
        }

        $dynamicTutorials = AppDynamicTutorial::orderBy('sort')->get();
        return view('backend/app_styling/index')->with([
            'single_category_products'=> $single_category_products, 'selected_single_category_products' => $selected_single_category_products,
            'all_pickup_category'=> $all_pickup_category,'langs' => $langs,'tertiary_color_options' => $tertiary_color_options, 'secondary_color_options' => $secondary_color_options, 'primary_color_options' => $primary_color_options, 'medium_font_options' => $medium_font_options, 'bold_font_options' => $bold_font_options, 'regular_font_options' => $regular_font_options, 'tab_style_options' => $tab_style_options, 'homepage_style_options' => $homepage_style_options, 'signup_tag_line_text' => $signup_tag_line_text, 'dynamicTutorials' => $dynamicTutorials,'home_page_labels' => $home_page_labels,'cab_booking_layouts' => $cab_booking_layouts,'select_products'=>$select_products,'selected_ids'=>$selected_ids]);
    }         
    /**
     * Store a regular font.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateFont(Request $request)
    {
        $font = AppStylingOption::where('id', $request->fonts)->first();
        $option_change = AppStylingOption::where('app_styling_id', '=', $font->app_styling_id)->update(array('is_selected' => 0));
        $font->is_selected = 1;
        $font->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateColor(Request $request)
    {
        $app_styling = AppStyling::where('name', $request->color_type.' Color')->first();
        $app_styling_option = AppStylingOption::where('app_styling_id', $app_styling->id)->first();
        if($request->color_type == "Primary"){
            $app_styling_option->name = $request->primary_color;
        }
        else if($request->color_type == "Secondary"){
            $app_styling_option->name = $request->secondary_color;
        }
        else if($request->color_type == "Tertiary"){
            $app_styling_option->name = $request->tertiary_color;
        }
        $app_styling_option->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateTabBar(Request $request)
    {
        $font = AppStylingOption::where('id', $request->tab_bars)->first();
        $option_change = AppStylingOption::where('app_styling_id', '=', $font->app_styling_id)->update(array('is_selected' => 0));
        $font->is_selected = 1;
        $font->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateHomePage(Request $request)
    {
        $font = AppStylingOption::where('id', $request->home_styles)->first();
        $option_change = AppStylingOption::where('app_styling_id', $font->app_styling_id)->update(array('is_selected' => 0));
        $font->is_selected = 1;
        $font->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully!'
        ]);
    }

    public function updateSignupTagLine(Request $request)
    {
        $signUpTag = AppStylingOption::find($request->id);
        $signUpTag->name = $request->updated_text;
        $signUpTag->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully!'
        ]);
    }

    public function addTutorials(Request $request)
    {
        $maxTutorialValue = AppDynamicTutorial::max('sort');
        $tutorialObj = new AppDynamicTutorial;
        if ($request->hasFile('file_name')) {    /* upload logo file */
            $file = $request->file('file_name');
            $tutorialObj->file_name = Storage::disk('s3')->put($this->folderName, $file, 'public');
        }
        $tutorialObj->sort = (!empty($maxTutorialValue))?($maxTutorialValue+1):1;
        $tutorialObj->save();
        return redirect()->back()->with('success', __("Tutorial updated successfully"));
    }

    public function saveOrderTutorials(Request $request)
    {
        foreach ($request->order as $key => $value) {
            $home_page = AppDynamicTutorial::where('id', $value['row_id'])->first();
            $home_page->sort = $key + 1;
            $home_page->save();
        }
        return response()->json([
            'status'=>'success',
            'message' => __('Tutorials order updated Successfully!'),
        ]);
    }
    
    public function deleteTutorials(Request $request, $domain, $id)
    {
        $tutorialObj = AppDynamicTutorial::find($id);
        $tutorialObj->delete();
        return redirect()->back()->with('success', __("Tutorial deleted successfully"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAppStylesNew(Request $request){
        foreach ($request->home_labels as $key => $value) {
           
            $home_translation = CabBookingLayoutTranslation::where('language_id', $request->languages[$key])->where('cab_booking_layout_id', $request->home_labels[$key])->first();
            if (!$home_translation) {
                $home_translation = new CabBookingLayoutTranslation();
            }
            $home_translation->title = $request->names[$key];
            $home_translation->cab_booking_layout_id  = $request->home_labels[$key];
            $home_translation->language_id = $request->languages[$key];
            $home_translation->save();


        }
        if(@$request->product_category){
            $this->updateSingleCategoryProductsToDb($request);
        }

        if(@$request->selected_products)
        {
            //For Appside type = 1
            $this->updateSelectedProductstoDb($home_translation->cab_booking_layout_id,$request,1);
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
            }
            else{
                $is_cat =  0;
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

            if($is_img != 0){
                $del = CabBookingLayoutBanner::where('cab_booking_layout_id',$request->pickup_labels[$key])->where('type', 1)->delete();
                    $folderName='banner';
                    $filePath = $folderName . '/' . Str::random(40);
                    $file = $is_img;
                   
                    $orignal_name = $is_img->getClientOriginalName();
                    $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                  
                    $url = Storage::disk('s3')->url($file_name);
                
                
                $cate = new CabBookingLayoutBanner();
                $cate->cab_booking_layout_id  = $request->pickup_labels[$key];
                $cate->banner_image_url  = $url;
                $cate->type  = 2; //2 = App styling
                $cate->save();

            }


        }

        return response()->json([
            'status' => 'success',
            'message' => 'Web Styling Updated Successfully!'
        ]);
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
        $featured_products->type = 2;
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

}
