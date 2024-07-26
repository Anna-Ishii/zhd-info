<?php

namespace App\Exports;

use App\Models\Message;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class MessageViewRateExport implements
    FromView,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    protected $message_id;
    protected $request;

    public function __construct($message_id = null, $request)
    {
        $this->message_id = $message_id;
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
        $message = Message::where('id', $this->message_id)
            ->withCount(['user as total_users'])
            ->withCount(['readed_user as read_users'])
            ->first();

        $_brand = $message->organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();

        $brand_id = $this->request->brand;
        $shop_code = $this->request->shop_code;
        $shop_name = $this->request->shop_name;
        $org3 = $this->request->org3;
        $org4 = $this->request->org4;
        $org5 = $this->request->org5;
        $read_flg = $this->request->read_flg;
        $readed_date = $this->request->readed_date;

        $shop_list = $message
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

        $user_list = $message
            ->user()
            ->with(['shop', 'shop.organization3', 'shop.organization4', 'shop.organization5', 'shop.brand'])
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

        return view('exports.message-viewrate-export', [
            'users' => $user_list,
            'brand' => $message->brands_string($brands),
            'emergency_flg' => $message->emergency_flg ? "重要" : "",
            'category_name' => $message->category?->name,
            'title' => $message->title,
            'start_datetime' => $message->formatted_start_datetime,
            'end_datetime' => $message->formatted_end_datetime,
            'status' => $message->status->text(),
            'read_user' => $message->readed_user->count(),
            'target_user' => $message->user->count(),
            'read_rate' => (($message->total_users != 0) ? round((($message->read_users / $message->total_users) * 100), 1) : 0),
        ]);
    }
}
