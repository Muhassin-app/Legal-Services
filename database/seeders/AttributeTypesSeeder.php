<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttributeType;

class AttributeTypesSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'id' => 1,
                'title' => 'subscription',
                'status' => '1',
                'description' => 'This is for subscription module.'
            ]
        ];
        foreach ($types as $type) {
            AttributeType::upsert($type, [
                'id',
                'title',
                'status',
                'description'
            ]);
        }
    }
}
