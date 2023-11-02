<?php

namespace App\Exports;

use App\Models\Shop;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ShopsIMSExport implements FromView, ShouldAutoSize
{
    protected $shops;

    public function __construct()
    {
        $this->shops = Shop::all();    
    }

    public function view(): View
    {
        return view('exports.shops-ims-export', [
            'shops' => $this->shops
        ]);
    }
}