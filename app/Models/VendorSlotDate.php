<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSlotDate extends Model
{
    protected $fillable = ['vendor_id','category_id','start_time','end_time','specific_date','working_today','dine_in','takeaway','delivery','service_area_id'];

    public function deleteVendorSlotDates($vendor_id)
    {
    	return $this->where('vendor_id',$vendor_id)->delete();
    }

    public function geos(){
        return $this->hasMany('App\Models\VendorSlotDateServiceArea', 'vendor_slot_date_id', 'id');
    }

    public function syncGeos(){
        return $this->belongsToMany('App\Models\VendorSlotDateServiceArea', 'vendor_slot_date_service_areas', 'vendor_slot_date_id', 'service_area_id')->withTimestamps();
    }
}
