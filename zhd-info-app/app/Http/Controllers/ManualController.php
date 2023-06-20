<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use Illuminate\Http\Request;

class ManualController extends Controller
{
    function index()
    {
        $manuals = Manual::get();

        return view('manual.index', [
            'manuals' => $manuals
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
