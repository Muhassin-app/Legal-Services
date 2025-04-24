<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorFacilty extends Model
{
    use HasFactory;
    public function Facilty(){
        return $this->hasMany('App\Models\Facilty', 'facilty_id', 'id');
      }
  
}
