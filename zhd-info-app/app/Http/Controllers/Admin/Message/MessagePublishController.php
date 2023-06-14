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

            $file = $request->file('file');
            $directory = 'uploads';
            // ファイル名を生成します（一意の名前を使用する場合は、例えばユーザーIDやタイムスタンプを組み合わせることもできます）
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            // ファイルを指定したディレクトリに保存します
            $path = $file->storeAs($directory, $filename, 'public');
            
            $category = $request->category;
            $emergency_flg = $request->is_emergency == "on" ? true : false;

            $start_datetime = $request->start_datetime; 
            if ($request->start_datetime == 'on') $start_datetime = null;
            $end_datetime = $request->end_datetime;
            if ($request->end_datetime == 'on') $end_datetime = null;
            // $target_roll = $request->target_roll;
            // $target_organization1 = $request->target_organization1;
            // $target_block = $request->target_block;

            //TODO
            // target_roll
            // target_organizationがが含まれているかチェック
            // ロールと対象ブロックは後で。
            $message = new Message();
            $message->title = $title;
            $message->content_url = $path;
            $message->category_id = $category;
            $message->create_user = $user->employee_code;
            $message->status = 0;
            $message->emergency_flg = $emergency_flg;
            $message->start_datetime = !empty($start_datetime) ? Carbon::parse($start_datetime) : null;
            $message->end_datetime = !empty($end_datetime) ? Carbon::parse($end_datetime) : null;
            $message->save();
            redirect('message.publish');
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
