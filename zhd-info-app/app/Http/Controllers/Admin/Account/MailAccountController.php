<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Organization1;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use App\Models\UsersRole;
use App\Models\SearchCondition;
use App\Exports\MailAccountExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class MailAccountController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $organization1_list = $admin->organization1()->orderby('name')->get();
        $roll_list = Roll::all();

        // request
        $orgs = $request->input('org');
        $shop_freeword = $request->input('shop_freeword');

        $organization1_id = $request->input('organization1') ? base64_decode($request->input('organization1')) : $organization1_list[0]->id;
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
                'users.shop_id',
                'users_roles.DM_id',
                'users_roles.DM_name',
                'users_roles.DM_email',
                'users_roles.DM_view_notification',
                'users_roles.BM_id',
                'users_roles.BM_name',
                'users_roles.BM_email',
                'users_roles.BM_view_notification',
                'users_roles.AM_id',
                'users_roles.AM_name',
                'users_roles.AM_email',
                'users_roles.AM_view_notification',
                'organization3.name as org3_name',
                'organization4.name as org4_name',
                'organization5.name as org5_name'
            )
            ->leftJoin('shops', 'users.shop_id', '=', 'shops.id')
            ->leftJoin('users_roles', 'users.shop_id', '=', 'users_roles.shop_id')
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
            ->when(isset($orgs['DS']), function ($query) use ($orgs) {
                $query->whereIn('shops.organization3_id', $orgs['DS']);
            })
            ->when(isset($orgs['AR']), function ($query) use ($orgs) {
                $query->whereIn('shops.organization4_id', $orgs['AR']);
            })
            ->when(isset($orgs['BL']), function ($query) use ($orgs) {
                $query->whereIn('shops.organization5_id', $orgs['BL']);
            })
            ->when(isset($shop_freeword), function ($query) use ($shop_freeword) {
                $query->where(function ($query) use ($shop_freeword) {
                    $query->where('shops.name', 'like', '%' . addcslashes($shop_freeword, '%_\\') . '%')
                        ->orWhere('shops.shop_code', 'like', '%' . addcslashes($shop_freeword, '%_\\') . '%');
                });
            })
            ->orderBy('organization3.order_no')
            ->orderBy('organization4.order_no')
            ->orderBy('organization5.order_no')
            ->orderBy('shops.shop_code')
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

        // 業連閲覧状況メール配信のチェックを〇に変換
        $users->getCollection()->transform(function ($user) {
            $user->DM_view_notification = $user->DM_view_notification ? '〇' : '';
            $user->BM_view_notification = $user->BM_view_notification ? '〇' : '';
            $user->AM_view_notification = $user->AM_view_notification ? '〇' : '';
            return $user;
        });

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

        return view('admin.account.mail.index', [
            'users' => $users,
            'roll_list' => $roll_list,
            'organization1_list' => $organization1_list,
            'organization_list' => $organization_list,
            'organizations' => $organizations,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
        ]);
    }

    // SESSIONに検索条件を保存
    public function saveSessionConditions(Request $request)
    {
        try {
            session(['mail_account_url' => $request->input('params')]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 業連閲覧状況メール配信の設定を更新
    public function userRoleUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            $userRoleData = json_decode($request->input('userRoleData'), true);

            foreach ($userRoleData as $data) {
                $userRole = UsersRole::where('user_id', $data['user_id'])->where('shop_id', $data['shop_id'])->first();

                if (!$userRole) {
                    continue;
                }

                $userRole->update([
                    'DM_view_notification' => $data['DM_status'],
                    'BM_view_notification' => $data['BM_status'],
                    'AM_view_notification' => $data['AM_status'],
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
        $organization1_id = $request->input('organization1') ? base64_decode($request->input('organization1')) : $organization1_list[0]->id;
        $organization1 = Organization1::find($organization1_id);

        $organization1 = $organization1->name;
        $now = new Carbon('now');
        $file_name = 'メール配信設定_' . $organization1 . $now->format('_Y_m_d') . '.xlsx';
        return Excel::download(
            new MailAccountExport($request),
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
