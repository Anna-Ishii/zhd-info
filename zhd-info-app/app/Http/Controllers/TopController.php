<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class TopController extends Controller
{
    public function index()
    {
        $user = session('member');

        $today = Carbon::now();
        $thisweek_start = $today->copy()->startOfWeek(Carbon::MONDAY); //週初め
        $thisweek_end = $today->copy()->endOfWeek(Carbon::SUNDAY); // 週終わり
        $lastweek_start = $thisweek_start->copy()->subWeek();
        $lastweek_end = $thisweek_end->copy()->subWeek();
        // 今週初め ~ 今日　掲載された業連
        $message_thisweek = $user->message()
                                ->whereBetween('start_datetime', [$thisweek_start, now('Asia/Tokyo')])
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->where('editing_flg', false)
                                ->orderBy('start_datetime', 'desc')
                                ->get();
        // 今週初め ~ 今日　掲載されたマニュアル
        $manual_thisweek = $user->manual()
                                ->whereBetween('start_datetime', [$thisweek_start, now('Asia/Tokyo')])
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->where('editing_flg', false)
                                ->orderBy('created_at', 'desc')
                                ->get();
        // 先週初め ~ 先週終わり 掲載された業連
        $message_lastweek = $user->message()
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                        ->orWhereNull('end_datetime');
                                })
                                ->whereBetween('start_datetime', [$lastweek_start, $lastweek_end])
                                ->where('editing_flg', false)
                                ->orderBy('start_datetime', 'desc')
                                ->get();
        // 先週初め ~ 先週終わり 掲載されたマニュアル
        $manual_lastweek = $user->manual()
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->whereBetween('start_datetime', [$lastweek_start, $lastweek_end])
                                ->where('editing_flg', false)
                                ->orderBy('start_datetime', 'desc')
                                ->get();

        // 未読の業連
        $message_unread = $user->unreadMessages()
                                ->publishingMessage()
                                ->orderBy('start_datetime', 'desc')
                                ->get();
        
        // 未読のマニュアル
        $manual_unread = $user->unreadManuals()
                                ->publishingManual()
                                ->orderBy('start_datetime', 'desc')
                                ->get();

        return view('top', [
            'message_thisweek' => $message_thisweek,
            'manual_thisweek' => $manual_thisweek,
            'message_lastweek' => $message_lastweek,
            'manual_lastweek' => $manual_lastweek,
            'message_unread' => $message_unread,
            'manual_unread' => $manual_unread,
            'thisweek_start' => $thisweek_start,
            'thisweek_end' => $thisweek_end,
            'lastweek_start' => $lastweek_start,
            'lastweek_end' => $lastweek_end,
        ]);
    }
    
}
