<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'is_active', 'attribute_id', 'attribute_option_id', 'key_name', 'key_value', 'latitude', 'longitude'];

    public function attributeOption(){
        return $this->hasOne('App\Models\AttributeOption', 'id', 'attribute_option_id');
    }

    public function attribute() {
        return $this->hasOne('App\Models\Attribute', 'id', 'attribute_id');
    }

    public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}
