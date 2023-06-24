<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Roll;
use Carbon\Carbon;

class TopController extends Controller
{
    public function index()
    {
        $member = session('member');
        $now = Carbon::now();
        // 今日掲載された業連
        $message_now = Message::query()
                                ->whereDate('start_datetime', $now->format('Y/m/d'))
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now())
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('created_at', 'desc')
                                ->get();
        $roll = $member->roll;
        // スタッフ用の業連
        $message_crew = $roll->message()
                                ->where('start_datetime', '<', now())
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now())
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('created_at', 'desc')
                                ->get();
        // 掲載中の業連
        $message_posting = Message::query()
                                ->where('start_datetime', '<', now())
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now())
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('created_at', 'desc')
                                ->get();
        return view('top', [
            'message_now' => $message_now,
            'message_crew' => $message_crew,
            'message_posting' => $message_posting
        ]);
    }
    
}
