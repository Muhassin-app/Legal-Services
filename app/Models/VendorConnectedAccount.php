<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorConnectedAccount extends Model
{
    use HasFactory;

    public function vendor(){
	    return $this->hasOne('App\Models\Vendor' , 'id', 'vendor_id'); 
	}

    public function user(){
	    return $this->hasOne('App\Models\User' , 'id', 'requested_by')->withTrashed(); 
	}

    public function payoutOption(){
        return $this->hasOne('App\Models\PayoutOption' , 'id', 'payment_option_id');
    }
    
}
