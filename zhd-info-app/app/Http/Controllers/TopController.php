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
        // 今日掲載された業連
        $message_thisweek = $user->message()
                                ->whereBetween('start_datetime', [$thisweek_start, $thisweek_end])
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('start_datetime', 'desc')
                                ->get();
        // 今日掲載されたマニュアル
        $manual_thisweek = $user->manual()
                                ->whereBetween('start_datetime', [$thisweek_start, $thisweek_end])
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->orderBy('created_at', 'desc')
                                ->get();

        $message_lastweek = $user->message()
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                        ->orWhereNull('end_datetime');
                                })
                                ->whereBetween('start_datetime', [$lastweek_start, $lastweek_end])
                                ->orderBy('start_datetime', 'desc')
                                ->get();
                                
        $manual_lastweek = $user->manual()
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->whereBetween('start_datetime', [$lastweek_start, $lastweek_end])
                                ->orderBy('start_datetime', 'desc')
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
