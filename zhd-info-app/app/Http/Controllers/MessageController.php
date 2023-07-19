<?php

namespace App\Http\Controllers;

use App\Models\MessageCategory;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    function index(Request $request)
    {
        $category_id = $request->input('category');
        $emergency = $request->input('emergency');

        $search_status_name = '全て';

        $user = session("member");
        // 掲示中のデータをとってくる
        $messages = $user->message()
            ->when(isset($emergency), function ($query) {
                $query->where('emergency_flg', true);
            })
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->where('start_datetime', '<', now('Asia/Tokyo'))
            ->where(function ($query) {
                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                    ->orWhereNull('end_datetime');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(request()->query());

        $categories = MessageCategory::get();

        if (isset($emergency)){
            $search_status_name = '重要';
        }elseif(isset($category_id)) {
            $search_status_name = $categories[$category_id - 1]->name;
        }

        return view('message.index', [
            'messages' => $messages,
            'categories' => $categories,
            'search_status_name' => $search_status_name,
        ]);
    }

    function detail($message_id)
    {
        $member = session('member');
        $message = Message::find($message_id);

        // 既読をつける
        $member->message()->updateExistingPivot($message_id, [
            'read_flg' => true, 
        ]);

        return view('message.detail', [
            'message' => $message
        ]);
    }
}
