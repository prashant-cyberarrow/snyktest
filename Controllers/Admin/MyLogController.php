<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\UserLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class MyLogController extends Controller
{
    public function index()
    {
        // dd(Route::current()->uri);
        $logs = UserLog::orderBy('id', 'desc')->where('user_id', Auth::guard('web')->user()->id)->paginate(20);
        $users = User::admins()->where('delete_status', '0')->get();
        return view('admin.list.userlog', compact('logs', 'users'));
    }

    public function fetch(Request $request)
    {
        $date = explode("-", $request->dateRange);
    
        if (empty($date[0])) {
            $date = null;
        }

        $logs = UserLog::when($date != null, function ($query) use ($date) {
            $from = date("Y-m-d", strtotime($date[0]));
            $to = date("Y-m-d", strtotime($date[1]));
            return $query->whereBetween('date', array($from, $to));
        })->orderBy('id', 'desc')->paginate(20);

        $users = User::admins()->where('delete_status', '0')->get();

        return view('admin.list.ajaxlist.userlog', compact('logs', 'users'));
    }
}
