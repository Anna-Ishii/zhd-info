<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class UserComposer
{
    public function compose(View $view)
    {
        $admin = session('admin');
        $view->with([
            'admin' => $admin,
        ]);
    }
}