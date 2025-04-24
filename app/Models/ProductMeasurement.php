<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMeasurement extends Model
{
    use HasFactory;
    protected $table='product_measurement';
    protected  $fillable=['product_id','product_variant_id','key_id','key_value'];

    public function measurements(){
		return $this->hasMany('App\Models\Measurements', 'id', 'key_id');
    }
    public function variant()
    {
      return $this->belongsTo('App\Models\ProductVariant','product_variant_id','id');
    }
    public function productVariants()
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_measurement',
            'key_id',
            'product_variant_id'
        )->withPivot('product_id', 'key_value');
    }



}