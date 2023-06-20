<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class AdminComposer
{
    public function compose(View $view)
    {
        $user = session('member');
        $view->with([
            'user' => $user,
        ]);
    }
}
