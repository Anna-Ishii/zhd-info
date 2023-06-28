<?php

namespace App\Http\Livewire\Admin;

use App\Models\Organization2;
use App\Models\Shop;
use Livewire\Component;

class AccountSearchForm extends Component
{

    private $shops = [];
    public $brand;
    public function updatingBrand($value)
    {
        $this->getShops($value);
    }

    public function getShops($org2) {
        $this->shops = Shop::where('organization2_id', '=', $org2)->get();
    }
    
    public function render()
    {
        return view('livewire.admin.account-search-form',[
            'shop_list' => $this->shops,
            'organization2_list' => Organization2::get(),
        ]);
    }
}

