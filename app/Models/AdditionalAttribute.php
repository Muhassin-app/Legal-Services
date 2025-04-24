<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalAttribute extends Model
{
    use HasFactory;

    protected $table = 'additional_attributes';

    public static $fieldType = [
        'selector' => 'DropDown',
        'color' => 'Color',
        'radio' => 'Radio',
        'textbox' => 'Textbox',
        'checkbox' => 'Checkbox',
        'location' => 'Location',
        'datepicker' => 'DatePicker'
    ];

    public static function fieldType($id = null)
    {
        $fieldTypes = self::$fieldType;
        if ($id == null)
            return $fieldTypes;
        return isset($fieldTypes[$id]) ? $fieldTypes[$id] : null;
    }

    public function translation()
    {
        return $this->hasMany('App\Models\AdditionalAttributeTranslation')
            ->join('languages', 'additional_attributes_translations.language_id', 'languages.id')
            ->select('additional_attributes_translations.id', 'additional_attributes_translations.title', 'additional_attributes_translations.additional_attribute_id', 'additional_attributes_translations.language_id', 'languages.name');
    }

    public function translation_one()
    {
        $primary = ClientLanguage::orderBy('is_primary', 'desc')->first();
        if (isset($primary) && ! empty($primary)) {
            $langset = $primary->language_id;
        } else {
            $langset = 1;
        }

        return $this->hasOne('App\Models\AdditionalAttributeTranslation')
            ->select('additional_attribute_id', 'title')
            ->where('language_id', $langset);
    }

    public function primary()
    {
        return $this->hasOne('App\Models\AdditionalAttributeTranslation')
            ->join('client_languages as cl', 'cl.language_id', 'additional_attributes_translations.language_id')
            ->where('cl.is_primary', 1);
    }

    public function option()
    {
        return $this->hasMany('App\Models\AdditionalAttributeOption');
    }

    public function trans()
    {
        return $this->hasOne('App\Models\AdditionalAttributeTranslation')->select('title', 'additional_attribute_id');
    }

    public function attributeType()
    {
        return $this->hasOne('App\Models\AttributeType', 'id', 'type_id')->select('title');
    }
}

