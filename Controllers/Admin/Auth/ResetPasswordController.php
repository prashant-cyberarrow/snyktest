<?php

namespace App\Http\Controllers\Admin\Auth;

use Auth;
use Password;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Mockery\Generator\StringManipulation\Pass\Pass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/ps-admin';

    public function __construct()
    {
        $this->middleware('guest:web');
    }

    protected function guard()
    {
        return Auth::guard('web');
    }

    protected function broker()
    {
        return Password::broker('users');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('admin.auth.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }
}