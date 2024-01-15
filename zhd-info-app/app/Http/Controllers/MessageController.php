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
        // 掲示中のデータをとってくる
        $messages = $user->message()
            ->with('category', 'tag')
            ->publishingMessage()
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
        $message = Message::findOrFail($message_id);

        $user->message()->wherePivot('read_flg', false)->updateExistingPivot($message->id, [
            'read_flg' => true,
            'readed_datetime' => Carbon::now(),
        ]);

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
}
