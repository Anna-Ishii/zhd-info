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
use App\Models\WowtalkShop;
use App\Exports\ShopAccountExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $organization1_list = $admin->organization1()->orderby('name')->get();
        $roll_list = Roll::all();

        // request
        $org = $request->input('org');
        $shop_freeword = $request->input('shop_freeword');

        $organization1_id = $request->input('organization1', $organization1_list[0]->id);
        $organization1 = Organization1::find($organization1_id);
        $organization2 = $request->input('organization2');
        $roll = $request->input('roll');
        $q = $request->input('q');

        $organization_list = [];
        $organizations = [];

        // 店舗情報を取得
        $shops = [];
        if (is_null($request->input('shop'))) {
            if (isset($organization2)) {
                $shops = Shop::select('id')->where('organization2_id', '=', $organization2)->get()->toArray();
            }
        } else {
            $shops[] = $request->input('shop');
        }

        // ユーザー情報を取得
        $users = User::query()
            ->select(
                'users.id',
                'shops.name as shop_name',
                'shops.shop_code',
                'users.email',
                'users.shop_id',
                'wowtalk_shops.wowtalk1_id',
                'wowtalk_shops.notification_target1',
                'wowtalk_shops.business_notification1',
                'wowtalk_shops.wowtalk2_id',
                'wowtalk_shops.notification_target2',
                'wowtalk_shops.business_notification2',
                'organization3.name as org3_name',
                'organization4.name as org4_name',
                'organization5.name as org5_name'
            )
            ->leftJoin('shops', 'users.shop_id', '=', 'shops.id')
            ->leftJoin('wowtalk_shops', 'users.shop_id', '=', 'wowtalk_shops.shop_id')
            ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
            ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
            ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
            ->when(isset($q), function ($query) use ($q) {
                $query->whereLike('shops.name', $q);
            })
            ->when(!empty($shops), function ($query) use ($shops) {
                $query->whereIn('users.shop_id', $shops);
            })
            ->when(isset($roll), function ($query) use ($roll) {
                $query->where('roll_id', '=', $roll);
            })
            ->when(isset($organization1_id), function ($query) use ($organization1_id) {
                $query->where('shops.organization1_id', '=', $organization1_id);
            })
            ->where('shops.organization1_id', '=', $organization1->id)
            ->when(isset($org['DS']), function ($query) use ($org) {
                $query->where('shops.organization3_id', '=', $org['DS']);
            })
            ->when(isset($org['AR']), function ($query) use ($org) {
                $query->where('shops.organization4_id', '=', $org['AR']);
            })
            ->when(isset($org['BL']), function ($query) use ($org) {
                $query->where('shops.organization5_id', '=', $org['BL']);
            })
            ->when(isset($shop_freeword), function ($query) use ($shop_freeword) {
                $query->where(function ($query) use ($shop_freeword) {
                    $query->where('shops.name', 'like', '%' . addcslashes($shop_freeword, '%_\\') . '%')
                        ->orWhere(DB::raw('SUBSTRING(shops.id, -4)'), 'LIKE', '%' . $shop_freeword . '%')
                        ->orWhere('shops.shop_code', 'like', '%' . addcslashes($shop_freeword, '%_\\') . '%'); // 追加部分
                });
            })
            ->orderBy('organization3.order_no')
            ->orderBy('organization4.order_no')
            ->orderBy('organization5.order_no')
            ->orderBy('users.shop_id')
            ->paginate(5000)
            ->appends(request()->query());

        // 組織情報を取得
        if ($organization1->isExistOrg3()) {
            $organization_list["DS"] = $organization1->getOrganization3();
        }
        if ($organization1->isExistOrg4()) {
            $organization_list["AR"] = $organization1->getOrganization4();
        }
        if ($organization1->isExistOrg5()) {
            $organization_list["BL"] = $organization1->getOrganization5();
        }

        foreach ($users as $user) {
            $shop_id = $user->shop_id;
            if ($user->org3_name) {
                $organizations[$shop_id]['DS'] = collect([['org3_name' => $user->org3_name]]);
            }
            if ($user->org4_name) {
                $organizations[$shop_id]['AR'] = collect([['org4_name' => $user->org4_name]]);
            }
            if ($user->org5_name) {
                $organizations[$shop_id]['BL'] = collect([['org5_name' => $user->org5_name]]);
            }
        }

        // 閲覧状況通知、業務連絡通知のチェックを〇に変換
        $users->getCollection()->transform(function ($user) {
            $user->notification_target1 = $user->notification_target1 ? '〇' : '';
            $user->business_notification1 = $user->business_notification1 ? '〇' : '';
            $user->notification_target2 = $user->notification_target2 ? '〇' : '';
            $user->business_notification2 = $user->business_notification2 ? '〇' : '';
            return $user;
        });

        return view('admin.account.index', [
            'users' => $users,
            'roll_list' => $roll_list,
            'organization1_list' => $organization1_list,
            'organization_list' => $organization_list,
            'organizations' => $organizations,
        ]);
    }

    public function new()
    {
        $user_count = User::withTrashed()->max('id') + 1;
        $organization1_list = Organization1::get();
        $organization2_list = Organization2::get();
        $shops = Shop::where('organization2_id', 1)->get();
        $roll_list = Roll::get();
        return view('admin.account.new', [
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

    // WowTalkのアラート設定を更新
    public function wowtalkAlertUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            $wowtalkAlertData = json_decode($request->input('wowtalkAlertData'), true);

            foreach ($wowtalkAlertData as $data) {
                $wowtalkShop = WowtalkShop::where('shop_id', $data['shop_id'])->first();

                if (!$wowtalkShop) {
                    continue;
                }

                $wowtalkShop->update([
                    'notification_target1' => $data['WT1_status'],
                    'business_notification1' => $data['WT1_send'],
                    'notification_target2' => $data['WT2_status'],
                    'business_notification2' => $data['WT2_send'],
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => '更新が完了しました。'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => '更新中にエラーが発生しました。',
                'errors' => ['system' => [$e->getMessage()]]
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $admin = session('admin');
        $organization1_list = $admin->organization1()->orderby('name')->get();
        $organization1_id = $request->input('organization1', $organization1_list[0]->id);
        $organization1 = Organization1::find($organization1_id);

        $organization1 = $organization1->name;
        $now = new Carbon('now');
        $file_name = '店舗アカウント_' . $organization1 . $now->format('_Y_m_d') . '.xlsx';
        return Excel::download(
            new ShopAccountExport($request),
            $file_name,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function getOrganization(Request $request)
    {
        $organization1_id = $request->input("organization1");
        $organization1 = Organization1::findOrFail($organization1_id);

        $organization3 = $organization1->getOrganization3();
        $organization4 = $organization1->getOrganization4();
        $organization5 = $organization1->getOrganization5();

        return response()->json([
            'organization3' => $organization3,
            'organization4' => $organization4,
            'organization5' => $organization5,
        ], 200);
    }
}
