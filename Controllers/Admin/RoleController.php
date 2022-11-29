<?php

namespace App\Http\Controllers\Admin;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\UserLogTrait;

class RoleController extends Controller
{
    use UserLogTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function index()
    {
        $roles = Role::get();
        return view('admin.list.role', compact('roles'));
    }

    public function create()
    {
        $contentPermission = Permission::where('table_name', 'tbl_contents')->get();
        $pagePermission = Permission::where('table_name', 'tbl_informations')->get();
        $categoryPermission = Permission::where('table_name', 'tbl_categories')->get();

        $reporterPermission = Permission::where('table_name', 'tbl_reporters')->get();
        $guestPermission = Permission::where('table_name', 'tbl_guests')->get();
        $teamPermission = Permission::where('table_name', 'tbl_teams')->get();

        $adminPermission = Permission::where('table_name', 'tbl_users')->get();
        $advertisementPermission = Permission::where('table_name', 'tbl_advertisements')->get();
        $tagPermission = Permission::where('table_name', 'tbl_tags')->get();

        $settingPermission = Permission::where('table_name', 'tbl_settings')->get();
        $mediaLibraryPermission = Permission::where('table_name', 'tbl_media_library')->get();
        $userLogPermission = Permission::where('table_name', 'tbl_user_logs')->get();
        $portalPermission = Permission::where('table_name', 'tbl_portals')->get();
        $rssPermission = Permission::where('table_name', 'tbl_link_setups')->get();
        $analyticsPermission = Permission::where('table_name', 'tbl_analytics')->get();

        $rolesPermission = Permission::where('table_name', 'tbl_roles')->get();

        return view('admin.form.role', compact(
            'contentPermission',
            'pagePermission',
            'categoryPermission',
            'reporterPermission',
            'guestPermission',
            'teamPermission',
            'adminPermission',
            'advertisementPermission',
            'tagPermission',
            'settingPermission',
            'mediaLibraryPermission',
            'userLogPermission',
            'portalPermission',
            'rssPermission',
            'analyticsPermission',
            'rolesPermission'
        ));
    }

    public function store(Request $request)
    {
        // $galleryId = request('gallery');

        //validate the form
        $this->validate(request(), [
            'name' => 'required|unique:roles|max:255',
        ]);
        //dd('wait');
        //create and save category
        $role = new Role();

        $role->name = request('name');
        // $role->guard_name = 'admin';

        $role->save();

        $permissionId = request('permissions');
        // dd($permissionId);

        if ($permissionId !== null) {
            foreach ($permissionId as $permission) {
                // dd($role);
                $role = Role::where('id', $role->id)->first();
                $role->givePermissionTo($permission);
            }
        }

        $this->storeUserLog('Role', $role->id,$role->name, 'create');

        return redirect('/ps-admin/roles')->with('success', 'Role created successfully.');
    }

    public function edit($role)
    {
        $role = Role::where('id', $role)->first();

        $contentPermission = Permission::where('table_name', 'tbl_contents')->get();
        $pagePermission = Permission::where('table_name', 'tbl_informations')->get();
        $categoryPermission = Permission::where('table_name', 'tbl_categories')->get();

        $reporterPermission = Permission::where('table_name', 'tbl_reporters')->get();
        $guestPermission = Permission::where('table_name', 'tbl_guests')->get();
        $teamPermission = Permission::where('table_name', 'tbl_teams')->get();

        $adminPermission = Permission::where('table_name', 'tbl_users')->get();
        $advertisementPermission = Permission::where('table_name', 'tbl_advertisements')->get();
        $tagPermission = Permission::where('table_name', 'tbl_tags')->get();

        $settingPermission = Permission::where('table_name', 'tbl_settings')->get();
        $mediaLibraryPermission = Permission::where('table_name', 'tbl_media_library')->get();
        $userLogPermission = Permission::where('table_name', 'tbl_user_logs')->get();
        $portalPermission = Permission::where('table_name', 'tbl_portals')->get();
        $rssPermission = Permission::where('table_name', 'tbl_link_setups')->get();
        $analyticsPermission = Permission::where('table_name', 'tbl_analytics')->get();

        $rolesPermission = Permission::where('table_name', 'tbl_roles')->get();


        $permissionSelected = $role->getAllPermissions();

        // dd($permissionSelected);

        return view('admin.form.role', compact(
            'role',
            'contentPermission',
            'pagePermission',
            'permissionSelected',
            'categoryPermission',
            'reporterPermission',
            'guestPermission',
            'teamPermission',
            'adminPermission',
            'advertisementPermission',
            'tagPermission',
            'settingPermission',
            'mediaLibraryPermission',
            'userLogPermission',
            'portalPermission',
            'rssPermission',
            'analyticsPermission',
            'rolesPermission'
        ));
    }

    public function update(Request $request, $id)
    {
        $role = Role::where('id', $id)->first();
        // dd($role);

        $this->validate(request(), [
            'name' => 'required',
        ]);

        $data = ([
            'name' => request('name'),
        ]);

        Role::where('id', $id)->update($data);

        $role->permissions()->detach();
        $permissionId = request('permissions');
        // dd($permissionId);

        if ($permissionId !== null) {
            foreach ($permissionId as $permission) {
                $role = Role::where('id', $id)->first();
                $role->givePermissionTo($permission);
            }
        }

        $this->storeUserLog('Role', $role->id,$role->name, 'update');

        //redirect to dashboard
        return redirect('/ps-admin/roles')->with('success', 'Role updated successfully.');
    }
}
