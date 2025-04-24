<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use DB;
class MainRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { 

            $maps = array(
                array(
                    'id' => 1,
                    'name' => 'Super Admin',
                    'guard_name'=>'web'
                ),
                array(
                    'id' => 2,
                    'name' => 'Admin',
                    'guard_name'=>'web'

                ),
                // array(
                //     'id' => 3,
                //     'name' => 'Buyer',
                //     'guard_name'=>'web'
                // ),
                array(
                    'id' => 4,
                    'name' => 'Vendor',
                    'guard_name'=>'web'

                ),
                // array(
                //     'id' => 5,
                //     'name' => 'Manager',
                //     'guard_name'=>'web'

                // ),
                // array(
                //     'id' => 6,
                //     'name' => 'User',
                //     'guard_name'=>'web'

                // ),
                array(
                    'id' => 7,
                    'name' => 'Lawyer',
                    'guard_name'=>'web'

                ),
            ); 
          

        $option_count = DB::table('main_roles')->count();
        if($option_count == 0)
        {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('main_roles')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::table('main_roles')->insert($maps);

        }else{

            foreach ($maps as $key=> $permission) {
                $payop = Role::where('id', $permission['id'])->first();
  
                if ($payop !== null) {
                    $payop->update(['name' => $permission['name']]);
                } else {
                    $payop = Role::create([
                        'id' => $permission['id'],
                        'name' => $permission['name'],
                    ]);
                }
            }
        }


    }
}
