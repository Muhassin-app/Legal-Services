<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelMode extends Model
{
    use HasFactory;
    protected $table = "travel_mode";

    public function getTravelModeNameAttribute()
    {
        return "{$this->travelmode} - {$this->desc}";
    }

    public function products(){
        return $this->hasMany('App\Models\Product', 'travel_mode_id', 'id');
    }
}
