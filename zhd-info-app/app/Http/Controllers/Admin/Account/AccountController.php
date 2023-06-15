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
            if ($request->password != $request->password2) return redirect()
                                                                    ->route('admin.account.new')
                                                                    ->withInput()
                                                                    ->with('error', '入力エラーがあります');

            $params = $request
                        ->only([
                            'name', 
                            'belong_label', 
                            'shop_id', 
                            'employee_code', 
                            'password', 
                            'email', 
                            'roll_id'
                        ]);
            try {
                User::create($params);
            } catch (\Throwable $th) {
                return redirect()
                        ->route('admin.account.new')
                        ->withInput()
                        ->with('error', '入力エラーがあります');
            }

            return redirect()->route('admin.account.index');
        }
        // TODO
        // 正式なものに直す
        $user_count = User::all()->count() + 1;

        return view('admin.account.new',[
            'user_count' => $user_count,
        ]);
    }

}