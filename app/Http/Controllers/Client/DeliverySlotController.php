<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlot;
use Illuminate\Http\Request;
use DataTables;

class DeliverySlotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $deliverySlot = DeliverySlot::where('parent_id', 0);
        if ($request->ajax()) {
            return Datatables::of($deliverySlot)
                ->addIndexColumn()
                ->addColumn('title', function ($deliverySlot) {
                    return $deliverySlot->title;
                })
                ->addColumn('start_time', function ($deliverySlot) {
                    return $deliverySlot->start_time;
                })
                ->addColumn('end_time', function ($deliverySlot) {
                    return $deliverySlot->end_time;
                })
                ->addColumn('price', function ($deliverySlot) {
                    return $deliverySlot->price;
                })
                ->addColumn('slot_interval', function ($deliverySlot) {
                    return $deliverySlot->slot_interval??'-';
                })
                ->addColumn('cutOff_time', function ($deliverySlot) {
                    return $deliverySlot->cutOff_time??'-';
                })
                ->addColumn('action', function ($deliverySlot) use ($request) {
                    $delete_url = route('delivery-slot.destroy', $deliverySlot->id);
                    $action = '<div class="form-ul" style="width: 60px;">
                    <div class="inner-div" style="float: left;">
                        <a class="action-icon addSlotBtn"
                            href="javascript:void(0);"
                            data-id="'.$deliverySlot->id.'" data-title="'.$deliverySlot->title.'" data-start-time="'.$deliverySlot->start_time.'" data-end-time="'.$deliverySlot->end_time.'" data-price="'.$deliverySlot->price.'" data-slot-duration="'.$deliverySlot->slot_interval.'" data-cut-off-time="'.$deliverySlot->cutOff_time.'"><i class="mdi mdi-square-edit-outline"></i></a>
                    </div>
                    <div class="inner-div">
                        <form id="deleteproduct_'.$deliverySlot->id.'" method="POST"
                            action="'. $delete_url.'">
                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                            <input type="hidden" name="_method" value="DELETE">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary-outline action-icon delete-product" data-destroy_url="'. $delete_url.'" data-rel="'.$deliverySlot->id.'"><i class="mdi mdi-delete"></i></button>
                            </div>
                        </form>
                    </div>
                </div>';
                return $action;
                })
                ->addColumn('status', function ($deliverySlot) {
                    if($deliverySlot->status == 1){
                        $status = 'Disabled';
                    }else{
                        $status = 'Active';
                    }
                    return $status;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('backend.delivery-slots.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $starttime = $request->start_time;  // your start time
        $endtime = $request->end_time;  // End time
        $duration = $request->slot_minutes??0;
        $cutOff_time = $request->cutoff_time??NULL;        
        $data = [
            'title' => $request->slot_title,
            'start_time' => $starttime,
            'end_time' => $endtime,
            'price' => $request->price,
            'slot_interval' => $duration,
            'cutOff_time' => $cutOff_time
        ];

        $deliverySlot = DeliverySlot::updateOrCreate([ 'id'   => $request->slot_id, ], $data);
        
        $time_interval = $this->timeInterval($starttime, $endtime, $duration);

        if (!empty($time_interval)) {
            $previous_time = '';
            if($request->slot_id != ''){
                DeliverySlot::where('parent_id', $request->slot_id)->delete();
            }
            foreach ($time_interval as $key => $time) {
                if($key == 0){
                    $previous_time = $time;
                    continue;
                }
                $slot_interval = [
                    'title' => $request->slot_title,
                    'start_time' => $previous_time,
                    'end_time' => $time,
                    'price' => $request->price,
                    'parent_id' => $deliverySlot->id
                ];
                DeliverySlot::create($slot_interval);
                $previous_time = $time;
            }
        }
        if($request->slot_id != ''){
            return redirect()->back()->with('success', 'Slot updated successfully');
        }
        return redirect()->back()->with('success', 'Slot added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeliverySlot  $deliverySlot
     * @return \Illuminate\Http\Response
     */
    public function show(DeliverySlot $deliverySlot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeliverySlot  $deliverySlot
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliverySlot $deliverySlot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeliverySlot  $deliverySlot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliverySlot $deliverySlot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliverySlot  $deliverySlot
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliverySlot $deliverySlots, $domain = '', $id)
    {
        $deliverySlots = DeliverySlot::find($id);
        $deliverySlots->delete();
        return redirect()->back()->with('success', 'Slot deleted successfully');
    }

    public function timeInterval($starttime, $endtime, $duration){
        $array_of_time = array ();
        $start_time    = strtotime ($starttime); //change to strtotime
        $end_time      = strtotime ($endtime); //change to strtotime
        $add_mins  = $duration * 60;
        while ($start_time <= $end_time) // loop between time
        {
            $array_of_time[] = date ("H:i", $start_time);
            $start_time += $add_mins; // to check endtie=me
        }
        return $array_of_time;
    }
}
