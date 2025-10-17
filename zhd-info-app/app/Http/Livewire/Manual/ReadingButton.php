<?php

namespace App\Http\Livewire\Manual;

use Livewire\Component;

class ReadingButton extends Component
{
    public $manual;
    public $read_flg;
    public $read_flg_count;

    public function reading()
    {
        $user = session('member');
        $this->manual->user()->updateExistingPivot($user->id, ['read_flg' => true]);
    }

    public function render()
    {
        $user = session('member');
        $this->read_flg_count = $this->manual->user()->where('manual_user.read_flg', '=', true)->count();
        $this->read_flg = $this->manual->user()->where('user_id', '=', $user->id)->pluck('manual_user.read_flg');
        return view('livewire.manual.reading-button');
    }
}