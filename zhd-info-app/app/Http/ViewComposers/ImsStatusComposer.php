<?php

namespace App\Http\ViewComposers;

use App\Models\ImsSyncLog;
use Illuminate\View\View;

class ImsStatusComposer
{
    public function compose(View $view)
    {
        $log = ImsSyncLog::orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->first();
        $view->with([
            'is_error_ims' =>  $log->is_error()
        ]);
    }
}
