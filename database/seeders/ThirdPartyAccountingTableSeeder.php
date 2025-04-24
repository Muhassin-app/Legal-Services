<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThirdPartyAccounting;
use DB;

class ThirdPartyAccountingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $option_count = DB::table('third_party_accounting')->count();

      	$accounting_options = array(
        	array('id' => '1', 'path' => 'xeroapi/xero-php-oauth2', 'code' => 'xero',  'title' => 'Xero', 'status' => '0','test_mode'=>'1'),
      	); 

      	if($option_count == 0)
      	{
        	DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        	DB::table('third_party_accounting')->truncate();
        	DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        	DB::table('third_party_accounting')->insert($accounting_options);
      	}
      	else{
          	foreach ($accounting_options as $option) {
	            $create = ThirdPartyAccounting::updateOrCreate([
	            	 'code' => $option['code'],
	            ],[
	                'title' => $option['title'],
	                'path' => $option['path'],
	                'status' => $option['status']
	            ]);    
        	}
      	}
    }
}
