<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function index ()
    {
        return view('admin.auth.index');
    }

    public function login (Request $request)
    {
        $user = User::where('email', $request->loginname)->first();
        if (empty($user)) {
            return view('admin.auth.index', ['message' => 'ログインに失敗しました。']);
        }

        session()->put(['user' => $user]);

        return redirect()->route('admin.message.publish.index');
    }
}
