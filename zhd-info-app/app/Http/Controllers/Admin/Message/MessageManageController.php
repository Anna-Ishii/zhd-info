<?php

namespace App\Http\Controllers\Admin\Message;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Message;
use App\Models\Organization4;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\DB;

class MessageManageController extends Controller
{
    public function index()
    {
        $category_list = Category::all();

        // $message_list = $user->message;
        $message_list = Message::all();

        return view('admin.message.manage.index', [
            'category_list' => $category_list,
            'message_list' => $message_list

        ]);
    }

    public function detail(Request $request, $message_id)
    {
        $message = Message::find($message_id);

        // メッセージの該当ショップを取得
        $target_org4 = $message->organization4()->select('id')->get()->makeHidden('pivot')->toArray();
        $target_shop = Shop::whereIn('organization4_id', $target_org4)->get();


        return view('admin.message.manage.detail', [
            "message" => $message,
            "target_shop" => $target_shop
        ]);
    }
}