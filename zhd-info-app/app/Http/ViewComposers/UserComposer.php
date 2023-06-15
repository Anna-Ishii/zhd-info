<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class UserComposer
{
    public function compose(View $view)
    {
        $user = session('user');
        $view->with([
            'user' => $user,
        ]);
    }
}