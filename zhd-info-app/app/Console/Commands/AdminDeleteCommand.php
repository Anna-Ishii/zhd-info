<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Organization1;
use Illuminate\Console\Command;

class AdminDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:admin-delete-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('start');
        $organization_id = $this->ask('業態番号を入力してください');
        $employee_code = $this->ask('従業員コードを入力してください');
        
        $admin = Admin::where('organization1_id', '=', $organization_id)
                        ->where('employee_code', '=', $employee_code)
                        ->first();
        if(!isset($admin)) {
            $this->info('該当のアカウントが見つかりませんでした');
            return 0;
        } 
        $organization1 = $admin->organization1->name;
        $this->info("以下のアカウントを削除します");
        $this->info("名前： $admin->name");
        $this->info("従業員コード： $admin->employee_code");
        $this->info("業態： $organization1");

        if ($this->confirm('この内容で実行してよろしいですか?')) {
            $admin->delete();
        }
        $this->info('end');
    }
}
