<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Controller;
use App\Models\ImsSyncLog;

class ImsController extends Controller
{
    public function index() {
        $log = ImsSyncLog::orderBy('id', 'desc')->limit(30)->get();

        return view('admin.manage.ims', [
            'log' => $log
        ]);
    }
}
