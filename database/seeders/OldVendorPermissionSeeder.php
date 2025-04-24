<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
class OldVendorPermissionSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

        $users = User::where('is_admin',1)->get();
        
        foreach ($users as $key=> $user) {
            //pr($users->toArray());
            $user->syncRoles(4);
            $user->save();
        }
       
    
    }
        
}
