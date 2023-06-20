<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Roll;
use Carbon\Carbon;

class TopController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $message = new Message();
        $message_now = $message->whereDate('start_datetime', $now->format('Y/m/d'))->orderBy('created_at', 'desc')->get();
        $roll = Roll::find(2); //クルー
        $message_crew = $roll->message()->orderBy('created_at', 'desc')->get();; 
        $message_posting = $message->where('status', '=', '1')->orderBy('created_at', 'desc')->get();
        return view('top', [
            'message_now' => $message_now,
            'message_crew' => $message_crew,
            'message_posting' => $message_posting
        ]);
    }
    
}
