<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NomenclatureTranslation extends Model
{
    use HasFactory;

    protected $table = 'nomenclatures_translations';

    protected $fillable = [
        'language_id',
        'nomenclature_id',
        'name'
    ];

    public static function getNameBylanguageId($languageId, $nomenclatureName)
    {
        $key = 'nomenclature_' . $languageId . '_' . $nomenclatureName;
        Cache::forget('nomenclature_' . $languageId . '_' . $nomenclatureName);
        $translatedName = Cache::remember($key, 60 * 60, function () use ($languageId, $nomenclatureName) {
            if(is_numeric($nomenclatureName)){
                $nomenclatureId = $nomenclatureName;
            }else{
                $nomenclatureId = Nomenclature::getIdByName($nomenclatureName);
            }
            $nomenclatureTranslation = NomenclatureTranslation::where('nomenclature_id', $nomenclatureId)
            ->where('language_id', $languageId)
            ->first();
            return $nomenclatureTranslation ? $nomenclatureTranslation->name : null;
        });
        return $translatedName;
    }
    

   
}
