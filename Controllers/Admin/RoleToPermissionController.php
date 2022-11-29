<?php

namespace App\Http\Controllers\Admin;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleToPermissionController extends Controller
{
    public function index()
    {
        $roles = Role::get();
        $i=0;
        foreach($roles as $role)
        {
            $permissions = $role->getAllPermissions();
            $roles[$i]->setAttribute('permissions',$permissions);
            $i++;
        }
        return view('admin.list.ptor',compact('roles'));
    }

    public function edit($role)
    {
        $role = Role::where('id', $role)->first();
        $permissions = Permission::get();
        $permissionSelected = $role->getAllPermissions();
        
        return view('admin.form.ptor',compact('role','permissions','permissionSelected'));
    }

    public function update(Request $request,$id)
    {
        $role = Role::where('id', $id)->first();
        // dd($role);
        $role->permissions()->detach();
        $permissionId = request('permissions');
        
        // dd($permissionId);
        if($permissionId !== null){
            foreach($permissionId as $permission){
                $role = Role::where('id', $id)->first();
                $role->givePermissionTo($permission);
            }
        }

        //redirect to dashboard
        return redirect('/ps-admin/rolepermissions')->with('success','Permission assigned successfully.');
    }
}
