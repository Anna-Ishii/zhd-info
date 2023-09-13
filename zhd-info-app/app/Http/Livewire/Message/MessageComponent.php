<?php

namespace App\Http\Livewire\Message;

use App\Models\Message;
use Carbon\Carbon;
use Livewire\Component;
class MessageComponent extends Component
{
    public Message $message;

    public function reading()
    {
        $member = session("member");
        // 既読をつける
        $member->message()->wherePivot('read_flg', false)->updateExistingPivot($this->message->id, [
            'read_flg' => true,
            'readed_datetime' => Carbon::now(),
        ]);
        return redirect()->to($this->message->content_url);
    }

    public function render()
    {
        return view('livewire.message.message-component');
    }
}