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
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $messages = Message::orderBy('created_at', 'desc')
                ->get();
        }

        $categories = Category::get();
        return view('message.index', [
            'messages' => $messages,
            'categories' => $categories,
            'category_id' => $category_id
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
