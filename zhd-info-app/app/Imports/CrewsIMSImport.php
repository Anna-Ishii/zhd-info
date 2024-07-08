<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

use App\Console\Commands\ImportImsCsvCommand;


class CrewsIMSImport implements
    ToCollection,
    WithCalculatedFormulas,
    WithStartRow,
    WithChunkReading
{
    use Importable;

    protected $command;

    public function __construct(ImportImsCsvCommand $command)
    {
        $this->command = $command;
    }

    public function collection(Collection $rows)
    {
        $this->command->import_crews($rows);
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
