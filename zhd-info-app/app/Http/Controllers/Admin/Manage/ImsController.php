<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Controller;
use App\Models\ImsSyncLog;
use App\Models\SearchCondition;

class ImsController extends Controller
{
    public function index() {
        $admin = session('admin');
        $log = ImsSyncLog::orderBy('id', 'desc')->limit(30)->get();

        // 検索条件を取得
        $message_saved_url = SearchCondition::where('admin_id', $admin->id)
            ->where('page_name', 'message-publish')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();
        $manual_saved_url = SearchCondition::where('admin_id', $admin->id)
            ->where('page_name', 'manual-publish')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();
        $analyse_personal_saved_url = SearchCondition::where('admin_id', $admin->id)
            ->where('page_name', 'analyse-personal')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();


        return view('admin.manage.ims', [
            'log' => $log,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
        ]);
    }
}
