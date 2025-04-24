<?php

namespace Database\Seeders;
use App\Models\AppStyling;
use App\Models\AppStylingOption;
use Illuminate\Database\Seeder;

class AddAppStylingFontFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){ 


        $app_styling_options = array(
            array('styling_name' => 'Regular Font', 'name' => 'Poppins-Regular'),
            array('styling_name' => 'Medium Font', 'name' => 'Poppins-Medium'),
            array('styling_name' => 'Bold Font', 'name' => 'Poppins-Bold')
        );

        foreach ($app_styling_options as $option) {
            $app_style = AppStyling::where(['name'=> $option['styling_name'],'type'=>'2'])->first();         
            if ($app_style) {
                $exist =  AppStylingOption::where([
                    'app_styling_id' => $app_style->id,
                    'name' =>$option['name'],
                ])->first();
                if(empty($exist)){
                    $app_styling_option = AppStylingOption::insert([
                        'app_styling_id' => $app_style->id,
                        'name' =>$option['name'],
                        'is_selected' => '0'
                    ]);
                }
            } 
        }
    }
}
