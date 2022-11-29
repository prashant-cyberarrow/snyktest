<?php

namespace App\Http\Controllers\Admin\Auth;

use Auth;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware(['guest:web'])->except('logout');
    }

    public function showLoginForm()
    {
        return view('admin.auth.admin-login');
    }

    public function login(Request $request)
    {
        $statusCheck = User::admins()->where('username',$request->username)->first();
        if(!isset($statusCheck))
        {
            return back()->with('error','Invalid Username');
        }
        if($statusCheck->publish_status == '0'){
            return back()->with('error','Invalid Access');
        }
        if($statusCheck->delete_status == '1'){
            return back()->with('error','Invalid Access');
        }
        //valid the form data
        $this->validate($request,[
            'username' => 'required',
            'password' => 'required|min:6'
        ]);
        
        //Attempt to log the user in
        if(Auth::guard('web')->attempt(['username'=>$request->username,'password'=>$request->password],$request->remember)){
            //if success then redirect to location
            return redirect()->intended(route('admin.dashboard'));
            // return redirect()->intended(route('admin.index'));
        }
        //if unsuccessful then return back to login
        return back()->withInput($request->only('username','remember'))->with('error','Username and Password do not match');
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return redirect('ps-admin/login');
    }
}
