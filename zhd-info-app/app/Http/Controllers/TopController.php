<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Roll;

class TopController extends Controller
{
    public function index()
    {
        $user = session('member');
        // 今日掲載された業連
        $message_now = $user->message()
                                ->whereDate('start_datetime', now('Asia/Tokyo'))
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('created_at', 'desc')
                                ->get();
        $roll = Roll::find(4);// 店長ロール
        // スタッフ用の業連
        $message_crew = $roll->message()
                                ->where('start_datetime', '<=', now('Asia/Tokyo'))
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('created_at', 'desc')
                                ->get();
        // 掲載中の業連
        $message_posting = $user->message()
                                ->where('start_datetime', '<=', now('Asia/Tokyo'))
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
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
