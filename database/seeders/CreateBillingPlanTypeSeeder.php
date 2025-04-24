<?php

namespace Database\Seeders;
use DB;
use Illuminate\Database\Seeder;

class CreateBillingPlanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('billing_plan_types')->delete();
 
        $plantypes = array(
            array(
                'id' => 1,
                'title' => 'Software License'
            ),
            array(
                'id' => 2,
                'title' => 'Hosting Plan'
            ),
        ); 
        \DB::table('billing_plan_types')->insert($plantypes);
    }
}
