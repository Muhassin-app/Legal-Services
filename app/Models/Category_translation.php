<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Laravel\Scout\Searchable;

class Category_translation extends Model
{
    protected $table='category_translations';
    protected $fillable = ['language_id', 'name', 'category_id','meta_title','meta_description','meta_keywords'];


       
}
