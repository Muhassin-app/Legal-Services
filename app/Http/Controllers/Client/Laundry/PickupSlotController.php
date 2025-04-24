<?php

namespace App\Http\Controllers\Client\Laundry;

use DateTime;
use App\Models\Vendor;
use App\Models\SlotDay;
use App\Models\VendorSlot;
use Illuminate\Http\Request;
use App\Models\VendorSlotDate;
use App\Models\ClientPreference;
use App\Http\Controllers\Controller;

class PickupSlotController extends Controller
{
    public function store(Request $request, $domain = '', $id)
    {

        $vendor = Vendor::where('id', $id)->firstOrFail();

        $slotData = array();
        $dine_in = $request->has('pickup_slot_type') ? (in_array('dine_in', $request->pickup_slot_type) ? '1' : 0) : 0;
        $takeaway = $request->has('pickup_slot_type') ? (in_array('takeaway', $request->pickup_slot_type) ? '1' : 0) : 0;
        $delivery = $request->has('pickup_slot_type') ? (in_array('delivery', $request->pickup_slot_type) ? '1' : 0) : 0;
        $laundry = $request->has('pickup_slot_type') ? (in_array('laundry', $request->pickup_slot_type) ? '1' : 0) : 0;

        // if(empty($request->slot_date) || $request->slot_date == 'null'){
        if($request->pickup_stot_type == 'day'){
            $slot = new VendorSlot();
            $slot->vendor_id    = $vendor->id;
            $slot->start_time   = $request->pickup_start_time;
            $slot->end_time     = $request->pickup_end_time;
            $slot->dine_in      = $dine_in;
            $slot->takeaway     = $takeaway;
            $slot->delivery     = $delivery;
            $slot->laundry      = $laundry;
            $slot->slot_type     = '1';
            $slot->save();

            foreach ($request->pickup_week_day as $key => $value) {
                $slotData['slot_id']    = $slot->id;
                $slotData['day']        = $value;
                SlotDay::insert($slotData);  
            }
        }else{
            $slotData['vendor_id']          = $vendor->id;
            $slotData['start_time']         = $request->pickup_start_time;
            $slotData['end_time']           = $request->pickup_end_time;
            $slotData['specific_date']      = $request->pickup_slot_date;
            $slotData['dine_in']            = $dine_in;
            $slotData['takeaway']           = $takeaway;
            $slotData['delivery']           = $delivery;
            $slotData['laundry']            = $laundry;
            $slotData['slot_type']          = '1';
            $slotData['working_today']      = 1;

            VendorSlotDate::insert($slotData);
        }
        return redirect()->back()->with('success', 'Slot saved successfully!');

    }

    public function update(Request $request, $domain = '', $id)
    {
        $vendor = Vendor::where('id', $id)->firstOrFail();
        $dine_in = $request->has('pickup_slot_type') ? (in_array('dine_in', $request->pickup_slot_type) ? '1' : 0) : 0;
        $takeaway = $request->has('pickup_slot_type') ? (in_array('takeaway', $request->pickup_slot_type) ? '1' : 0) : 0;
        $delivery = $request->has('pickup_slot_type') ? (in_array('delivery', $request->pickup_slot_type) ? '1' : 0) : 0;
        $laundry = $request->has('pickup_slot_type') ? (in_array('laundry', $request->pickup_slot_type) ? '1' : 0) : 0;

        if($request->pickup_edit_type == 'day') {
            $slotDay = SlotDay::where('id', $request->pickup_edit_type_id)->where('day', $request->pickup_edit_day)->first();
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
                    $dateSlot->start_time       = $request->pickup_start_time;
                    $dateSlot->end_time         = $request->pickup_end_time;
                    $dateSlot->specific_date    = $request->pickup_slot_date;
                    $dateSlot->dine_in          = $dine_in;
                    $dateSlot->takeaway         = $takeaway;
                    $dateSlot->delivery         = $delivery;
                    $dateSlot->laundry          = $laundry;
                    $dateSlot->working_today    = 1;
                    $dateSlot->save();

                    return redirect()->back()->with('success', 'Slot saved successfully!');
                }
            }

            $slot->vendor_id    = $vendor->id;
            $slot->start_time   = $request->pickup_start_time;
            $slot->end_time     = $request->pickup_end_time;
            $slot->dine_in      = $dine_in;
            $slot->takeaway     = $takeaway;
            $slot->delivery     = $delivery;
            $slot->laundry     = $laundry;
            $slot->slot_type     = '1';
            $slot->save();

            $slotDay->slot_id =  $slot->id;
            $slotDay->day = $request->pickup_edit_day;
            $slotDay->save();

        }else{
            $dateSlot = VendorSlotDate::where('id', $request->pickup_edit_type_id)->first();
            if(!$dateSlot){
                $dateSlot = new VendorSlotDate();
            }else{
                if( $request->pickup_slot_type_edit == 'day' ){
                    $vendor_id = $dateSlot->vendor_id;
                    // delete date slot
                    $dateSlot->delete();
                    // delete day slot
                    $vendor_slot_day = SlotDay::whereHas('vendor_slot', function($q) use($vendor_id){
                        $q->where('vendor_slots.vendor_id', $vendor_id);
                    })->where('day', $request->pickup_edit_day)->delete();

                    $slot = new VendorSlot();
                    $slot->vendor_id    = $vendor->id;
                    $slot->start_time   = $request->pickup_start_time;
                    $slot->end_time     = $request->pickup_end_time;
                    $slot->dine_in      = $dine_in;
                    $slot->takeaway     = $takeaway;
                    $slot->delivery     = $delivery;
                    $slot->laundry      = $laundry;
                    $slot->slot_type     = '1';
                    $slot->save();

                    $sday = new SlotDay();
                    $sday->slot_id =  $slot->id;
                    $sday->day = $request->pickup_edit_day;
                    $sday->save();
                    return redirect()->back()->with('success', 'Slot saved successfully!');
                }
            }
            $dateSlot->vendor_id        = $vendor->id;
            $dateSlot->start_time       = $request->pickup_start_time;
            $dateSlot->end_time         = $request->pickup_end_time;
            $dateSlot->specific_date    = $request->pickup_slot_date;
            $dateSlot->dine_in          = $dine_in;
            $dateSlot->takeaway         = $takeaway;
            $dateSlot->delivery         = $delivery;
            $dateSlot->laundry          = $laundry;
            $dateSlot->working_today    = 1;
            $dateSlot->save();

        }
        return redirect()->back()->with('success', 'Slot saved successfully!');

    }

    public function destroy(Request $request, $domain = '', $id)
    {
        if($request->pickup_slot_type == 'date'){
            if($request->pickup_old_slot_type == 'day')
            {
                VendorSlotDate::updateOrCreate([
                    'vendor_id'=>$id,
                    'specific_date' => $request->pickup_slot_date
                ],[
                    'working_today' => 0
                ]);
            }else{
                $dateSlot = VendorSlotDate::where('id', $request->pickup_slot_id)->delete();
            }
        } else {
            $slotDay = SlotDay::where('slot_id', $request->pickup_slot_id)->get();
            if($slotDay->count() == 1){
                $vendorSlot = VendorSlot::where('id', $request->pickup_slot_id)->where('slot_type', '1')->delete();
            }
            $slot_day = SlotDay::where('id', $request->pickup_slot_day_id)->delete();
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
        $slot = VendorSlot::join('slot_days', 'slot_days.slot_id', 'vendor_slots.id')->where('vendor_id', $id)->orderBy('slot_days.day', 'asc')->where('slot_type', '1')->get();
        
        $slotDate = VendorSlotDate::whereBetween('specific_date', [$date[0], $date[$lst]])->orderBy('specific_date','asc')->get();

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
                        $showData[$count]['type_id'] = $v->id;
                        $showData[$count]['slot_id'] = $v->slot_id;
                        $showData[$count]['slot_dine_in'] = ($dinein_check == 1 && $v->dine_in == 1 && $vendor->dine_in == 1) ? 1 : 0;
                        $showData[$count]['slot_takeaway'] = ($takeaway_check == 1 && $v->takeaway == 1 && $vendor->takeaway == 1) ? 1 : 0;
                        $showData[$count]['slot_delivery'] = ($delivery_check == 1 && $v->delivery == 1 && $vendor->delivery == 1) ? 1 : 0;
                        $showData[$count]['slot_type'] = $v->slot_type;
                        $count++;
                    }
                }
            } 
        }
        echo $json  = json_encode($showData);
    }
}
