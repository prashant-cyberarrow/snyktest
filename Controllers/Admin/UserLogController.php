<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\UserLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserLogController extends Controller
{
    public function index()
    {
        $logs = UserLog::orderBy('id', 'desc')->paginate(20);
        $users = User::admins()->where('delete_status', '0')->get();
        return view('admin.list.userlog', compact('logs', 'users'));
    }

    public function fetch(Request $request)
    {
        $date = explode("-", $request->dateRange);
        // dd($date);
        // if($date != null)
        // {
        //     dd(date("Y-m-d", strtotime($date[0])));
        // }
        if(empty($date[0])){
            $date = null;
        }
     
        // dump(date("Y-m-d", strtotime($date[0])));
        
        $logs = UserLog::when($date != null, function ($query) use($date) {
            $from = date("Y-m-d", strtotime($date[0]));
            $to = date("Y-m-d", strtotime($date[1]));
            return $query->whereBetween('date', array($from, $to));
        })->orderBy('id', 'desc')->paginate(20);

        $users = User::admins()->where('delete_status', '0')->get();

        return view('admin.list.ajaxlist.userlog', compact('logs','users'));
    }
}
