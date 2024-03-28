<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthLoginRequest;
use App\Models\Admin;
use App\Models\Organization1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index ()
    {
        //ログイン中か確認
        $admin = session('admin');

        if (isset($admin)) {
            return redirect()->route('admin.message.publish.index');
        }

        return view('admin.auth.index');
    }

    public function login (AuthLoginRequest $request)
    {
        $validated = $request->validated();

        $admin = Admin::where('employee_code', $request->employee_code)->first();

        if (empty($admin)) {
            return redirect()
                ->back()
                ->with('error', '存在しない社員番号です')
                ->withInput();
        }

        if(Hash::check($request->password, $admin->password)){
            session()->put(['admin' => $admin]);

            return redirect()->route('admin.message.publish.index');
        }

        return redirect()
            ->back()
            ->with('error', 'ログインに失敗しました')
            ->withInput();
    }

    public function logout ()
    {
        $admin = session('admin');
        session()->forget('admin');
        return redirect()->route('admin.auth');
    }
}
