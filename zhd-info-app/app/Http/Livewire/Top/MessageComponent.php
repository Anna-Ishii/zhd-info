<?php

namespace App\Http\Livewire\Top;

use Livewire\Component;

class MessageComponent extends Component
{
    public $ms;

    public function reading()
    {
        $member = session("member");
        // 既読をつける
        $member->message()->updateExistingPivot($this->ms->id, [
            'read_flg' => true, 
        ]);
        return redirect()->to(url()->full());
    }

    public function render()
    {
        return view('livewire.top.message-component');
    }
}