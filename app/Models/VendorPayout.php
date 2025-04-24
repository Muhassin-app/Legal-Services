<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    use HasFactory;

    public function vendor(){
	    return $this->hasOne('App\Models\Vendor' , 'id', 'vendor_id'); 
	}

	public function user(){
	    return $this->hasOne('App\Models\User' , 'id', 'requested_by')->withTrashed(); 
	}

    public function payoutOption(){
        return $this->hasOne('App\Models\PayoutOption' , 'id', 'payout_option_id');
    }

    public function getStatusAttribute($value){
        if($value == '1'){
            return 'Paid';
        }elseif($value == '2'){
            return 'Failed';
        }else{
            return 'Pending';
        }
    }
}
