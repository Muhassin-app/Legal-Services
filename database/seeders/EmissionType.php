<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class EmissionType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('vehicle_emission_type')->delete();
 
        $vehicle_emission_type = array(
            array(
                'id' => 1,
                'emission_type' => 'VEHICLE_EMISSION_TYPE_UNSPECIFIED',
                'desc' => 'No emission type specified. Default to GASOLINE'
            ),
            array(
                'id' => 2,
                'emission_type' => 'GASOLINE',
                'desc' => 'Gasoline/petrol fueled vehicle'
            ),
            array(
                'id' => 3,
                'emission_type' => 'ELECTRIC',
                'desc' => 'Electricity powered vehicle'
            ),
            array(
                'id' => 4,
                'emission_type' => 'HYBRID',
                'desc' => 'Hybrid fuel (such as gasoline + electric) vehicle'
            ),
        ); 
        \DB::table('vehicle_emission_type')->insert($vehicle_emission_type);
    }
}
