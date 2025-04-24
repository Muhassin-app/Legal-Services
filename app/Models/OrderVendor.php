<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderVendor extends Model{
    use HasFactory;
    const CANCEL_STATUS = 'Cancelled';

	protected $fillable = ['web_hook_code','payment_option_id', 'is_restricted','dispatch_traking_url','delivery_response', 'roadie_tracking_url','delivery_fee','waiting_price','waiting_time','borzoe_order_id', 'borzoe_order_name','order_status_option_id','reject_reason','cancelled_by'];

	public function orderDetail(){
	    return $this->hasOne('App\Models\Order' , 'id', 'order_id');
	}
	public function order(){
	    return $this->hasOne('App\Models\Order' , 'id', 'order_id');
	}
	public function LuxuryOption(){
	    return $this->hasOne('App\Models\Order' , 'id', 'order_id')->select('id','luxury_option_id');
	}
	public function paymentOption(){
	    return $this->hasOne('App\Models\PaymentOption' , 'id', 'payment_option_id');
	}
    public function vendor(){
	    return $this->hasOne('App\Models\Vendor' , 'id', 'vendor_id');
	}
	public function user(){
	    return $this->hasOne('App\Models\User' , 'id', 'user_id')->withTrashed();
	}
    public function products(){
	    return $this->hasMany('App\Models\OrderProduct' , 'order_vendor_id', 'id');
	}
	public function payment(){
	    return $this->hasOne('App\Models\Payment' , 'order_id', 'order_id');
	}
	public function accounting(){
	    return $this->hasOne('App\Models\OrderVendorAccounting' , 'order_vendor_id', 'id');
	}
	public function coupon(){
	    return $this->hasOne('App\Models\Promocode' , 'id', 'coupon_id');
	}
	public function status(){
	    return $this->hasOne('App\Models\VendorOrderStatus','order_vendor_id','id')->orderBy('id', "DESC");
	}
	public function exchanged_to_order(){
	    return $this->hasOne('App\Models\OrderVendor','exchange_order_vendor_id','id');
	}
	public function exchanged_of_order(){
	    return $this->belongsTo('App\Models\OrderVendor','exchange_order_vendor_id','id');
	}
	public function orderstatus(){
	    return $this->hasOne('App\Models\VendorOrderStatus' , 'vendor_id', 'vendor_id', 'order_id', 'order_id')->orderBy('id', 'DESC')->latest();
	}
	public function OrderStatusOption(){
       return $this->hasOne('App\Models\OrderStatusOption', 'id', 'order_status_option_id');
    }
	public function cancelledBy()
	{
		return $this->belongsTo('App\Models\User','cancelled_by','id')->select('id','name')->withTrashed();
	}

    public function orderDocument(){
        return $this->hasMany('App\Models\OrderDocument','order_vendor_product_id','id');
    }

	public function reqCancelOrder()
    {
        return $this->hasOne('App\Models\OrderCancelRequest'); //, 'order_id', 'id'
    }
	public function acceptedBy()
	{
		return $this->belongsTo('App\Models\User','accepted_by','id')->select('id','name')->withTrashed();
	}
	public function scopeBetween($query, $from, $to){
        $query->whereBetween('created_at', [$from, $to]);
    }

	# get dispatcher status title
	public function getDispatcherStatusAttribute($value)
    {
		$title = DispatcherStatusOption::where('id',$value)->value('title');
		$dispatcheeStatus =	VendorOrderDispatcherStatus::where(['dispatcher_id' => null,
					'order_id' =>  $this->order_id,
					'vendor_id' =>  $this->vendor_id,
		])->orderBy('id', 'desc')->first();
		if($dispatcheeStatus){
			$type = $dispatcheeStatus->type;
			$dispatcher_status_option = $dispatcheeStatus->dispatcher_status_option_id;
			switch ($dispatcher_status_option) {
				case 1:
					if ($type == '1') {
						$title = __(getNomenclatureName('Hold on! We are looking for drivers nearby!',true));
					}
				break;
				case 2:
					if ($type == '1') {
						$title = __('Your driver has been assigned!');
					}
				break;
				case 3:
					if ($type == '1') {
						$title = __('Driver heading to the pickup location');
					} else {
						$title = __('Driver heading to dropoff location');
					}
				break;
				case 4:
					if ($type == '1') {
						$title = __('Driver arrived at pickup location');
					}else{
						$title = __('Driver arrived at dropoff location');
					}
				break;
				case 5:
					if ($type == '1') {
						$title = __('Your driver has reached to your pickup location!');
					}else{
						$title = __('You have arrived at your destination!');
					}
				break;
				default:
					$title = __(getNomenclatureName('Hold on! We are looking for drivers nearby!'));
			   }
		}
        return ucfirst($title);
    }

	public function allStatus(){
	    return $this->hasMany('App\Models\VendorOrderStatus','order_vendor_id','id');
	}

	public function dineInTable(){
	    return $this->belongsTo('App\Models\VendorDineinTable' , 'vendor_dinein_table_id', 'id');
	}

	public function tempCart(){
	    return $this->hasOne('App\Models\TempCart' , 'order_vendor_id', 'id');
	}

	public function cancel_request(){
        return $this->hasOne('App\Models\OrderCancelRequest', 'order_vendor_id', 'id')->select('*', 'status as status_id')->orderBy('updated_at', 'desc');
    }

    public function getVendorAmountAttribute(){
        $vendor_amount = $this->subtotal_amount;
        $discount = 0;
        if($this->coupon_paid_by == 0){
            $discount = $this->discount_amount;
        }
        return decimal_format($vendor_amount - $discount - $this->admin_commission_percentage_amount);
    }

    public function getTotalPriceAttribute(){
        $amount = $this->payable_amount;
        $tip = !empty($this->orderDetail)?number_format($this->orderDetail->tip_amount, 2):0.00;
        $amount += $tip;
        return decimal_format($amount);
    }

}
