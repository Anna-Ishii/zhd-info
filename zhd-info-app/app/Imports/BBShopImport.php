<?php

namespace App\Imports;

use App\Models\Organization2;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStartRow;

class BBShopImport implements ToCollection, 
    WithCalculatedFormulas, 
    WithStartRow
{
    use Importable;
    public function collection(Collection $rows)
    {
        
    }

    public function headingRow(): int
    {
        return 3;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 6;
    }


}