<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Models\MessageCategory;
use App\Models\Message;
use App\Models\Shop;
use Illuminate\Http\Request;

class MessageManageController extends Controller
{
    public function index(Request $request)
    {
        $category_list = MessageCategory::all();
        $category_id = $request->input('category');
        $status = $request->input('status');
        $q = $request->input('q');
        $message_list =
            Message::query()
            ->when(isset($q), function ($query) use ($q) {
                $query->whereLike('title', $q);
            })
            ->when(isset($status), function ($query) use ($status) {
                switch ($status) {
                    case 1:
                        $query->where('end_datetime', '>', now('Asia/Tokyo'))
                        ->where(function ($query) {
                            $query->where('start_datetime', '>', now('Asia/Tokyo'))
                            ->orWhereNull('start_datetime');
                        })
                            ->orWhereNull('end_datetime')
                            ->where(function ($query) {
                                $query->where('start_datetime', '>', now('Asia/Tokyo'))
                                ->orWhereNull('start_datetime');
                            });
                        break;
                    case 2:
                        $query->where('start_datetime', '<=', now('Asia/Tokyo'))
                        ->where(function ($query) {
                            $query->where('end_datetime', '>', now('Asia/Tokyo'))
                            ->orWhereNull('end_datetime');
                        });
                        break;
                    case 3:
                        $query->where('end_datetime', '<=', now('Asia/Tokyo'));
                        break;
                    default:
                        break;
                }
            })
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->appends(request()->query());

        return view('admin.message.manage.index', [
            'category_list' => $category_list,
            'message_list' => $message_list
        ]);
    }

    public function detail($message_id)
    {
        $message = Message::find($message_id);

        // メッセージの該当ショップを取得
        $target_org4 = $message->organization4()->select('id')->get()->makeHidden('pivot')->toArray();
        $target_shop = Shop::whereIn('organization4_id', $target_org4);


        return view('admin.message.manage.detail', [
            "message" => $message,
            "target_shop" => $target_shop->paginate(10)
                ->appends(request()->query()),
        ]);
    }
}