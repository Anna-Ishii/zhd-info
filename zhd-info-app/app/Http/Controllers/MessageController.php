<?php

namespace App\Http\Controllers;

use App\Enums\SearchPeriod;
use App\Models\MessageCategory;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $search_period = SearchPeriod::tryFrom($request->input('search_period', SearchPeriod::All->value));

        $user = session("member");

        $sub = DB::table('messages')
                    ->select([
                        DB::raw('messages.id as message_id'),
                        DB::raw('count(c.id) as crew_count'),
                        DB::raw('count(c_m_l.crew_id) as readed_crew_count'),
                        DB::raw('round((count(c_m_l.crew_id) / count(c.id)) * 100, 0) as view_rate')
                    ])
                    ->leftjoin('message_user as m_u', 'messages.id', '=', 'm_u.message_id')
                    ->leftjoin('crews as c', 'm_u.user_id', '=', 'c.user_id')
                    ->leftjoin('crew_message_logs as c_m_l', function($join){
                        $join->on('c_m_l.crew_id', '=', 'c.id')
                            ->where('c_m_l.message_id', '=', DB::raw('messages.id'));
                    })
                    ->where('m_u.user_id', '=', $user->id)
                    ->groupBy('messages.id');

        // 掲示中のデータをとってくる
        $messages = $user->message()
            ->with('category', 'tag')
            ->select([
                'sub.crew_count as crew_count',
                'sub.readed_crew_count as readed_crew_count',
                'sub.view_rate as view_rate'
                ])
            ->publishingMessage()
            ->JoinSub($sub, 'sub', 'messages.id', 'sub.message_id')
            ->when(isset($keyword), function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereLike('title', $keyword)
                        ->orWhereHas('tag', function ($query) use ($keyword) {
                            $query->where('name', $keyword);
                        });
                });
            })
            ->when(isset($search_period), function ($query) use ($search_period) {
                switch ($search_period) {
                    case SearchPeriod::All:
                        break;
                    case SearchPeriod::Past_week:
                        $query->where('start_datetime', '>=', now('Asia/Tokyo')->subWeek()->isoFormat('YYYY/MM/DD'));
                        break;
                    case SearchPeriod::Past_month:
                        $query->where('start_datetime', '>=', now('Asia/Tokyo')->subMonth()->isoFormat('YYYY/MM/DD'));
                        break;
                    default:
                        break;
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(request()->query());

        $categories = MessageCategory::get();

        $keywords = DB::table("message_search_logs")
                    ->select('keyword', DB::raw('COUNT(*) as count'))
                    ->groupBy('keyword')
                    ->orderBy('count', 'desc')
                        ->limit(3)
                        ->get();

        return view('message.index', [
            'messages' => $messages,
            'categories' => $categories,
            'keywords' => $keywords
        ]);
    }

    function detail($message_id)
    {
        $user = session('member');
        $crews = session('crews');
        $message = Message::findOrFail($message_id);

        $user->message()->wherePivot('read_flg', false)->updateExistingPivot($message->id, [
            'read_flg' => true,
            'readed_datetime' => Carbon::now(),
        ]);

        $message->putCrewRead($crews);
        return redirect()->to($message->content_url);
    }

    function search(Request $request)
    {
        $user = session('member');
        $param = [
            'keyword' => $request['keyword'],
            'search_period' => $request['search_period']
        ];

        if ($request->filled('keyword')) {
            DB::table('message_search_logs')->insert([
                'keyword' => $request['keyword'],
                'shop_id' => $user->shop_id,
                'searched_datetime' => new Carbon('now')
            ]);
        }

        return redirect()->route('message.index', $param);
    }

    public function putCrews(Request $request) 
    {
        $crew = $request->input('crew');
        $crews = [];

        $crews = session('crews',[]);
        if(!empty($crews) && in_array((int)$crew, $crews, true)){
            $crews = array_diff($crews, array((int)$crew));
        } else {
            $crews[] += (int)$crew;
        }
        $request->session()->put('crews', $crews);

        return response()->json(['message' => '完了']);

    }
}
