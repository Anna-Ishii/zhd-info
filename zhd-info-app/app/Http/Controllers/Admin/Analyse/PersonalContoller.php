<?php

namespace App\Http\Controllers\Admin\Analyse;

use App\Exports\MessagePersonalExport;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PersonalContoller extends Controller
{
    public function index(Request $request) {
        $admin = session('admin');

        // request
        $publish_from_date = $request->input('publish-from-date');
        $publish_to_date = $request->input('publish-to-date');
        $publish_from_check = $request->has('publish-from-check');
        $publish_to_check = $request->has('publish-to-check');
        $org = $request->input('org');
        $shop_freeword = $request->input('shop_freeword');
        $message_freeword = $request->input('message_freeword');

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
                        ->where('messages.organization1_id','=', $admin->organization1_id)
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
        if ($admin->organization1->isExistOrg3()) {
            $orgazanizations[] = "DS";
            $organization_list["DS"] = $admin->organization1->getOrganization3();
        }
        if ($admin->organization1->isExistOrg4()) {
            $orgazanizations[] = "AR";
            $organization_list["AR"] = $admin->organization1->getOrganization4();
        }
        if ($admin->organization1->isExistOrg5()) {
            $orgazanizations[] = "BL";
            $organization_list["BL"] = $admin->organization1->getOrganization5();
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
                ->leftJoin('crew_message_logs', function($join) use($ms) {
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
                    ->where('shops.organization1_id', '=', $admin->organization1_id)
                    ->when(isset($org['DS']), function ($query) use ($org) {
                        $query->where('shops.organization3_id', '=', $org['DS']);
                    })
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
                    ->where('shops.organization1_id', '=', $admin->organization1_id)
                    ->when(isset($org['AR']), function ($query) use ($org) {
                        $query->where('shops_organization4_id', '=', $org['AR']);
                    })
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
                    ->where('shops.organization1_id', '=', $admin->organization1_id)
                    ->when(isset($org['BL']), function ($query) use ($org) {
                        $query->where('shops.organization5_id', '=', $org['BL']);
                    })
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
                ->leftJoin('crew_message_logs', function($join) use($ms) {
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
                    DB::raw('shops.display_name as shop_display_name'),
                    DB::raw('shops.shop_code as shop_code'),
                    DB::raw('view_rate.*')
                ])
                ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
                ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
                ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                ->leftJoinSub($viewrate_sub, 'view_rate', function($join) {
                    $join->on('shops.id', '=', 'view_rate._shop_id');
                })
                ->where('shops.organization1_id', '=', $admin->organization1_id)
                ->when(isset($org['DS']), function($query) use ($org) {
                    $query->where('shops.organization3_id', '=', $org['DS']);
                })
                ->when(isset($org['AR']), function ($query) use ($org) {
                    $query->where('shops.organization4_id', '=', $org['AR']);
                })
                ->when(isset($org['BL']), function ($query) use ($org) {
                    $query->where('shops.organization5_id', '=', $org['BL']);
                })
                ->when(isset($shop_freeword), function ($query) use ($shop_freeword) {
                    $query->where('shops.name', 'like', '%' . addcslashes($shop_freeword, '%_\\') . '%')
                        ->orwhere(DB::raw('SUBSTRING(shop_code, -4)'), 'LIKE', '%' . $shop_freeword . '%');
                })
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

        return view('admin.analyse.personal',[
            'messages' => $messages,
            'viewrates' => $viewrates,
            'organizations' => $orgazanizations,
            'organization_list' => $organization_list
        ]);
    }

    public function export(Request $request) 
    {
        $admin = session('admin');

        $organization1 = $admin->organization1->name;
        $now = new Carbon('now');
        $file_name = '業務連絡閲覧状況_' . $organization1 . $now->format('_Y_m_d') . '.xlsx';
        return Excel::download(
            new MessagePersonalExport($request),
            $file_name,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}