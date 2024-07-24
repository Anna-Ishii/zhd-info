<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class MessageNewStoreListExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    protected $organization1_id;

    public function __construct($organization1_id = null)
    {
        $this->organization1_id = $organization1_id;
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
            ->where('shops.organization1_id', $this->organization1_id)
            ->groupBy('shops.id')
            ->orderBy('shops.shop_code')
            ->get()
            ->toArray();

        foreach ($store_list as &$store) {
            $store->checked_store = '先行';
        }
        unset($store); // 参照を解除

        return view('exports.message-store-list-export', [
            'store_list' => $store_list,
            'admin' => $admin
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
