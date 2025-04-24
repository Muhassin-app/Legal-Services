<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCancelRequest extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'order_vendor_id', 'vendor_id', 'reject_reason', 'status', 'vendor_reject_reason', 'return_reason_id'];

    public function order(){
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public function order_vendor(){
        return $this->belongsTo('App\Models\OrderVendor', 'order_vendor_id', 'id');
    }

    public function reason(){
        return $this->belongsTo('App\Models\ReturnReason', 'return_reason_id', 'id');
    }

    public function vendor(){
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id');
    }

    public function updated_by_user(){
        return $this->belongsTo('App\Models\User', 'updated_by', 'id')->withTrashed();
    }

    public function getStatusAttribute($value){
        if($value == 1){
            return 'Approved';
        }elseif($value == 2){
            return 'Rejected';
        }else{
            return 'Pending';
        }
    }

}
