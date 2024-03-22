<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminPage;
use App\Models\Organization1;

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
}