<?php

namespace App\Http\Controllers\Front;

use DB;
use Session;
use DataTables;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Models\{VendorCityTranslations,VendorCities,Vendor,VendorCategory,CabBookingLayout};

class VendorCitiesController extends FrontController
{
    use ApiResponser;

     /**
     * Get cities by slug.
     *
     * @param  int  $slug
     * @return \Illuminate\Http\Response
     */
    public function getCities(Request $request,$domain, $slug ){
        try {
            $langId = Session::get('customerLanguage');
            $VendorCities = VendorCities::where(['slug' => $slug])->with(['translations'=>function($q)use ($langId){
                $q->where('language_id',$langId);
            }])->firstOrFail();
            if(!$VendorCities){
                abort(404);
            }
           //pr($VendorCities->toArray());
            
            $vendorType = Session::get('vendorType');
            if(!$vendorType){
            $vendorType = 'delivery';
            }
            $preferences = (object)Session::get('preferences');
            $navCategories = $this->categoryNav($langId);
            $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 30;
            $latitude = $VendorCities->latitude;
            $longitude = $VendorCities->longitude;
            $ses_vendors = $this->getServiceAreaVendorsWithoutHyperlocal( $latitude, $longitude);

            $categoryTypes = getServiceTypesCategory($vendorType);
            
            $vendors = Vendor::whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                $q->whereIn('type_id',$categoryTypes);
            })->with('products')->select('id', 'name', 'banner', 'address', 'order_pre_time','is_show_vendor_details' ,'order_min_amount', 'logo', 'slug', 'latitude', 'longitude')->where(['status'=> 1,$vendorType => 1]);

          
           
            $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
            
            //3961 for miles and 6371 for kilometers
            $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
            $vendors = $vendors->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                    cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                    sin( radians(' . $latitude . ') ) *
                    sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))
                    ->orderBy('vendorToUserDistance', 'ASC');
            $vendors = $vendors->whereIn('id', $ses_vendors);

        $vendors = $vendors->paginate($pagiNate);

        foreach ($vendors as $key => $value) {
            $value = $this->getLineOfSightDistanceAndTime($value, $preferences);
            $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $value->id)->where('status', 1)->get();
            $categoriesList = '';
            foreach($vendorCategories as $key => $category){
                if($category->category){
                    $categoriesList = $categoriesList . (!is_null($category->category->translation_one) ? $category->category->translation_one->name : '');
                    if( $key !=  $vendorCategories->count()-1 ){
                        $categoriesList = $categoriesList . ', ';
                    }
                }
            }
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $value = $this->getVendorDistanceWithTime($latitude, $longitude, $value, $preferences);
            }
            $value->categoriesList = $categoriesList;
            $value->vendorRating = $this->vendorRating($value->products);
        }
        $for_no_product_found_html = CabBookingLayout::with('translations')->where('is_active', 1)->web()->where('for_no_product_found_html',1)->orderBy('order_by')->get();
        $page_title = ($VendorCities->translations->first() ? $VendorCities->translations->first()->name : $VendorCities->slug).' '.getNomenclatureName('Vendors', true);  
        return view('frontend/vendor-all')->with(['navCategories' => $navCategories,'for_no_product_found_html' => $for_no_product_found_html,'vendors' => $vendors,'page_title'=> $page_title ]);
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$domain, $id)
    {
        
       
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        pr($id);
    }
   
}
