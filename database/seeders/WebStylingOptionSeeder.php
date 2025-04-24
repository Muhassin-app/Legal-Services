<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use App\Models\{WebStyling,WebStylingOption};

class WebStylingOptionSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $web_styling = WebStyling::where(['name' => 'Home Page Style', 'type' => '1'])->value('id');
        if($web_styling > 0){
            $web_styling_options_count = DB::table('web_styling_options')->count();
            $web_styling_options = array(
                array('web_styling_id' => $web_styling, 'name' => 'Home Page 1', 'is_selected' => '1', 'template_id' => '1', 'image' => 'template-one.jpg'),
                array('web_styling_id' => $web_styling, 'name' => 'Home Page 2', 'is_selected' => '0', 'template_id' => '2', 'image' => 'template-two.jpg'),
                array('web_styling_id' => $web_styling, 'name' => 'Food Delivery', 'is_selected' => '0', 'template_id' => '3', 'image' => 'template-three.jpg'),
                array('web_styling_id' => $web_styling, 'name' => 'E-Commerce', 'is_selected' => '0', 'template_id' => '4', 'image' => 'template-four.jpg'),
               // array('web_styling_id' => $web_styling, 'name' => 'Pickup & Drop', 'is_selected' => '0', 'template_id' => '5', 'image' => 'template-five.jpg'),
                array('web_styling_id' => $web_styling, 'name' => 'On Demand Service', 'is_selected' => '0', 'template_id' => '6', 'image' => 'template-six.jpg'),
                array('web_styling_id' => $web_styling, 'name' => 'E-Commerce 2', 'is_selected' => '0', 'template_id' => '8', 'image' => 'template-eight.jpg'),
                array('web_styling_id' => $web_styling, 'name' => 'p2p', 'is_selected' => '0', 'template_id' => '9', 'image' => 'template-nine.jpg'),
                array('web_styling_id' => $web_styling, 'name' => 'Car Rental', 'is_selected' => '0', 'template_id' => '10', 'image' => 'template-ten.PNG')
            );
            if($web_styling_options_count == 0)
            {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('web_styling_options')->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                DB::table('web_styling_options')->insert($web_styling_options);
            }
            else{
                foreach ($web_styling_options as $option) {
                    $webStylingOption = WebStylingOption::where('template_id', $option['template_id'])->first();
                    if ($webStylingOption !== null) {
                        $webStylingOption->update(['web_styling_id' => $web_styling, 'name' => $option['name'], 'image' => $option['image'], 'is_template' => 1]);
                    } else {
                        WebStylingOption::create([
                            'name' => $option['name'],
                            'image' => $option['image'],
                            'is_selected' => $option['is_selected'],
                            'template_id' => $option['template_id'],
                            'web_styling_id' => $option['web_styling_id'],
                            'is_template' => 1

                        ]);
                    }
                }
            }

        }
    }
}