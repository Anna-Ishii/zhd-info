<?php

namespace App\Http\Livewire\Message;

use Livewire\Component;

class MessageComponent extends Component
{
    public $message;

    public function reading()
    {
        $member = session("member");
        // 既読をつける
        $member->message()->updateExistingPivot($this->message->id, [
            'read_flg' => true, 
        ]);
        return redirect()->to(url()->full());
    }

    public function render()
    {
        return view('livewire.message.message-component');
    }
}