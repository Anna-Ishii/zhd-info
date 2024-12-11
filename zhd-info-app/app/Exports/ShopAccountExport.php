<?php

namespace App\Exports;

use App\Models\Shop;
use App\Models\User;
use App\Models\Organization1;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ShopAccountExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => false,
            'output_encoding' => 'CP932',
        ];
    }

    public function view(): View
    {
        $admin = session('admin');
        $organization1_list = $admin->organization1()->orderby('name')->get();

        // request
        $org = $this->request->input('org');
        $shop_freeword = $this->request->input('shop_freeword');
        $message_freeword = $this->request->input('message_freeword');

        $organization1_id = $this->request->input('organization1', $organization1_list[0]->id);
        $organization1 = Organization1::find($organization1_id);
        $organization2 = $this->request->input('organization2');
        $roll = $this->request->input('roll');
        $q = $this->request->input('q');

        $organization_list = [];
        $organizations = [];

        // 店舗情報を取得
        $shops = [];
        if (is_null($this->request->input('shop'))) {
            if (isset($organization2)) {
                $shops = Shop::select('id')->where('organization2_id', '=', $organization2)->get()->toArray();
            }
        } else {
            $shops[] = $this->request->input('shop');
        }

        // ユーザー情報を取得
        $users =
            User::query()
            ->select(
                'users.id',
                'shops.name as shop_name',
                'users.email',
                'users.shop_id',
                'wowtalk_shops.wowtalk1_id',
                'wowtalk_shops.notification_target1',
                'wowtalk_shops.business_notification1',
                'wowtalk_shops.wowtalk2_id',
                'wowtalk_shops.notification_target2',
                'wowtalk_shops.business_notification2',
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
                'users_roles.AM_view_notification'
            )
            ->leftJoin('shops', 'users.shop_id', '=', 'shops.id')
            ->leftJoin('wowtalk_shops', 'users.shop_id', '=', 'wowtalk_shops.shop_id')
            ->leftJoin('users_roles', 'users.id', '=', 'users_roles.user_id')
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
                        ->orwhere(DB::raw('SUBSTRING(shops.id, -4)'), 'LIKE', '%' . $shop_freeword . '%');
                });
            })
            ->when(isset($message_freeword), function ($query) use ($message_freeword) {
                $query->where(function ($query) use ($message_freeword) {
                    $query->where('users_roles.DM_id', 'like', '%' . $message_freeword . '%')
                        ->orWhere('users_roles.BM_id', 'like', '%' . $message_freeword . '%')
                        ->orWhere('users_roles.AM_id', 'like', '%' . $message_freeword . '%')
                        ->orWhere('users_roles.DM_name', 'like', '%' . $message_freeword . '%')
                        ->orWhere('users_roles.BM_name', 'like', '%' . $message_freeword . '%')
                        ->orWhere('users_roles.AM_name', 'like', '%' . $message_freeword . '%');
                });
            })
            ->orderBy('users.created_at', 'desc')
            ->paginate(50)
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

        $organization_ids = $users->pluck('shop_id')->unique();

        $org3_data = DB::table('shops')
            ->select('shops.id as shop_id', 'organization3.name as org3_name')
            ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
            ->whereIn('shops.id', $organization_ids)
            ->get()
            ->groupBy('shop_id');

        $org4_data = DB::table('shops')
            ->select('shops.id as shop_id', 'organization4.name as org4_name')
            ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
            ->whereIn('shops.id', $organization_ids)
            ->get()
            ->groupBy('shop_id');

        $org5_data = DB::table('shops')
            ->select('shops.id as shop_id', 'organization5.name as org5_name')
            ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
            ->whereIn('shops.id', $organization_ids)
            ->get()
            ->groupBy('shop_id');

        foreach ($users as $user) {
            $shop_id = $user->shop_id;
            if (isset($org3_data[$shop_id])) {
                $organizations[$shop_id]['DS'] = $org3_data[$shop_id];
            }
            if (isset($org4_data[$shop_id])) {
                $organizations[$shop_id]['AR'] = $org4_data[$shop_id];
            }
            if (isset($org5_data[$shop_id])) {
                $organizations[$shop_id]['BL'] = $org5_data[$shop_id];
            }
        }

        // 閲覧状況通知、業務連絡通知のチェックを〇に変換
        $users->getCollection()->transform(function ($user) {
            $user->notification_target1 = $user->notification_target1 ? '〇' : '';
            $user->business_notification1 = $user->business_notification1 ? '〇' : '';
            $user->notification_target2 = $user->notification_target2 ? '〇' : '';
            $user->business_notification2 = $user->business_notification2 ? '〇' : '';
            $user->DM_view_notification = $user->DM_view_notification ? '〇' : '';
            $user->BM_view_notification = $user->BM_view_notification ? '〇' : '';
            $user->AM_view_notification = $user->AM_view_notification ? '〇' : '';
            return $user;
        });

        return view('exports.shop-account-export', [
            'users' => $users,
            'organization1_list' => $organization1_list,
            'organization_list' => $organization_list,
            'organizations' => $organizations,
        ]);
    }
}
