<?php

namespace App\Http\Livewire\Admin;

use App\Models\Organization1;
use Livewire\Component;

class LogoutButton extends Component
{
    public function logout()
    {
        $admin = session('admin');
        $organization1 = Organization1::find($admin->organization1_id);
        session()->forget('admin');
        return redirect()->route('admin.auth', ['organization1' => $organization1->name]);
    }

    public function render()
    {
        return view('livewire.admin.logout-button');
    }
}