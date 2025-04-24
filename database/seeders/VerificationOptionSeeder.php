<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VerificationOption;
use DB;

class VerificationOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $option_count = DB::table('verification_options')->count();

        $verification_options = [
        	['id' => '1', 'path' => 'passbase/passbase-php', 'code' => 'passbase',  'title' => 'Passbase', 'status' => '0','test_mode'=>'1'],
        	['id' => '2', 'path' => '', 'code' => 'yoti',  'title' => 'Yoti Age Verification', 'status' => '0','test_mode'=>'1'],
        ];

        if($option_count == 0)
      	{
        	DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        	DB::table('verification_options')->truncate();
        	DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        	DB::table('verification_options')->insert($verification_options);
      	}else{
          	foreach ($verification_options as $option) {
				$verifyop = VerificationOption::where('code', $option['code'])->first();
              if ($verifyop !== null) {
                  $verifyop->update(['title' => $option['title']]);
              } else {
                  $verifyop = VerificationOption::create([
                    'title' => $option['title'],
                    'code' => $option['code'],
                    'path' => $option['path'],
                    'status' => $option['status'],
                    'test_mode' => $option['test_mode'],
                  ]);
              }

	 
        	}
      	}
    }
}
