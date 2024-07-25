<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CrewsIMSImport implements
    ToCollection,
    WithCalculatedFormulas,
    WithStartRow,
    WithChunkReading
{
    use Importable;

    protected $crews_data;

    public function __construct(&$crews_data)
    {
        $this->crews_data = &$crews_data;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->crews_data[] = $row;
        }
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

    public function chunkSize(): int
    {
        return 10000;
    }
}
