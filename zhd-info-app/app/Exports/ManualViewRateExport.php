<?php

namespace App\Exports;

use App\Models\Manual;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ManualViewRateExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    protected $manual_id;
    protected $request;

    public function __construct($manual_id = null, $request)
    {
        $this->manual_id = $manual_id;
        $this->request = $request;
    }

    public function getCsvSettings(): array
    {
        return [
            'output_encoding' => 'CP932',
        ];
    }

    public function view(): View
    {
        $admin = session('admin');
        $manual = Manual::where('id', $this->manual_id)
            ->withCount(['user as total_users'])
            ->withCount(['readed_user as read_users'])
            ->first();

        $_brand = $manual->organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();

        $brand_id = $this->request->brand;
        $shop_code = $this->request->shop_code;
        $shop_name = $this->request->shop_name;
        $org3 = $this->request->org3;
        $org4 = $this->request->org4;
        $org5 = $this->request->org5;
        $read_flg = $this->request->read_flg;
        $readed_date = $this->request->readed_date;

        $shop_list = $manual
            ->shop()
            ->when(isset($brand_id), function ($query) use ($brand_id) {
                $query->where('brand_id', $brand_id);
            })
            ->when(isset($shop_code), function ($query) use ($shop_code) {
                $query->where('shop_code', $shop_code);
            })
            ->when(isset($shop_name), function ($query) use ($shop_name) {
                $query->whereLike('name', $shop_name);
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
            ->with(['shop', 'shop.organization3', 'shop.organization4', 'shop.organization5'])
            ->when(isset($read_flg), function ($query) use ($read_flg) {
                if ($read_flg == 'true') $query->where('read_flg', true);
                if ($read_flg == 'false') $query->where('read_flg', false);
            })
            ->when((isset($readed_date[0])), function ($query) use ($readed_date) {
                $query
                    ->where('readed_datetime', '>=', $readed_date[0]);
            })
            ->when((isset($readed_date[1])), function ($query) use ($readed_date) {
                $query
                    ->where(function ($query) use ($readed_date) {
                        $query->where('readed_datetime', '<=', $readed_date[1]);
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
            ->get();

        $category_name = $manual->category_level1 ? $manual->category_level1?->name . "|" . $manual->category_level2->name : $manual->category_level2->name;
        return view('exports.manual-viewrate-export', [
            'users' => $user_list,
            'brand' => $manual->brands_string($brands),
            'category_name' => $category_name,
            'title' => $manual->title,
            'start_datetime' => $manual->formatted_start_datetime,
            'end_datetime' => $manual->formatted_end_datetime,
            'status' => $manual->status->text(),
            'read_user' => $manual->readed_user->count(),
            'target_user' => $manual->user->count(),
            'read_rate' => (($manual->total_users != 0) ? round((($manual->read_users / $manual->total_users) * 100), 1) : 0)
        ]);
    }
}
