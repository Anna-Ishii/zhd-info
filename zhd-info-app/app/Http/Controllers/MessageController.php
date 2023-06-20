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

        if (isset($category_id)) {
            $messages = Message::where('category_id', '=', $category_id)
                                    ->orderBy('created_at', 'desc');
        } else {
            $messages = Message::orderBy('created_at', 'desc');
        }

        $categories = Category::get();

        return view('message.index', [
            'messages' => $messages->paginate(5)
                                    ->appends(request()->query()),
            'categories' => $categories,
        ]);
    }
    function detail(Request $request, $message_id)
    {
        $message = Message::find($message_id);

        return view('message.detail', [
            'message' => $message
        ]);
    }
}
