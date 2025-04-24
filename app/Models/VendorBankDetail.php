<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBankDetail extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'bank_name',
        'IBAN',
        'address',
    ];
    use HasFactory;

    public function vendor(){
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id'); 
    }
}
