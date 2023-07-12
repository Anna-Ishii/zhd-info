<?php

namespace App\Http\Controllers;

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
        // 今日掲載されたマニュアル
        $manual_now = $user->manual()
            ->whereDate('start_datetime', now('Asia/Tokyo'))
            ->where(function ($query) {
                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                ->orWhereNull('end_datetime');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('top', [
            'message_now' => $message_now,
            'manual_now' => $manual_now,
        ]);
    }
    
}
