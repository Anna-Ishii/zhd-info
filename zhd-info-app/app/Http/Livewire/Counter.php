<?php

namespace App\Http\Livewire;

use App\Models\Organization4;
use App\Models\Organization2;
use App\Models\Shop;
use App\Models\User;
use Livewire\Component;

class Counter extends Component
{
    public $count = 0;

    public $users;

    public $organization2_id = 1;

    public function increment()
    {
        $this->organization2_id++;
        $this->count++;
    }

    public function render()
    {
        $shop_list = Shop::where('organization2_id', '=', $this->organization2_id)->get();
        $organization2_list = Organization2::get();
        $this->users = User::get();
        return view('livewire.counter',[
            'shop_list' => $shop_list,
            'organization2_list' => $organization2_list
        ]);
    }
}

