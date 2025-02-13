<?php

namespace App\Http\Controllers;

use App\Enums\SearchPeriod;
use App\Models\Manual;
use App\Models\ManualCategoryLevel1;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualController extends Controller
{
    function index(Request $request)
    {
        session()->put('current_url', $request->fullUrl());
        $keyword = $request->input('keyword');
        $search_period = SearchPeriod::tryFrom($request->input('search_period', SearchPeriod::All->value));
        $category_level2 = $request->input('category_level2');

        $user = session("member");
        // 掲示中のデータをとってくる
        $manuals = $user->manual()
            ->with('content', 'tag', 'category_level2')
            ->publishingManual()
            ->when(isset($category_level2), function ($query) use ($category_level2) {
                $query->whereIn('category_level2_id', $category_level2);
            })
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

        $category_level1s = ManualCategoryLevel1::query()
            ->with('level2s')
            ->get();

        $organization1_id =  $user->shop->organization1->id;
        $keywords = DB::table("manual_search_logs as m_s_l")
            ->select([
                'keyword',
                DB::raw('COUNT(*) as count'),
            ])
            ->leftJoin('shops as s', 's.id', 'm_s_l.shop_id')
            ->Join('organization1 as o1', function ($join) use ($organization1_id) {
                $join->on('o1.id', '=', 's.organization1_id')
                    ->where('o1.id', '=', $organization1_id);
            })
            ->groupBy('keyword', 'o1.id')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        return view('manual.index', [
            'manuals' => $manuals,
            'category_level1s' => $category_level1s,
            'keywords' => $keywords
        ]);
    }

    function detail($manual_id)
    {
        $member = session('member');
        $manual = Manual::find($manual_id);

        // 既読をつける
        $member->manual()->updateExistingPivot($manual->id, [
            'read_flg' => true,
            'readed_datetime' => Carbon::now(),
        ]);

        $contents = $manual->content;
        return view('manual.detail', [
            'manual' => $manual,
            'contents' => $contents,
        ]);
    }

    function watched(Request $request)
    {
        try {
            $user = session('member');
            $manual_id = $request->manual_id;
            $manual = Manual::find($manual_id);
            $manual->user()->updateExistingPivot($user->id, ['read_flg' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'エラーメッセージ'], 500);
        }

        return response()->json(['message' => '閲覧しました']);
    }

    function search(Request $request)
    {
        $user = session('member');
        $param = [
            'keyword' => $request['keyword'],
            'search_period' => $request['search_period'],
            'category_level2' => $request['category_level2']
        ];

        if ($request->filled('keyword')) {
            DB::table('manual_search_logs')->insert([
                'keyword' => $request['keyword'],
                'shop_id' => $user->shop_id,
                'searched_datetime' => new Carbon('now')
            ]);
        }

        return redirect()->route('manual.index', $param);
    }
}
