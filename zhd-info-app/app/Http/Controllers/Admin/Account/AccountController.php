<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc');
        return view('admin.account.index',[
            'users' => $users->paginate(5)
                            ->appends(request()->query()),
        ]);
    }
    
    public function new(Request $request)
    {
        if ($request->isMethod('post')) {
            if ($request->password != $request->password2) return redirect()
                                                                    ->route('admin.account.new')
                                                                    ->withInput()
                                                                    ->with('error', 'パスワードが一致しません'); 
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
            $params['password'] = Hash::make($request->password);
            
            try {
                User::create($params);

            } catch (\Throwable $th) {
                return redirect()
                        ->route('admin.account.new')
                        ->withInput()
                        ->with('error', 'データベースエラーです');
            }

            return redirect()->route('admin.account.index');
        }
        // TODO
        // 正式なものに直す
        $user_count = User::max('id') + 1;
        $shops = Shop::get();
        return view('admin.account.new',[
            'user_count' => $user_count,
            'shops' => $shops,
        ]);
    }

}