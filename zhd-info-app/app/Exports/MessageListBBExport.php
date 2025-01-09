<?php

namespace App\Exports;

use App\Enums\PublishStatus;
use App\Models\Message;
use App\Models\MessageShop;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Http\Request;

class MessageListBBExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
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
            $category_id = $this->request->input('category');
            $status = PublishStatus::tryFrom($this->request->input('status'));
            $q = $this->request->input('q');
            $label = $this->request->input('label');
            $publish_date = $this->request->input('publish-date');
            $cte = DB::table('messages')
                ->select([
                    'messages.id as message_id',
                    DB::raw('
                                CASE
                                    WHEN (COUNT(DISTINCT b.name)) = 0 THEN ""
                                    ELSE group_concat(distinct b.name order by b.name)
                                END as brand_name')
                ])
                ->leftJoin('message_brand as m_b', 'messages.id', '=', 'm_b.message_id')
                ->leftJoin('brands as b', 'm_b.brand_id', '=', 'b.id')
                ->groupBy('messages.id');

            $message_list = Message::query()
                ->select([
                    'messages.*',
                    'org.*',
                ])
                ->with(['category', 'brand', 'tag'])
                ->leftJoin('message_user', 'messages.id', '=', 'message_id')
                ->leftJoinSub($cte, 'org', function ($join) {
                    $join->on('messages.id', '=', 'org.message_id');
                })
                ->where('messages.organization1_id', $organization1_id)
                ->groupBy('messages.id')
                ->when($q, function ($query) use ($q) {
                    $query->where(function ($query) use ($q) {
                        $query->whereLike('title', $q)
                            ->orWhereHas('tag', function ($query) use ($q) {
                                $query->where('name', $q);
                            });
                    });
                })
                ->when($status, function ($query) use ($status) {
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
                ->when($category_id, function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                })
                ->when($label, function ($query) {
                    $query->where('emergency_flg', true);
                })
                ->when($publish_date, function ($query) use ($publish_date) {
                    if (isset($publish_date[0])) {
                        $query->where('start_datetime', '>=', $publish_date[0]);
                    }
                    if (isset($publish_date[1])) {
                        $query->where(function ($query) use ($publish_date) {
                            $query->where('end_datetime', '<=', $publish_date[1])
                                ->orWhereNull('end_datetime');
                        });
                    }
                })
                ->join('admin', 'create_admin_id', '=', 'admin.id')
                ->orderBy('messages.number', 'asc')
                ->get();

        } else {
            // 一部ページのデータをエクスポート
            $message_list = session('message_list');

            if ($message_list) {
                // メッセージリストをソート
                $message_list = $message_list->sortBy('number');
            }
        }

        // 店舗を取得
        if ($message_list) {
            // すべての店舗数を取得
            $all_shops = Shop::where('organization1_id', $organization1_id)->get();
            $all_shop_count = $all_shops->count();
            $all_shop_names = $all_shops->pluck('display_name', 'id')->toArray();

            foreach ($message_list as &$message) {
                // 各メッセージに関連する店舗IDを取得
                $shop_ids = MessageShop::where('message_id', $message->id)
                    ->pluck('shop_id')
                    ->toArray();
                $shop_count = count($shop_ids);

                if (!empty($shop_ids)) {
                    $shop_display_names = array_intersect_key($all_shop_names, array_flip($shop_ids));
                    $message->shop_names = implode(',', $shop_display_names);
                } else {
                    $message->shop_names = "";
                }

                // 全店舗数と同じ場合は「全店」と表示
                if ($shop_count == $all_shop_count) {
                    $message->shop_names = "全店";
                }
            }
        }

        return view('exports.message-list-bb-export', [
            'message_list' => $message_list,
            'admin' => $admin
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
