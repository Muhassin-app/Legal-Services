<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class VendorSlot extends Model
{
    protected $fillable = ['vendor_id','category_id','geo_id','start_time','end_time','dine_in','takeaway','delivery','rental','pick_drop','on_demand','appointment','service_area_id'];
    
    public function day(){
        $client = Client::first();
        $mytime = Carbon::now()->setTimezone($client->timezone);
        return $this->hasMany('App\Models\SlotDay', 'slot_id', 'id')->where('day', $mytime->dayOfWeek+1); 
    }

    public function dayOne(){
        $client = Client::first();
        $mytime = Carbon::now()->setTimezone($client->timezone);
        return $this->hasOne('App\Models\SlotDay', 'slot_id', 'id')->where('day', $mytime->dayOfWeek+1); 
    }

    public function days(){
        return $this->hasOne('App\Models\SlotDay', 'slot_id', 'id'); 
    }
    public function deleteVendorSlots($vendor_id)
    {
        return $this->where('vendor_id',$vendor_id)->delete();
    }

    public function geos(){
        return $this->hasMany('App\Models\VendorSlotServiceArea', 'vendor_slot_id', 'id');
    }

    public function syncGeos(){
        return $this->belongsToMany('App\Models\VendorSlotServiceArea', 'vendor_slot_service_areas', 'vendor_slot_id', 'service_area_id')->withTimestamps();
    }

}
