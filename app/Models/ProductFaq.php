<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFaq extends Model
{
    use HasFactory;
    
    public function primary(){
      $langData = $this->hasOne('App\Models\ProductFaqTranslation')->join('client_languages as cl', 'cl.language_id', 'product_faq_translations.language_id')->where('cl.is_primary', 1);
      return $langData;
    }
    public function translations(){
      $langData = $this->hasMany('App\Models\ProductFaqTranslation');
      return $langData;
    }

    public function selection(){
      $langData = $this->hasMany('App\Models\ProductFaqSelectOption');
      return $langData;
    }
}
