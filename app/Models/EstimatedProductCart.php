<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimatedProductCart extends Model
{
    use HasFactory;
    
    protected $table = 'estimated_product_cart_new';

    public function estimated_products(){
        return $this->hasMany('App\Models\EstimatedProduct', 'estimated_cart_id' );
    }

    public function estimated_cart_user(){
        return $this->belongsTo('App\Models\User', 'user_id' )->withTrashed();
    }

    public function estimated_cart_currency(){
        return $this->belongsTo('App\Models\Currency', 'currency_id' );
    }
}
