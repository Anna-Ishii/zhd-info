<?php

namespace App\Http\Controllers\Admin\Message;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Message;
use App\Models\Organization4;
use App\Models\Roll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\DB;

class MessageManageController extends Controller
{
    public function index()
    {
        return view('admin.message.manage.index');
    }

    public function detail(Request $request, $message_id)
    {
        return view('admin.message.manage.detail');
    }
}