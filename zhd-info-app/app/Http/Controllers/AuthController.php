<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.index');
    }

    public function login(Request $request)
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
            session()->put(['member' => $user]);

            return redirect()->route('top');
        }

        return redirect()
            ->back()
            ->with('error', 'ログインに失敗しました');
 
    }
}
