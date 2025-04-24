<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Banner seeder*/
        \DB::table('attributes')->delete();
        $attributes = array(
            array(
                'id' => 1,
                'title' => 'name',
                'type' => 4,
                'position' => 1,
                'status' => 1,
                'user_id' => 1,
                'is_mandatory' => 1,
                'is_predefined' => 1
            ),
           
        );
        \DB::table('attributes')->insert($attributes);



        \DB::table('attribute_translations')->delete();
        $attribute_translations = array(
            array(
                'id' => 1,
                'title' => 'name',
                'attribute_id' => 1,
                'language_id' => 1
            ),
           
        );
        \DB::table('attribute_translations')->insert($attribute_translations);



        \DB::table('attribute_categories')->delete();
        $attribute_categories = array(
            array(
                'id' => 1,
                'attribute_id' => 1,
                'category_id' => 1,
            ),
           
        );
        \DB::table('attribute_categories')->insert($attribute_categories);

        
        \DB::table('attribute_options')->delete();
        $attribute_options = array(
            array(
                'id' => 1,
                'title' => 'name',
                'attribute_id' => 1,
                'hexacode' => null,
                'position' => 1
            ),
           
        );
        \DB::table('attribute_options')->insert($attribute_options);


        \DB::table('attribute_option_translations')->delete();
        $attribute_option_translations = array(
            array(
                'id' => 1,
                'title' => 'name',
                'attribute_option_id' => 1,
                'language_id' => 1
            ),
           
        );
        \DB::table('attribute_option_translations')->insert($attribute_option_translations);
    }
}
