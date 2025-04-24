<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\AppStylingOption;

class AppStylingAddOptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $app_styling_options = array(
            array('app_styling_id' => '8', 'name' => 'Home Page 7', 'image' => 'home_seven.png', 'template_id' => '6', 'is_selected' => '0'),
            array('app_styling_id' => '8', 'name' => 'Home Page 8', 'image' => 'home_eight.png', 'template_id' => '7', 'is_selected' => '0'),
            array('app_styling_id' => '7', 'name' => 'Tab 6', 'image' => 'bar_six.png', 'template_id' => '6', 'is_selected' => '0'),
            array('app_styling_id' => '8', 'name' => 'Home Page 9', 'image' => 'home_nine.png', 'template_id' => '8', 'is_selected' => '0'),
            array('app_styling_id' => '8', 'name' => 'Home Page 10', 'image' => 'home_ten.png', 'template_id' => '9', 'is_selected' => '0'),
            array('app_styling_id' => '8', 'name' => 'Home Page 11', 'image' => 'home_eleven.png', 'template_id' => '10', 'is_selected' => '0'),
            array('app_styling_id' => '8', 'name' => 'Home Page 12', 'image' => 'home_twelve.png', 'template_id' => '11', 'is_selected' => '0')
        );
        

      
        foreach ($app_styling_options as $option) {
            $app_style = AppStylingOption::where('image', $option['image'])->first();
            if ($app_style) {
                $app_style->update(['app_styling_id' => $option['app_styling_id'], 'name' => $option['name'], 'template_id' => $option['template_id'],]);
            } else {
                $app_style = AppStylingOption::create([
                    'app_styling_id' => $option['app_styling_id'],
                    'name' => $option['name'],
                    'image' => $option['image'],
                    'template_id' => $option['template_id'],
                    'is_selected' => $option['is_selected'],

                ]);
            }
        }
    }
}
