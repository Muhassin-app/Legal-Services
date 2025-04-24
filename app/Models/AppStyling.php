<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppStyling extends Model
{

    use HasFactory;

    public function styleOption()
    {
        return $this->hasOne('App\Models\AppStylingOption')->where('is_selected', 1);
    }
    public function styleOptionDetail()
    {
        return $this->hasOne('App\Models\AppStylingOption');
    }

    public static function getSelectedData()
    {
        $app_styles_array = [];
        $app_styles = AppStyling::select('id', 'name')->with('styleOption')->get();
        foreach ($app_styles as $app_style) {
            $key_name = str_replace(" ", "_", strtolower($app_style->name));
            if ($app_style->name == "Tab Bar Style" || $app_style->name == "Home Page Style") {
                $template_id = $app_style->styleOption ? $app_style->styleOption->template_id : 3;
            } else {


                $template_id = $app_style->styleOption->name ?? $app_style->styleOptionDetail->name;
            }
            $app_styles_array[] = array(
                'key' => $key_name,
                'value' => $template_id,
            );
        }
        return $app_styles_array;
    }
}
