<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Controller;
use App\Models\ImsSyncLog;
use App\Models\SearchCondition;
use App\Jobs\importjob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImsController extends Controller
{
    public function dl($id)
    {
        $data = Storage::disk('local')->get('imscsv/' . $id . '.csv');
        if (!$data) {
            abort(404, '更新データはありません');
        }

        header('Content-Type: application/octet-stream');
        $file = "ims_" . date('Ymd_His') . ".csv";
        header('Content-Disposition: attachment; filename=' . $file);
        if (mb_detect_encoding($data) == 'UTF-8') {
            echo mb_convert_encoding($data, "SJIS-win", "UTF-8");
        } else {
            echo $data;
        }

        exit();
    }

    public function execute()
    {
        importjob::dispatch();
        return redirect('/admin/manage/ims')->with('message', 'バッチ処理を受け付けました。完了までお待ちください。');
    }

    public function index()
    {
        $admin = session('admin');

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

        $isJobRunning = DB::table('jobs')
            ->where('payload', 'like', '%importjob%')
            ->exists();

        $perPage = 30;
        $log = ImsSyncLog::orderBy('import_at', 'desc')->paginate($perPage);

        return view('admin.manage.ims', [
            'log' => $log,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
            'isJobRunning' => $isJobRunning,
        ]);
    }
}
