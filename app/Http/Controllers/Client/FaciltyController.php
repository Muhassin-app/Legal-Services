<?php

namespace App\Http\Controllers\Client;

use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Facilty,FaciltyTranslation,VendorFacilty};
use GuzzleHttp\Client as GCLIENT;
use Illuminate\Support\Str;
use DB;

use App\Http\Traits\ApiResponser;
class FaciltyController extends BaseController
{  
    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function store(Request $request){
        try {

            $this->validate($request, [
              'name.0' => 'required|string|max:60',
            ],['name.0' => 'The default language name field is required.']);
           
            DB::beginTransaction();
            $facilty = new Facilty();
            if ($request->hasFile('facilty_image')) {   
                 /* upload logo file */
                $file = $request->file('facilty_image');
                $facilty->image = Storage::disk('s3')->put('/facilty/icon', $file, 'public');
            }else{
                $facilty->image = 'default/default_image.png';
            }
            $facilty->save();
            $language_id = $request->language_id;
            foreach ($request->name as $k => $name) {
                if($name){
                    $FaciltyTranslation = new FaciltyTranslation();
                    $FaciltyTranslation->name = $name;
                    $FaciltyTranslation->slug = Str::slug($name, '-');
                    $FaciltyTranslation->language_id = $language_id[$k];
                    $FaciltyTranslation->facilties_id = $facilty->id;
                    $FaciltyTranslation->save();
                }
            }

            DB::commit();
            return $this->successResponse($facilty, 'Vendor Tag Added Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
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
      
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category_translation  $category_translation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'name.0' => 'required|string|max:60',
          ],['name.0' => 'The default language name field is required.']);
       
        $facilty = Facilty::where('id', $request->facilty_id)->first();
        if ($request->hasFile('facilty_image')) {   
                /* upload logo file */
            $file = $request->file('facilty_image');
            $facilty->image = Storage::disk('s3')->put('/facilty/icon', $file, 'public');
        }
        $facilty->save();
        $language_id = $request->language_id;
       
        if ($facilty) {
            if ($request->has('language_id')) {
                foreach ($request->language_id as $key => $value) {
                    $FaciltyTranslation = FaciltyTranslation::where('facilties_id', $facilty->id)->where('language_id', $value)->first();
                    if (!$FaciltyTranslation) {
                        $FaciltyTranslation = new FaciltyTranslation();
                        $FaciltyTranslation->slug = Str::slug($request->name[$key], '-');
                        $FaciltyTranslation->language_id = $value;
                        $FaciltyTranslation->facilties_id = $facilty->id;
                    }
                    $FaciltyTranslation->name = $request->name[$key];
                    $FaciltyTranslation->save();
                }
            }
            
            return $this->successResponse([], __('Vendor Tag updated Successfully!'));
        }
        return $this->successResponse([], __('Vendor Tag updated Successfully!'));
    }

    /**
     * save and update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category_translation  $category_translation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        try {
            Facilty::where('id',$request->facilty_id)->delete();
            FaciltyTranslation::where('facilties_id',$request->facilty_id)->delete();
            VendorFacilty::where('facilty_id',$request->facilty_id)->delete();
            return $this->successResponse([],__('Vendor Tag deleted successfully!'));
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }

    public function show(Request $request){
       
       try {
            $language_id = $request->language_id;
            $facilty = Facilty::with(['translations'])->where(['id' => $request->facilty_id])->firstOrFail();
           
            return $this->successResponse($facilty, '');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }

}
