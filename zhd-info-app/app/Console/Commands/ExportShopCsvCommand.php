<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ShopsIMSExport;

class ExportShopCsvCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-shops-csv-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shop情報をcsvでエクスポートします';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('start');
        Excel::store(new ShopsIMSExport(), 'Depertment.csv', 's3');
        $this->info('end');
    }
}