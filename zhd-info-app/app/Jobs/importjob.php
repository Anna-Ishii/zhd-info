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
    public $timeout;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->timeout = config('ims.job_timeout', 5400);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Artisan::call('app:import-ims-csv-command');
    }
}
