<?php

namespace App\Exports;

use App\Enums\PublishStatus;
use App\Models\Brand;
use App\Models\Organization1;
use App\Models\Message;
use App\Models\MessageShop;
use App\Models\MessageUser;
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

        $category_id = $this->request->input('category');
        $status = PublishStatus::tryFrom($this->request->input('status'));
        $q = $this->request->input('q');
        $organization1_id = $this->request->input('brand', $admin->firstOrganization1()->id);
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

        // グループ結合の最大長を設定
        DB::statement("SET SESSION group_concat_max_len = 200000");

        $message_list = Message::query()
            ->select([
                'messages.*',
                'org.*',
                DB::raw('GROUP_CONCAT(DISTINCT message_shop.shop_id) as shop_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT message_user.shop_id) as user_shop_ids')
            ])
            ->with(['category', 'brand', 'tag'])
            ->leftJoin('message_user', 'messages.id', '=', 'message_id')
            ->leftJoinSub($cte, 'org', function ($join) {
                $join->on('messages.id', '=', 'org.message_id');
            })
            ->leftJoin('message_shop', 'messages.id', '=', 'message_shop.message_id')
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
            ->orderBy('messages.number', 'desc')
            ->get();

        // すべての店舗数を取得
        $all_shops = Shop::where('organization1_id', $organization1_id)->get();
        $all_shop_count = $all_shops->count();
        $all_shop_names = $all_shops->pluck('display_name', 'id')->toArray();

        // メッセージリストをループして、店舗数を割り当て
        foreach ($message_list as &$message) {
            $shop_ids = array_filter(explode(',', $message->shop_ids));
            $user_shop_ids = array_filter(explode(',', $message->user_shop_ids));

            // $shop_idsが存在する場合はそれを使用し、存在しない場合は$user_shop_idsを使用
            $all_ids = !empty($shop_ids) ? $shop_ids : $user_shop_ids;
            $shop_count = count($all_ids);

            if (!empty($all_ids)) {
                $shop_display_names = array_intersect_key($all_shop_names, array_flip($all_ids));
                $message->shop_names = implode(',', $shop_display_names);
            } else {
                $message->shop_names = "";
            }

            // 全店舗数と同じ場合は「全店」と表示
            if ($shop_count == $all_shop_count) {
                $message->shop_names = "全店";
            }
        }

        return view('exports.message-list-bb-export', [
            'message_list' => $message_list,
            'admin' => $admin
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
