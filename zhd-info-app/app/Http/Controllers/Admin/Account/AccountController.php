<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.account.index',[
            'users' => $users,
        ]);
    }
    
    public function new(Request $request)
    {
        if ($request->isMethod('post')) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->employee_code = $request->employee_code;
            $user->shop_id = 1;//$request->shop_id;
            $user->roll_id = $request->target_roll;
            $user->save();
            session()->put(['user' => $user]);
            redirect('admin.account.index');
        }
        // TODO
        // 正式なものに直す
        $user_count = User::all()->count() + 1;
        return view('admin.account.new',[
            'user_count' => $user_count,
        ]);
    }

}