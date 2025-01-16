<?php

namespace App\Exports;

use App\Enums\PublishStatus;
use App\Models\Message;
use App\Models\Organization1;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PersonAnalysisExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings,
    WithHeadings,
    WithStyles
{
    protected $organization1;
    protected $startOfLastWeek;
    protected $endOfLastWeek;

    public function __construct($organization1, $startOfLastWeek, $endOfLastWeek)
    {
        $this->organization1 = $organization1;
        $this->startOfLastWeek = $startOfLastWeek;
        $this->endOfLastWeek = $endOfLastWeek;
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
        $organization1 = $this->organization1;
        $startOfLastWeek = $this->startOfLastWeek;
        $endOfLastWeek = $this->endOfLastWeek;
        $orgazanizations = [];
        $viewrates = [];

        // 業務連絡を9件取得
        $messages = Message::query()
            ->select('messages.*')
            ->leftJoin('message_user', 'message_user.message_id', '=', 'messages.id')
            ->leftJoin('users', 'message_user.user_id', '=', 'users.id')
            ->leftJoin('shops', 'shops.id', '=', 'message_user.shop_id')
            ->where('start_datetime', '>=', $startOfLastWeek)
            ->where('start_datetime', '<=', $endOfLastWeek)
            ->where('messages.organization1_id', '=', $organization1->id)
            ->orderBy('messages.id', 'desc')
            ->groupBy('messages.id')
            ->get();
        // 業務連絡の10件のidを取得
        $_messages = $messages->pluck('id')->toArray();

        // DS, AR, BLがあるかで処理を分ける
        if ($organization1->isExistOrg3()) {
            $orgazanizations[] = "DS";
            $organization_list["DS"] = $organization1->getOrganization3();
        }
        if ($organization1->isExistOrg4()) {
            $orgazanizations[] = "AR";
            $organization_list["AR"] = $organization1->getOrganization4();
        }
        if ($organization1->isExistOrg5()) {
            $orgazanizations[] = "BL";
            $organization_list["BL"] = $organization1->getOrganization5();
        }

        foreach ($_messages as $key => $ms) {
            // 業業 (計)
            $viewrate_org1 = DB::table('message_user')
            ->select([
                DB::raw('count(crews.id) as count'),
                DB::raw('count(crew_message_logs.crew_id) as readed_count'),
                DB::raw('round((count(crew_message_logs.crew_id) / count(crews.id)) * 100, 1)  as view_rate')
            ])
                ->leftJoin('messages', 'message_user.message_id', '=', 'messages.id')
                ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                ->leftJoin('organization1', 'shops.organization1_id', '=', 'organization1.id')
                ->leftJoin('users', 'message_user.user_id', '=', 'users.id')
                ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                    $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                    ->where('crew_message_logs.message_id', '=', $ms);
                })
                ->where('messages.id', '=', $ms)
                ->groupBy('shops.organization1_id')
                ->get();

            $viewrates['org1'][] = $viewrate_org1;
            $viewrates['org1_sum'] = ($viewrates['org1_sum'] ?? 0) + ($viewrate_org1[0]->count ?? 0);
            $viewrates['org1_readed_sum'] = ($viewrates['org1_readed_sum'] ?? 0) +  ($viewrate_org1[0]->readed_count ?? 0);

            // 組織ごと
            if (in_array('DS', $orgazanizations)) {
                $viewrates_org_sub =
                    DB::table('message_user')
                    ->select([
                        DB::raw('shops.organization3_id as o3_id'),
                        DB::raw('count(crews.id) as count'),
                        DB::raw('count(crew_message_logs.id) as readed_count'),
                        DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
                    ])
                    ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                    ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                    ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                        $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                        ->where('crew_message_logs.message_id', '=', $ms);
                    })
                    ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                    ->where('message_user.message_id', '=', $ms)
                    ->groupBy('shops.organization3_id');

                $viewrate =
                    DB::table('shops')
                    ->select([
                        DB::raw('organization3.id as id'),
                        DB::raw('organization3.name as name'),
                        DB::raw('sub.count as count'),
                        DB::raw('sub.readed_count as readed_count'),
                        DB::raw('sub.view_rate as view_rate')
                    ])
                    ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                    ->leftJoinSub($viewrates_org_sub, 'sub', function ($join) {
                        $join->on('shops.organization3_id', '=', 'sub.o3_id');
                    })
                    ->where('shops.organization1_id', '=', $organization1->id)
                    ->groupBy('shops.organization3_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization3.id')
                    ->get();

                $viewrates['DS'][] = $viewrate;
                $viewrates_array = $viewrate->toArray();
                foreach ($viewrates_array as $key => $value) {
                    $viewrates['DS_sum'][$value->id] = ($viewrates['DS_sum'][$value->id] ?? 0) + $value->count;
                    $viewrates['DS_readed_sum'][$value->id] = ($viewrates['DS_readed_sum'][$value->id] ?? 0) + $value->readed_count;
                }
            }
            if (in_array('AR', $orgazanizations)) {
                $viewrates_org_sub =
                    DB::table('message_user')
                    ->select([
                        DB::raw('shops.organization4_id as o4_id'),
                        DB::raw('count(crews.id) as count'),
                        DB::raw('count(crew_message_logs.id) as readed_count'),
                        DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
                    ])
                    ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                    ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                    ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                        $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                        ->where('crew_message_logs.message_id', '=', $ms);
                    })
                    ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                    ->where('message_user.message_id', '=', $ms)
                    ->groupBy('shops.organization4_id');

                $viewrate =
                    DB::table('shops')
                    ->select([
                        DB::raw('organization4.id as id'),
                        DB::raw('organization4.name as name'),
                        DB::raw('sub.count as count'),
                        DB::raw('sub.readed_count as readed_count'),
                        DB::raw('sub.view_rate as view_rate')
                    ])
                    ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
                    ->leftJoinSub($viewrates_org_sub, 'sub', function ($join) {
                        $join->on('shops.organization4_id', '=', 'sub.o4_id');
                    })
                    ->where('shops.organization1_id', '=', $organization1->id)
                    ->groupBy('shops.organization4_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization4.id')
                    ->get();

                $viewrates['AR'][] = $viewrate;
                $viewrates_array = $viewrate->toArray();
                foreach ($viewrates_array as $key => $value) {
                    $viewrates['AR_sum'][$value->id] = ($viewrates['AR_sum'][$value->id] ?? 0) + $value->count;
                    $viewrates['AR_readed_sum'][$value->id] = ($viewrates['AR_readed_sum'][$value->id] ?? 0) + $value->readed_count;
                }
            }
            if (in_array('BL', $orgazanizations)) {
                $viewrates_org_sub =
                    DB::table('message_user')
                    ->select([
                        DB::raw('shops.organization5_id as o5_id'),
                        DB::raw('count(crews.id) as count'),
                        DB::raw('count(crew_message_logs.id) as readed_count'),
                        DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
                    ])
                    ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                    ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                    ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                        $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                        ->where('crew_message_logs.message_id', '=', $ms);
                    })
                    ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                    ->where('message_user.message_id', '=', $ms)
                    ->groupBy('shops.organization5_id');

                $viewrate =
                    DB::table('shops')
                    ->select([
                        DB::raw('organization5.id as id'),
                        DB::raw('organization5.name as name'),
                        DB::raw('sub.count as count'),
                        DB::raw('sub.readed_count as readed_count'),
                        DB::raw('sub.view_rate as view_rate')
                    ])
                    ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
                    ->leftJoinSub($viewrates_org_sub, 'sub', function ($join) {
                        $join->on('shops.organization5_id', '=', 'sub.o5_id');
                    })
                    ->where('shops.organization1_id', '=', $organization1->id)
                    ->groupBy('shops.organization5_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization5.id')
                    ->get();

                $viewrates['BL'][] = $viewrate;
                $viewrates_array = $viewrate->toArray();
                foreach ($viewrates_array as $key => $value) {
                    $viewrates['BL_sum'][$value->id] = ($viewrates['BL_sum'][$value->id] ?? 0) + $value->count;
                    $viewrates['BL_readed_sum'][$value->id] = ($viewrates['BL_readed_sum'][$value->id] ?? 0) + $value->readed_count;
                }
            }


            // 店舗ごと
            $viewrate_sub = DB::table('message_user')
            ->select([
                DB::raw('message_user.shop_id as _shop_id'),
                DB::raw('count(crews.id) as count'),
                DB::raw('count(crew_message_logs.id) as readed_count'),
                DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
            ])
                ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                ->leftJoin('crews', 'crews.user_id', 'users.id')
                ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                    $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                    ->where('crew_message_logs.message_id', '=', $ms);
                })
                ->where('message_user.message_id', '=', $ms)
                ->groupBy('message_user.shop_id');

            $viewrate = DB::table('shops')
            ->select([
                DB::raw('organization5.name as o5_name'),
                DB::raw('organization4.name as o4_name'),
                DB::raw('organization3.name as o3_name'),
                DB::raw('shops.name as shop_name'),
                DB::raw('shops.shop_code as shop_code'),
                DB::raw('view_rate.*')
            ])
                ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
                ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
                ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                ->leftJoinSub($viewrate_sub, 'view_rate', function ($join) {
                    $join->on('shops.id', '=', 'view_rate._shop_id');
                })
                ->where('shops.organization1_id', '=', $organization1->id)
                ->orderBy('organization3.id')
                ->orderBy('organization4.id')
                ->orderBy('organization5.id')
                ->groupBy('shops.id')
                ->get();

            $viewrates['shop'][] = $viewrate;
            $viewrates_array = $viewrate->toArray();
            foreach ($viewrates_array as $key => $value) {
                $viewrates['shop_sum'][$value->shop_code] = ($viewrates['shop_sum'][$value->shop_code] ?? 0) + $value->count;
                $viewrates['shop_readed_sum'][$value->shop_code] = ($viewrates['shop_readed_sum'][$value->shop_code] ?? 0) + $value->readed_count;
            }
        }

        return view('exports.message-personal-export', [
            'messages' => $messages,
            'viewrates' => $viewrates,
            'organizations' => $orgazanizations,
            'organization_list' => $organization_list,
            'organization1' => $organization1
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
        // ヘッダー行とA～D列を固定
        $sheet->freezePane('E2'); // E2の位置で固定
    }
}
