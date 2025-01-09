<?php

namespace App\Exports;

use App\Enums\PublishStatus;
use App\Models\Manual;
use App\Models\ManualShop;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ManualListExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    protected $manual_id;
    protected $request;

    public function __construct(Request $request)
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
        $organization1_id = $this->request->input('brand', $admin->firstOrganization1()->id);

        // クエリパラメータから全ページか一部ページかを判断
        $all = filter_var($this->request->query('all', false), FILTER_VALIDATE_BOOLEAN);

        if ($all === true) {
            // 全ページのデータをエクスポート
            $new_category_id = $this->request->input('new_category');
            $status = PublishStatus::tryFrom($this->request->input('status'));
            $q = $this->request->input('q');
            $publish_date = $this->request->input('publish-date');
            $cte = DB::table('manuals')
                ->select([
                    'manuals.id as manual_id',
                    DB::raw('
                                CASE
                                    WHEN (COUNT(DISTINCT b.name)) = 0 THEN ""
                                    ELSE group_concat(distinct b.name order by b.name)
                                END as brand_name')
                ])
                ->leftjoin('manual_brand as m_b', 'manuals.id', '=', 'm_b.manual_id')
                ->leftjoin('brands as b', 'm_b.brand_id', '=', 'b.id')
                ->groupBy('manuals.id');

            $manual_list =
                Manual::query()
                ->select([
                    'manuals.*',
                    DB::raw('count(distinct manualcontents.id) as content_counts'),
                    'org.*'
                ])
                ->with('create_user', 'updated_user', 'brand', 'tag', 'category_level1', 'category_level2')
                ->leftjoin('manual_user', 'manuals.id', '=', 'manual_id')
                ->leftJoin('manualcontents', function ($join) {
                    $join->on('manuals.id', '=', 'manualcontents.manual_id');
                })
                ->leftJoinSub($cte, 'org', function ($join) {
                    $join->on('manuals.id', '=', 'org.manual_id');
                })
                ->where('manuals.organization1_id', $organization1_id)
                ->groupBy('manuals.id')
                // 検索機能 キーワード
                ->when(isset($q), function ($query) use ($q) {
                    $query->where(function ($query) use ($q) {
                        $query->whereLike('manuals.title', $q)
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
                ->orderBy('manuals.number', 'asc')
                ->get();

        } else {
            // 一部ページのデータをエクスポート
            $manual_list = session('manual_list');

            if ($manual_list) {
                // コンテンツ数をカウント
                foreach ($manual_list as &$manual) {
                    $manual->content_counts = count($manual->content);
                }
                // マニュアルリストをソート
                $manual_list = $manual_list->sortBy('number');
            }
        }

        // 店舗を取得
        if ($manual_list) {
            // すべての店舗数を取得
            $all_shops = Shop::where('organization1_id', $organization1_id)->get();
            $all_shop_count = $all_shops->count();
            $all_shop_names = $all_shops->pluck('display_name', 'id')->toArray();

            foreach ($manual_list as &$manual) {
                // 各メッセージに関連する店舗IDを取得
                $shop_ids = ManualShop::where('manual_id', $manual->id)
                    ->pluck('shop_id')
                    ->toArray();
                $shop_count = count($shop_ids);

                if (!empty($shop_ids)) {
                    $shop_display_names = array_intersect_key($all_shop_names, array_flip($shop_ids));
                    $manual->shop_names = implode(',', $shop_display_names);
                } else {
                    $manual->shop_names = "";
                }

                // 全店舗数と同じ場合は「全店」と表示
                if ($shop_count == $all_shop_count) {
                    $manual->shop_names = "全店";
                }
            }
        }

        return view('exports.manual-list-export', [
            'manual_list' => $manual_list,
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
