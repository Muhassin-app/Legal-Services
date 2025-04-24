<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\{Pincode, PincodeDeliveryOption};
use Illuminate\Http\Request;
use DataTables;

class PincodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $search = $request->search??'';
        $pincode = Pincode::with('deliveryOptions')->where('vendor_id', $request->vendor_id);
        if(!empty($search)){
            $pincode = $pincode->where('pincode', 'like', '%' . $search . '%');
        }
        if ($request->ajax()) {
            return Datatables::of($pincode)
                ->addIndexColumn()

                ->addColumn('action', function ($pincode) use ($request) {
                    $delete_url = route('pincode.destroy', $pincode->id);
                    $action = '<div class="form-ul" style="width: 60px;">
                    <div class="inner-div" style="float: left;">
                        <a class="action-icon editPincodeBtn"
                            href="javascript:void(0);"
                            data-id="'.$pincode->id.'"><i class="mdi mdi-square-edit-outline"></i></a>
                    </div>
                    <div class="inner-div">
                        <form id="deleteproduct_'.$pincode->id.'" method="POST"
                            action="'. $delete_url.'">
                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                            <input type="hidden" name="_method" value="DELETE">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary-outline action-icon delete-product" data-destroy_url="'. $delete_url.'" data-rel="'.$pincode->id.'"><i class="mdi mdi-delete"></i></button>
    
                            </div>
                        </form>
                    </div>
                </div>';
                return $action;
                })


                ->addColumn('status', function ($pincode) {
                    if($pincode->is_disabled == 1){
                        $status = 'Disabled';
                    }else{
                        $status = 'Active';
                    }
                    return $status;
                })

                ->addColumn('type', function ($pincode) {
                    return $pincode->deliveryOptions->pluck('delivery_option_type_text')->toArray();
                })
                ->rawColumns(['action', 'status', 'type'])
                ->make(true);
        }
        return view('backend.pincode.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $pincode = Pincode::updateOrCreate([ 'id'   => $request->pincode_id, ],[
            'pincode' => $request->pincode,
            'vendor_id' => $request->vendor_id
        ]);

        if($request->pincode_id != ''){
            PincodeDeliveryOption::where('pincode_id', $request->pincode_id)->delete();
        }
        $delivery_option_ids = $request->delivery_option_ids;
        foreach($delivery_option_ids as $delivery_option_id){
            PincodeDeliveryOption::updateOrCreate([ 'id'   => $request->pincode_id, ],[
                'pincode_id' => $pincode->id,
                'delivery_option_type' => $delivery_option_id
            ]); 
        }
        if($request->pincode_id != ''){
            return redirect()->back()->with('success', 'Pincode updated successfully');
        }
        return redirect()->back()->with('success', 'Pincode added successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pincode  $pincode
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pincode $pincodes, $domain = '', $id)
    {

        $returnReason = Pincode::find($id); 
        $returnReason->delete();
        return redirect()->back()->with('success', 'Pincode deleted successfully');
    }

    public function pincodeData(Request $request){

        if($request->ajax()){
            $pincode = Pincode::with('deliveryOptions')->where('id', $request->id)->first();
            return view('backend.pincode.edit-pincode-ajax')->with('pincode', $pincode); 
        } 
    }
}
