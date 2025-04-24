<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOptionTranslation extends Model
{
    protected $fillable = ['title','attribute_option_id','language_id'];
}
