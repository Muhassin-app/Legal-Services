<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagTranslation extends Model
{
    use HasFactory;


    public function language()
    {
      return $this->belongsTo('App\Models\Language','language_id','id')->select('id', 'name', 'sort_code','nativeName');
    }

    public function tag()
    {
      return $this->belongsTo('App\Models\Tag','tag_id','id');
    }


    public function primary(){
        return $this->hasOne('App\Models\ClientLanguage', 'language_id', 'language_id')->where('is_primary',1);
     }

    
}
