<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerAttributeOption extends Model
{
    protected $table = 'influ_attr_opt';
    protected $guarded = [];

    public function translation(){
        return $this->hasMany('App\Models\InfluencerAttributeOptionTranslation', 'attribute_option_id', 'id')->join('languages', 'influ_attr_opt_trans.language_id', 'languages.id'); 
    }
}
