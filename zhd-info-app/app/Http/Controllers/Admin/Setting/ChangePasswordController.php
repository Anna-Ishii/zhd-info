<?php

namespace App\Http\Controllers\Admin\Setting;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
{
    public function index()
    {
        return view('admin.setting.change_password');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'oldpasswd' => 'required',
            'newpasswd' => 'required',
        ]);
        if ($validator->fails()) {
            // エラー発生時の処理
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = session('user');

        // 現在のパスワードチェック
        if (!Hash::check($request->oldpasswd, $user->password)) {
            return redirect()
                ->back()
                ->with('error', 'パスワードが一致しません');
        }

        $newpassword = Hash::make($request->newpasswd);

        try {
            $user->password = $newpassword;
            $user->save();
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'サーバーエラーです');
        }

        return redirect()
            ->back()
            ->with('message', 'パスワード変更完了しました');
    }
        
    
}
