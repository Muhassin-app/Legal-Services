<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalAttributeOption extends Model
{
    use HasFactory;

    protected $table = 'additional_attributes_options';

    public function translation()
    {
        return $this->hasMany('App\Models\AdditionalAttributeOptionTranslation')->join('languages', 'additional_attributes_option_translations.language_id', 'languages.id');
    }

    public function trans()
    {
        return $this->hasOne('App\Models\AdditionalAttributeOptionTranslation')->select('title', 'additional_attribute_option_id', 'id');
    }
}

