<?php

namespace App\Http\Controllers\Admin;

use Image;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\UserLogTrait;

class AdminController extends Controller
{
    use UserLogTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
    }
    
    public function index(Request $request)
    {
        // $request->session()->forget('adminsearch');
        $admins = User::admins()->orderBy('id','desc')->paginate(10);
        $i=0;
        foreach($admins as $admin)
        {
            $roles = $admin->getRoleNames();
            $admins[$i]->setAttribute('roles',$roles);
            $admins[$i]->setAttribute('totalCreatedContent',$admin->contentCreates->count());
            $i++;
        }  
        return view('admin.list.admin',compact('admins'));
    }

    public function fetch(Request $request)
    {
        $adminName = $request->adminName;

        $admins = User::admins()->when($adminName,function($query,$adminName){
            return $query->where("full_name","LIKE","%$adminName%");
        })->orderBy('id', 'desc')->paginate(10);
        $i=0;
        foreach($admins as $admin)
        {
            $roles = $admin->getRoleNames();
            $admins[$i]->setAttribute('roles',$roles);
            $admins[$i]->setAttribute('totalCreatedContent',$admin->contentCreates->count());
            $i++;
        }

        return view('admin.list.ajaxlist.admin', compact('admins'));
    }

    public function create(User $user)
    {
        // if(\Gate::denies('create',$admin)){
        //     return redirect('/admin');
        // }
        // if(1 !== Auth::guard('admin')->user()->id){
        //     abort(403);
        // }
        $roles = Role::get();
        // dd($roles);
        return view('admin.form.admin',compact('roles'));
    }

    public function store(Request $request,User $user)
    {
        //validate the form
        $this->validate(request(), [

            'full_name' => 'required',
            'username' => 'required|unique:tbl_users|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_users,email,'.$user->id,
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'

        ]);
        //dd('wait');
        //create and save category
        $user = new User();

        $user->full_name = request('full_name');
        $user->username = request('username');
        $user->type = 'admin';
        $user->password = Hash::make($request->password);
        $user->email = request('email');
        $user->publish_status = request('publish_status');

        $file = request()->file('image');

        if($file != null) {

            $image_name = "admin-".time().".".$file->clientExtension();

            // open an image file
            $img = Image::make($file);

            // save image in desired format
            $img->save('uploads/'.'admins/'.$image_name);

            $user->image = $image_name;
        }

        $user->save();

        $roleId = request('roles');

        if($roleId !== null){
            foreach($roleId as $role){
                // dd($role);
                $user = User::where('id',$user->id)->first();
                $user->assignRole($role);
            }
        }

        $this->storeUserLog('Admin',$user->id,$user->full_name,'create');

        //redirect to dashboard
        return redirect('/ps-admin/admins')->with('success','Admin created successfully.');
    }

    public function viewProfile($id)
    {
        $admin = User::admins()->where('id', $id)->first();
    
        $roles = $admin->getRoleNames();
        $admin->setAttribute('roles',$roles);
        $admin->setAttribute('totalCreatedContent',$admin->contentCreates->count());
 
        return view('admin.pages.admin',compact('admin','roles'));
    }

    public function edit($id)
    {
        // $id = $admin->id;
        // if($admin->id !== Auth::guard('admin')->user()->id){
        //     abort(403);
        // }
        $admin = User::admins()->where('id', $id)->first();
        $roles = Role::get();
        $roleSelected = $admin->getRoleNames();
        // dd($roleSelected[1]);

        // dd($admin);
        return view('admin.form.admin',compact('admin','roles','roleSelected'));
    }

    public function update(Request $request, $id,User $user)
    {
        $admin = User::admins()->find($id);

        $this->validate(request(), [

            'full_name' => 'required',
            'username' => 'required|unique:tbl_users,username,'.$id,      
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_users,email,'.$id,
            'password' => 'required_with:password_confirmation|same:password_confirmation',
        ]);

        $data = ([
            'full_name' => request('full_name'),
            'username' => request('username'),
            'email' => request('email'),
            'publish_status' => request('publish_status'),
        ]);

        /////////// For password change//////////////////
        $pass = request('password');
        if($pass != null){
            $data2 = ([
                'password' => Hash::make(request('password'))
            ]);
            User::admins()->where('id', $id)->update($data2);
        }

        $file = request()->file('image');

        if($file != null) {

            //deleting previous image
            $image = $admin->image;
            @unlink('uploads/'.'admins/'.$image);

            $image_name = "admin-".time().".".$file->clientExtension();

            // open an image file
            $img = Image::make($file);

            $img->save('uploads/'.'admins/'.$image_name);

            $data1 = (['image' => $image_name]);
            User::admins()->where('id', $id)->update($data1);
        }

        User::admins()->where('id', $id)->update($data);

        $user = User::admins()->where('id',$id)->first();
        $user->roles()->detach();
        $roleId = request('roles');
        // dd($roleId);
        if($roleId !== null){
            foreach($roleId as $role){
                // dd($role);
                $user = User::admins()->where('id',$id)->first();
                // dd($role);
                $user->assignRole($role);
            }
        }

        $this->storeUserLog('Admin',$user->id,$user->full_name,'update');

        //redirect to dashboard
        return redirect('/ps-admin/admins')->with('success','Admin updated successfully.');
    }

    public function destroy($id)
    {
        abort_if(auth()->id() != 1, 403);
        $admin = User::admins()->where('id', $id)->first();
        if($admin->id == 1){
            return back()->with('error','Super Admin cannot be delete!');
        }

        if(isset($admin))
        {
            // $image = $admin->image;
            // @unlink('uploads/'.'admins/'.$image);
            $data = ([
                'delete_status' => '1',
            ]);
            //deleting admin
            User::admins()->where('id', $id)->update($data);
            
            $this->storeUserLog('Admin',$admin->id,$admin->full_name,'delete');

            return redirect('/ps-admin/admins')->with('success','Admin deleted successfully.');
        }

        return redirect('/ps-admin/admins')->with('error','Admin deletion failed.');
    }
}
