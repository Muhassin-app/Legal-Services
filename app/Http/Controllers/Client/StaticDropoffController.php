<?php

namespace App\Http\Controllers\Client;

use Auth;
use DB;
use Session;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\BaseController;
use App\Models\{StaticDropoffLocation};

class StaticDropoffController extends BaseController{
    use ApiResponser;
    public function index(Request $request)
    {
        $staticLocation = StaticDropoffLocation::get();
        return Datatables::of($staticLocation)
        ->editColumn('title', function($row){
            $btn = '<a class="edit_static_dropoff_btn" data-static_dropoff_id="'.$row->id.'" href="javascript:void(0)">'.$row->title.'</a>';
            return $btn;
        })
        ->addColumn('action', function ($row) {
            $delete_url = route('static-dropoff.destroy', $row->id);
            $action = '<div>
                            <div class="inner-div" style="float: left;"> 
                                <a class="action-icon edit_static_dropoff_btn"  data-static_dropoff_id="'.$row->id.'" href="javascript:void(0)">
                                <i class="mdi mdi-square-edit-outline"></i>
                                </a>
                            </div>
                            <div class="inner-div">
                                <div class="inner-div">
                                    <form id="deleteStaticLocation_'.$row->id.'" method="POST"
                                        action="'. $delete_url.'">
                                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                                        <input type="hidden" name="_method" value="DELETE">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary-outline action-icon delete-static_location" data-destroy_url="'. $delete_url.'" data-rel="'.$row->id.'"><i class="mdi mdi-delete"></i></button>
                                            
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
            
            return $action;
        })
        ->addIndexColumn()
        ->rawColumns(['title','action'])
        ->make(true);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $domain = ''){
        try {
            $validatedData = $request->validate([
                    'location_title' => 'required',
                    'static_address' => 'required',
                    'static_latitude' => 'required',
                    'static_longitude' => 'required',
            ], [
                'static_address.required' => __('The address field is required.'),
                'location_title.required' => __('Address Type is required'),
                'static_latitude.required' => __('The city field is required.'),
                'static_longitude.required' => __('The state field is required.'),
                
            ]);
            DB::beginTransaction();
            $StaticDropoffLocation = StaticDropoffLocation::updateOrCreate(
                [
                    'id' => $request->static_address_id
                    //'latitude'  => $request->static_latitude,
                    //'longitude' => $request->static_longitude,
                    //'place_id'  => $request->static_place_id,
                ],
                [
                    'title'     => $request->location_title,
                    'address'   => $request->static_address,
                    'latitude'  => $request->static_latitude,
                    'longitude' => $request->static_longitude,
                    'place_id' => $request->static_place_id
                ]
            );
            DB::commit();
            $mgs = __('Location added successfully');
            if(!empty($request->static_address_id)){
                $mgs = __('Location updated successfully');
            }
            return $this->successResponse($StaticDropoffLocation, $mgs);
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }

    public function edit(Request $request){
        try {

            $staticLocation = StaticDropoffLocation::where(['id' => $request->static_dropoff_id])->firstOrFail();
            return $this->successResponse($staticLocation, 'Static Location get Successfully.');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
   
    public function delete($domain = '', $id){
        $address = StaticDropoffLocation::where('id', $id)->delete();
        return redirect()->route('configure.customize')->with('success', __('Static Location Has Been Deleted Successfully!') );
    }


}
