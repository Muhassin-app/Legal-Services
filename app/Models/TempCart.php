<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempCart extends Model
{
    use HasFactory;

    protected $fillable = ['scheduled_slot','unique_identifier','status','is_gift','item_count','currency_id','user_id', 'created_by', 'schedule_type', 'scheduled_date_time','comment_for_dropoff_driver','comment_for_vendor','comment_for_pickup_driver','schedule_pickup','schedule_dropoff','specific_instructions','address_id', 'order_vendor_id', 'is_submitted', 'is_approved','vendor_wallet_amount_used','order_payable_amount'];

    public function cartProducts(){
      return $this->hasMany('App\Models\TempCartProduct', 'cart_id', 'id')->leftjoin('client_currencies as cc', 'cc.currency_id', 'temp_cart_products.currency_id')->select('temp_cart_products.id', 'temp_cart_products.cart_id', 'temp_cart_products.product_id', 'temp_cart_products.quantity', 'temp_cart_products.variant_id', 'temp_cart_products.is_tax_applied', 'temp_cart_products.tax_rate_id', 'temp_cart_products.currency_id', 'cc.doller_compare', 'temp_cart_products.vendor_id')->orderBy('temp_cart_products.created_at', 'asc')->orderBy('temp_cart_products.vendor_id', 'asc');
    }

    public function coupon(){
      return $this->hasOne('App\Models\TempCartCoupon', 'cart_id', 'id')->select("cart_id", "coupon_id", "vendor_id");
    }

    public function product(){
      return $this->belongsTo('App\Models\Product');
    }

    public function variant(){
      return $this->belongsTo('App\Models\ProductVariant');
    }

    public function cartvendor(){
      return $this->hasMany('App\Models\TempCartProduct')->select('cart_id', 'vendor_id');
    }

    public function address(){
      return $this->belongsTo('App\Models\UserAddress', 'address_id', 'id');
    }

    public function currency(){
      return $this->belongsTo('App\Models\Currency', 'currency_id', 'id');
    }
}