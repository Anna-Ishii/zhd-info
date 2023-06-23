<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index ()
    {
        //ログイン中か確認
        $user = session('user');
        if (isset($user)) {
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

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            return redirect()
                ->back()
                ->with('error', 'ログインに失敗しました');
        }

        if(Hash::check($request->password, $user->password)){
            session()->put(['user' => $user]);

            return redirect()->route('admin.message.publish.index');
        }

        return redirect()
            ->back()
            ->with('error', 'ログインに失敗しました');
    }
}
