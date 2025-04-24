<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCountries extends Model
{
    use HasFactory;

    protected $fillable = ['client_code', 'country_id', 'is_primary', 'is_active'];

    public function country(){
      return $this->belongsTo('App\Models\Country','country_id','id')->select('id', 'code', 'nicename','iso3');
    }
}
