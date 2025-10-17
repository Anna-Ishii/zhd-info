<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Organization1;
use App\Models\Roll;
use App\Models\AdminRecipient;
use App\Models\UsersRole;
use App\Models\SearchCondition;
use App\Exports\MailAccountExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class MailAdminAccountController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $organization1_list = $admin->organization1()->orderby('name')->get();
        $roll_list = Roll::all();

        // request
        $orgs = $request->input('org');
        $organization1_id = $request->input('organization1') ? base64_decode($request->input('organization1')) : $organization1_list[0]->id;
        $organization1 = Organization1::find($organization1_id);
        $roll = $request->input('roll');

        $organization_list = [];
        $organizations = [];

        // ユーザー情報を取得
        $users = AdminRecipient::query()
            ->select(
                'admin_recipients.id',
                'admin_recipients.employee_number',
                'admin_recipients.name',
                'admin_recipients.organization1_id',
                'admin_recipients.email',
                'admin_recipients.target',
            )
            ->leftJoin('organization1', 'admin_recipients.organization1_id', '=', 'organization1.id')
            ->when(isset($roll), function ($query) use ($roll) {
                $query->where('roll_id', '=', $roll);
            })
            ->when(isset($organization1_id), function ($query) use ($organization1_id) {
                $query->where('organization1_id', '=', $organization1_id);
            })
            ->orderBy('admin_recipients.id')
            ->paginate(5000)
            ->appends(request()->query());

        // 業連閲覧状況メール配信のチェックを〇に変換
        $users->getCollection()->transform(function ($user) {
            $user->status = $user->target ? '〇' : '';
            return $user;
        });

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

        return view('admin.account.adminmail.index', [
            'users' => $users,
            'roll_list' => $roll_list,
            'organization1_list' => $organization1_list,
            'organization_list' => $organization_list,
            'organizations' => $organizations,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
        ]);
    }

    // SESSIONに検索条件を保存
    public function saveSessionConditions(Request $request)
    {
        try {
            session(['mail_admin_account_url' => $request->input('params')]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 業連閲覧状況メール配信の設定を更新
    public function adminAccountUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            $adminAccountData = json_decode($request->input('adminAccountData'), true);

            // データをチャンクして処理
            $chunkSize = 500; // チャンクサイズを設定
            collect($adminAccountData)->chunk($chunkSize)->each(function ($chunk) {
                foreach ($chunk as $data) {
                    $adminAccount = AdminRecipient::where('id', $data['id'])->first();

                    if (!$adminAccount) {
                        continue;
                    }

                    $adminAccount->update([
                        'target' => $data['status'],
                        'updated_at' => now()
                    ]);
                }
            });

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => '更新が完了しました。'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => '更新中にエラーが発生しました。',
                'errors' => ['system' => [$e->getMessage()]]
            ], 500);
        }
    }
}
