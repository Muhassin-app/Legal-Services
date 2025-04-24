<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTag extends Model
{
   protected $fillable = ['product_id','tag_id'];

    public function tag(){
       return $this->belongsTo('App\Models\Tag', 'tag_id', 'id'); 
    }

  
}