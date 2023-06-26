<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    public function login (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            // エラー発生時の処理
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $admin = Admin::where('email', $request->email)->first();

        if (empty($admin)) {
            return redirect()
                ->back()
                ->with('error', 'ログインに失敗しました');
        }

        if(Hash::check($request->password, $admin->password)){
            session()->put(['admin' => $admin]);

            return redirect()->route('admin.message.publish.index');
        }

        return redirect()
            ->back()
            ->with('error', 'ログインに失敗しました');
    }

    public function logout (Request $request)
    {
        $request->session()->forget('admin');
        return response(status:200);
    }
}
