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
use Illuminate\Support\Facades\DB;

class MessagePublishController extends Controller
{
    public function index(Request $request)
    {
        
        // $user = $request->session()->get('user');
        $user = User::find(1234567890);
        
        $category_list = Category::all();
        
        // $message_list = $user->message;
        $message_list = Message::all();
        return view('admin.message.publish', [
            'category_list' => $category_list,
            'message_list' => $message_list

        ]);
    }

    public function new(Request $request)
    {
        $user = $request->session()->get('user');

        if ($request->isMethod('post')) {
            $title = $request->title;
            $content_url = $request->file;
            $category = $request->category;
            $emergency_flg = $request->is_emergency == "on" ? true : false;
            $start_datetime = $request->start_datetime;
            $end_datetime = $request->end_datetime;
            $target_roll = $request->target_roll;
            $target_organization1 = $request->target_organization1;
            $target_block = $request->target_block;

            //TODO
            // target_roll
            // target_organizationがが含まれているかチェック
            // 
            $message = new Message();
            $message->title = $title;
            $message->content_url = "https://www.adobe.com/jp/acrofamily/features/acro_nikkei/pdfs/fonts.pdf";
            $message->category_id = $category;
            $message->create_user = $user->employee_code;
            $message->status = 0;
            $message->emergency_flg = $emergency_flg;
            $message->start_datetime = Carbon::now()->format('Y-m-d H:i:s');
            $message->end_datetime = Carbon::now()->format('Y-m-d H:i:s');
            $message->save();

        }

        $category_list = Category::all();
        // 「一般」は使わない
        $target_roll_list = Roll::where('id','!=','1')->get();
        // 業態一覧を取得する
        $organization4_list = Organization4::all();
        
        return view('admin.message.publish.new', [
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'organization4_list' => $organization4_list
        ]);
    }

}
