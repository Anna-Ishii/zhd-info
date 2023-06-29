<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Account\AccountStoreRequest;
use App\Models\Manual;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\Organization2;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc');

        return view('admin.account.index',[
            'users' => $users->paginate(5)
                            ->appends(request()->query()),
        ]);
    }
    
    public function new()
    {
        $user_count = User::withTrashed()->max('id') + 1;
        $organization1_list = Organization1::get();
        $organization2_list = Organization2::get();
        $shops = Shop::get();
        $roll_list = Roll::get();
        return view('admin.account.new',[
            'user_count' => $user_count,
            'shops' => $shops,
            'organization1_list' => $organization1_list,
            'organization2_list' => $organization2_list,
            'roll_list' => $roll_list,
        ]);
    }

    public function store(AccountStoreRequest $request)
    {
        $validated = $request->validated();

        $params = $request->safe()->all();
        $params['password'] = Hash::make($request->password);
        
        $roll_id = $request->roll_id;
        $shop = Shop::find($request->shop_id);
        $organization4_id = $shop->organization4_id;
        $organization1_id = $shop->organization1_id;

        try {
            DB::beginTransaction();
            $user = User::create($params);
            $message_data = [];
            // 該当のメッセージを登録
            $messages = Message::whereHas('roll', function ($query) use ($roll_id) {
                $query->where('roll_id', '=', $roll_id);
            })->whereHas('organization4', function ($query) use ($organization4_id) {
                $query->where('organization4_id', '=', $organization4_id);
            })->get('id')->toArray();
            foreach ($messages as $message) {
                $message_data[$message['id']] = ['shop_id' => $request->shop_id];
            }
            $user->message()->attach($message_data);

            $manual_data = [];
            // 該当のマニュアルを登録
            $manuals = Manual::whereHas('organization1', function ($query) use ($organization1_id) {
                $query->where('organization1_id', '=', $organization1_id);
            })->get('id')->toArray();
            foreach ($manuals as $manual) {
                $manual_data[$manual['id']] = ['shop_id' => $request->shop_id];
            }
            $user->manual()->attach($manual_data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        return redirect()->route('admin.account.index');
    }

    public function delete(Request $request)
    {
        $data = $request->json()->all();
        User::whereIn('id', $data['user_id'])->delete();
        return response()->json(['message' => '削除しました'], status: 200);
    }
}