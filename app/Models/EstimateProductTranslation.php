<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateProductTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','language_id','estimate_product_id'];

    public function language()
    {
      return $this->belongsTo('App\Models\Language','language_id','id')->select('id', 'name', 'sort_code','nativeName');
    }

    public function estimate_product()
    {
      return $this->belongsTo('App\Models\EstimateProduct',' estimate_product_id','id');
    }


    public function primary(){
        return $this->hasOne('App\Models\ClientLanguage', 'language_id', 'language_id')->where('is_primary',1);
     }

    
}
