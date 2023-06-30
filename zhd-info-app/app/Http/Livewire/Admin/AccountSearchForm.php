<?php

namespace App\Http\Livewire\Admin;

use App\Models\Organization2;
use App\Models\Roll;
use App\Models\Shop;
use Illuminate\Http\Request;

use Livewire\Component;

class AccountSearchForm extends Component
{

    private $shop_list = [];
    public $current_organization2;
    public $current_shop;
    public $current_roll; 
    public $current_q;

    public $organization2_list;
    
    public function mount(Request $request)
    {
        $this->current_organization2 = $request->organization2;
        $this->current_shop = $request->shop;
        $this->current_roll = $request->roll;
        $this->current_q = $request->q;

        $this->organization2_list = Organization2::get();
        $this->roll_list = Roll::get();
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
        
        if(isset($org2))$this->shop_list = Shop::where('organization2_id', '=', $org2)->get();
    }
    
    public function render()
    {
        $this->getShops($this->current_organization2);
        return view('livewire.admin.account-search-form',[
            'shop_list' => $this->shop_list,
            'organization2_list' => $this->organization2_list,
            'roll_list' => $this->roll_list,
        ]);
    }
}

