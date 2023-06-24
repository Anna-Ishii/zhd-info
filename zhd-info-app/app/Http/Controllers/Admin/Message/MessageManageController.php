<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Message;
use App\Models\Shop;
use Illuminate\Http\Request;

class MessageManageController extends Controller
{
    public function index()
    {
        $category_list = Category::all();

        // $message_list = $user->message;
        $message_list = Message::orderBy('created_at', 'desc');

        return view('admin.message.manage.index', [
            'category_list' => $category_list,
            'message_list' => $message_list->paginate(5)
                                            ->appends(request()->query())
        ]);
    }

    public function detail(Request $request, $message_id)
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