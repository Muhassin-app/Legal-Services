<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingOption;
use DB;

class ShippingOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$option_count = DB::table('shipping_options')->count();

      	$shipping_options = array(
        	array('id' => '1', 'path' => '', 'code' => 'shiprocket',  'title' => 'ShipRocket', 'status' => '0','test_mode'=>'1'),
        	array('id' => '2', 'path' => '', 'code' => 'lalamove', 'title' => 'Lalamove', 'status' => '0','test_mode'=>'1'),
        	array('id' => '3', 'path' => '', 'code' => 'dunzo', 'title' => 'Dunzo', 'status' => '0','test_mode'=>'1'),
        	array('id' => '4', 'path' => '', 'code' => 'ahoy', 'title' => 'Ahoy', 'status' => '0','test_mode'=>'1'),
        	array('id' => '5', 'path' => '', 'code' => 'shippo', 'title' => 'Shippo', 'status' => '0','test_mode'=>'1'),
        	array('id' => '6', 'path' => '', 'code' => 'kwikapi', 'title' => 'KwikApi', 'status' => '0','test_mode'=>'1'),
        	array('id' => '7', 'path' => '', 'code' => 'roadie', 'title' => 'Roadie', 'status' => '0','test_mode' => '1'),
        	array('id' => '8', 'path' => '', 'code' => 'shipengine', 'title' => 'ShipEngine', 'status' => '0','test_mode' => '1'),
            array('id' => '9', 'path' => '', 'code' => 'd4b_dunzo', 'title' => 'D4B Dunzo', 'status' => '1','test_mode'=>'1'),
            array('id' => '10', 'path' => '', 'code' => 'borzo', 'title' => 'Borzoe', 'status' => '1','test_mode'=>'1'),
      	);

      	if($option_count == 0)
      	{
        	DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        	DB::table('shipping_options')->truncate();
        	DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        	DB::table('shipping_options')->insert($shipping_options);
      	}
      else{
          foreach ($shipping_options as $option) {

            $find = ShippingOption::where('code',$option['code'])->first();
            if(!$find){
            $newUser = ShippingOption::Create([
                'title' => $option['title'],
                'code' => $option['code'],
                'path' => $option['path'],
                'status' => $option['status'],
            ]);
          }
        }
      }
    }
}
