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
        $users = User::query()
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
                        ->orwhere(DB::raw('SUBSTRING(shops.id, -4)'), 'LIKE', '%' . $shop_freeword . '%');
                });
            })
            ->when(isset($message_freeword), function ($query) use ($message_freeword) {
                $query->where(function ($query) use ($message_freeword) {
                    $query->where('wowtalk_shops.wowtalk1_id', 'like', '%' . $message_freeword . '%')
                        ->orWhere('wowtalk_shops.wowtalk2_id', 'like', '%' . $message_freeword . '%');
                });
            })
            ->orderBy('organization3.order_no')
            ->orderBy('organization4.order_no')
            ->orderBy('organization5.order_no')
            ->orderBy('users.shop_id')
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

        return view('exports.shop-account-export', [
            'users' => $users,
            'organization1_list' => $organization1_list,
            'organization_list' => $organization_list,
            'organizations' => $organizations,
        ]);
    }
}
