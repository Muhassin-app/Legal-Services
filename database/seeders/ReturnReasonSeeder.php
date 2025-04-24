<?php
namespace Database\Seeders;
use DB;
use Illuminate\Database\Seeder;

class ReturnReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('return_reasons')->delete();
        $return_reason_array = array(
            ['id' => 1,
                'title' => 'Change of Mind ',
                'order' =>'1',
                'type' => 3 
            ],
            ['id' => 2,
                'title' => 'Found Another Lawyer',
                'order' =>'2',
                'type' => 3 
            ],
            ['id' => 3,
                'title' => "Incorrect Booking ",
                'order' =>'3',
                'type' => 3 
            ],
            ['id' => 4,
                'title' => 'Legal Issue Resolved',
                'order' =>'4',
                'type' => 3 
            ],
            ['id' => 5,
                'title' => 'Lawyer’s Experience Not Matching ',
                'order' =>'5',
                'type' => 3 
        ],
            ['id' => 6,
            'title' => 'Other',
            'order' =>'6',
            'type' => 3 
        ]
        ); 
        DB::table('return_reasons')->insert($return_reason_array);
    }
}
