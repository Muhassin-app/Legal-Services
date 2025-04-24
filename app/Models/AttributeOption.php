<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    protected $fillable = ['title', 'attribute_id', 'hexacode', 'position'];

	public function translation(){
       return $this->hasMany('App\Models\AttributeOptionTranslation')->join('languages', 'attribute_option_translations.language_id', 'languages.id'); 
    }

    public function trans(){
       return $this->hasOne('App\Models\AttributeOptionTranslation')->select('title', 'attribute_option_id'); 
    }
}