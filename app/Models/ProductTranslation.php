<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Laravel\Scout\Searchable;

class ProductTranslation extends Model{
	protected $fillable = ['title','body_html','meta_title','meta_keyword','meta_description','product_id','language_id'];

	public function getTranslationDescriptionAttribute()
    {
	  return	isset($this->body_html) ? html_entity_decode(strip_tags($this->body_html),ENT_QUOTES) : '';
    }
}