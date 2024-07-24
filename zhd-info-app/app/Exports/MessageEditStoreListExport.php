<?php

namespace App\Exports;

use App\Models\Message;
use App\Models\MessageShop;
use App\Models\MessageUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class MessageEditStoreListExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    protected $message_id;

    public function __construct($message_id = null)
    {
        $this->message_id = $message_id;
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
        try {
            $admin = session('admin');
            $message = Message::find($this->message_id);

            if (!$message) {
                throw new \Exception('メッセージが見つかりません');
            }

            $store_list = DB::table('shops')
                ->select([
                    'shops.*',
                    DB::raw("GROUP_CONCAT(brands.name SEPARATOR ',') as brand_name")
                ])
                ->join('brands', function ($join) {
                    $join->on('shops.organization1_id', '=', 'brands.organization1_id')
                        ->on('shops.brand_id', '=', 'brands.id');
                })
                ->where('shops.organization1_id', $message->organization1_id)
                ->groupBy('shops.id')
                ->orderBy('shops.shop_code')
                ->get();

            $all_store_list = $store_list->toArray();
            $target_brand_ids = $message->brand()->pluck('brands.id')->toArray();

            // すべての関連shop_idを一度に取得
            $shop_ids = MessageShop::where('message_id', $message->id)
                ->whereIn('brand_id', $target_brand_ids)
                ->pluck('shop_id')
                ->toArray();

            // shop_idsが空の場合、MessageUserからshop_idを取得
            if (empty($shop_ids)) {
                $shop_ids = MessageUser::where('message_id', $message->id)
                    ->pluck('shop_id')
                    ->toArray();
            }

            // 各店舗のchecked_storeを設定
            foreach ($all_store_list as &$store) {
                $store->checked_store = in_array($store->id, $shop_ids) ? '先行' : '通常';
            }
            unset($store); // 参照を解除

            return view('exports.message-store-list-export', [
                'store_list' => $all_store_list,
                'admin' => $admin
            ]);
        } catch (\Exception $e) {
            Log::error('CSVエクスポートエラー: ' . $e->getMessage());
            throw new \Exception('内部サーバーエラーが発生しました。');
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
