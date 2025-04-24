<?php
namespace Database\Seeders;
use DB;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionAssignSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

        $vendorPermissions = ['dashboard-view','dashboard-weekRevenue','dashboard-locationRevenue','dashboard-monthRevenue','dashboard-totalRevenue','order-view','order-accept','vendor-view','vendor-add','vendor-setting','vendor-catalog','vendor-config','vendor-categories','vendor-payout','vendor-add-users'];
        
        $rolesAdmin =  Role::findOrFail(2);
        $rolesSeller =  Role::findOrFail(4);
        
        $rolesAdmin->syncPermissions();
        $rolesAdmin->syncPermissions($vendorPermissions);

        $rolesSeller->syncPermissions();
        $rolesSeller->syncPermissions($vendorPermissions);
       
    }
}
