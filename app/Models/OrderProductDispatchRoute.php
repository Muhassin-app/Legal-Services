<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductDispatchRoute extends Model
{
    use HasFactory;
    public function DispatchStatus(){
        return $this->hasMany('App\Models\VendorOrderProductDispatcherStatus', 'order_product_route_id', 'id')->orderBy('id','DESC');
    }
    public function order(){
	    return $this->hasOne('App\Models\Order' , 'id', 'order_id'); 
	}
}
