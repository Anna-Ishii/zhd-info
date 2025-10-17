<?php

namespace App\Console\Commands;

use App\Imports\CrewsIMSImport;
use Illuminate\Console\Command;
use App\Imports\ShopsIMSImport;
use App\Models\Brand;
use App\Models\Crew;
use App\Models\ImsSyncLog;
use App\Models\Manual;
use App\Models\MessageOrganization;
use App\Models\Organization1;
use App\Models\Organization2;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Organization5;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CheckS3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-s3-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'S3の接続確認を行います。';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('start');
        $this->info('S3へ接続します。');
        $ims_log = new ImsSyncLog();

        $now = new Carbon('now');
        $now_str = $now->format("Ymd");
        $now_str = "20240221";
        $organization_filename = "organization_{$now_str}.csv";
        $crews_filename = "crew_{$now_str}.csv";
        $directory = "IMS2/FR_BUSINESS/";
        $organization_path = $directory . $organization_filename;
        $crews_path = $directory . $crews_filename;
        $this->info($organization_path);
        $this->info($crews_path);

        foreach (Storage::disk('s3')->allFiles($directory) as $file) {
            // your file is in $file
            $this->info($file);
        }

    }
}
