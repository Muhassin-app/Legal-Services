<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegistrationDocuments extends Model
{
    use HasFactory;

    public function primary(){
        $langData = $this->hasOne('App\Models\UserRegistrationDocumentTranslation', "user_registration_document_id" ,'id')
        ->join('client_languages as cl', 'cl.language_id', 'user_registration_document_translations.language_id')
        ->where('cl.is_primary', 1);
        return $langData;
    }
    public function translations(){
        $langData = $this->hasMany('App\Models\UserRegistrationDocumentTranslation', "user_registration_document_id" ,'id');
        return $langData;
    }
    public function user_document(){
        return $this->hasOne('App\Models\UserDocs','user_registration_document_id','id',);
    }

    public function options(){
        return $this->hasMany('App\Models\UserRegistrationSelectOption', 'user_registration_documents_id','id');
    }
    public function option(){
        return $this->hasOne('App\Models\UserRegistrationSelectOption', 'user_registration_documents_id','id');
    }

}
