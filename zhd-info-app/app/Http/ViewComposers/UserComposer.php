<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class UserComposer
{
    public function compose(View $view)
    {
        $admin = session('admin');
        $arrow_pages = $admin?->allowpage()->pluck('code')->toArray();
        
        $view->with([
            'admin' => $admin,
            'arrow_pages' => $arrow_pages
        ]);
    }
}