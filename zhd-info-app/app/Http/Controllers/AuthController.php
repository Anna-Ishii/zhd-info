<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        //ログイン中か確認
        $user = session('member');
        if (isset($user)) {
            return redirect()->route('top');
        }
        return view('auth.index');
    }

    public function login(AuthLoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            return redirect()
                ->back()
                ->with('error', '存在しないメールアドレスです');
        }

        if(Hash::check($request->password, $user->password)){
            session()->put(['member' => $user]);

            return redirect()->route('top');
        }

        return redirect()
            ->back()
            ->with('error', 'ログインに失敗しました');
 
    }
    public function logout(Request $request)
    {
        $request->session()->forget('member');
        return redirect()->route('auth');
    }
}
