<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $permission = null, $guard = null)
    {
                // permission middleware
                $checkPermissionEnable = @getAdditionalPreference(['is_role_and_permission_enable'])['is_role_and_permission_enable'];
                if($checkPermissionEnable)
                {

                        $guard = $guard ?? config('auth.defaults.guard');
                        $user = auth()->user();
                        $authGuard = app('auth')->guard($guard);
                        $permissionArray = $this->permissionUser($user);
                        $page = $request->route()->action['controller'];
                        $check = explode('\\',$page);
                        $cnt = count($check);
                        $pageUrl = $check[$cnt-1];
                        $check = explode('@',$pageUrl);
                        $page = $check[0];
                        $permissions = [];


                        if(isset($permissionArray[$page]) && count($permissionArray[$page])>0)
                        {
                            $permissions =  $permissionArray[$check[0]];
                        }else{
                           
                            if(@$user->is_superadmin==1 || @$user->is_admin==1){
                                return $next($request);
                            }
                            throw UnauthorizedException::forPermissions($permissions);
                        }

                        foreach ($permissions as $permission) {
                            if ($authGuard->user()->can($permission)) {
                                return $next($request);
                            }
                        }

                        throw UnauthorizedException::forPermissions($permissions);
                }else{
                return $next($request);
            }
    }


    public function permissionUser($user)
    {
        $permissionArray = array();

        foreach ($user->roles as $role) {
            foreach ($role->permissions as $key=> $perm) {
                $permissionArray[$perm->controller][] = $perm->name;
            }
        }
        return $permissionArray;
    }

}
