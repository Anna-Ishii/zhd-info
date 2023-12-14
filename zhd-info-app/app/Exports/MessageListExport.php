<?php

namespace App\Exports;

use App\Enums\PublishStatus;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class MessageListExport implements FromView, ShouldAutoSize, WithCustomCsvSettings
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

        $category_id = $this->request->input('category');
        $status = PublishStatus::tryFrom($this->request->input('status'));
        $q = $this->request->input('q');
        $rate = $this->request->input('rate');
        $brand_id = $this->request->input('brand');
        $label = $this->request->input('label');
        $publish_date = $this->request->input('publish-date');
        $cte = DB::table('messages')
                    ->select([
                        'messages.id as message_id',
                        DB::raw('
                            CASE
                                WHEN (COUNT(DISTINCT o5.name)) = 0 THEN ""
                                WHEN (
                                    SELECT COUNT(DISTINCT organization5_id) 
                                    FROM shops 
                                    WHERE organization1_id = messages.organization1_id
                                ) = COUNT(DISTINCT o5.name) THEN "全て"
                                ELSE group_concat(distinct o5.name)
                            END as o5_name'),
                        DB::raw('
                            CASE
                                WHEN (COUNT(DISTINCT o4.name)) = 0 THEN ""
                                WHEN (
                                    SELECT COUNT(DISTINCT organization4_id) 
                                    FROM shops 
                                    WHERE organization1_id = messages.organization1_id
                                ) = COUNT(DISTINCT o4.name) THEN "全て"
                                ELSE group_concat(distinct o4.name)
                            END as o4_name'),
                        DB::raw('
                            CASE
                                WHEN (COUNT(DISTINCT o3.name)) = 0 THEN ""
                                WHEN (
                                    SELECT COUNT(DISTINCT organization3_id) 
                                    FROM shops 
                                    WHERE organization1_id = messages.organization1_id
                                ) = COUNT(DISTINCT o3.name) THEN "全て"
                                ELSE group_concat(distinct o3.name)
                            END as o3_name'),
                        DB::raw('
                            CASE
                                WHEN (COUNT(DISTINCT b.name)) = 0 THEN ""
                                WHEN (
                                    SELECT COUNT(DISTINCT _b.name) 
                                    FROM brands as _b
                                    WHERE _b.organization1_id = messages.organization1_id
                                ) = COUNT(DISTINCT b.name) THEN "全て"
                                ELSE group_concat(distinct b.name)
                            END as brand_name')
                    ])
                    ->leftjoin('message_organization as m_o', 'messages.id', '=', 'm_o.message_id')
                    ->leftjoin('organization5 as o5', 'm_o.organization5_id', '=', 'o5.id')
                    ->leftjoin('organization4 as o4', 'm_o.organization4_id', '=', 'o4.id')
                    ->leftjoin('organization3 as o3', 'm_o.organization3_id', '=', 'o3.id')
                    ->leftjoin('message_brand as m_b', 'messages.id', '=', 'm_b.message_id')
                    ->leftjoin('brands as b', 'm_b.brand_id', '=', 'b.id')
                    ->groupBy('messages.id');
        
        $message_list =
            Message::query()
                ->select([
                    'messages.*',
                    DB::raw('round((sum(message_user . read_flg) / count(message_user . user_id)) * 100, 1) as view_rate'),
                    'org.*'
                ])
                ->with('category', 'brand', 'tag')
                ->leftjoin('message_user', 'messages.id', '=', 'message_id')
                ->leftJoinSub($cte, 'org', function($join) {
                    $join->on('messages.id', '=', 'org.message_id');
                })
                ->where('messages.organization1_id', $admin->organization1_id)
                ->groupBy('messages.id')
                ->when(isset($q), function ($query) use ($q) {
                    $query->where(function ($query) use ($q) {
                        $query->whereLike('title', $q)
                            ->orWhereHas('tag', function ($query) use ($q) {
                                $query->where('name', $q);
                            });
                    });
                })
                ->when(isset($status), function ($query) use ($status) {
                    switch ($status) {
                        case PublishStatus::Wait:
                            $query->waitMessage();
                            break;
                        case PublishStatus::Publishing:
                            $query->publishingMessage();
                            break;
                        case PublishStatus::Published:
                            $query->publishedMessage();
                            break;
                        case PublishStatus::Editing:
                            $query->where('editing_flg', '=', true);
                            break;
                        default:
                            break;
                    }
                })
                ->when(isset($category_id), function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                })
                ->when(isset($brand_id), function ($query) use ($brand_id) {
                    $query->leftjoin('message_brand', 'messages.id', '=', 'message_brand.message_id')
                    ->where('message_brand.brand_id', '=', $brand_id);
                })
                ->when(isset($label), function ($query) use ($label) {
                    $query->where('emergency_flg', true);
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
                            $query->where('end_datetime', '<=',$publish_date[1])
                                ->orWhereNull('end_datetime');
                        });
                })
                ->join('admin', 'create_admin_id', '=', 'admin.id')
                ->orderBy('messages.number', 'desc')
                ->get();
        
        return view('exports.message-list-export',[
            'message_list' => $message_list,
            'admin' => $admin
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
