<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class TopController extends Controller
{
    public function index()
    {
        $user = session('member');

        $today = Carbon::now();
        $d_s = $today->copy()->startOfWeek(Carbon::MONDAY); //週初め
        $d_e = $today->copy()->endOfWeek(Carbon::SUNDAY); // 週終わり
        // 今日掲載された業連
        $message_now = $user->message()
                                ->whereBetween('start_datetime', [$d_s, $d_e])
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('start_datetime', 'desc')
                                ->get();
        // 今日掲載されたマニュアル
        $manual_now = $user->manual()
            ->whereBetween('start_datetime', [$d_s, $d_e])
            ->where(function ($query) {
                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                ->orWhereNull('end_datetime');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $message_unread = $user->unreadMessages()
                            ->where(function ($query) {
                                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                ->orWhereNull('end_datetime');
                            })
                            ->orderBy('start_datetime', 'desc')
                            ->get();

        $manual_unread = $user->unreadManuals()
            ->where(function ($query) {
                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                ->orWhereNull('end_datetime');
            })
            ->orderBy('start_datetime', 'desc')
            ->get();

        return view('top', [
            'message_now' => $message_now,
            'manual_now' => $manual_now,
            'message_unread' => $message_unread,
            'manual_unread' => $manual_unread
        ]);
    }
    
}
