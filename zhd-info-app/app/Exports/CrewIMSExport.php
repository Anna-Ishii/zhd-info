<?php

namespace App\Exports;

use App\Models\Shop;
use Faker\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CrewIMSExport implements FromView, ShouldAutoSize
{
    protected $export;

    public function __construct($export)
    {
        $this->export = $export;
    }

    public function view(): View
    {


        return view('exports.crews-ims-export', [
            'export' => $this->export
        ]);
    }
}
