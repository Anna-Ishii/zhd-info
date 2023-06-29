<?php

namespace App\Http\Livewire\Admin;

use App\Models\Organization2;
use App\Models\Shop;
use Illuminate\Http\Request;

use Livewire\Component;

class AccountSearchForm extends Component
{

    private $shops = [];
    public $brand;
    public $shop_id;
    public $current_shop = 0;

    public function mount(Request $request)
    {
        $this->brand = $request->organization2;
        $this->shop_id = $request->shop;
    }

    public function updatingBrand($value)
    {
        $this->getShops($value);
    }

    public function updatingShop($value)
    {
        $this->shop = $value;
    }

    public function getShops($org2) {
        
        if(isset($org2))$this->shops = Shop::where('organization2_id', '=', $org2)->get();
    }
    
    public function render()
    {
        $this->getShops($this->brand);
        return view('livewire.admin.account-search-form',[
            'shop_list' => $this->shops,
            'organization2_list' => Organization2::get(),
        ]);
    }
}

