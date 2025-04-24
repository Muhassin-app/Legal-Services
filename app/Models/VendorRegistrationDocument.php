<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRegistrationDocument extends Model
{
    use HasFactory;

    public function primary(){
      $langData = $this->hasOne('App\Models\VendorRegistrationDocumentTranslation')->join('client_languages as cl', 'cl.language_id', 'vendor_registration_document_translations.language_id')->where('cl.is_primary', 1);
      return $langData;
    }
    public function translations(){
      $langData = $this->hasMany('App\Models\VendorRegistrationDocumentTranslation');
      return $langData;
    }
    public function options(){
        return $this->hasMany('App\Models\VendorRegistrationSelectOption', 'vendor_registration_documents_id','id');
    }
    public function option(){
        return $this->hasOne('App\Models\VendorRegistrationSelectOption', 'vendor_registration_documents_id','id');
    }
}
