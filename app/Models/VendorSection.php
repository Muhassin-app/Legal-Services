<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSection extends Model
{
    use HasFactory;
    public function headingTranslation(){
        return $this->hasMany('App\Models\VendorSectionHeadingTranslation');
    }
    public function SectionTranslation(){
        return $this->hasMany('App\Models\VendorSectionTranslation');
    }
    public function primary(){

        $langData = $this->hasOne('App\Models\VendorSectionHeadingTranslation')->join('client_languages as cl', 'cl.language_id', 'vendor_section_heading_translations.language_id')->select('vendor_section_heading_translations.*')->where('cl.is_primary', 1);
  
        if(!$langData){
          $langData = $this->hasOne('App\Models\VendorSectionHeadingTranslation')->join('client_languages as cl', 'cl.language_id', 'vendor_section_heading_translations.language_id')->select('vendor_section_heading_translations.*')->limit(1);
        }
        return $langData;
      }
}
