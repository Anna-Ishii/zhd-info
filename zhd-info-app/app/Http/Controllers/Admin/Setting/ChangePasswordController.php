<?php

namespace App\Http\Controllers\Admin\Setting;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\ChangePasswordEditRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\SearchCondition;

class ChangePasswordController extends Controller
{
    public function index()
    {
        $admin = session('admin');

        // 検索条件を取得
        $message_saved_url = SearchCondition::where('admin_id', $admin->id)
            ->where('page_name', 'message-publish')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();
        $manual_saved_url = SearchCondition::where('admin_id', $admin->id)
            ->where('page_name', 'manual-publish')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();
        $analyse_personal_saved_url = SearchCondition::where('admin_id', $admin->id)
            ->where('page_name', 'analyse-personal')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();

        return view('admin.setting.change_password', compact('message_saved_url', 'manual_saved_url', 'analyse_personal_saved_url'));
    }

    public function edit(ChangePasswordEditRequest $request)
    {
        $validated = $request->validated();

        $admin = session('admin');

        // 現在のパスワードチェック
        if (!Hash::check($request->oldpasswd, $admin->password)) {
            return redirect()
                ->back()
                ->with('error', 'パスワードが一致しません');
        }

        $newpassword = Hash::make($request->newpasswd);

        try {
            $admin->password = $newpassword;
            $admin->save();
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
