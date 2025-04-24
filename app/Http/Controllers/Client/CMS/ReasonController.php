<?php

namespace App\Http\Controllers\Client\CMS;

use App\Http\Controllers\Controller;
use App\Models\ReturnReason;
use Illuminate\Http\Request;
use DataTables;

class ReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $reason = ReturnReason::where('status', 'Active');
        if ($request->ajax()) {
            return Datatables::of($reason)
                ->addIndexColumn()

                ->addColumn('action', function ($reason) use ($request) {
                    $delete_url = route('reason.destroy', $reason->id);
                    $action = '<div class="form-ul" style="width: 60px;">
                    <div class="inner-div" style="float: left;">
                        <a class="action-icon editReasonBtn"
                            href="javascript:void(0);"
                            data-id="'.$reason->id.'" data-title="'.$reason->title.'" data-type="'.$reason->type.'"><i
                                class="mdi mdi-square-edit-outline"></i></a>
                    </div>
                    <div class="inner-div">
                        <form id="deleteproduct_'.$reason->id.'" method="POST"
                            action="'. $delete_url.'">
                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                            <input type="hidden" name="_method" value="DELETE">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary-outline action-icon delete-product" data-destroy_url="'. $delete_url.'" data-rel="'.$reason->id.'"><i class="mdi mdi-delete"></i></button>
    
                            </div>
                        </form>
                    </div>
                </div>';
                return $action;
                })


                ->addColumn('status', function ($reason) {
                    $status = $reason->status;
                    return $status;
                })
                ->addColumn('type', function ($reason) {
                    if($reason->type == 1){
                        $type = "Return";
                    }elseif($reason->type == 2){
                        $type = "Exchange";
                    }else{
                        $type = "Cancellation";
                    }
                    return $type;
                })
                ->addColumn('title', function ($reason) {
                    $btn  = $reason->title;
                    return $btn;
                })
                ->rawColumns(['action','type','title', 'status'])
                ->make(true);
        }
        return view('backend.reason.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $data = [
            'title' => $request->title,
            'type' => $request->type
        ];
        $reason = ReturnReason::updateOrCreate([ 'id'   => $request->reason_id, ],[
            'title'     => $request->title,
            'type'     => $request->type
        ]);
        if($request->reason_id != ''){
            return redirect()->back()->with('success', 'Reason updated successfully');
        }
        return redirect()->back()->with('success', 'Reason added successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReturnReason  $returnReason
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReturnReason $returnReason,$domain = '', $id)
    {
        $returnReason = ReturnReason::find($id);
        $returnReason->delete();
        return redirect()->back()->with('success', 'Reason deleted successfully');
    }
}
