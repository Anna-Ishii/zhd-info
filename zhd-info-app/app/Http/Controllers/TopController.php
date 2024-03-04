<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopController extends Controller
{
    public function index(Request $request)
    {
        $user = session('member');

        $request->session()->forget('check_crew');
        $request->session()->forget('reading_crews');

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
        $organization1_id =  $user->shop->organization1->id;
        $keywords = DB::table(
                        DB::raw(
                            "(SELECT keyword, shop_id FROM message_search_logs 
                                UNION ALL 
                                SELECT keyword, shop_id FROM manual_search_logs
                        ) as keyword_tables")
                    )->select([
                            'keyword',
                            DB::raw('COUNT(*) as count'),
                    ])->leftJoin('shops as s', 's.id', 'keyword_tables.shop_id')
                      ->Join('organization1 as o1', function ($join) use ($organization1_id) {
                            $join->on('o1.id', '=', 's.organization1_id')
                                ->where('o1.id', '=', $organization1_id);
                    })
                    ->groupBy('keyword')
                    ->orderBy('count', 'desc')
                    ->limit(3)
                    ->get();
                        
        return view('top', [
            'recent_messages' => $recent_messages,
            'recent_message_start_datetime' => $recent_message_start_datetime,
            'recent_manuals' => $recent_manuals,
            'recent_manual_start_datetime' => $recent_manual_start_datetime,
            'keywords' => $keywords
        ]);
    }

    public function search(Request $request)
    {
        $user = session('member');
        $type = $request['type'];
        $param = [
            'keyword' => $request['keyword'],
            'search_period' => $request['search_period']
        ];

        if($type == 1) {
            if($request->filled('keyword')){
                DB::table('message_search_logs')->insert([
                    'keyword' => $request['keyword'],
                    'shop_id' => $user->shop_id,
                    'searched_datetime' => new Carbon('now')
                ]);
            }
            return redirect()->route('message.index', $param);
        } elseif($type == 2) {
            if ($request->filled('keyword')) {
                DB::table('manual_search_logs')->insert([
                    'keyword' => $request['keyword'],
                    'shop_id' => $user->shop_id,
                    'searched_datetime' => new Carbon('now')
                ]);
            }

            return redirect()->route('manual.index', array_merge(
                $param
            ));
        }
    }
    
}
