<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Traits\{ApiResponser};
use Illuminate\Support\Facades\Cache;

class RolePermissionController extends Controller
{
    use ApiResponser;
    public function indexRole(Request $request)
    {
        $roles = Role::with('permissions');
        // if(auth()->user()->is_superadmin != 1){
        //     $roles =$roles->where('id','>','5');
        // }
        $roles =$roles->orderBy('id','ASC')->get();
        $permissions = Permission::get();
        $prmArr = [];
        if(sizeof($permissions) > 0) {
            foreach($permissions as $key => $permission){
                if($permission->controller){
                    $prmArr[$permission->controller][$key]['id'] = $permission->id;
                    $prmArr[$permission->controller][$key]['web']= $permission->web;
                    $prmArr[$permission->controller][$key]['name'] = $permission->name;
                    $prmArr[$permission->controller][$key]['controller'] = $permission->controller;
                } 
            }
        }
        // pr($prmArr);
        return view('backend/role_permission/index',compact('roles','prmArr'));
    }

    public function getRole(Request $request,$id)
    {
        $roles = Role::with('permissions')->findOrFail($request->id);
        $permissions = Permission::get();
        $select ='';
        $selected = "";
        foreach($permissions as $perm)
        {
            if(in_array($perm->id,$roles->permissions->pluck('id')->toArray()))
            {
                $selected = 'Selected';
            }else{
                $selected = '';
            }
            $select .="<option value='{$perm->id}' {$selected}>$perm->name</option>";
        }

        return response()->json(['select'=>$select,'role'=>$roles]);
    }

    public function saveRole(Request $request)
    {    
        try{
            $this->validate($request, [
                'role_name' => 'required|unique:main_roles,name,'.$request->id
            ]);
            
            $role = Role::updateOrCreate(
                ['id' => $request->id],
                ['name' => $request->input('role_name')]
            );
            return redirect()->back()->withSuccess('Role Created.');
        }catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage() . ' in file: ' . $e->getFile() . ' at line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Something went wrong, please try again later'], 500);
        }
    }

    public function saveRolePermissions(Request $request)
    {
        try {
            $role = Role::findOrFail($request->role_id);
            return $role->syncPermissions($request->checkedPermission) ? response()->json([ 'status' => __('success'), 'message' => __('Permission updated Successfully!') ]) : response()->json([ 'status' => __('error'), 'message' => __('Something went wrong!') ]);
        }catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage() . ' in file: ' . $e->getFile() . ' at line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Something went wrong, please try again later'], 500);
        }
    }


    public function indexPermission(Request $request)
    {
         // $role = Role::create(['name' => 'Super Admin']);
        // $permission = Permission::create(['name' => 'All Pages articles']);
        // $role = Role::first();
        // $permission = Permission::first();
        // $role->givePermissionTo($permission);
        // $permission->assignRole($role);

        // $user = auth()->user();
        // dd($user);

        $permissions = Permission::orderBy('id','ASC')->get();
        return view('backend/role_permission/permission',compact('permissions'));
    }
    
    public function savePermission(Request $request){    
        $val = $this->validate($request, [
            'permission_name' => 'required|unique:main_permissions,name'
        ]);
        $role = Permission::create(['name' => $request->input('permission_name')]);
        return redirect()->back()->withSuccess('Permission Created.');
    }

    public function getRolePermission(Request $request){
        try {
            $cacheKey = 'permissions_by_controller';
            $permissionsByController = Cache::rememberForever($cacheKey, function () {
                $permissions = Permission::whereNotNull('controller')->get();
                return $permissions->groupBy('controller')
                ->map(function ($permissions) {
                    return $permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'web' => $permission->web,
                            'name' => $permission->name,
                            'controller' => $permission->controller,
                        ];
                    });
                })->toArray();
            });
        
            $roles = Role::with('permissions')->findOrFail($request->role_id);
            $role_has_permission_ids = $roles->permissions->pluck('id')->toArray();
            //pr($role_has_permission_ids);
            if ($request->ajax()) {
                $permission_html = view('backend.role_permission.roleTable', ['role_has_permission_ids' => $role_has_permission_ids,'prmArr' => $permissionsByController])->render();
                return $this->successResponse(['permission_html' => $permission_html], '', 201);
            }
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage() . ' in file: ' . $e->getFile() . ' at line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Something went wrong, please try again later'], 500);
        }
    }
}