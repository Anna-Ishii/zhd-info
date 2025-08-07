<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class importjob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout= 5400;
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    // public function __construct()
    // {
    //     $this->timeout = config('ims.job_timeout', 7200);
    // }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('max_execution_time', '7200');
        set_time_limit(7200);
        Artisan::call('app:import-ims-csv-command');
    }
}

