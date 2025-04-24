<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabBookingLayoutTranslation extends Model
{
    use HasFactory;
    protected $table = 'cab_booking_layout_transaltions';


    public function layout(){
        return  $this->belongsTo('App\Models\CabBookingLayout', 'cab_booking_layout_id', 'id');
    }
}
