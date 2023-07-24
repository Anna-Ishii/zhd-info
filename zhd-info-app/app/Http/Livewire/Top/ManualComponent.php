<?php

namespace App\Http\Livewire\Top;

use Livewire\Component;

class ManualComponent extends Component
{
    public $ml;

    public function reading()
    {
        $member = session("member");
        // 既読をつける
        $member->manual()->updateExistingPivot($this->ml->id, [
            'read_flg' => true,
        ]);
        return redirect()->to(route('top'));
    }

    public function render()
    {
        return view('livewire.top.manual-component');
    }
}
