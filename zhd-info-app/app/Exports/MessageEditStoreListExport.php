<?php

namespace App\Exports;

use App\Models\Message;
use App\Models\MessageShop;
use App\Models\MessageUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class MessageEditStoreListExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    protected $request;
    protected $message_id;

    public function __construct($request, $message_id = null)
    {
        $this->request = $request;
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
        $admin = session('admin');
        $message = Message::find($this->message_id);

        $store_list =
            DB::table('shops')
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
            ->get();

        $all_store_list = $store_list->toArray();
        $target_brand = $message->brand()->pluck('brands.id')->toArray();

        $shop_ids = [];
        foreach ($all_store_list as &$store) {
            foreach ($target_brand as $brand) {
                $shop_ids = array_merge($shop_ids, MessageShop::where('message_id', $message->id)->where('brand_id', $brand)->pluck('shop_id')->toArray());
            }

            // MessageShopにshop_idが見つからない場合はMessageUserを確認
            if (empty($shop_ids)) {
                $shop_ids = MessageUser::where('message_id', $message->id)->pluck('shop_id')->toArray();
            }

            // 先行か通常かを設定
            $store->checked_store = in_array($store->id, $shop_ids) ? '先行' : '通常';
        }
        unset($store); // 参照を解除

        return view('exports.message-store-list-export', [
            'store_list' => $all_store_list,
            'admin' => $admin
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
