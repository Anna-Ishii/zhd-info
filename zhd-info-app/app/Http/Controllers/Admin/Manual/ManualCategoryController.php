<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManualCategoryController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        return view('admin.manual.category.index');
    }
}
