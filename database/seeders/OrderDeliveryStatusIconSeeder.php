<?php

namespace Database\Seeders;

use App\Models\OrderDeliveryStatusIcon;
use Illuminate\Database\Seeder;
use DB;

class OrderDeliveryStatusIconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
     
        $count = DB::table('order_delivery_status_icon')->count();
        $options = array(
              array('id' => '1','name' => __('Step 1'),'image' => 'assets/icons/driver_1_1.png'),
              array('id' => '2','name' => __('Step 2'),'image' => 'assets/icons/driver_2_1.png'),
              array('id' => '3','name' => __('Step 3'),'image' => 'assets/icons/driver_4_1.png'),
              array('id' => '4','name' => __('Step 4'),'image' => 'assets/icons/driver_3_1.png'),
              array('id' => '5','name' => __('Step 5'),'image' => 'assets/icons/driver_4_2.png'),
              array('id' => '6','name' => __('Step 6'),'image' => 'assets/icons/driver_5_1.png')
          );
         
        if($count == 0)
        {
          DB::statement('SET FOREIGN_KEY_CHECKS=0;');
          DB::table('order_delivery_status_icon')->truncate();
          DB::statement('SET FOREIGN_KEY_CHECKS=1;');
  
          DB::table('order_delivery_status_icon')->insert($options);
        }
        else{
        
            foreach ($options as $option) {
                    $payop = OrderDeliveryStatusIcon::updateOrCreate(['id'=>$option['id']],[
                      'id'      => $option['id'],
                      'name'    => $option['name'],
                      'image'   => $option['image']
                    ]);                
            }
        }
      }
}
