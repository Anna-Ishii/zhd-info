<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Artisan;

class importjob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    // public $timeout= 7200;
    public $tries = 3;
    public $timeout = 3600;
    public $backoff = 30;

    /**
     * Create a new job instance.
     */
    // public function __construct()
    // {
    //     $this->timeout = config('ims.job_timeout', 7200);
    // }

    /**
     * ジョブが重複して実行されないようにする
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('importjob-lock'))->expireAfter(3600),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            ini_set('max_execution_time', '7200');
            set_time_limit(7200);
            Artisan::call('app:import-ims-csv-command');
            \Log::info('手動バッチ成功 importjob completed.');
        } catch (\Throwable $e) {
            \Log::error('手動バッチ失敗 importjob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

