<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleEmissionType extends Model
{
    use HasFactory;
    protected $table = "vehicle_emission_type";

    public function getEmissionTypeNameAttribute()
    {
        return "{$this->emission_type} - {$this->desc}";
    }

    public function products(){
        return $this->hasMany('App\Models\Product', 'emission_type_id', 'id');
    }
}
