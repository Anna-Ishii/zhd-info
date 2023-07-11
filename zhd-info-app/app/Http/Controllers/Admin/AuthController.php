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
    public function index ($organization1)
    {
        //ログイン中か確認
        $admin = session('admin');

        if (isset($admin)) {
            return redirect()->route('admin.message.publish.index');
        }
        // 業態がなければ404を返す
        if(!Organization1::where('name', $organization1)->exists()) abort(404);
        return view('admin.auth.index');
    }

    public function login (AuthLoginRequest $request, $organization1)
    {
        $validated = $request->validated();
        
        $org1 = Organization1::where('name', $organization1)
                                ->firstOrFail();

        $admin = Admin::where('organization1_id', $org1->id)
                        ->where('employee_code', $request->employee_code)->first();

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

    public function logout (Request $request)
    {
        $request->session()->forget('admin');
        return response(status:200);
    }
}
