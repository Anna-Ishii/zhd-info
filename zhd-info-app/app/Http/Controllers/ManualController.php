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

        // NEW判定用の基準日（トップ画面の新着仕様に合わせる場合は過去1週間）
        $newSince = Carbon::now()->subDays(7)->startOfDay();

        // 全件取得
        $allManualsCollection = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->get();

        // NEW/改訂 + OM/動画
        $allManuals = $this->enrichManuals($allManualsCollection, $newSince);

        // カテゴリ関連
        $categories = ManualCategoryLevel1::with('level2s')->get();
        $firstLevel1 = $categories->first();
        $firstLevel2Id = $firstLevel1?->level2s->first()?->id;

        // カテゴリー初期表示用マニュアル（絞り込みあり）
        $categoryManualsCollection = (clone $baseQuery)
            ->when($firstLevel2Id, fn($q) => $q->where('category_level2_id', $firstLevel2Id))
            ->orderBy('created_at', 'desc')
            ->get();

        // NEW/改訂 + OM/動画
        $categoryManuals = $this->enrichManuals($categoryManualsCollection, $newSince);

        return view('manual.index', [
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
        $newSince = Carbon::now()->subDays(7)->startOfDay();

        // 公開中でユーザが閲覧できるマニュアル
        $baseManuals = $user->manual()
            ->with('content')
            ->publishingManual()
            ->orderBy('created_at', 'desc')
            ->get();

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

        // NEW/改訂 + OM/動画
        $manuals = $this->enrichManuals($manuals, $newSince);

        // Blade に返却
        return view('manual._list', ['allManuals' => $manuals])->render();
    }

    // Ajax: 子カテゴリー切替
    public function filterByCategory(Request $request)
    {
        $user = session('member');
        $level2Id = $request->input('level2_id');
        $newSince = Carbon::now()->subDays(7)->startOfDay();

        // 子カテゴリー絞り込み
        $manuals = $user->manual()
            ->with('content')
            ->publishingManual()
            ->when($level2Id, fn($q) => $q->where('category_level2_id', $level2Id))
            ->orderBy('created_at', 'desc')
            ->get();

        // NEW/改訂 + OM/動画
        $manuals = $this->enrichManuals($manuals, $newSince);

        return view('manual._recent', ['categoryManuals' => $manuals])->render();
    }

    // Ajax: 大カテゴリー → 子カテゴリー取得
    public function getLevel2ByLevel1(Request $request)
    {
        $level1Id = $request->input('level1_id');
        $level1 = ManualCategoryLevel1::with('level2s')->find($level1Id);

        return response()->json($level1?->level2s ?? []);
    }

    /**
     * NEW/改訂フラグ付与
     * @param \Illuminate\Support\Collection $manuals
     * @param Carbon|null $newSince NEW判定用の基準日（任意）
     * @return \Illuminate\Support\Collection
     */
    private function addFlags($manuals, $newSince = null)
    {
        // マニュアル全体の start_datetime の最新日付を取得（NEW判定用）
        $latestStartDatetime = $manuals->max('start_datetime');

        return $manuals->map(function ($manual) use ($latestStartDatetime, $newSince) {
            // このマニュアルの start_datetime が最新かどうか
            $isLatest = $manual->start_datetime == $latestStartDatetime;

            // NEW判定用の基準日を超えているか（$newSince が設定されている場合）
            $isRecent = $newSince ? $manual->start_datetime >= $newSince : true;

            // NEWフラグ付与：最新かつ基準日以降であれば true
            $manual->is_new = $isLatest && $isRecent;

            // 改訂フラグ付与：updated_at があり、作成日より更新日が後なら true
            $manual->is_revised = $manual->updated_at && $manual->updated_at > $manual->created_at;

            return $manual;
        });
    }

    /**
     * OM/動画タグ付与
     * content 配列がある場合は配列、ない場合は manual 自身の content_name で判定
     * @param \Illuminate\Support\Collection $manuals
     * @return \Illuminate\Support\Collection
     */
    private function addTags($manuals)
    {
        $videoExts = ['.mp4', '.mov', '.avi', '.mkv'];

        return $manuals->map(function ($manual) use ($videoExts) {

            // 判定用の拡張子リストを取得
            if ($manual->content->isNotEmpty()) {
                $extensions = $manual->content
                    ->pluck('content_name')
                    ->map(fn($n) => strtolower(trim($n)));
            } else {
                $extensions = collect([strtolower(trim($manual->content_name))]);
            }

            // OM判定(PDF)
            $manual->has_om = $extensions->contains(fn($n) => str_ends_with($n, '.pdf'));

            // 動画判定
            $manual->has_video = $extensions->contains(
                fn($n) =>
                collect($videoExts)->contains(fn($ext) => str_ends_with($n, $ext))
            );

            return $manual;
        });
    }

    /**
     * 共通: NEW/改訂 + OM/動画 判定セット
     */
    private function enrichManuals($manuals, $newSince = null)
    {
        return $this->addTags($this->addFlags($manuals, $newSince));
    }
}
