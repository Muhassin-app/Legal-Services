<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryKycDocuments extends Model
{
    use HasFactory;

    public function primary(){
        $langData = $this->hasOne('App\Models\CategoryKycDocumentTranslation', "category_kyc_document_id" ,'id')
        ->join('client_languages as cl', 'cl.language_id', 'category_kyc_document_translations.language_id')
        ->where('cl.is_primary', 1);
        return $langData;
    }
    public function translations(){
        $langData = $this->hasMany('App\Models\CategoryKycDocumentTranslation', "category_kyc_document_id" ,'id');
        return $langData;
    }

    public function categoryMapping(){
        return  $this->hasMany('App\Models\CategoryKycDocumentMapping', "category_kyc_document_id" ,'id');
      
    }
    public function category_doc(){
        return  $this->hasMany('App\Models\CaregoryKycDoc', "category_kyc_document_id" ,'id');
      
    }
   
}
