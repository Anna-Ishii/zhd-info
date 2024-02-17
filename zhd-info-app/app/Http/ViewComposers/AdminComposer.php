<?php

namespace App\Http\ViewComposers;

use App\Models\Crew;
use Illuminate\View\View;

class AdminComposer
{
    public function compose(View $view)
    {
        $user = session('member');
        $check_crew = session('check_crew');

        $readed_crews_id = session('reading_crews');
        $readed_crew = Crew::find($readed_crews_id[0]);

        $view->with([
            'user' => $user,
            'check_crew' => $check_crew,
            'readed_crew' => $readed_crew
        ]);
    }
}
