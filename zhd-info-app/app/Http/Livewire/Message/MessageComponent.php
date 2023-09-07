<?php

namespace App\Http\Livewire\Message;

use Carbon\Carbon;
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
            'readed_datetime' => Carbon::now(),
        ]);
        return redirect()->to(route('message.index'));
    }

    public function render()
    {
        return view('livewire.message.message-component');
    }
}