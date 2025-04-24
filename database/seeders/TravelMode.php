<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TravelMode extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('travel_mode')->delete();
 
        $travel_mode = array(
            array(
                'id' => 1,
                'travelmode' => 'TRAVEL_MODE_UNSPECIFIED',
                'desc' => 'No travel mode specified. Defaults to DRIVE'
            ),
            array(
                'id' => 2,
                'travelmode' => 'TAXI',
                'desc' => 'Travel by TAXI or Cab'
            ),
            array(
                'id' => 3,
                'travelmode' => 'DRIVE',
                'desc' => 'Travel by passenger car'
            ),
            array(
                'id' => 4,
                'travelmode' => 'BICYCLE',
                'desc' => 'Travel by bicycle.'
            ),
            array(
                'id' => 5,
                'travelmode' => 'WALK',
                'desc' => 'Travel by walking.'
            ),
            array(
                'id' => 6,
                'travelmode' => 'TWO_WHEELER',
                'desc' => 'Two-wheeled, motorized vehicle. For example, motorcycle. Note that this differs from the BICYCLE travel mode which covers human-powered mode.'
            ),
        ); 
        \DB::table('travel_mode')->insert($travel_mode);
    }
}
