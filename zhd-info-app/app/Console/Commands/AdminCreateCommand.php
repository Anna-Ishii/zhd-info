<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Organization1;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Hash;

class AdminCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:admin-create-command';

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

        $name = $this->ask('名前を入力してください');
        $email = $this->ask('メールアドレスを入力してください');
        $password = $this->ask('パスワードを入力してください');
        $employee_code = $this->ask('従業員コードを入力してください');

        $organization1_list = Organization1::get();
        foreach ($organization1_list as $key => $value) {
            $this->info("業態番号: $value->id , 業態名: $value->name");
        }

        $organization_id = $this->ask('業態番号を入力してください');

        $organization = Organization1::where('id',$organization_id)->first();

        $this->info("名前 : $name");
        $this->info("メールアドレス : $email");
        $this->info("従業員コード : $employee_code");
        $this->info("パスワード : $password");
        $this->info("業態 : $organization->name");

        if ($this->confirm('この内容で実行してよろしいですか?')) {
            $admin = new Admin([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'employee_code' => $employee_code,
                'organization1_id' => $organization_id,
            ]);
            try {
                $admin->save();
                $this->info('adminユーザーを作成しました');
            } catch (\Throwable $th) {
                $this->info('DBエラーです');
            }

        } else {
            $this->info('cancel');
        }

        $this->info('end');
    }
}
