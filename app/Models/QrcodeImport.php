<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrcodeImport extends Model
{
    use HasFactory;

    protected $fillable = ['code','vendor_id'];

    public function qrcode(){
        return $this->belongsTo('App\Models\AssignQrcodesToOrder');
     }

     public function vendorDetail(){
        return $this->hasOne('App\Models\Vendor','id','vendor_id');
     }
 
}
