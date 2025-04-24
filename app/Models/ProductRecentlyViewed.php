<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecentlyViewed extends Model
{
    public $timestamps = true;
    protected $table = 'product_recently_viewed';
    protected $fillable = ['product_id','token_id','user_id','created_at','updated_at'];

}
