<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Manual;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MessageUserUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:message-user-update-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'すべての業務連絡の配信条件を読み取りユーザーに配布するコマンド';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('start');

        $users = User::get();
        if ($this->confirm('更新してよろしいですか?')) {
            try {
                DB::beginTransaction();

                foreach ($users as $user) {
                    $this->info("$user->name さんのアカウントを更新します");
                    $roll_id = $user->roll->id;
                    $organization5_id = $user->shop->organization5_id;
                    $organization4_id = $user->shop->organization4_id;

                    $message_data = [];
                    // 該当のメッセージを登録
                    $messages = Message::whereHas('roll', function ($query) use ($roll_id) {
                        $query->where('roll_id', '=', $roll_id);
                    });
                    if (isset($organization5_id)) {
                        $messages = $messages->whereHas('organization5', function ($query) use ($organization5_id) {
                            $query->where('organization5_id', '=', $organization5_id);
                        });
                    } elseif (isset($organization4_id)) {
                        $messages = $messages->whereHas('organization4', function ($query) use ($organization4_id) {
                            $query->where('organization4_id', '=', $organization4_id);
                        });
                    }
                    $messages = $messages->get('id')->toArray();

                    foreach ($messages as $message) {
                        $message_data[$message['id']] = ['shop_id' => $user->shop_id];
                    }
                    $user->message()->sync($message_data);

                    $brand_id = $user->shop->brand_id;
                    $manual_data = [];
                    // 該当のマニュアルを登録
                    $manuals = Manual::whereHas('brand', function ($query) use ($brand_id) {
                        $query->where('brand_id', '=', $brand_id);
                    })->get('id')->toArray();
                    foreach ($manuals as $manual) {
                        $manul_id = $manual['id'];
                        $this->info($manul_id);
                        $manual_data[$manual['id']] = ['shop_id' => $user->shop_id];
                    }
                    $user->manual()->sync($manual_data);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                $this->info('データベースエラーです。');
                $th_msg  = $th->getMessage();
                $this->info("$th_msg");
            }
        } else {
            $this->info('cancel');
        }
        $this->info('end');
    }
}
