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

        $user = session('member');

        // ベースクエリ
        $baseQuery = $user->manual()
            ->with('content', 'category_level2')
            ->publishingManual();

        // テーブル全体の最新 start_datetime を取得（NEW判定用）
        $latestStartDatetime = (clone $baseQuery)->max('start_datetime');

        // 全件取得（NEW/改訂フラグ付き）
        $allManuals = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($manual) use ($latestStartDatetime) {
                $manual->is_new = $manual->start_datetime == $latestStartDatetime;
                $manual->is_revised = $manual->updated_at > $manual->created_at;
                return $manual;
            });

        // カテゴリ関連
        $categories = ManualCategoryLevel1::with('level2s')->get();
        $firstLevel1 = $categories->first();
        $firstLevel2Id = $firstLevel1?->level2s->first()?->id;

        // カテゴリー初期表示用マニュアル（絞り込みあり）
        $categoryManuals = (clone $baseQuery)
            ->when($firstLevel2Id, fn($q) => $q->where('category_level2_id', $firstLevel2Id))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($manual) use ($latestStartDatetime) {
                // NEW/改訂フラグはテーブル全体の最新と比較
                $manual->is_new = $manual->start_datetime == $latestStartDatetime;
                $manual->is_revised = $manual->updated_at > $manual->created_at;
                return $manual;
            });

        return view('manual.index-new', [
            'allManuals' => $allManuals,
            'categoryManuals' => $categoryManuals,
            'categories' => $categories,
            'selectedLevel1Id' => $firstLevel1?->id,
            'selectedLevel2Id' => $firstLevel2Id,
            'selectedType' => 'all',
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

    // Ajax: タブ切替・検索
    public function filterByType(Request $request)
    {
        $user = session('member');
        $type = $request->input('type', 'all');
        $keyword = $request->input('keyword');

        // 公開中でユーザが閲覧できるマニュアル
        $baseManuals = $user->manual()
            ->with('content')
            ->publishingManual()
            ->orderBy('created_at', 'desc')
            ->get();

        // テーブル全体の最新 start_datetime を取得（NEW判定用）
        $latestStartDatetime = $user->manual()
            ->publishingManual()
            ->max('start_datetime');

        // キーワード検索（タイトルのみ）※まず全マニュアルから絞る
        $manuals = $baseManuals;
        if ($keyword) {
            $manuals = $manuals->filter(fn($m) => str_contains($m->title, $keyword));
        }

        // タイプでフィルター（om / video の場合のみ）
        if ($type === 'om') {
            $manuals = $manuals->filter(fn($m) => $m->content->contains(
                fn($c) => str_ends_with(strtolower($c->content_name), '.pdf')
            ));
        } elseif ($type === 'video') {
            $videoExts = ['.mp4', '.mov', '.avi', '.mkv'];
            $manuals = $manuals->filter(fn($m) => $m->content->contains(
                fn($c) => collect($videoExts)->contains(fn($ext) => str_ends_with(strtolower($c->content_name), $ext))
            ));
        }

        // NEW / 改訂フラグを追加（テーブル全体の最新と比較）
        $manuals = $manuals->map(function ($manual) use ($latestStartDatetime) {
            $manual->is_new = $manual->start_datetime == $latestStartDatetime;
            $manual->is_revised = $manual->updated_at > $manual->created_at;
            return $manual;
        });

        // Blade に返却
        return view('manual._list', ['allManuals' => $manuals])->render();
    }

    // Ajax: 子カテゴリー切替
    public function filterByCategory(Request $request)
    {
        $user = session('member');
        $level2Id = $request->input('level2_id');

        // ベースクエリ（全体取得用）
        $baseQueryAll = $user->manual()
            ->publishingManual();

        // テーブル全体の最新 start_datetime を取得
        $latestStartDatetime = $baseQueryAll->max('start_datetime');

        // 中カテゴリー絞り込み
        $baseQuery = $user->manual()
            ->with('content')
            ->publishingManual()
            ->when($level2Id, fn($q) => $q->where('category_level2_id', $level2Id))
            ->orderBy('created_at', 'desc');

        // マニュアル取得＆NEW/改訂フラグ設定
        $manuals = $baseQuery->get()->map(function ($manual) use ($latestStartDatetime) {
            $manual->is_new = $manual->start_datetime == $latestStartDatetime; // テーブル全体の最新と比較
            $manual->is_revised = $manual->updated_at > $manual->created_at;
            return $manual;
        });

        // Blade に返す
        return view('manual._recent', ['categoryManuals' => $manuals])->render();
    }

    // Ajax: 大カテゴリー → 子カテゴリー取得
    public function getLevel2ByLevel1(Request $request)
    {
        $level1Id = $request->input('level1_id');
        $level1 = ManualCategoryLevel1::with('level2s')->find($level1Id);

        return response()->json($level1?->level2s ?? []);
    }
}
