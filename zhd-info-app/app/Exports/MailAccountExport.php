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
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MailAccountExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings,
    WithHeadings,
    WithStyles
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
        $orgs = $this->request->input('org');
        $shop_freeword = $this->request->input('shop_freeword');

        $organization1_id = $this->request->input('organization1') ? base64_decode($this->request->input('organization1')) : $organization1_list[0]->id;
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
            ->get();

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
        $users->transform(function ($user) {
            $user->DM_view_notification = $user->DM_view_notification ? '〇' : '';
            $user->BM_view_notification = $user->BM_view_notification ? '〇' : '';
            $user->AM_view_notification = $user->AM_view_notification ? '〇' : '';
            return $user;
        });

        return view('exports.mail-account-export', [
            'users' => $users,
            'organization1_list' => $organization1_list,
            'organization_list' => $organization_list,
            'organizations' => $organizations,
        ]);
    }

    public function headings(): array
    {
        return [
            'ヘッダー1',
            'ヘッダー2',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ヘッダー行とA～E列を固定
        $sheet->freezePane('F3'); // F3の位置で固定
    }
}
