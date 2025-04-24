<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nomenclature;

class NomenclatureTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(!NomenClature::where(['label'=>'Want To Tip'])->exists()){
            NomenClature::Create(['label'=>'Want To Tip']);
        }
        if(!NomenClature::where(['label'=>'Fixed Fee'])->exists()){
            NomenClature::Create(['label'=>'Fixed Fee']);
        }
        if(!NomenClature::where(['label'=>'P2P'])->exists()){
            NomenClature::Create(['label'=>'P2P']);
        }
        if(!NomenClature::where(['label'=>'Car-Rental'])->exists()){
            NomenClature::Create(['label'=>'Car-Rental']);
        }
        if(!NomenClature::where(['label'=>'Online'])->exists()){
            NomenClature::Create(['label'=>'Online']);
        }
        if(!NomenClature::where(['label'=>'Products'])->exists()){
            NomenClature::Create(['label'=>'Products']);
        }
        if(!NomenClature::where(['label'=>'Include Gift'])->exists()){
            NomenClature::Create(['label'=>'Include Gift']);
        }
        if(!NomenClature::where(['label'=>'Control Panel'])->exists()){
            NomenClature::Create(['label'=>'Control Panel']);
        }
    }
}
