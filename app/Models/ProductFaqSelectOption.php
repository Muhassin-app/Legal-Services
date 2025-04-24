<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFaqSelectOption extends Model
{
    use HasFactory;
    protected $fillable = ['product_faq_id'];
    public function primary(){
        $langData = $this->hasOne('App\Models\ProductFaqSelectOptionTranslation')->join('client_languages as cl', 'cl.language_id', 'product_faq_select_option_translations.language_id')->where('cl.is_primary', 1);
        return $langData;
    }
    public function translation(){
        return $this->hasOne('App\Models\ProductFaqSelectOptionTranslation');
    }
    public function translations(){
        return $this->hasMany('App\Models\ProductFaqSelectOptionTranslation');
    }
}
