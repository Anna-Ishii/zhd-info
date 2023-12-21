<?php

namespace App\Http\Controllers;

use App\Enums\SearchPeriod;
use App\Models\MessageCategory;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        return view('message.index', [
            'messages' => $messages,
            'categories' => $categories,
        ]);
    }
}
