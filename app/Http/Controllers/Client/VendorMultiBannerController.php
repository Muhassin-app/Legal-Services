<?php

namespace App\Http\Controllers\Client;

use Image;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Banner, VendorMultiBanner, Vendor, Category, ClientLanguage, ClientPreference, Client, ServiceAreaForBanner};
use DB,Log;
class VendorMultiBannerController extends BaseController
{
    private $folderName = 'banner';
    private $fstatus = 1;

    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/banner';
    }

    
   

 
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            DB::beginTransaction();
       
            $banner = new VendorMultiBanner();
            if ($request->hasFile('banner_image')) {    /* upload logo file */
                $file = $request->file('banner_image');
                $banner->image = Storage::disk('s3')->put('/banner', $file,'public');
            }
            $banner->vendor_id = $request->vendor_id;
            $banner->save();

            DB::commit();
            
            return response()->json([
                'status'=>'success',
                'message' => 'Banner created Successfully!',
                'data' => $banner
            ]);
            
           
         
        }
        catch(Exception $ex){
            DB::rollback();
            return response()->json([
                'status'=>'success',
                'message' => $ex->message(),
                'data' => []
            ]);
           
        }
    }

   
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cms  $cms
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$domain = '', $id)
    {
        try{
            DB::beginTransaction();
            VendorMultiBanner::where('id',$id)->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Banner deleted successfully!');
        }catch(Exception $ex){
            DB::rollback();
            return response()->json([
                'status'=>'success',
                'message' => $ex->message(),
                'data' => []
            ]);
            
        }
    }

 
     
}