<?php

namespace App\Http\Controllers\Client;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{ClientPreference, VendorSlotDate, Vendor, VendorSlot, SlotDay, ServiceArea, VendorSlotServiceArea, VendorSlotDateServiceArea};

class VendorSlotController extends BaseController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $domain = '', $id)
    {
        $vendor = Vendor::where('id', $id)->firstOrFail();

        $slotData = array();
        // $dine_in = $request->has('slot_type') ? (in_array('dine_in', $request->slot_type) ? '1' : 0) : 0;
        // $takeaway = $request->has('slot_type') ? (in_array('takeaway', $request->slot_type) ? '1' : 0) : 0;
        // $delivery = $request->has('slot_type') ? (in_array('delivery', $request->slot_type) ? '1' : 0) : 0;

        // if(empty($request->slot_date) || $request->slot_date == 'null'){
        if($request->stot_type == 'day'){
            $slot = new VendorSlot();
            $slot->vendor_id    = $vendor->id;
            $slot->start_time   = $request->start_time;
            $slot->end_time     = $request->end_time;
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $slot->$VendorTypesName      = $request->has('slot_type') ? (in_array($VendorTypesName, $request->slot_type) ? '1' : 0) : 0;;
            }
            // $slot->dine_in      = $dine_in;
            // $slot->takeaway     = $takeaway;
            // $slot->delivery     = $delivery;
            // $slot->service_area_id = $request->slot_service_area;
            $slot->save();

            if($request->has('slot_service_area')){
                $slot->syncGeos()->sync($request->slot_service_area);
            }

            foreach ($request->week_day as $key => $value) {
                $slotData['slot_id']    = $slot->id;
                $slotData['day']        = $value;
                SlotDay::insert($slotData);  
            }
        }else{
            // $slotData['vendor_id']          = $vendor->id;
            // $slotData['start_time']         = $request->start_time;
            // $slotData['end_time']           = $request->end_time;
            // $slotData['specific_date']      = $request->slot_date;
            // $slotData['dine_in']            = $dine_in;
            // $slotData['takeaway']           = $takeaway;
            // $slotData['delivery']           = $delivery;
            // $slotData['service_area_id']    = $request->slot_service_area;
            // $slotData['working_today']      = 1;

            // $slot_date = VendorSlotDate::insert($slotData);

            $slotDate = new VendorSlotDate();
            $slotDate->vendor_id          = $vendor->id;
            $slotDate->start_time         = $request->start_time;
            $slotDate->end_time           = $request->end_time;
            $slotDate->specific_date      = $request->slot_date;
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $slotDate->$VendorTypesName      = $request->has('slot_type') ? (in_array($VendorTypesName, $request->slot_type) ? '1' : 0) : 0;;
            }
            // $slotDate->dine_in            = $dine_in;
            // $slotDate->takeaway           = $takeaway;
            // $slotDate->delivery           = $delivery;
            $slotDate->working_today      = 1;
            $slotDate->save();
            if($request->has('slot_service_area')){
                $slotDate->syncGeos()->sync($request->slot_service_area);
            }
        }
        return redirect()->back()->with('success', 'Slot saved successfully!');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\VendorSlot  $vendorSlot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {
        $vendor = Vendor::where('id', $id)->firstOrFail();
        // $dine_in = $request->has('slot_type') ? (in_array('dine_in', $request->slot_type) ? '1' : 0) : 0;
        // $takeaway = $request->has('slot_type') ? (in_array('takeaway', $request->slot_type) ? '1' : 0) : 0;
        // $delivery = $request->has('slot_type') ? (in_array('delivery', $request->slot_type) ? '1' : 0) : 0;
      
        if($request->edit_type == 'day') {
            $slotDay = SlotDay::where('id', $request->edit_type_id)->where('day', $request->edit_day)->first();
            if(!$slotDay){
                $slotDay = new SlotDay();
                $slot = new VendorSlot();
            }
            else{
                $slot_id = $slotDay->slot_id;
                $slot = VendorSlot::where('id', $slot_id)->first();
                
                if($request->slot_type_edit == 'date'){
                    // delete slot day
                    $slotDay->delete();
                    // delete vendor slot
                    // $slot->delete();

                    $dateSlot = new VendorSlotDate();
                    $dateSlot->vendor_id        = $vendor->id;
                    $dateSlot->start_time       = $request->start_time;
                    $dateSlot->end_time         = $request->end_time;
                    $dateSlot->specific_date    = $request->slot_date;
                    foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                        $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                        $dateSlot->$VendorTypesName      = $request->has('slot_type') ? (in_array($VendorTypesName, $request->slot_type) ? '1' : 0) : 0;;
                    }
                    // $dateSlot->dine_in          = $dine_in;
                    // $dateSlot->takeaway         = $takeaway;
                    // $dateSlot->delivery         = $delivery;
                    // $dateSlot->service_area_id  = $request->edit_slot_service_area;
                    $dateSlot->working_today    = 1;
                    $dateSlot->save();
                    if($request->has('edit_slot_service_area')){
                        $dateSlot->syncGeos()->sync($request->edit_slot_service_area);
                    }

                    return redirect()->back()->with('success', 'Slot saved successfully!');
                }
            }

            $slot->vendor_id    = $vendor->id;
            $slot->start_time   = $request->start_time;
            $slot->end_time     = $request->end_time;
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $slot->$VendorTypesName      = $request->has('slot_type') ? (in_array($VendorTypesName, $request->slot_type) ? '1' : 0) : 0;;
            }
            // $slot->dine_in      = $dine_in;
            // $slot->takeaway     = $takeaway;
            // $slot->delivery     = $delivery;
            // $slot->service_area_id  = $request->edit_slot_service_area;
            $slot->save();
            if($request->has('edit_slot_service_area')){
                $slot->syncGeos()->sync($request->edit_slot_service_area);
            }

            $slotDay->slot_id =  $slot->id;
            $slotDay->day = $request->edit_day;
            $slotDay->save();

        }else{
            $dateSlot = VendorSlotDate::where('id', $request->edit_type_id)->first();

            if(!$dateSlot){
                $dateSlot = new VendorSlotDate();
            }
            else{
                if( $request->slot_type_edit == 'day' ){
                    $vendor_id = $dateSlot->vendor_id;
                    // delete date slot
                    $dateSlot->delete();
                    // delete day slot
                    $vendor_slot_day = SlotDay::whereHas('vendor_slot', function($q) use($vendor_id){
                        $q->where('vendor_slots.vendor_id', $vendor_id);
                    })->where('day', $request->edit_day)->delete();

                    $slot = new VendorSlot();
                    $slot->vendor_id    = $vendor->id;
                    $slot->start_time   = $request->start_time;
                    $slot->end_time     = $request->end_time;
                    foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                        $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                        $slot->$VendorTypesName      = $request->has('slot_type') ? (in_array($VendorTypesName, $request->slot_type) ? '1' : 0) : 0;;
                    }
                    // $slot->dine_in      = $dine_in;
                    // $slot->takeaway     = $takeaway;
                    // $slot->delivery     = $delivery;
                    // $slot->service_area_id  = $request->edit_slot_service_area;
                    $slot->save();

                    if($request->has('edit_slot_service_area')){
                        $slot->syncGeos()->sync($request->edit_slot_service_area);
                    }

                    $sday = new SlotDay();
                    $sday->slot_id =  $slot->id;
                    $sday->day = $request->edit_day;
                    $sday->save();
                    return redirect()->back()->with('success', 'Slot saved successfully!');
                }
            }
            $dateSlot->vendor_id        = $vendor->id;
            $dateSlot->start_time       = $request->start_time;
            $dateSlot->end_time         = $request->end_time;
            $dateSlot->specific_date    = $request->slot_date;
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $dateSlot->$VendorTypesName      = $request->has('slot_type') ? (in_array($VendorTypesName, $request->slot_type) ? '1' : 0) : 0;;
            }
            // $dateSlot->dine_in          = $dine_in;
            // $dateSlot->takeaway         = $takeaway;
            // $dateSlot->delivery         = $delivery;
            $dateSlot->working_today    = 1;
            // $dateSlot->service_area_id  = $request->edit_slot_service_area;
            $dateSlot->save();

            if($request->has('edit_slot_service_area')){
                $dateSlot->syncGeos()->sync($request->edit_slot_service_area);
            }

        }
        return redirect()->back()->with('success', 'Slot saved successfully!');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\VendorSlot  $vendorSlot
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $domain = '', $id)
    {
        if($request->slot_type == 'date'){
            if($request->old_slot_type == 'day')
            {
                VendorSlotDate::updateOrCreate([
                    'vendor_id'=>$id,
                    'specific_date' => $request->slot_date
                ],[
                    'working_today' => 0
                ]);
            }else{
                $dateSlot = VendorSlotDate::find($request->slot_id);
                $dateSlot->syncGeos()->detach();
                $dateSlot->delete();
            }
        } else {
            $slotDay = SlotDay::where('slot_id', $request->slot_id)->get();
            if($slotDay->count() == 1){
                $vendorSlot = VendorSlot::find($request->slot_id);
                $vendorSlot->syncGeos()->detach();
                $vendorSlot->delete();
            }
            $slot_day = SlotDay::where('id', $request->slot_day_id)->delete();
        }
        return redirect()->back()->with('success', 'Slot deleted successfully!');
    }

    public function returnJson(Request $request, $domain = '', $id)
    {
        $vendor = Vendor::findOrFail($id);
        $date = $day = array();

        if($request->has('start')){
            $start = explode('T', $request->start);
            $end = explode('T', $request->end);

            $startDate = date('Y-m-d', strtotime($start[0])); 
            $endDate = date('Y-m-d', strtotime($end[0]));

            $datetime1 = new \DateTime($startDate);
            $datetime2 = new \DateTime($endDate);

            $interval = $datetime2->diff($datetime1);
            $days = $interval->format('%a');

            $date[] = $startDate;
            $day[] = 1;

            for ($i = 1; $i < $days; $i++) {
                $date[] = date('Y-m-d', strtotime('+'.$i.' day', strtotime($startDate)));
                $day[] = $i + 1;
            }
        }else{
            $dayArray = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
            foreach ($dayArray as $key => $value) {
                $th = ($value == 'sunday') ? 'previous sunday' : $value.' this week';
                $date[] =  date( 'Y-m-d', strtotime($th));
                $day[] = $key + 1;
            }
        }

        $lst = count($date) - 1;
        $slot = VendorSlot::select('vendor_slots.*', 'slot_days.id as slot_day_id', 'slot_days.slot_id', 'slot_days.day')->with(['geos'])->join('slot_days', 'slot_days.slot_id', 'vendor_slots.id')->where('vendor_id', $id)->orderBy('slot_days.day', 'asc')->get();
        
        $slotDate = VendorSlotDate::with(['geos'])->whereBetween('specific_date', [$date[0], $date[$lst]])->orderBy('specific_date','asc')->get();

        $showData = array();
        $count = 0;

        $client_preferences = ClientPreference::first();
        if($client_preferences){
            $dinein_check = $client_preferences->dinein_check;
            $takeaway_check = $client_preferences->takeaway_check;
            $delivery_check = $client_preferences->delivery_check;
        }

        foreach ($day as $key => $value) {
            $exist = 0;
            $start = $end = $color = '';

            if($slotDate){
                foreach ($slotDate as $k => $v) {
                    $title = '';
                    if($date[$key] == $v->specific_date){
                        $exist = 1;
                        $title .= ($dinein_check == 1 && $v->dine_in == 1 && $vendor->dine_in == 1) ? ' Dine' : '';
                        $title .= ($takeaway_check == 1 && $v->takeaway == 1 && $vendor->takeaway == 1) ? ' Takeaway' : '';
                        $title .= ($delivery_check == 1 && $v->delivery == 1 && $vendor->delivery == 1) ? ' Delivery' : '';

                        $showData[$count]['title'] = trim($title);
                        $showData[$count]['start'] = $date[$key].'T'.$v->start_time;
                        $showData[$count]['end'] = $date[$key].'T'.$v->end_time;
                        $showData[$count]['color'] = ($v->working_today == 0) ? '#43bee1' : '';
                        $showData[$count]['type'] = 'date';
                        $showData[$count]['type_id'] = $v->id;
                        $showData[$count]['slot_id'] = $v->id;
                        $showData[$count]['slot_dine_in'] = ($dinein_check == 1 && $v->dine_in == 1 && $vendor->dine_in == 1) ? 1 : 0;
                        $showData[$count]['slot_takeaway'] = ($takeaway_check == 1 && $v->takeaway == 1 && $vendor->takeaway == 1) ? 1 : 0;
                        $showData[$count]['slot_delivery'] = ($delivery_check == 1 && $v->delivery == 1 && $vendor->delivery == 1) ? 1 : 0;
                        $showData[$count]['service_area'] = $v->geos->pluck('service_area_id')->toArray();
                        $count++;
                    }
                }
            }

            if($exist == 0){
                foreach ($slot as $k => $v) {
                    $title = '';
                    if($value == $v->day){

                        $title .= ($dinein_check == 1 && $v->dine_in == 1 && $vendor->dine_in == 1) ? ' Dine' : '';
                        $title .= ($takeaway_check == 1 && $v->takeaway == 1 && $vendor->takeaway == 1) ? ' Takeaway' : '';
                        $title .= ($delivery_check == 1 && $v->delivery == 1 && $vendor->delivery == 1) ? ' Delivery' : '';

                        $showData[$count]['title'] = trim($title);
                        $showData[$count]['start'] = $date[$key].'T'.$v->start_time;
                        $showData[$count]['end'] = $date[$key].'T'.$v->end_time;
                        $showData[$count]['type'] = 'day';
                        $showData[$count]['color'] = ($v->working_today == 0) ? '#43bee1' : '';
                        $showData[$count]['type_id'] = $v->slot_day_id;
                        $showData[$count]['slot_id'] = $v->slot_id;
                        $showData[$count]['slot_dine_in'] = ($dinein_check == 1 && $v->dine_in == 1 && $vendor->dine_in == 1) ? 1 : 0;
                        $showData[$count]['slot_takeaway'] = ($takeaway_check == 1 && $v->takeaway == 1 && $vendor->takeaway == 1) ? 1 : 0;
                        $showData[$count]['slot_delivery'] = ($delivery_check == 1 && $v->delivery == 1 && $vendor->delivery == 1) ? 1 : 0;
                        $showData[$count]['service_area'] = $v->geos->pluck('service_area_id')->toArray();
                        $count++;
                    }
                }
            } 
        }
        echo $json  = json_encode($showData);
    }
}
