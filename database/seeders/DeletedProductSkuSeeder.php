<?php

namespace Database\Seeders;
use DB;
use Illuminate\Database\Seeder;

class DeletedProductSkuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $deleted_products = DB::table('products')->whereNotNull('deleted_at')->get();
        foreach($deleted_products as $product)
        {
        	$update = DB::table('products')->where('id',$product->id)->update(['sku' => $product->sku.'_deleted']);
        }
    }
}
