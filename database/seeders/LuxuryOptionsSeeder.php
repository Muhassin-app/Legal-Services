<?php

namespace Database\Seeders;

use App\Models\LuxuryOption;
use Illuminate\Database\Seeder;
use DB;

class LuxuryOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $option_count = DB::table('luxury_options')->count();
        $luxury_options = array(
                                array('id' => '1','title' => 'delivery'),
                                array('id' => '2','title' => 'dine_in'),
                                array('id' => '3','title' => 'takeaway'),
                                array('id' => '4','title' => 'rental'),
                                array('id' => '5','title' => 'pick_drop'),
                                array('id' => '6','title' => 'on_demand'),
                                array('id' => '7','title' => 'laundry'),
                                array('id' => '8','title' => 'appointment'),
                                array('id' => '9','title' => 'p2p'),
                                array('id' => '10','title' => 'car_rental')
                            );
          // LuxuryOption::truncate();
      
        foreach ($luxury_options as $option) { 
            $LuxuryOption = LuxuryOption::where('title', $option['title'])->first();
            if(!$LuxuryOption)
            {
                $luxuryOption = LuxuryOption::insert([
                        'title' => $option['title'],
                    ]);
            }
        }
        
    }
}
