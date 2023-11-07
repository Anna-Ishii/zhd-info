<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CrewsIMSImport implements
    ToCollection,
    WithCalculatedFormulas,
    WithStartRow
{
    use Importable;

    public function collection(Collection $rows)
    {
    }
    public function headingRow(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }


}
