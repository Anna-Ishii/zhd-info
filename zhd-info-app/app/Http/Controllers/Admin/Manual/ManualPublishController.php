<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Enums\PublishStatus;
use App\Exports\ManualListExport;
use App\Exports\ManualStoreCsvExport;
use App\Exports\ManualViewRateExport;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Repository\Organization1Repository;
use App\Http\Requests\Admin\Manual\FileUpdateApiRequest;
use App\Http\Requests\Admin\Manual\PublishStoreRequest;
use App\Http\Requests\Admin\Manual\PublishUpdateRequest;
use App\Imports\ManualCsvImport;
use App\Imports\ManualStoreCsvImport;
use App\Models\Brand;
use App\Models\Manual;
use App\Models\ManualCategoryLevel1;
use App\Models\ManualCategoryLevel2;
use App\Models\ManualContent;
use App\Models\ManualOrganization;
use App\Models\ManualTagMaster;
use App\Models\ManualShop;
use App\Models\ManualUser;
use App\Models\ManualViewRate;
use App\Models\Organization1;
use App\Models\Shop;
use App\Models\User;
use App\Utils\ImageConverter;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ManualPublishController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $new_category_list = ManualCategoryLevel2::query()
            ->select([
                'manual_category_level2s.id as id',
                DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
            ])
            ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
            ->get();

        $organization1_list = $admin->getOrganization1();

        // request
        $new_category_id = $request->input('new_category');
        $status = PublishStatus::tryFrom($request->input('status'));
        $q = $request->input('q');
        $rate = $request->input('rate');
        $organization1_id = $request->input('brand', $organization1_list[0]->id);
        $publish_date = $request->input('publish-date');

        $organization1 = Organization1::find($organization1_id);

        // セッションにデータを保存
        session()->put('brand_id', $organization1_id);

        $sub = DB::table('manuals as m')
            ->select([
                'm.id as m_id',
                DB::raw('
                    case
                        when (count(distinct b.name)) = 0 then ""
                        else group_concat(distinct b.name order by b.name)
                    end as b_name
                ')
            ])
            ->leftjoin('manual_brand as m_b', 'm.id', 'm_b.manual_id')
            ->leftjoin('brands as b', 'b.id', 'm_b.brand_id')
            ->groupBy('m.id');

        // 閲覧率のデータを集計
        $viewRatesSub = DB::table('manual_view_rates')
        ->select([
                'manual_id',
                DB::raw('MAX(view_rate) as view_rate'),
                DB::raw('MAX(read_users) as read_users'),
                DB::raw('MAX(total_users) as total_users'),
                DB::raw('MAX(updated_at) as last_updated')
            ])
            ->groupBy('manual_id');

        $manual_list = Manual::query()
            ->with('create_user', 'updated_user', 'brand', 'tag', 'category_level1', 'category_level2')
            ->leftjoin('manual_user', 'manuals.id', '=', 'manual_id')
            ->leftjoin('manual_brand', 'manuals.id', '=', 'manual_brand.manual_id')
            ->leftjoin('brands', 'brands.id', '=', 'manual_brand.brand_id')
            ->leftJoinSub($viewRatesSub, 'view_rates', function ($join) {
                $join->on('manuals.id', '=', 'view_rates.manual_id');
            })
            ->leftJoinSub($sub, 'sub', function ($join) {
                $join->on('sub.m_id', '=', 'manuals.id');
            })
            ->select([
                'manuals.*',
                'view_rates.view_rate',
                'view_rates.read_users',
                'view_rates.total_users',
                'view_rates.last_updated',
                'sub.b_name as brand_name',
            ])
            ->where('manuals.organization1_id', $organization1_id)
            ->groupBy(DB::raw('manuals.id'))
            // 検索機能 キーワード
            ->when(isset($q), function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->whereLike('title', $q)
                        ->orWhereHas('tag', function ($query) use ($q) {
                            $query->where('name', $q);
                        });
                });
            })
            // 検索機能 状態
            ->when(isset($status), function ($query) use ($status) {
                switch ($status) {
                    case PublishStatus::Wait:
                        $query->waitManual();
                        break;
                    case PublishStatus::Publishing:
                        $query->publishingManual();
                        break;
                    case PublishStatus::Published:
                        $query->publishedManual();
                        break;
                    case PublishStatus::Editing:
                        $query->where('editing_flg', '=', true);
                        break;
                    default:
                        break;
                }
            })
            // 検索機能 カテゴリ
            ->when(isset($new_category_id), function ($query) use ($new_category_id) {
                $query->where('category_level2_id', $new_category_id);
            })
            ->when((isset($rate[0]) || isset($rate[1])), function ($query) use ($rate) {
                $min = isset($rate[0]) ? $rate[0] : 0;
                $max = isset($rate[1]) ? $rate[1] : 100;
                $query->havingRaw('view_rate between ? and ?', [$min, $max]);
            })
            ->when((isset($publish_date[0])), function ($query) use ($publish_date) {
                $query
                    ->where('start_datetime', '>=', $publish_date[0]);
            })
            ->when((isset($publish_date[1])), function ($query) use ($publish_date) {
                $query
                    ->where(function ($query) use ($publish_date) {
                        $query->where('end_datetime', '<=', $publish_date[1])
                            ->orWhereNull('end_datetime');
                    });
            })
            ->join('admin', 'create_admin_id', '=', 'admin.id')
            ->orderBy('manuals.number', 'desc')
            ->paginate(50)
            ->appends(request()->query());

        // 店舗数をカウント
        // すべてのメッセージIDを取得
        $manual_ids = $manual_list->pluck('id')->toArray();
        // すべての店舗数を取得
        $all_shop_count = Shop::where('organization1_id', $organization1_id)->count();
        // 各メッセージに関連する店舗数を取得
        $manual_shop_counts = ManualShop::select('manual_id', DB::raw('COUNT(*) as shop_count'))
            ->whereIn('manual_id', $manual_ids)
            ->groupBy('manual_id')
            ->pluck('shop_count', 'manual_id');
        // 各メッセージに関連するユーザー数を取得（店舗数が0の場合に使用）
        $manual_user_counts = ManualUser::select('manual_id', DB::raw('COUNT(*) as user_count'))
            ->whereIn('manual_id', $manual_ids)
            ->groupBy('manual_id')
            ->pluck('user_count', 'manual_id');

        // メッセージリストをループして、店舗数を割り当て
        foreach ($manual_list as &$manual) {
            $shop_count = $manual_shop_counts[$manual->id] ?? 0;
            // 店舗数が0の場合は、ユーザー数を使用
            if ($shop_count == 0) {
                $shop_count = $manual_user_counts[$manual->id] ?? 0;
            }
            // 全店舗数と同じ場合は「全店」と表示
            if ($shop_count == $all_shop_count) {
                $shop_count = "全店";
            }
            $manual->shop_count = $shop_count;
        }

        return view('admin.manual.publish.index', [
            'new_category_list' => $new_category_list,
            'manual_list' => $manual_list,
            'organization1' => $organization1,
            'organization1_list' => $organization1_list,
        ]);
    }

    // 閲覧率の更新処理
    public function updateViewRates(Request $request)
    {
        $admin = session('admin');
        $organization1_id = $request->input('brand', $admin->firstOrganization1()->id);
        $rate = $request->input('rate');
        $manual_id = $request->input('manual_id'); // manual_idを取得

        // メッセージの既読・総ユーザー数を一度に集計
        $manualRates = DB::table('manual_user')
            ->select([
                'manual_user.manual_id',
                DB::raw('sum(manual_user.read_flg) as read_users'),
                DB::raw('count(distinct manual_user.user_id) as total_users'),
                DB::raw('round((sum(manual_user.read_flg) / count(distinct manual_user.user_id)) * 100, 1) as view_rate')
            ])
            ->join('manuals', 'manual_user.manual_id', '=', 'manuals.id')
            ->where('manuals.organization1_id', $organization1_id)
            ->when($manual_id, function ($query) use ($manual_id) {
                $query->where('manual_user.manual_id', $manual_id); // manual_idでフィルタリング
            })
            ->groupBy('manual_user.manual_id')
            ->when((isset($rate[0]) || isset($rate[1])), function ($query) use ($rate) {
                $min = isset($rate[0]) ? $rate[0] : 0;
                $max = isset($rate[1]) ? $rate[1] : 100;
                $query->havingRaw('view_rate between ? and ?', [$min, $max]);
            })
            ->get();

        // バルクアップデート用のデータ準備
        $updateData = [];
        foreach ($manualRates as $manual) {
            $updateData[] = [
                'manual_id' => $manual->manual_id,
                'view_rate' => $manual->view_rate,     // 閲覧率の計算
                'read_users' => $manual->read_users,   // 既読ユーザー数
                'total_users' => $manual->total_users, // 全体ユーザー数
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // バルクアップデートを実行
        DB::table('manual_view_rates')->upsert(
            $updateData,
            ['manual_id'],
            ['view_rate', 'read_users', 'total_users', 'created_at', 'updated_at']
        );

        // 処理完了後にページをリダイレクトして結果を表示
        return redirect()->back()->with('success', '閲覧率が更新されました。');
    }

    public function show(Request $request, $manual_id)
    {
        $manual = Manual::where('id', $manual_id)
            ->withCount(['user as total_users'])
            ->withCount(['readed_user as read_users'])
            ->first();

        $organization1 = $manual->organization1;
        $_brand = $organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();
        $brand_list = $_brand->get();
        $org3_list = Organization1Repository::getOrg3($organization1);
        $org4_list = Organization1Repository::getOrg4($organization1);
        $org5_list = Organization1Repository::getOrg5($organization1);

        // request
        $brand_id = $request->input('brand');
        $shop_freeword = $request->input('shop_freeword');
        $org3 = $request->input('org3');
        $org4 = $request->input('org4');
        $org5 = $request->input('org5');
        $read_flg = $request->input('read_flg');
        $readed_date = $request->input('readed_date');

        $shop_list = $manual
            ->shop()
            ->when(isset($brand_id), function ($query) use ($brand_id) {
                $query->where('brand_id', $brand_id);
            })
            ->when(isset($shop_freeword), function ($query) use ($shop_freeword) {
                $query->whereLike('name', $shop_freeword)
                    ->orwhere(DB::raw('SUBSTRING(shop_code, -4)'), 'LIKE', '%' . $shop_freeword . '%');
            })
            ->when(isset($org3), function ($query) use ($org3) {
                $query->where('organization3_id', $org3);
            })
            ->when(isset($org4), function ($query) use ($org4) {
                $query->where('organization4_id', $org4);
            })
            ->when(isset($org5), function ($query) use ($org5) {
                $query->where('organization5_id', $org5);
            })
            ->pluck('id')
            ->unique()
            ->toArray();

        $user_list = $manual
            ->user()
            ->with(['shop', 'shop.organization3', 'shop.organization4', 'shop.organization5', 'shop.brand'])
            ->when(isset($read_flg), function ($query) use ($read_flg) {
                if ($read_flg == 'true') $query->where('read_flg', true);
                if ($read_flg == 'false') $query->where('read_flg', false);
            })
            ->when((isset($readed_date[0])), function ($query) use ($readed_date) {
                $from = Util::delweek_string($readed_date[0]);
                $query->whereRaw("DATE_FORMAT(readed_datetime, '%Y/%m/%d %H:%i') >= ?", $from);
            })
            ->when((isset($readed_date[1])), function ($query) use ($readed_date) {
                $to = Util::delweek_string($readed_date[1]);
                $query->where(function ($query) use ($to) {
                    $query->whereRaw("DATE_FORMAT(readed_datetime, '%Y/%m/%d %H:%i') <= ?", $to);
                });
            })
            ->wherePivotIn('shop_id', $shop_list)
            ->join('shops', 'users.shop_id', '=', 'shops.id')
            ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
            ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
            ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
            ->orderBy('organization3.order_no')
            ->orderBy('organization4.order_no')
            ->orderBy('organization5.order_no')
            ->orderBy('shops.shop_code')
            ->paginate(50)
            ->appends(request()->query());

        return view('admin.manual.publish.show', [
            'manual' => $manual,
            'user_list' => $user_list,
            'brand_list' => $brand_list,
            'org3_list' => $org3_list,
            'org4_list' => $org4_list,
            'org5_list' => $org5_list,
            'brands' => $brands,
        ]);
    }

    public function new(Organization1 $organization1)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加
        ini_set('max_execution_time', 300); // 実行時間を一時的に300秒に設定

        $new_category_list = ManualCategoryLevel2::query()
            ->select([
                'manual_category_level2s.id as id',
                DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
            ])
            ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
            ->get();

        // ブランド一覧を取得する
        $brand_list = Brand::where('organization1_id', $organization1->id)->get();
        $organization_list = [];
        $organization_list = Shop::query()
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'organization2_id',
                'organization2.name as organization2_name',
                'organization2.order_no as organization2_order_no',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization3.order_no as organization3_order_no',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization4.order_no as organization4_order_no',
                'organization5_id',
                'organization5.name as organization5_name',
                'organization5.order_no as organization5_order_no',
            )
            ->where('organization1_id', $organization1->id)
            ->orderByRaw('organization2_id is null asc')
            ->orderByRaw('organization3_id is null asc')
            ->orderByRaw('organization4_id is null asc')
            ->orderByRaw('organization5_id is null asc')
            ->orderBy("organization2_order_no", "asc")
            ->orderBy("organization3_order_no", "asc")
            ->orderBy("organization4_order_no", "asc")
            ->orderBy("organization5_order_no", "asc")
            ->get()
            ->toArray();


        // 店舗情報を取得する
        $brand_ids = $brand_list->pluck('id')->toArray();
        $all_shops = Shop::query()
            ->select(
                'shops.id as id',
                'shops.shop_code',
                'shops.display_name',
                'shops.organization5_id',
                'shops.organization4_id',
                'shops.organization3_id',
                'shops.organization2_id'
            )
            ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
            ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
            ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
            ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
            ->where('shops.organization1_id', $organization1->id)
            ->whereIn('shops.brand_id', $brand_ids)
            ->orderBy('shops.shop_code', 'asc')
            ->get()
            ->toArray();

        // 組織別にデータを整理する
        $organization_list = array_map(function ($org) use ($all_shops) {
            $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization5_id'] == $org['organization5_id'];
            });
            $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
            });
            $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            return $org;
        }, $organization_list);

        // shop_code でソート済みの $all_shops をそのまま利用
        $all_shop_list = array_map(function ($shop) {
            return [
                'shop_id' => $shop['id'],
                'shop_code' => $shop['shop_code'],
                'display_name' => $shop['display_name'],
            ];
        }, $all_shops);


        // 店舗コードでshopsをソート
        usort($all_shop_list, function ($a, $b) {
            return strcmp($a['shop_code'], $b['shop_code']);
        });

        // メモリ制限と実行時間をデフォルトの設定に戻す
        ini_restore('memory_limit');
        ini_restore('max_execution_time');

        return view('admin.manual.publish.new', [
            'organization1' => $organization1,
            'new_category_list' => $new_category_list,
            'brand_list' => $brand_list,
            'organization_list' => $organization_list,
            'all_shop_list' => $all_shop_list,
        ]);
    }

    public function store(PublishStoreRequest $request, Organization1 $organization1)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加
        ini_set('max_execution_time', 300); // 実行時間を一時的に300秒に設定

        $validated = $request->validated();

        $admin = session('admin');
        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_level1_id'] = $this->level1CategoryParam($request->new_category_id);
        $manual_params['category_level2_id'] = $this->level2CategoryParam($request->new_category_id);
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $manual_params['content_name'] = $request->file_name;
        $manual_params['content_url'] = $request->file_path ? $this->registerFile($request->file_path) : null;
        $manual_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($manual_params['content_url']) : null;
        $manual_params['create_admin_id'] = $admin->id;
        $manual_params['organization1_id'] = $organization1->id;
        $manual_params['number'] = Manual::getCurrentNumber($organization1->id) + 1;
        $manual_params['editing_flg'] = isset($request->save);

        try {
            DB::beginTransaction();
            $manual = Manual::create($manual_params);
            $manual->updated_at = null;
            $manual->save();

            foreach (['org5', 'org4', 'org3', 'org2'] as $level) {
                if (isset($request->organization[$level][0])) {
                    // 事前にIDの配列を取得
                    $ids = explode(',', $request->organization[$level][0]);

                    $bulkData = [];
                    foreach ($ids as $id) {
                        $orgData = [
                            'manual_id' => $manual->id,
                            'organization1_id' => $organization1->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // レベルごとにフィールドを設定
                        switch ($level) {
                            case 'org5':
                                $orgData['organization5_id'] = $id;
                                break;
                            case 'org4':
                                $orgData['organization4_id'] = $id;
                                break;
                            case 'org3':
                                $orgData['organization3_id'] = $id;
                                break;
                            case 'org2':
                                $orgData['organization2_id'] = $id;
                                break;
                        }

                        // まとめてデータを追加
                        $bulkData[] = $orgData;
                    }

                    // バルクインサート
                    DB::table('manual_organization')->insert($bulkData);
                }
            }

            // チャンクサイズを設定
            $chunkSize = 200;

            // manual_shopにshop_idとmanual_idをバルクインサート
            if (isset($request->organization_shops)) {
                $organization_shops = explode(',', $request->organization_shops);

                // ショップデータの事前取得とグループ化
                $shopsData = Shop::whereIn('id', $organization_shops)
                    ->whereIn('brand_id', $request->brand)
                    ->get(['id', 'brand_id'])
                    ->groupBy('id');

                $insertData = [];
                // 事前に選択フラグを決定
                $selectedFlg = (isset($request->select_organization['all']) && $request->select_organization['all'] === 'selected') ? 'all' : 'store';

                foreach ($organization_shops as $_shop_id) {
                    if (isset($shopsData[$_shop_id])) {
                        foreach ($shopsData[$_shop_id] as $shop) {
                            $insertData[] = [
                                'manual_id' => $manual->id,
                                'shop_id' => $shop->id,
                                'brand_id' => $shop->brand_id,
                                'selected_flg' => $selectedFlg,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];

                            // チャンクサイズに達したらバルクインサート
                            if (count($insertData) >= $chunkSize) {
                                ManualShop::insert($insertData);
                                $insertData = [];
                            }
                        }
                    }
                }

                // 最後に残ったデータをインサート
                if (!empty($insertData)) {
                    ManualShop::insert($insertData);
                }
            }

            $manual->brand()->attach($request->brand);

            if (!isset($request->save)) {
                $manual->user()->attach($this->getTargetUsersByShopId($request));
            }

            $manual->content()->createMany($this->manualContentsParam($request));

            if (isset($request->tag_name)) {
                $tag_ids = [];
                foreach ($request->tag_name as $tag_name) {
                    $tag = ManualTagMaster::firstOrCreate(['name' => $tag_name]);
                    $tag_ids[] = $tag->id;
                }
                $manual->tag()->attach($tag_ids);
            }

            DB::commit();

            // 閲覧率の更新処理
            $this->updateViewRates(new Request(['manual_id' => $manual->id, 'brand' => $organization1->id]));

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->rollbackRegisterFile($request->file_path);
            $this->rollbackManualContentFile($request);
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        // メモリ制限と実行時間をデフォルトの設定に戻す
        ini_restore('memory_limit');
        ini_restore('max_execution_time');

        return redirect()->route('admin.manual.publish.index', ['brand' => session('brand_id')]);
    }

    public function edit($manual_id)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加
        ini_set('max_execution_time', 300); // 実行時間を一時的に300秒に設定

        $manual = Manual::find($manual_id);
        if (empty($manual)) return redirect()->route('admin.manual.publish.index', ['brand' => session('brand_id')]);

        $admin = session('admin');

        // 業態一覧を取得する
        $brand_list = Brand::where('organization1_id', $manual->organization1_id)->get();

        $organization_list = [];
        $organization_list = Shop::query()
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'organization2_id',
                'organization2.name as organization2_name',
                'organization2.order_no as organization2_order_no',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization3.order_no as organization3_order_no',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization4.order_no as organization4_order_no',
                'organization5_id',
                'organization5.name as organization5_name',
                'organization5.order_no as organization5_order_no',
            )
            ->where('organization1_id', $manual->organization1_id)
            ->orderByRaw('organization2_id is null asc')
            ->orderByRaw('organization3_id is null asc')
            ->orderByRaw('organization4_id is null asc')
            ->orderByRaw('organization5_id is null asc')
            ->orderBy("organization2_order_no", "asc")
            ->orderBy("organization3_order_no", "asc")
            ->orderBy("organization4_order_no", "asc")
            ->orderBy("organization5_order_no", "asc")
            ->get()
            ->toArray();


        // 店舗情報を取得する
        $brand_ids = $brand_list->pluck('id')->toArray();
        $all_shops = Shop::query()
            ->select(
                'shops.id as id',
                'shops.shop_code',
                'shops.display_name',
                'shops.organization5_id',
                'shops.organization4_id',
                'shops.organization3_id',
                'shops.organization2_id'
            )
            ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
            ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
            ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
            ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
            ->where('shops.organization1_id', $manual->organization1_id)
            ->whereIn('shops.brand_id', $brand_ids)
            ->orderBy('shops.shop_code', 'asc')
            ->get()
            ->toArray();

        // 組織別にデータを整理する
        $organization_list = array_map(function ($org) use ($all_shops) {
            $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization5_id'] == $org['organization5_id'];
            });
            $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
            });
            $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            return $org;
        }, $organization_list);

        // shop_code でソート済みの $all_shops をそのまま利用
        $all_shop_list = array_map(function ($shop) {
            return [
                'shop_id' => $shop['id'],
                'shop_code' => $shop['shop_code'],
                'display_name' => $shop['display_name'],
            ];
        }, $all_shops);


        // ManualOrganizationテーブルから各組織IDを取得し、配列に格納
        $target_org = [];
        $target_org['org5'] = ManualOrganization::where('manual_id', $manual_id)->pluck('organization5_id')->toArray();
        $target_org['org4'] = ManualOrganization::where('manual_id', $manual_id)->pluck('organization4_id')->toArray();
        $target_org['org3'] = ManualOrganization::where('manual_id', $manual_id)->pluck('organization3_id')->toArray();
        $target_org['org2'] = ManualOrganization::where('manual_id', $manual_id)->pluck('organization2_id')->toArray();

        $target_brand = $manual->brand()->pluck('brands.id')->toArray();
        $contents = $manual->content()->orderBy("order_no")->get();

        $new_category_list =
            ManualCategoryLevel2::query()
            ->select([
                'manual_category_level2s.id as id',
                DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
            ])
            ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
            ->get();

        $target_org['shops'] = [];
        $target_org['select'] = null;

        $selectedFlg = null;
        $chunkSize = 200; // チャンクサイズを設定
        $offset = 0;

        // ManualShopテーブルからメッセージに関連する店舗情報を取得
        while (true) {
            $shops = ManualShop::where('manual_id', $manual_id)
                ->whereIn('brand_id', $target_brand)
                ->offset($offset)
                ->limit($chunkSize)
                ->get(['shop_id', 'selected_flg']);

            // 取得したデータが空ならループを終了
            if ($shops->isEmpty()) {
                break;
            }

            // 取得した店舗データをtarget_org['shops']配列に格納し、selected_flgを設定
            foreach ($shops as $shop) {
                $target_org['shops'][] = $shop->shop_id;
                if (!$selectedFlg) {
                    $selectedFlg = $shop->selected_flg;
                }
            }
            $offset += $chunkSize;
        }

        // ManualShopテーブルにshop_idが存在しない場合はManualUserテーブルを確認
        if (empty($target_org['shops'])) {
            ManualUser::where('manual_id', $manual_id)
                ->orderBy('manual_id')
                ->chunk($chunkSize, function ($users) use (&$target_org) {
                    foreach ($users as $user) {
                        $target_org['shops'][] = $user->shop_id;
                    }
                });
            $target_org['select'] = 'oldStore';
        }

        // target_org['shops']の配列内の重複する店舗IDを削除
        $target_org['shops'] = array_unique($target_org['shops']);

        // selectedFlgが設定されている場合はtarget_org['select']にその値を設定
        if ($selectedFlg) {
            $target_org['select'] = $selectedFlg;
        }


        // 店舗コードでshopsをソート
        usort($all_shop_list, function ($a, $b) {
            return strcmp($a['shop_code'], $b['shop_code']);
        });

        // メモリ制限と実行時間をデフォルトの設定に戻す
        ini_restore('memory_limit');
        ini_restore('max_execution_time');

        return view('admin.manual.publish.edit', [
            'manual' => $manual,
            'brand_list' => $brand_list,
            'target_brand' => $target_brand,
            'contents' => $contents,
            'new_category_list' => $new_category_list,
            'organization_list' => $organization_list,
            'all_shop_list' => $all_shop_list,
            'target_org' => $target_org,
        ]);
    }

    public function update(PublishUpdateRequest $request, $manual_id)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加
        ini_set('max_execution_time', 300); // 実行時間を一時的に300秒に設定

        $validated = $request->validated();

        // ファイルを移動したかフラグ
        $manual_changed_flg = false;
        $manual_content_changed_flg = false;

        $admin = session('admin');
        $manual = Manual::find($manual_id);

        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_level1_id'] = $this->level1CategoryParam($request->new_category_id);
        $manual_params['category_level2_id'] = $this->level2CategoryParam($request->new_category_id);
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        if ($this->isChangedFile($manual->content_url, $request->file_path)) {
            $manual_params['content_name'] = $request->file_name;
            $manual_params['content_url'] = $request->file_path ? $this->registerFile($request->file_path) : null;
            $manual_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($manual_params['content_url']) : null;
            $manual_changed_flg = true;
        } else {
            $manual_params['content_name'] = $manual->content_name;
            $manual_params['content_url'] = $manual->content_url;
            $manual_params['thumbnails_url'] = $manual->thumbnails_url;
        }
        $manual_params['updated_admin_id'] = $admin->id;
        $manual_params['editing_flg'] = isset($request->save) ? true : false;


        // 手順を登録する
        $content_data = [];

        try {
            DB::beginTransaction();
            // 登録されているコンテンツが削除されていた場合、deleteフラグを立てる
            $manual = Manual::find($manual_id);
            $content = $manual->content()->whereNotIn('id', $this->getExistContentIds($request['manual_flow']));
            $content->delete();

            //手順を登録する
            if (isset($request['manual_flow'])) {
                foreach ($request['manual_flow'] as $i => $r) {
                    // 登録されている手順を変更する
                    if (isset($r['content_id'])) {
                        $id = (int)$r['content_id'];
                        $manual_content = ManualContent::find($id);
                        $manual_content->title = $r['title'];
                        $manual_content->description = $r['detail'];
                        $manual_content->order_no = $i + 1;

                        // 変更部分だけ取り込む
                        if ($this->isChangedFile($manual_content->content_url, $r['file_path'])) {
                            $manual_content->content_name = $r['file_name'];
                            $manual_content->content_url = $this->registerFile($r['file_path']);
                            $manual_content->thumbnails_url = ImageConverter::convert2image($manual_content->content_url);
                            $manual_content_changed_flg = true;
                        }

                        $manual_content->save();
                    } else {
                        // 手順の新規登録
                        $content_data[$i]['title'] = $r['title'];
                        $content_data[$i]['description'] = $r['detail'];
                        $content_data[$i]['order_no'] = $i + 1;
                        if (isset($r['file_name']) && isset($r['file_path'])) {
                            $content_data[$i]['content_name'] = $r['file_name'];
                            $content_data[$i]['content_url'] = $this->registerFile($r['file_path']);
                            $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                        }
                    }
                }
            }

            $manual->update($manual_params);

            // メッセージに関連する組織データを削除
            ManualOrganization::where('manual_id', $manual_id)->delete();

            foreach (['org5', 'org4', 'org3', 'org2'] as $level) {
                if (isset($request->organization[$level][0])) {
                    // 事前にIDの配列を取得
                    $ids = explode(',', $request->organization[$level][0]);

                    $bulkData = [];
                    foreach ($ids as $id) {
                        $orgData = [
                            'manual_id' => $manual->id,
                            'organization1_id' => $admin->organization1_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // レベルごとにフィールドを設定
                        switch ($level) {
                            case 'org5':
                                $orgData['organization5_id'] = $id;
                                break;
                            case 'org4':
                                $orgData['organization4_id'] = $id;
                                break;
                            case 'org3':
                                $orgData['organization3_id'] = $id;
                                break;
                            case 'org2':
                                $orgData['organization2_id'] = $id;
                                break;
                        }

                        // まとめてデータを追加
                        $bulkData[] = $orgData;
                    }

                    // バルクインサート
                    DB::table('manual_organization')->insert($bulkData);
                }
            }

            // メッセージに関連するショップデータを削除
            ManualShop::where('manual_id', $manual_id)->delete();

            // チャンクサイズを設定
            $chunkSize = 200;

            // manual_shopにshop_idとmanual_idをバルクインサート
            if (isset($request->organization_shops)) {
                $organization_shops = explode(',', $request->organization_shops);

                // ショップデータの事前取得とグループ化
                $shopsData = Shop::whereIn('id', $organization_shops)
                    ->whereIn('brand_id', $request->brand)
                    ->get(['id', 'brand_id'])
                    ->groupBy('id');

                $insertData = [];
                // 事前に選択フラグを決定
                $selectedFlg = (isset($request->select_organization['all']) && $request->select_organization['all'] === 'selected') ? 'all' : 'store';

                foreach ($organization_shops as $_shop_id) {
                    if (isset($shopsData[$_shop_id])) {
                        foreach ($shopsData[$_shop_id] as $shop) {
                            $insertData[] = [
                                'manual_id' => $manual->id,
                                'shop_id' => $shop->id,
                                'brand_id' => $shop->brand_id,
                                'selected_flg' => $selectedFlg,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];

                            // チャンクサイズに達したらバルクインサート
                            if (count($insertData) >= $chunkSize) {
                                ManualShop::insert($insertData);
                                $insertData = [];
                            }
                        }
                    }
                }

                // 最後に残ったデータをインサート
                if (!empty($insertData)) {
                    ManualShop::insert($insertData);
                }
            }

            $manual->brand()->sync($request->brand);

            // 既存ユーザーとターゲットユーザーの比較
            $targetUsers = !isset($request->save) ? $this->getTargetUsersByShopId($request) : [];
            $currentUsers = $manual->user()->pluck('user_id')->toArray();

            // チャンクサイズを設定
            $chunkSize = 200;

            // 削除処理
            $usersToDetach = array_diff($currentUsers, array_keys($targetUsers));
            if (!empty($usersToDetach)) {
                foreach (array_chunk($usersToDetach, $chunkSize) as $chunk) {
                    // チャンクごとにユーザーをデタッチ
                    $manual->user()->detach($chunk);
                }
            }

            // 追加または更新処理
            $usersToAttach = array_diff_key($targetUsers, array_flip($currentUsers));
            if (!empty($usersToAttach)) {
                // チャンクに分割して処理
                foreach (array_chunk($usersToAttach, $chunkSize, true) as $chunk) {
                    $attachData = [];
                    foreach ($chunk as $userId => $shopData) {
                        $attachData[$userId] = ['shop_id' => $shopData['shop_id']];
                    }
                    // ユーザーを関連付け
                    $manual->user()->attach($attachData);
                }
            }

            $manual->content()->createMany($content_data);

            $tag_ids = [];
            foreach ($request->input('tag_name', []) as $tag_name) {
                $tag = ManualTagMaster::firstOrCreate(['name' => $tag_name]);
                $tag_ids[] = $tag->id;
            }
            $manual->tag()->sync($tag_ids);

            DB::commit();

            // 閲覧率の更新処理
            $this->updateViewRates(new Request(['manual_id' => $manual->id, 'brand' => $admin->organization1_id]));

        } catch (\Throwable $th) {
            DB::rollBack();
            if ($manual_changed_flg) $this->rollbackRegisterFile($request->file_path);
            if ($manual_content_changed_flg) $this->rollbackManualContentFile($request);
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '入力エラーがあります');
        }

        // メモリ制限と実行時間をデフォルトの設定に戻す
        ini_restore('memory_limit');
        ini_restore('max_execution_time');

        return redirect()->route('admin.manual.publish.index', ['brand' => session('brand_id')]);
    }

    public function detail($manual_id)
    {
        $manual = Manual::find($manual_id);
        $contents = $manual->content()
            ->orderBy("order_no", "desc")
            ->get();
        $target_org1 = $manual->organization1()->pluck('organization1.id')->toArray();
        $target_shop = Shop::whereIn("organization4_id", $target_org1)->get();

        return view('admin.manual.publish.edit', [
            "manual" => $manual,
            "contents" => $contents,
            "target_shop" => $target_shop
        ]);
    }

    public function stop(Request $request)
    {
        $data = $request->json()->all();
        $manual_id = $data['manual_id'];
        $manual = Manual::find($manual_id)->first();
        $status = $manual->status;
        //掲載終了だと、エラーを返す
        if ($status == PublishStatus::Published) return response()->json(['message' => 'すでに掲載終了しています']);

        $admin = session('admin');
        $now = Carbon::now();
        Manual::whereIn('id', $manual_id)->update([
            'end_datetime' => $now,
            'updated_admin_id' => $admin->id,
            'editing_flg' => false
        ]);

        return response()->json(['message' => '停止しました']);
    }

    // 詳細画面のエクスポート
    public function export(Request $request, $manual_id)
    {
        $now = new Carbon('now');
        $now->format('Y_m_d-H_i_s');
        return Excel::download(
            new ManualViewRateExport($manual_id, $request),
            $now->format('Y_m_d-H_i') . '-動画マニュアルエクスポート.csv'
        );
    }

    // マニュアル一覧のエクスポート
    public function exportList(Request $request)
    {
        $admin = session('admin');
        $organization1_id = $request->input('brand', $admin->firstOrganization1()->id);
        $organization1 = Organization1::find($organization1_id);

        $file_name = '動画マニュアル_' . $organization1->name . now()->format('_Y_m_d') . '.csv';
        return Excel::download(
            new ManualListExport($request),
            $file_name
        );
    }

    // 動画マニュアルCSV エクスポート（新規登録/編集）
    public function csvStoreExport(Request $request)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加
        ini_set('max_execution_time', 300); // 実行時間を一時的に300秒に設定

        // 新規登録か編集かを判定
        $isEdit = $request->has('manual_id');
        $organization1_id = null;

        if ($isEdit) {
            // 編集時の処理
            $manual_id = (int) $request->input('manual_id');
            $manual = Manual::find($manual_id);
            if (!$manual) {
                return response()->json(['error' => 'Manual not found'], 404);
            }
            $organization1_id = $manual->organization1_id;
        } else {
            // 新規登録時の処理
            $organization1_id = (int) $request->input('organization1_id');
        }

        $organization1 = Organization1::find($organization1_id);
        if (!$organization1) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $file_name = $organization1->name . now()->format('_Y_m_d') . '.csv';

        // メモリ制限と実行時間をデフォルトの設定に戻す
        ini_restore('memory_limit');
        ini_restore('max_execution_time');

        return Excel::download(
            new ManualStoreCsvExport($organization1_id),
            $file_name
        );
    }

    public function fileUpload(FileUpdateApiRequest $request)
    {
        $validated = $request->validated();

        $file = $request->file;
        // $file->move(sys_get_temp_dir(),uniqid() . '.' . $file->getClientOriginalExtension());
        $file_path = Storage::putFile('/tmp', $file);
        $file_name = $file->getClientOriginalName();

        return  response()->json([
            'content_name' => $file_name,
            'content_url' => $file_path
        ]);
    }

    public function csvUpload(Request $request)
    {
        $log_file_name = $request->input('log_file_name');
        $file_path = public_path() . '/log/' . $log_file_name;
        file_put_contents($file_path, "0");

        $admin = session('admin');
        $csv = $request->file;
        $organization1 = (int) $request->input('organization1');

        $csv_content = file_get_contents($csv);
        $encoding = mb_detect_encoding($csv_content);
        if ($encoding == "UTF-8") {
            $shift_jis_content = mb_convert_encoding($csv_content, 'CP932', 'UTF-8');
            file_put_contents($csv, $shift_jis_content);
        }

        $brands = $this->getBrandNameArray($organization1);

        $csv_path = Storage::putFile('csv', $csv);
        Log::info("マニュアルCSVインポート", [
            'csv_path' => $csv_path,
            'admin' => $admin
        ]);

        try {
            Excel::import(new ManualCsvImport($organization1, $brands), $csv, \Maatwebsite\Excel\Excel::CSV);
            $collection = Excel::toCollection(new ManualCsvImport($organization1, $brands), $csv, \Maatwebsite\Excel\Excel::CSV);

            $count = $collection[0]->count();
            if ($count >= 100) {
                File::delete($file_path);
                return response()->json([
                    'message' => "100行以内にしてください"
                ], 500);
            }
            $array = [];
            foreach (
                $collection[0] as $key => [
                    $no,
                    $category,
                    $title,
                    $tag1,
                    $tag2,
                    $tag3,
                    $tag4,
                    $tag5,
                    $start_datetime,
                    $end_datetime,
                    $status,
                    $brand,
                    $description
                ]
            ) {
                $manual = Manual::where('number', $no)
                    ->where('organization1_id', $organization1)
                    ->firstOrFail();

                $CONTENTS_RAW_NUMBER = 13;
                $row_contents = $collection[0][$key]->slice($CONTENTS_RAW_NUMBER);

                $contents = [];
                if (isset($manual->content)) {
                    foreach ($manual->content as $key => $content) {
                        $content = [];
                        $content["title"] = $row_contents[($key * 2) + 13] ?? '';
                        $content["description"] = $row_contents[($key * 2) + 14] ?? '';
                        array_push($contents, $content);
                    }
                }
                $brand_param = ($brand == "全て") ? $brands : Brand::whereIn('name',  $this->strToArray($brand))->pluck('id')->toArray();
                $category_array = isset($category) ? explode('|', $category) : null;
                $category_level1_name = isset($category_array[0]) ? str_replace(' ', '', trim($category_array[0], "\"")) : NULL;
                $category_level2_name = isset($category_array[1]) ? str_replace(' ', '', trim($category_array[1], "\"")) : NULL;

                array_push($array, [
                    'id' => $manual->id,
                    'number' => $no,
                    'category_level1_id' =>  isset($category_array[0]) ? ManualCategoryLevel1::where('name', $category_level1_name)->pluck('id')->first() : NULL,
                    'category_level2_id' =>  isset($category_array[1]) ? ManualCategoryLevel2::where('name', $category_level2_name)->pluck('id')->first() : NULL,
                    'title' => $title,
                    'tag' => $this->tagImportParam([$tag1, $tag2, $tag3, $tag4, $tag5]),
                    'start_datetime' => $start_datetime,
                    'end_datetime' => $end_datetime,
                    'brand' => $brand_param,
                    'description' => $description,
                    'contents' => $contents
                ]);

                file_put_contents($file_path, ceil((($key + 1) / $count) * 100));
            }

            return response()->json([
                'json' => $array
            ], 200);
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorMessage = [];
            foreach ($failures as $index => $failure) {
                $errorMessage[$index]["row"] = $failure->row(); // row that went wrong
                $errorMessage[$index]["attribute"] = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $errorMessage[$index]["errors"] = $failure->errors(); // Actual error messages from Laravel validator
                $errorMessage[$index]["value"] = $failure->values(); // The values of the row that has failed.
            }

            File::delete($file_path);
            return response()->json([
                'message' => $errorMessage
            ], 422);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());

            File::delete($file_path);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function progress(Request $request)
    {
        $file_name = $request->file_name;
        $file_path = public_path() . '/log/' . $file_name;
        if (!File::exists($file_path)) {
            return response()->json([
                'message' => "ログファイルが存在しません"
            ], 500);
        }


        $log = File::get($file_path);
        if ($log == 100) {
            File::delete($file_path);
        }
        return $log;
    }

    public function import(Request $request)
    {
        $admin = session('admin');
        $manuals = $request->json();

        $admin = session('admin');

        $log_id = DB::table('manual_csv_logs')->insertGetId([
            'imported_datetime' => new Carbon('now'),
            'is_success' => false
        ]);

        try {
            DB::beginTransaction();
            foreach ($manuals as $key => $ml) {
                $manual = Manual::find($ml["id"]);
                $manual->number = $ml["number"];
                $manual->description = $ml["description"];
                $manual->category_level1_id = $ml["category_level1_id"];
                $manual->category_level2_id = $ml["category_level2_id"];
                $manual->title = $ml["title"];
                $manual->tag()->sync($ml["tag"]);
                $manual->start_datetime = $ml["start_datetime"];
                $manual->end_datetime = $ml["end_datetime"];
                if ($manual->isDirty()) $manual->updated_admin_id = $admin->id;
                $manual->save();

                $manual->brand()->sync($ml["brand"]);

                if (isset($manual->content)) {
                    foreach ($manual->content as $key => $content) {
                        $content_title = $ml["contents"][$key]["title"] ?? '';
                        $content_description = $ml["contents"][$key]["description"]  ?? '';
                        $content->title = $content_title;
                        $content->description = $content_description;
                        $content->save();
                    }
                }

                // ユーザー配信
                if (!$manual->editing_flg) {
                    $origin_user = $manual->user()->pluck('id')->toArray();
                    $new_target_user = $this->targetUserParam($ml["brand"]);
                    $new_target_user_id = array_keys($new_target_user);
                    $detach_user = array_diff($origin_user, $new_target_user_id);
                    $attach_user = array_diff($new_target_user_id, $origin_user);

                    $manual->user()->detach($detach_user);
                    foreach ($attach_user as $key => $user) {
                        $manual->user()->attach([$user => $new_target_user[$user]]);
                    }
                }
            }

            DB::table('manual_csv_logs')
                ->where('id', $log_id)
                ->update([
                    'imported_datetime' => new Carbon('now'),
                    'is_success' => true
                ]);

            DB::commit();
            return response()->json([
                'message' => "インポート完了しました"
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // 動画マニュアルCSV アップロード（新規登録/編集）
    public function csvStoreUpload(Request $request)
    {
        $log_file_name = $request->input('log_file_name');
        $file_path = public_path() . '/log/' . $log_file_name;
        file_put_contents($file_path, "0");

        $admin = session('admin');
        $csv = $request->file;
        $organization1 = (int) $request->input('organization1');

        $csv_content = file_get_contents($csv);
        $encoding = mb_detect_encoding($csv_content);
        if ($encoding == "UTF-8") {
            $shift_jis_content = mb_convert_encoding($csv_content, 'CP932', 'UTF-8');
            file_put_contents($csv, $shift_jis_content);
        }

        $shop_list = $this->getShopForm($organization1);
        $csv_path = Storage::putFile('csv', $csv);
        Log::info("マニュアルCSVインポート", [
            'csv_path' => $csv_path,
            'admin' => $admin
        ]);
        try {
            Excel::import(new ManualStoreCsvImport($organization1, $shop_list), $csv, \Maatwebsite\Excel\Excel::CSV);
            $collection = Excel::toCollection(new ManualStoreCsvImport($organization1, $shop_list), $csv, \Maatwebsite\Excel\Excel::CSV);

            $array = [];
            foreach (
                $collection[0] as $key => [
                    $store_code,
                    $store_name
                ]
            ) {
                array_push($array, [
                    'store_code' => $store_code,
                    'store_name' => $store_name
                ]);
                file_put_contents($file_path, ceil((($key + 1)) * 100));
            }

            return response()->json([
                'json' => $array
            ], 200);
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorManual = [];
            foreach ($failures as $index => $failure) {
                $errorManual[$index]["row"] = $failure->row(); // row that went wrong
                $errorManual[$index]["attribute"] = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $errorManual[$index]["errors"] = $failure->errors(); // Actual error manuals from Laravel validator
                $errorManual[$index]["value"] = $failure->values(); // The values of the row that has failed.
            }

            File::delete($file_path);
            return response()->json([
                'manual' => $errorManual
            ], 422);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());

            File::delete($file_path);
            return response()->json([
                'manual' => "エラーが発生しました"
            ], 500);
        }
    }

    public function storeProgress(Request $request)
    {
        $file_name = $request->file_name;
        $file_path = public_path() . '/log/' . $file_name;
        if (!File::exists($file_path)) {
            return response()->json([
                'manual' => "ログファイルが存在しません"
            ], 500);
        }

        $log = File::get($file_path);
        if ($log == 100) {
            File::delete($file_path);
        }
        return $log;
    }

    // 動画マニュアルCSV インポート（新規登録/編集）
    public function csvStoreImport(Request $request)
    {
        $admin = session('admin');

        // JSONデータの取得
        $storesJson = $request->json('file_json');
        $organization1_id = $request->json('organization1_id');

        $csvStoreIds = [];
        $brand_id = Brand::where('organization1_id', $organization1_id)->pluck('id')->toArray();

        // ショップIDを取得
        $csvStoreIds = DB::table('shops')
            ->join('brands', 'shops.brand_id', '=', 'brands.id')
            ->whereIn('brands.id', $brand_id)
            ->whereIn('shops.shop_code', array_column($storesJson, 'store_code'))
            ->pluck('shops.id')
            ->toArray();

        try {
            // 業態一覧を取得する
            $brand_list = Brand::where('organization1_id', $organization1_id)->get();

            $organization_list = [];
            $organization_list = Shop::query()
                ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
                ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
                ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
                ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
                ->distinct('organization4_id')
                ->distinct('organization5_id')
                ->select(
                    'organization2_id',
                    'organization2.name as organization2_name',
                    'organization2.order_no as organization2_order_no',
                    'organization3_id',
                    'organization3.name as organization3_name',
                    'organization3.order_no as organization3_order_no',
                    'organization4_id',
                    'organization4.name as organization4_name',
                    'organization4.order_no as organization4_order_no',
                    'organization5_id',
                    'organization5.name as organization5_name',
                    'organization5.order_no as organization5_order_no',
                )
                ->where('organization1_id', $organization1_id)
                ->orderByRaw('organization2_id is null asc')
                ->orderByRaw('organization3_id is null asc')
                ->orderByRaw('organization4_id is null asc')
                ->orderByRaw('organization5_id is null asc')
                ->orderBy("organization2_order_no", "asc")
                ->orderBy("organization3_order_no", "asc")
                ->orderBy("organization4_order_no", "asc")
                ->orderBy("organization5_order_no", "asc")
                ->get()
                ->toArray();


            // 事前に必要なデータをすべて一括取得
            $brand_ids = $brand_list->pluck('id')->toArray();
            $all_shops = Shop::query()
                ->select(
                    'shops.id as id',
                    'shops.shop_code',
                    'shops.display_name',
                    'shops.organization5_id',
                    'shops.organization4_id',
                    'shops.organization3_id',
                    'shops.organization2_id'
                )
                ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
                ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
                ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
                ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
                ->where('shops.organization1_id', $organization1_id)
                ->whereIn('shops.brand_id', $brand_ids)
                ->orderBy('shops.shop_code', 'asc')
                ->get()
                ->toArray();


            // 組織別にデータを整理する
            $organization_list = array_map(function ($org) use ($all_shops) {
                $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization5_id'] == $org['organization5_id'];
                });
                $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
                });
                $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
                });
                $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
                });
                return $org;
            }, $organization_list);

            // shop_code でソート済みの $all_shops をそのまま利用
            $all_shop_list = array_map(function ($shop) {
                return [
                    'shop_id' => $shop['id'],
                    'shop_code' => $shop['shop_code'],
                    'display_name' => $shop['display_name'],
                ];
            }, $all_shops);


            // shop_codeを基準にソートするためのカスタム比較関数を定義
            usort($all_shop_list, function ($a, $b) {
                return strcmp($a['shop_code'], $b['shop_code']);
            });

            return response()
                ->view('common.admin.manual-csv-store-modal', [
                    'storesJson' => $storesJson,
                    'brand_list' => $brand_list,
                    'organization_list' => $organization_list,
                    'all_shop_list' => $all_shop_list,
                    'csvStoreIds' => $csvStoreIds,
                ], 200)
                ->header('Content-Type', 'text/plain');
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'manual' => $th->getMessage()
            ], 500);
        }
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function isChangedFile($current_file_path, $next_file_path): Bool
    {
        $currnt_path = $current_file_path ? basename($current_file_path) : null;
        $next_path = $next_file_path ? basename($next_file_path) : null;

        return !($currnt_path == $next_path);
    }

    private function targetUserParam($brand): array
    {
        // manual_userに該当のユーザーを登録する
        $target_user_data = [];
        // 該当のショップID
        $shops_id = Shop::select('id')->whereIn('brand_id', $brand)->get()->toArray();
        // 該当のユーザー
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->get()->toArray();
        foreach ($target_users as $target_user) {
            $target_user_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }
        return $target_user_data;
    }

    private function getTargetUsersByShopId($organizations): array
    {
        $target_user_data = [];
        $chunkSize = 200; // チャンクサイズを設定

        if (isset($organizations->organization_shops)) {
            $organization_shops = explode(',', $organizations->organization_shops);

            // ユーザーとショップのデータを一度に取得
            $all_users = User::query()
                ->select(
                    'users.id as user_id',
                    'users.shop_id',
                    'shops.brand_id',
                    'shops.organization5_id',
                    'shops.organization4_id',
                    'shops.organization3_id',
                    'shops.organization2_id'
                )
                ->leftJoin('shops', 'users.shop_id', '=', 'shops.id')
                ->whereIn('shops.id', $organization_shops)
                ->whereIn('shops.brand_id', $organizations->brand)
                ->orderBy('users.shop_id', 'asc')
                ->get()
                ->toArray();

            // データをチャンクで処理
            $user_chunks = array_chunk($all_users, $chunkSize);

            foreach ($user_chunks as $chunk) {
                foreach ($chunk as $user) {
                    $target_user_data[$user['user_id']] = ['shop_id' => $user['shop_id']];
                }
            }
        }

        return $target_user_data;
    }

    // 「手順」を登録するために加工する
    private function manualContentsParam($request): array
    {
        if (!(isset($request['manual_flow']))) return [];

        $content_data = [];
        foreach ($request['manual_flow'] as $i => $r) {
            $content_data[$i]['title'] = $r['title'];
            $content_data[$i]['description'] = $r['detail'];
            $content_data[$i]['order_no'] = $i + 1;

            if (isset($r['file_name']) && isset($r['file_path'])) {
                $content_data[$i]['content_name'] = $r['file_name'];
                $content_data[$i]['content_url'] = $this->registerFile($r['file_path']);
                $content_data[$i]['thumbnails_url'] =
                    ImageConverter::convert2image($content_data[$i]['content_url']);
            }
        }
        return $content_data;
    }

    private function rollbackManualContentFile($request): Void
    {
        if (!(isset($request['manual_flow']))) return;
        foreach ($request['manual_flow'] as $i => $r) {
            if (isset($r['file_name']) && isset($r['file_path'])) {
                $current_path = storage_path('app/' . $r['file_path']);
                $next_path = public_path('uploads/' . basename($r['file_path']));
                try {
                    rename($next_path, $current_path);
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
            }
        }
    }

    private function registerFile($request_file_path): ?String
    {
        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);
        rename($current_path, $next_path);
        return $content_url;
    }
    private function rollbackRegisterFile($request_file_path): Void
    {
        if (!(isset($request_file_path))) return;
        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);
        try {
            rename($next_path, $current_path);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
        return;
    }

    private function getExistContentIds($request_manual_flow): array
    {
        if (!isset($request_manual_flow)) return [];

        $content_ids = [];
        foreach ($request_manual_flow as $i => $r) {
            if (isset($r['content_id'])) {
                $id = (int)$r['content_id'];
                $content_ids[] = $id;
            }
        }
        return $content_ids;
    }

    private function level1CategoryParam($level2_category_id): ?Int
    {
        if (!isset($level2_category_id) || $level2_category_id == "null") return null;
        return ManualCategoryLevel2::find($level2_category_id)->level1;
    }

    private function level2CategoryParam($level2_category_id): ?Int
    {
        if ($level2_category_id == "null") return null;
        return $level2_category_id;
    }

    private function tagImportParam(?array $tags): array
    {
        if (!isset($tags)) return [];

        $tags_pram = [];
        foreach ($tags as $key => $tag_name) {
            if (!isset($tag_name)) continue;
            $tag = ManualTagMaster::firstOrCreate(['name' => trim($tag_name, "\"")]);
            $tags_pram[] = $tag->id;
        }
        return $tags_pram;
    }

    private  function strToArray(?String $str): array
    {
        if (!isset($str)) return [];

        $array = explode(',', $str);

        $returnArray = [];
        foreach ($array as $key => $value) {
            $returnArray[] = trim($value, "\"");
        }

        return $returnArray;
    }

    private function getOrganizationForm($organization1_id)
    {
        return Shop::query()
            ->leftjoin('brands', 'brand_id', '=', 'brands.id')
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('brand_id')
            ->distinct('organization2_id')
            ->distinct('organization3_id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'brand_id',
                'brands.name as brand_name',
                'organization2_id',
                'organization2.name as organization2_name',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization5_id',
                'organization5.name as organization5_name'
            )
            ->where('shops.organization1_id', $organization1_id)
            ->get()
            ->toArray();
    }
    private function getBrandNameArray(Int $org1_id): array
    {
        return Brand::query()
            ->where('organization1_id', '=', $org1_id)
            ->pluck('name')
            ->toArray();
    }

    private function getShopForm($organization1_id)
    {
        return Shop::query()
            ->select([
                'shops.*',
                DB::raw("GROUP_CONCAT(brands.name SEPARATOR ',') as brand_names")
            ])
            ->join('brands', function ($join) {
                $join->on('shops.organization1_id', '=', 'brands.organization1_id')
                    ->on('shops.brand_id', '=', 'brands.id');
            })
            ->where('shops.organization1_id', $organization1_id)
            ->groupBy('shops.id')
            ->get()
            ->toArray();
    }
}
