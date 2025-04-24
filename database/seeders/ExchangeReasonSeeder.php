<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class ExchangeReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('exchange_reasons')->delete();
        $return_reason_array = array(
            ['id' => 1,
                'title' => 'I received a defective / wrong product',
                'order' =>'1'
            ],
            ['id' => 2,
                'title' => 'Size is too large',
                'order' =>'2'
            ],
            ['id' => 3,
                'title' => "size is too small",
                'order' =>'3'
            ],
           ['id' => 4,
                'title' => 'Other',
                'order' =>'5'
            ]
        ); 
        DB::table('exchange_reasons')->insert($return_reason_array);
    }
}
