<?php

namespace Database\Seeders;
use DB;
use App\Models\{Product};
use Illuminate\Database\Seeder;

class MinimumOrderCountSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
       
        $update = Product::where('minimum_order_count',0)->update(['minimum_order_count' => 1]);
    }
}
