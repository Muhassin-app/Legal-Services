<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PanelAuthUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $insert_vendor_details = array(
            'name' => '',
            'dial_code' => 1,
            'phone_number' =>  NULL,
            'email' =>  NULL,
            'import_user_id' =>  NULL,
            'password' => Hash::make('12345678'),
            'is_email_verified' =>  '1',
            'is_phone_verified' =>  '1',
            'status'=>'1',
            'is_panel_auth_user' => 1
        );

        User::insertGetId($insert_vendor_details);
        
    }
}
