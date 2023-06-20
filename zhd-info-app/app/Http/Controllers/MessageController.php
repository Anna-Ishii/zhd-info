<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    function index()
    {
        $messages = Message::orderBy('created_at', 'desc')->get();

        return view('message.index', [
            'messages' => $messages
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
