<?php
namespace Database\Seeders;

use App\Models\RoleOld;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('roles')->delete();
 
        $maps = array(
            array(
                'id' => 1,
                'role' => 'Buyer',
                'name' => 'Buyer',
                'status' => '1'
            ),
            array(
                'id' => 2,
                'role' => 'Seller',
                'name' => 'Seller',
                'status' => '1'
            ),
            array(
                'id' => 3,
                'role' => 'Corporate_user',
                'name' => 'Corporate User',
                'status' => '1'
            )
        ); 
        \DB::table('roles')->insert($maps);

    }
}
