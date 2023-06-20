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
        $manual = Manual::find($manual_id);

        return view('manual.detail', [
            'manual' => $manual,
            'contents' => $manual->content,
        ]);
    }
}
