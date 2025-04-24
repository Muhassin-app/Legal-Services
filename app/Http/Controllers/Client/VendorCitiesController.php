<?php

namespace App\Http\Controllers\Client;

use DB;
use DataTables;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{VendorCityTranslations,VendorCities};

class VendorCitiesController extends BaseController
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $vendorCity = VendorCities::with('primary')->get();
        
        return Datatables::of($vendorCity)
        ->addIndexColumn()
        ->addColumn('city_title', function ($vendorCity) {
           
            return $vendorCity->primary ? $vendorCity->primary->name : $vendorCity->slug;
        })
        ->addColumn('city_image', function ($vendorCity)  {
            $image = '';
            if($vendorCity->image){
                $image_path = $vendorCity->image['proxy_url'] . '30/30' . $vendorCity->image['image_path'];
                $image = '<img  class="rounded-circle" src="'. $image_path.'">';
            }
            return $image;
        })
        ->addColumn('edit_action', function ($vendorCity)  {
            return '';
        })
        ->rawColumns(['city_title','city_image'])
        ->make(true);
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
       
        try {

            $this->validate($request, 
                [
                'name.0' => 'required|string|max:60',
                'address' =>'required',
                'latitude' =>'required',
                'longitude' =>'required',
                ],
                [
                    'name.0' => 'The default language name field is required.'
                ]
            );
           
            DB::beginTransaction();
            $VendorCities = new VendorCities();
            if ($request->hasFile('vendor_city_image')) {   
                 /* upload logo file */
                $file = $request->file('vendor_city_image');
                $VendorCities->image = Storage::disk('s3')->put('/Cities/images', $file, 'public');
            }else{
                $VendorCities->image = 'default/default_image.png';
            }
            $slug = $request->name[0];
            $VendorCities->slug = Str::slug($slug, '-'); 
            $VendorCities->address = $request->address ;
            $VendorCities->latitude = $request->latitude ;
            $VendorCities->longitude = $request->longitude ;
            $VendorCities->place_id  = $request->place_id ;

            $VendorCities->save();
            $language_id = $request->language_id;
            foreach ($request->name as $k => $name) {
                if($name){
                    $CityTranslation = new VendorCityTranslations();
                    $CityTranslation->language_id = $language_id[$k];
                    $CityTranslation->vendor_city_id = $VendorCities->id;
                    $CityTranslation->name = $name;
                    $CityTranslation->save();
                }
            }

            DB::commit();
            return $this->successResponse($VendorCities, 'City Added Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
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
        
        $this->validate($request, [
                'name.0' => 'required|string|max:60',
                'address' =>'required',
                'latitude' =>'required',
                'longitude' =>'required',
          ],['name.0' => 'The default language name field is required.']);
       
        $VendorCities = VendorCities::where('id', $request->vendor_city_id)->first();
        if ($request->hasFile('vendor_city_image')) {   
                /* upload logo file */
            $file = $request->file('vendor_city_image');
            $VendorCities->image = Storage::disk('s3')->put('/Cities/images', $file, 'public');
        }
        $VendorCities->address = $request->address ;
        $VendorCities->latitude = $request->latitude ;
        $VendorCities->longitude = $request->longitude ;
        $VendorCities->save();
        $language_id = $request->language_id;
       
        if ($VendorCities) {
            if ($request->has('language_id')) {
                foreach ($request->language_id as $key => $value) {
                    if(isset( $request->name[$key])){
                        $CityTranslation = VendorCityTranslations::where('vendor_city_id', $VendorCities->id)->where('language_id', $value)->first();
                        if (!$CityTranslation) {
                            $CityTranslation = new VendorCityTranslations();
                            $CityTranslation->language_id = $value;
                            $CityTranslation->vendor_city_id = $VendorCities->id;
                        }
                        $CityTranslation->name = $request->name[$key];
                        $CityTranslation->save();
                    }
                    
                }
            }
            
            return $this->successResponse([], __('City updated Successfully!'));
        }
        return $this->successResponse([], __('City updated Successfully!'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$domain = '', $id)
    {
        
        try {
            $VendorCities = VendorCities::with(['translations'])->where(['id' => $id])->firstOrFail();
            return $this->successResponse($VendorCities, '');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
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
    // public function updateq(Request $request, $id)
    // {
    //     //
    // }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '',$id)
    {
        try {
           
            VendorCities::where('id',$id)->delete();
            VendorCityTranslations::where('vendor_city_id',$id)->delete();
           
            return response()->json(array('success' => true,'message'=>'Deleted successfully.'));
        } catch (Exception $e) {
            return response()->json(array('success' => false,'message'=>$e->getMessage()));
            //return $this->errorResponse([], $e->getMessage());
        }
       
    }


    
}
