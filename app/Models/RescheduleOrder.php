<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RescheduleOrder extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo('App\Models\User', 'reschedule_by')->withTrashed();
    }

    public function order(){
        return $this->belongsTo('App\Models\Order', 'order_id');
    }

    public function vendor(){
        return $this->belongsTo('App\Models\Vendor', 'vendor_id');
    }


    
}
