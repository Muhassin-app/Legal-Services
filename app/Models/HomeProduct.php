<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeProduct extends Model
{
    use HasFactory;

    protected $fillable = ['layout_id','slug','title','category_id','products','type'];

    public function categoryDetail(){
	    return $this->belongsTo('App\Models\Category', 'category_id', 'id')->whereNull('deleted_at'); 
	}

    public function products(){
        return $this->belongsTo(Product::class, 'product_id','id');
    }
}
