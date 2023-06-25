<?php

namespace App\Http\ViewComposers;

use App\Enums\PublishStatus;
use Illuminate\View\View;

class PublishStatusComposer
{
    public function compose(View $view)
    {
        $view->with([
            'publish_status' => PublishStatus::cases(),
        ]);
    }
}
