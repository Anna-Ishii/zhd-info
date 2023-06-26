<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Models\MessageCategory;
use App\Models\Message;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
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
        $target_shop = self::get_target_users($message);


        return view('admin.message.manage.detail', [
            "message" => $message,
            "target_shop" => $target_shop->paginate(10)
                ->appends(request()->query()),
        ]);
    }

    /**
     * メッセージの該当shopと、該当userの閲覧数と在籍者数を取得する
     *  
     * @param Message $message マニュアルオブジェクト
     * @return Shop 
     */
    private function get_target_users(Message $message)
    {
        $target_user_isread = DB::table('message_user')
        ->select('shop_id')
        ->selectRaw('COUNT(*) as total')
        ->where('message_id', $message->id)
            ->where('read_flg', 1)
            ->groupBy('shop_id');

        $target_user = DB::table('message_user')
        ->select('shop_id')
        ->selectRaw('COUNT(*) as total')
        ->where('message_id', $message->id)
            ->groupBy('shop_id');

        $result = Shop::rightJoinSub($target_user, 'target_user', function ($join) {
            $join->on('shops.id', '=', 'target_user.shop_id');
        })->leftJoinSub($target_user_isread, 'target_user_isread', function ($join) {
            $join->on('shops.id', '=', 'target_user_isread.shop_id');
        })
            ->select('shops.*', 'target_user_isread.total as target_user_isread_total', 'target_user.total as target_user_total');
        return $result;
    }
}