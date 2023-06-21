<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Manual;
use App\Models\Manualcategory;
use Illuminate\Http\Request;

class ManualController extends Controller
{
    function index(Request $request)
    {
        $category_id = $request->input('category');

        if(isset($category_id)){
            $manuals = Manual::where('category_id', '=', $category_id)
                                ->orderBy('created_at', 'desc');     
        }else {
            $manuals = Manual::orderBy('created_at', 'desc');
        }

        $categories = Manualcategory::get();

        return view('manual.index', [
            'manuals' => $manuals->paginate(5)
                                ->appends(request()->query()),
            'categories' => $categories,
        ]);
    }

    function detail(Request $request, $manual_id)
    {
        $user = session('member');
        $manual = Manual::find($manual_id);
        $read_flg = $manual->user()->where('user_id', '=', $user->id)->pluck('manual_user.read_flg');
        $read_flg_count = $manual->user()->where('manual_user.read_flg', '=', true)->count();

        return view('manual.detail', [
            'manual' => $manual,
            'contents' => $manual->content,
            'read_flg' => $read_flg,
            'read_flg_count' => $read_flg_count
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
