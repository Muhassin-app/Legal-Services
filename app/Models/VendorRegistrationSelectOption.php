<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRegistrationSelectOption extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_registration_documents_id'];
    public function primary(){
        $langData = $this->hasOne('App\Models\VendorRegistrationSelectOptionTranslations')->join('client_languages as cl', 'cl.language_id', 'vendor_registration_select_option_translations.language_id')->where('cl.is_primary', 1);
        return $langData;
    }
    public function translation(){
        return $this->hasOne('App\Models\VendorRegistrationSelectOptionTranslations');
    }
    public function translations(){
        return $this->hasMany('App\Models\VendorRegistrationSelectOptionTranslations');
    }

}
