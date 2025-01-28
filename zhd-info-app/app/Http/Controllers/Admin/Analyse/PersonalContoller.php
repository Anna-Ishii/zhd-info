<?php

namespace App\Http\Controllers\Admin\Analyse;

use App\Exports\MessagePersonalExport;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\SearchCondition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PersonalContoller extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $organization1_list = $admin->organization1()->orderby('name')->get();

        // request
        $publish_from_date = $request->input('publish-from-date');
        $publish_to_date = $request->input('publish-to-date');
        $publish_from_check = $request->has('publish-from-check');
        $publish_to_check = $request->has('publish-to-check');
        $orgs = $request->input('org');
        $shop_freeword = $request->input('shop_freeword');
        $message_freeword = $request->input('message_freeword');
        $organization1_id = $request->input('organization1', $organization1_list[0]->id);

        $organization1 = Organization1::find($organization1_id);
        $orgazanizations = [];
        $viewrates = [];

        // 業務連絡を9件取得
        $messages = Message::query()
            ->select('messages.*')
            ->leftJoin('message_user', 'message_user.message_id', '=', 'messages.id')
            ->leftJoin('users', 'message_user.user_id', '=', 'users.id')
            ->leftJoin('shops', 'shops.id', '=', 'message_user.shop_id')
            ->where('start_datetime', '<=', now('Asia/Tokyo'))
            ->where('editing_flg', false)
            ->where('messages.organization1_id', '=', $organization1->id)
            ->when(($publish_from_check && $publish_to_check), function ($query) use ($publish_from_date, $publish_to_date) {
                $query->when((isset($publish_from_date)), function ($query) use ($publish_from_date) {
                    $query
                        ->whereDate('start_datetime', '>=', $publish_from_date);
                })
                    ->when((isset($publish_to_date)), function ($query) use ($publish_to_date) {
                        $query
                            ->where(function ($query) use ($publish_to_date) {
                                $query->whereDate('end_datetime', '<=', $publish_to_date)
                                    ->orWhereNull('end_datetime');
                            });
                    });
            })
            ->when(($publish_from_check && !$publish_to_check), function ($query) use ($publish_from_date, $publish_to_date) {
                $query->when((isset($publish_from_date)), function ($query) use ($publish_from_date) {
                    $query
                        ->whereDate('start_datetime', '>=', $publish_from_date);
                })
                    ->when((isset($publish_to_date)), function ($query) use ($publish_to_date) {
                        $query
                            ->where(function ($query) use ($publish_to_date) {
                                $query->whereDate('start_datetime', '<=', $publish_to_date)
                                    ->orWhereNull('start_datetime');
                            });
                    });
            })
            ->when((!$publish_from_check && $publish_to_check), function ($query) use ($publish_from_date, $publish_to_date) {
                $query->when((isset($publish_from_date)), function ($query) use ($publish_from_date) {
                    $query
                        ->whereDate('end_datetime', '>=', $publish_from_date);
                })
                    ->when((isset($publish_to_date)), function ($query) use ($publish_to_date) {
                        $query
                            ->where(function ($query) use ($publish_to_date) {
                                $query->whereDate('end_datetime', '<=', $publish_to_date)
                                    ->orWhereNull('end_datetime');
                            });
                    });
            })
            ->when(isset($message_freeword), function ($query) use ($message_freeword) {
                $query->where(function ($query) use ($message_freeword) {
                    $query->whereLike('title', $message_freeword)
                        ->orWhereHas('tag', function ($query) use ($message_freeword) {
                            $query->where('name', $message_freeword);
                        });
                });
            })
            ->orderBy('messages.id', 'desc')
            ->groupBy('messages.id')
            ->limit(10)
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
                        DB::raw('organization3.order_no as order_no'),
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
                    ->when(isset($orgs['DS']), function ($query) use ($orgs) {
                        $query->whereIn('shops.organization3_id', $orgs['DS']);
                    })
                    ->groupBy('shops.organization3_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization3.order_no')
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
                        DB::raw('organization4.order_no as order_no'),
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
                    ->when(isset($orgs['AR']), function ($query) use ($orgs) {
                        $query->whereIn('shops.organization4_id', $orgs['AR']);
                    })
                    ->groupBy('shops.organization4_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization4.order_no')
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
                        DB::raw('organization5.order_no as order_no'),
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
                    ->when(isset($orgs['BL']), function ($query) use ($orgs) {
                        $query->whereIn('shops.organization5_id', $orgs['BL']);
                    })
                    ->groupBy('shops.organization5_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization5.order_no')
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
                            ->orwhere(DB::raw('SUBSTRING(shop_code, -4)'), 'LIKE', '%' . $shop_freeword . '%');
                    });
                })
                ->orderBy('organization3.order_no')
                ->orderBy('organization4.order_no')
                ->orderBy('organization5.order_no')
                ->orderBy('shops.shop_code')
                ->groupBy(
                    'shops.id',
                    'shops.shop_code',
                    'shops.name',
                    'organization3.name',
                    'organization3.order_no',
                    'organization4.name',
                    'organization4.order_no',
                    'organization5.name',
                    'organization4.order_no',
                )
                ->get();

            $viewrates['shop'][] = $viewrate;
            $viewrates_array = $viewrate->toArray();
            foreach ($viewrates_array as $key => $value) {
                $viewrates['shop_sum'][$value->shop_code] = ($viewrates['shop_sum'][$value->shop_code] ?? 0) + $value->count;
                $viewrates['shop_readed_sum'][$value->shop_code] = ($viewrates['shop_readed_sum'][$value->shop_code] ?? 0) + $value->readed_count;
            }
        }

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

        return view('admin.analyse.personal', [
            'messages' => $messages,
            'viewrates' => $viewrates,
            'organizations' => $orgazanizations,
            'organization_list' => $organization_list,
            'organization1' => $organization1,
            'organization1_list' => $organization1_list,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
        ]);
    }

    // 検索条件を保存
    public function saveSearchConditions(Request $request)
    {
        $admin = session('admin');

        try {
            SearchCondition::updateOrCreate(
                [
                    'admin_id' => $admin->id,
                    'page_name' => 'analyse-personal',
                ],
                [
                    'url' => $request->input('url'),
                ]
            );
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // エラーログを記録
            Log::error('Error saving search conditions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => '検索条件の保存中にエラーが発生しました。'], 500);
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
        $file_name = '業務連絡閲覧状況_' . $organization1 . $now->format('_Y_m_d') . '.xlsx';
        return Excel::download(
            new MessagePersonalExport($request),
            $file_name,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    //
    // API
    //
    public function getShopMessageViewRate(Request $request)
    {
        $shop = $request["shop"];
        $message = $request["message"];

        $crews = DB::table('messages as m')
            ->select([
                DB::raw('
                    c.part_code as part_code,
                    c.name as name,
                    c.name_kana as name_kana,
                    c.id as c_id
                    '),
                DB::raw('m.start_datetime'),
                DB::raw('DATE_FORMAT(c_m_l.readed_at, "%m/%d %H:%i") as readed_at'),
                DB::raw('
                            case
                                when c.register_date > m.start_datetime then true else false
                            end as new_face
                        '),
                DB::raw('
                            case
                                when c_m_l.id is null then false else true
                            end as readed
                        '),
                DB::raw("
                            case
                                when c.name_kana regexp '^[ｱ-ｵ]' then 1
                                when c.name_kana regexp '^[ｶ-ｺ]' then 2
                                when c.name_kana regexp '^[ｻ-ｿ]' then 3
                                when c.name_kana regexp '^[ﾀ-ﾄ]' then 4
                                when c.name_kana regexp '^[ﾅ-ﾉ]' then 5
                                when c.name_kana regexp '^[ﾊ-ﾎ]' then 6
                                when c.name_kana regexp '^[ﾏ-ﾓ]' then 7
                                when c.name_kana regexp '^[ﾔ-ﾖ]' then 8
                                when c.name_kana regexp '^[ﾗ-ﾛ]' then 9
                                when c.name_kana regexp '^[ﾜ-ﾝ]' then 10
                                else 0
                            end as name_sort
                        "),
            ])
            ->leftJoin('message_user as m_u', 'm.id', 'm_u.message_id')
            ->join('users as u', function ($join) use ($shop) {
                $join->on('m_u.user_id', '=', 'u.id')
                    ->where('u.shop_id', '=', $shop);
            })
            ->leftJoin('crews as c', 'u.id', 'c.user_id')
            ->leftJoin('crew_message_logs as c_m_l', function ($join) use ($message) {
                $join->on('c_m_l.crew_id', '=', 'c.id')
                    ->where('c_m_l.message_id', '=', $message);
            })
            ->where('m.id', '=', $message)
            ->orderBy('c.name_kana', 'asc')
            ->get();

        return response()->json([
            'crews' => $crews,
        ], 200);
    }

    public function getOrgMessageViewRate(Request $request)
    {
        $org_type = $request["org_type"];
        $org_id = $request["org_id"];
        $message = $request["message"];


        $crews = DB::table('messages as m')
            ->select([
                DB::raw('
                    c.part_code as part_code,
                    c.name as name,
                    c.name_kana as name_kana,
                    c.id as c_id
                    '),
                DB::raw('m.start_datetime'),
                DB::raw('DATE_FORMAT(c_m_l.readed_at, "%m/%d %H:%i") as readed_at'),
                DB::raw('
                            case
                                when c.register_date > m.start_datetime then true else false
                            end as new_face
                        '),
                DB::raw('
                            case
                                when c_m_l.id is null then false else true
                            end as readed
                        '),
                DB::raw("
                            case
                                when c.name_kana regexp '^[ｱ-ｵ]' then 1
                                when c.name_kana regexp '^[ｶ-ｺ]' then 2
                                when c.name_kana regexp '^[ｻ-ｿ]' then 3
                                when c.name_kana regexp '^[ﾀ-ﾄ]' then 4
                                when c.name_kana regexp '^[ﾅ-ﾉ]' then 5
                                when c.name_kana regexp '^[ﾊ-ﾎ]' then 6
                                when c.name_kana regexp '^[ﾏ-ﾓ]' then 7
                                when c.name_kana regexp '^[ﾔ-ﾖ]' then 8
                                when c.name_kana regexp '^[ﾗ-ﾛ]' then 9
                                when c.name_kana regexp '^[ﾜ-ﾝ]' then 10
                                else 0
                            end as name_sort
                        "),
            ])
            ->leftJoin('message_user as m_u', 'm.id', 'm_u.message_id')
            ->Join('users as u', 'm_u.user_id', 'u.id')
            ->Join('shops as s', function ($join) use ($org_id, $org_type) {
                $join->on('s.id', '=', 'u.shop_id')
                    ->when($org_type == 'Org1', function ($join) use ($org_id) {
                        $join->where('s.organization1_id', '=', $org_id);
                    })
                    ->when($org_type == 'DS', function ($join) use ($org_id) {
                        $join->where('s.organization3_id', '=', $org_id);
                    })
                    ->when($org_type == 'AR', function ($join) use ($org_id) {
                        $join->where('s.organization4_id', '=', $org_id);
                    })
                    ->when($org_type == 'BL', function ($join) use ($org_id) {
                        $join->where('s.organization5_id', '=', $org_id);
                    });
            })
            ->Join('crews as c', 'u.id', 'c.user_id')
            ->leftJoin('crew_message_logs as c_m_l', function ($join) use ($message) {
                $join->on('c_m_l.crew_id', '=', 'c.id')
                    ->where('c_m_l.message_id', '=', $message);
            })
            ->where('m.id', '=', $message)
            ->orderBy('c.name_kana', 'asc')
            ->get();

        return response()->json([
            'crews' => $crews,
        ], 200);
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
