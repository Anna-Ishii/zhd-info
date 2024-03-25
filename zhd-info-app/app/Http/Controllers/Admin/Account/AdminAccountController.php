<?php

namespace App\Http\Controllers\Admin\Account;

use App\Enums\AdminAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Account\AdminAccountStoreRequest;
use App\Http\Requests\Admin\Account\AdminAccountUpdateRequest;
use App\Models\Admin;
use App\Models\AdminPage;
use App\Models\Organization1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAccountController extends Controller 
{
    public function index()
    {
        $admin_list = Admin::withTrashed()->paginate(50);
        $organization1_list = Organization1::orderby('name')->get();
        $page_list = AdminPage::get();
        return view('admin.account.admin.index',[
            'admin_list' => $admin_list,
            'organization1_list' => $organization1_list,
            'page_list' => $page_list,
        ]);
    }

    public function new()
    {
        $organization1_list = Organization1::orderby('name')->get();
        $adminpage_list = AdminPage::get();
        return view('admin.account.admin.new', [
            'organization1_list' => $organization1_list,
            'ability_list' => AdminAbility::cases(),
            'adminpage_list' => $adminpage_list
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
        $admin = Admin::withTrashed()
            ->where('id', $admin_id)
            ->first();
        $organization1_list = Organization1::orderby('name')->get();
        $adminpage_list = AdminPage::get();
        return view('admin.account.admin.edit', [
            'organization1_list' => $organization1_list,
            'ability_list' => AdminAbility::cases(),
            'adminpage_list' => $adminpage_list,
            'edit_admin' => $admin
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

}