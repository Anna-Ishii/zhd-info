<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Roll;
use App\Models\User;
use Illuminate\Http\Request;


class MessagePublishController extends Controller
{
    public function index(Request $request)
    {
        $user_name = $request->session()->get('user_name');
        $category_list = Category::all();

        return view('admin.message.publish', [
            'user_name' => $user_name,
            'category_list' => $category_list
        ]);
    }

    public function new(Request $request)
    {
        if ($request->isMethod('post')) {
            $title = $request->title;
            // $file = $request->file;
            $categories = $request->category;
        }

        $user_name = $request->session()->get('user_name');
        $category_list = Category::all();
        $target_roll_list = Roll::all(); 
        
        return view('admin.message.publish.new', [
            'user_name' => $user_name,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list
        ]);
    }

}
