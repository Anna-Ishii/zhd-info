<?php

namespace App\Exports;

use App\Enums\PublishStatus;
use App\Models\Manual;
use App\Models\ManualCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ManualListExport implements FromView, ShouldAutoSize, WithCustomCsvSettings
{
    protected $manual_id;
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true
        ];
    }
    
    public function view(): View
    {
        $admin = session('admin');
        $category_list = ManualCategory::all();
        $_brand = $admin->organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();
        $brand_list = $_brand->get();

        // request
        $category_id = $this->request->input('category');
        $status = PublishStatus::tryFrom($this->request->input('status'));
        $q = $this->request->input('q');
        $rate = $this->request->input('rate');
        $brand_id = $this->request->input('brand');
        $publish_date = $this->request->input('publish-date');
        $cte = DB::table('manuals')
                    ->select([
                        'manuals.id as manual_id',
                        DB::raw('
                            CASE
                                WHEN (COUNT(DISTINCT b.name)) = 0 THEN ""
                                WHEN (
                                    SELECT COUNT(DISTINCT _b.name) 
                                    FROM brands as _b
                                    WHERE _b.organization1_id = manuals.organization1_id
                                ) = COUNT(DISTINCT b.name) THEN "全て"
                                ELSE group_concat(distinct b.name)
                            END as brand_name')
                        ])
                        ->leftjoin('manual_brand as m_b', 'manuals.id', '=', 'm_b.manual_id')
                        ->leftjoin('brands as b', 'm_b.brand_id', '=', 'b.id')
                        ->groupBy('manuals.id');

        $manual_list =
            Manual::query()
            ->select([
                'manuals.*',
                DB::raw('round((sum(manual_user.read_flg) / count(manual_user.user_id)) * 100, 1) as view_rate'),
                DB::raw('count(distinct manualcontents.id) as content_counts'),
                'org.*'
            ])
            ->with('category', 'create_user', 'updated_user', 'brand', 'tag')
            ->leftjoin('manual_user', 'manuals.id', '=', 'manual_id')
            ->leftJoin('manualcontents', function ($join) {
                $join->on('manuals.id', '=', 'manualcontents.manual_id');
            })
            ->leftJoinSub($cte, 'org', function ($join) {
                $join->on('manuals.id', '=', 'org.manual_id');
            })
            ->where('manuals.organization1_id', $admin->organization1_id)
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
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->when(isset($brand_id), function ($query) use ($brand_id) {
                $query->leftjoin('manual_brand', 'manuals.id', '=', 'manual_brand.manual_id')
                    ->where('manual_brand.brand_id', '=', $brand_id);
            })
            ->when((isset($rate[0])|| isset($rate[1])), function ($query) use ($rate) {
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
            ->get();

        return view('exports.manual-list-export', [
            'manual_list' => $manual_list,
            'brand_list' => $brand_list,
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
