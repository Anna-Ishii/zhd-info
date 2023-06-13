<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Message;
use App\Models\Roll;
use App\Models\User;
use Illuminate\Http\Request;


class MessagePublishController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->session()->get('user');
        $category_list = Category::all();
        

        return view('admin.message.publish', [
            'user' => $user,
            'category_list' => $category_list,

        ]);
    }

    public function new(Request $request)
    {
        if ($request->isMethod('post')) {
            $title = $request->title;
            $file = $request->file;
            $categories = $request->category;
            $is_emergency = $request->is_emergency;
             $start_datetime = $request->start_datetime;
             $end_datetime = $request->end_datetime;
            $target_roll = $request->target_roll;
             $target_oganization1 = $request->target_oganization1;
            $target_block = $request->target_block;

            $message = new Message;
            $message->title = "タイトル";
            $message->file = "https://jp-information-sys-html.dev.nssx.work/message/detail.html";
            $message->categories = 0;
            $message->is_emergency = false;
            // $message->start_datetime = 

        }

        $user = $request->session()->get('user');
        $category_list = Category::all();
        $target_roll_list = Roll::all(); 
        
        return view('admin.message.publish.new', [
            'user' => $user,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list
        ]);
    }

}
