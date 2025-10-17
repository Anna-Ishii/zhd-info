<?php

namespace App\Console\Commands;

use App\Exports\CrewIMSExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ShopsIMSExport;
use App\Models\Shop;
use Faker\Factory;
use Illuminate\Support\Arr;

class ExportCrewFakerCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-crew-faker-csv-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crew情報のテストデータをcsvでエクスポートします';

    /**
     * Execute the console command.
     */
    public function handle()
    {


        $this->info('start');
        $MAX = 1000;
        $faker = Factory::create('ja_JP');
        $shops = Shop::query()
            ->pluck('id')
            ->all();

        $export = [];
        for ($i = 0; $i < $MAX; $i++) {
            $shop_id = Arr::random($shops);
            $shop = Shop::find($shop_id);

            $export[$i]['brand_code'] = $shop->brand->brand_code;
            $export[$i]['brand_name'] = $shop->brand->name;
            if(isset($shop->organization2)) {
                $export[$i]['organization'][] = [
                    'label' => "営業部",
                    'name' => $shop->organization2?->name
                ];
            }
            if (isset($shop->organization3)) {
                $export[$i]['organization'][] = [
                    'label' => "DS",
                    'name' => $shop->organization3?->name
                ];
            }
            if (isset($shop->organization4)) {
                $export[$i]['organization'][] = [
                    'label' => "AR",
                    'name' => $shop->organization4?->name
                ];
            }
            if (isset($shop->organization5)) {
                $export[$i]['organization'][] = [
                    'label' => "BL",
                    'name' => $shop->organization5?->name
                ];
            }
            $export[$i]['employee_code'] = sprintf('%010d', fake()->unique()->numberBetween(0000000000, 9999999999));
            $export[$i]['name'] = $faker->name;
            $export[$i]['shop_code'] = $shop->shop_code;
            $export[$i]['shop_name'] = $shop->name;
            $export[$i]['birth_date'] = $faker->dateTimeBetween($startDate = '-50 years', $endDate = '-20 years')->format('Y/m/d');
            $export[$i]['register_date'] = $faker->dateTimeBetween($startDate = '-10 years', $endDate = 'now')->format('Y/m/d');
        }

        Excel::store(new CrewIMSExport($export), 'Crew.csv', 's3');
        $this->info('end');
    }
}
