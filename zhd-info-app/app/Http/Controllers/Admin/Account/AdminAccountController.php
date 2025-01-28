<?php

namespace App\Http\Controllers\Admin\Account;

use App\Enums\AdminAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Account\AdminAccountStoreRequest;
use App\Http\Requests\Admin\Account\AdminAccountUpdateRequest;
use App\Models\Admin;
use App\Models\Adminpage;
use App\Models\Organization1;
use App\Models\SearchCondition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAccountController extends Controller
{
    public function index()
    {
        $admin = session('admin');
        $admin_list = Admin::withTrashed()->paginate(50);
        $organization1_list = Organization1::orderby('name')->get();
        $page_list = Adminpage::orderby('name')->get();

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

        return view('admin.account.admin.index',[
            'admin_list' => $admin_list,
            'organization1_list' => $organization1_list,
            'page_list' => $page_list,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
        ]);
    }

    public function new()
    {
        $admin = session('admin');

        $organization1_list = Organization1::orderby('name')->get();
        $adminpage_list = Adminpage::orderby('name')->get();

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

        return view('admin.account.admin.new', [
            'organization1_list' => $organization1_list,
            'ability_list' => AdminAbility::cases(),
            'adminpage_list' => $adminpage_list,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
        ]);
    }

    public function store(AdminAccountStoreRequest $request)
    {
        $request->validated();
        try {
            DB::beginTransaction();
            $admin = new Admin;
            $admin->fill([
                'name' => $request->name,
                'password' => Hash::make($request->employee_code),
                'employee_code' => $request->employee_code,
                'ability' => $request->ability,
            ]);
            $admin->save();
            $admin->organization1()->attach($request->organization1);
            $admin->allowpage()->attach($request->page);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        return redirect()->route('admin.account.admin.index');
    }

    public function edit($admin_id)
    {
        $adminId = session('admin');

        $admin = Admin::withTrashed()
            ->where('id', $admin_id)
            ->first();
        $organization1_list = Organization1::orderby('name')->get();
        $adminpage_list = Adminpage::orderby('name')->get();

        // 検索条件を取得
        $message_saved_url = SearchCondition::where('admin_id', $adminId->id)
            ->where('page_name', 'message-publish')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();
        $manual_saved_url = SearchCondition::where('admin_id', $adminId->id)
            ->where('page_name', 'manual-publish')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();
        $analyse_personal_saved_url = SearchCondition::where('admin_id', $adminId->id)
            ->where('page_name', 'analyse-personal')
            ->where('deleted_at', null)
            ->select('page_name', 'url')
            ->first();

        return view('admin.account.admin.edit', [
            'organization1_list' => $organization1_list,
            'ability_list' => AdminAbility::cases(),
            'adminpage_list' => $adminpage_list,
            'edit_admin' => $admin,
            'message_saved_url' => $message_saved_url,
            'manual_saved_url' => $manual_saved_url,
            'analyse_personal_saved_url' => $analyse_personal_saved_url,
        ]);
    }

    public function update(AdminAccountUpdateRequest $request, $admin_id)
    {
        $request->validated();

        $is_valid = $request->is_valid;

        $admin = Admin::withTrashed()
            ->where('id', $admin_id)
            ->first();

        try {
            DB::beginTransaction();
            $admin->fill([
                'name' => $request->name,
                'employee_code' => $request->employee_code,
                'ability' => $request->ability,
            ]);
            $admin->save();
            $admin->organization1()->sync($request->organization1);
            $admin->allowpage()->sync($request->page);
            $is_valid ? $admin->restore() : $admin->delete();

            $exist_account_admin =  Admin::query()
                ->join('admin_page','admin.id', '=', 'admin_page.admin_id')
                ->join('adminpages', function($join) {
                    $join->on('admin_page.page_id', '=', 'adminpages.id')
                        ->where('adminpages.code', '=', 'account-admin');
                })
                ->where('ability', '!=', 0)
                ->exists();
            if(!$exist_account_admin) {
                throw new \Exception("本部アカウントを管理するアカウントは1つ以上存在する必要があります。");
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $th->getMessage());
        }

        return redirect()->route('admin.account.admin.index');
    }

}
