<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegistrationSelectOption extends Model
{
    use HasFactory;
    protected $fillable = ['user_registration_documents_id'];
    public function primary(){
        $langData = $this->hasOne('App\Models\UserRegistrationSelectOptionTranslations')->join('client_languages as cl', 'cl.language_id', 'vendor_registration_select_option_translations.language_id')->where('cl.is_primary', 1);
        return $langData;
    }

    public function translation(){
        return $this->hasOne('App\Models\UserRegistrationSelectOptionTranslations');
    }
    public function translations(){
        return $this->hasMany('App\Models\UserRegistrationSelectOptionTranslations');
    }
}
