<?php

namespace App\Http\Controllers\Client;

use Image;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{MobileBannerServiceArea, ClientPreference, MobileBanner, Vendor, Category, ClientLanguage, ServiceAreaForBanner};

class MobileBannerController extends BaseController
{

    private $fstatus = 1;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client_preferences = ClientPreference::first();
        $banners = MobileBanner::orderBy('sorting', 'asc')->get();

        $areas = ServiceAreaForBanner::where('type', 2)->orderBy('created_at', 'DESC')->get();

        $co_ordinates = $all_coordinates = array();
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
        }else{
            $all_coordinates[0]['name'] = '0-a';
            $all_coordinates[0]['coordinates'][0]['lat'] = floatval($defaultLatitude);
            $all_coordinates[0]['coordinates'][0]['lng'] = floatval($defaultLongitude);
        }

        $area1 = ServiceAreaForBanner::where('type', 2)->orderBy('created_at', 'DESC')->first();
        if ($area1) {
            $co_ordinates = $area1->geo_coordinates[0];
        } else {
            $co_ordinates = [
                'lat' => $defaultLatitude, //33.5362475,
                'lng' => $defaultLongitude //-111.9267386
            ];
        }

        return view('backend/mobile_banner/index')->with(['banners' => $banners, 'areas' => $areas, 'all_coordinates' => $all_coordinates, 'co_ordinates' => $co_ordinates, 'center' => $center, 'client_preferences' => $client_preferences]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $client_preferences = ClientPreference::first();
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $categories = Category::with(['translation' => function ($q) use ($langId) {
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                ->where('category_translations.language_id', $langId);
        }])
            ->select('id', 'slug')->where('status', $this->fstatus)->where('can_add_products', 1)->where('id', '>', 1)->get();
        foreach ($categories as $key => $category) {
            $category->translation_name = ($category->translation->first()) ? $category->translation->first()->name : $category->slug;
        }
        $vendors = Vendor::select('id', 'name')->where('status', $this->fstatus)->get();
        $areas = ServiceAreaForBanner::where('type', 2)->orderBy('created_at', 'DESC')->get();
        $banner = new MobileBanner();
        $returnHTML = view('backend.mobile_banner.form')->with(['banner' => $banner,  'vendors' => $vendors, 'categories' => $categories, 'areas' => $areas, 'selected_areas' => [], 'client_preferences' => $client_preferences])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cms  $cms
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id)
    {
        $client_preferences = ClientPreference::first();
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $banner = MobileBanner::where('id', $id)->first();
        $categories = Category::with(['translation' => function ($q) use ($langId) {
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                ->where('category_translations.language_id', $langId);
        }])
            ->select('id', 'slug')->where('status', $this->fstatus)->where('can_add_products', 1)->where('id', '>', 1)->get();
        foreach ($categories as $key => $category) {
            $category->translation_name = ($category->translation->first()) ? $category->translation->first()->name : $category->slug;
        }
        $vendors = Vendor::select('id', 'name')->where('status', $this->fstatus)->get();
        $areas = ServiceAreaForBanner::where('type', 2)->orderBy('created_at', 'DESC')->get();
        $selected_areas = MobileBannerServiceArea::where('banner_id', $id)->pluck('service_area_id')->toArray();
        $returnHTML = view('backend.mobile_banner.form')->with(['banner' => $banner,  'vendors' => $vendors, 'categories' => $categories, 'areas' => $areas, 'selected_areas' => $selected_areas, 'client_preferences' => $client_preferences])->render();
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
            'name' => 'required|string|max:150',
            'start_date_time' => 'required|before:end_date_time',
            'end_date_time' => 'required|after:start_date_time',
        );

        if ($request->hasFile('image')) {    /* upload logo file */
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }




        $validation  = Validator::make($request->all(), $rules)->validate();
        $banner = new MobileBanner();


        $savebanner = $this->save($request, $banner, 'false');


        if ($savebanner > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Banner created Successfully!',
                'data' => $banner
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cms  $cms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {
        $rules = array(
            'name' => 'required|string|max:150',
            'start_date_time' => 'required',
            'end_date_time' => 'required',
        );
        if ($request->hasFile('image')) {    /* upload logo file */
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();

        $banner = MobileBanner::find($id);
        $savebanner = $this->save($request, $banner, 'true');
        if ($savebanner > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Banner updated Successfully!',
                'data' => $banner
            ]);
        }
    }

    /**
     * save and update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, MobileBanner $banner, $update = 'false')
    {
        $banner->validity_on = ($request->has('validity_on') && $request->validity_on == 'on') ? 1 : 0;
        $banner->name = $request->name;
        $banner->start_date_time = $request->start_date_time;
        $banner->end_date_time = $request->end_date_time;

        if ($update == 'false') {
            $bannerSort = MobileBanner::select('id', 'sorting')->where('sorting', \DB::raw("(select max(`sorting`) from banners)"))->first();
            $banner->sorting = 1;
            if ($bannerSort) {
                $banner->sorting = $bannerSort->sorting + 1;
            }
        }
        if ($request->has('assignTo') && !empty($request->assignTo)) {
            $banner->link = $request->assignTo;
            $banner->redirect_category_id = ($request->assignTo == 'category') ? $request->category_id : NULL;
            $banner->redirect_vendor_id = ($request->assignTo == 'vendor') ? $request->vendor_id : NULL;
            $banner->link_url = ($request->assignTo == 'url') ? $request->link_url : NULL;
        }

        if ($request->hasFile('image')) {    /* upload logo file */
            $file = $request->file('image');
            $banner->image = Storage::disk('s3')->put('/banner', $file, 'public');
        }

        $saveRes = $banner->save();

        if($request->has('banner_service_area')){
            $banner->syncGeos()->sync($request->banner_service_area);
        }

        return $banner->id;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cms  $cms
     * @return \Illuminate\Http\Response
     */
    public function show(MobileBanner $banner)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cms  $cms
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id)
    {
        MobileBanner::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Banner deleted successfully!');
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
            $banner = MobileBanner::where('id', $value)->first();
            $banner->sorting = $key + 1;
            $banner->save();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Banner order updated Successfully!',
        ]);
    }
    /**
     * update the validity of banner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MobileBanner  $banner
     * @return \Illuminate\Http\Response
     */
    public function validity(Request $request)
    {
        $banner = MobileBanner::where('id', $request->banId)->first();
        $banner->validity_on = ($request->value == 1) ? 1 : 0;
        $banner->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Banner order updated Successfully!',
        ]);
    }
}
