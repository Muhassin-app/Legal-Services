<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllergicItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('allergic_items')->delete();
 
        $allergic_items = [
            ['title' => 'Almond'],
            ['title' => 'Cashew nut'],
            ['title' => 'Codfish'],
            ['title' => 'Cows milk'],
            ['title' => 'Egg white'],
            ['title' => 'Hazelnut'],
            ['title' => 'Peanut'],
            ['title' => 'Salmon'],
            ['title' => 'Scallop'],
            ['title' => 'Sesame seed'],
            ['title' => 'Shrimp'],
            ['title' => 'Soybean'],
            ['title' => 'Tuna'],
            ['title' => 'Walnut'],
            ['title' => 'Wheat'],
        ]; 
        DB::table('allergic_items')->insert($allergic_items);
    }
}
