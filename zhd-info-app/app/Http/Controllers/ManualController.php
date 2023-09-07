<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\ManualCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ManualController extends Controller
{
    function index(Request $request)
    {
        $category_id = $request->input('category');

        $user = session("member");
        // 掲示中のデータをとってくる
        $manuals = $user->manual()
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->publishingManual()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(request()->query());

        $categories = ManualCategory::get();

        return view('manual.index', [
            'manuals' => $manuals,
            'categories' => $categories,
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
        //
        
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
}
