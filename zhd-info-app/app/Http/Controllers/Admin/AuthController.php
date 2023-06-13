<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function index ()
    {
        return view('auth.index');
    }

    public function login (Request $request)
    {
        $user = User::where('email', $request->loginname)->first();
        if (empty($user)) {
            return view('auth.index', ['message' => 'ログインに失敗しました。']);
        }

        session()->put(['user_id' => $user->id, 'user_name' => $user->name]);

        return redirect()->route('massage.publish');
    }
}
