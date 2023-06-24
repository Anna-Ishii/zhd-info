<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    function index(Request $request)
    {
        $category_id = $request->input('category');
        // 掲示中のデータをとってくる
        $user = session("member");
        $messages = $user->message()
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->where('start_datetime', '<', now('Asia/Tokyo'))
            ->where(function ($query) {
                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                    ->orWhereNull('end_datetime');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->appends(request()->query());

        $categories = Category::get();

        return view('message.index', [
            'messages' => $messages,
            'categories' => $categories,
        ]);
    }
    function detail($message_id)
    {
        $message = Message::find($message_id);

        return view('message.detail', [
            'message' => $message
        ]);
    }
}
