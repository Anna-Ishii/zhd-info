<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TopController extends Controller
{
    public function __init()
    {

        view()->share('page_name', 'ホーム');
    }

    public function index()
    {
        $user = session('member');

        $start_date_time = Carbon::now()->subDays(7)->startOfDay();
        // 新着件数
        // 過去1週間にの件数
        $recent_messages = $user->message()
                                ->whereBetween('start_datetime', [$start_date_time, now('Asia/Tokyo')])
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->where('editing_flg', false)
                                ->orderBy('start_datetime', 'desc')
                                ->get();

        $recent_message_start_datetime = $user->message()
                                ->where('start_datetime', '<=', now('Asia/Tokyo'))
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->where('editing_flg', false)
                                ->orderBy('start_datetime', 'desc')
                                ->limit(1)
                                ->get();

        $recent_manuals = $user->manual()
                                ->whereBetween('start_datetime', [$start_date_time, now('Asia/Tokyo')])
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->where('editing_flg', false)
                                ->orderBy('start_datetime', 'desc')
                                ->get();

        $recent_manual_start_datetime = $user->manual()
                                ->where('start_datetime', '<=', now('Asia/Tokyo'))
                                ->where(function ($query) {
                                    $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('end_datetime');
                                })
                                ->where('editing_flg', false)
                                ->orderBy('start_datetime', 'desc')
                                ->limit(1)
                                ->get();
        view()->share('page_name', 'ホーム');
        return view('top', [
            'recent_messages' => $recent_messages,
            'recent_message_start_datetime' => $recent_message_start_datetime,
            'recent_manuals' => $recent_manuals,
            'recent_manual_start_datetime' => $recent_manual_start_datetime,
        ]);
    }

    public function search(Request $request)
    {
        $_request = $request; 
        $type = $request['type'];
        $param = [
            'keyword' => $request['keyword'],
            'search_period' => $request['search_period']
        ];

        if($type == 1) {
            return redirect()->route('message.index', $param);
        } elseif($type == 2) {
            return redirect()->route('manual.index', $param);
        }
    }
    
}
