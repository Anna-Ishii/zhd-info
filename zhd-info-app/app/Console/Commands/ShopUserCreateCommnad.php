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

class ShopUserCreateCommnad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shop-user-create-command';

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
        $this->info('店舗ユーザーを作成します');
        $organization1_list = Organization1::get();
        foreach ($organization1_list as $key => $value) {
            $this->info("業態番号: $value->id , 業態名: $value->name");
        }
        $organization_id = $this->ask('業態番号を入力してください');
        $shops_query = Shop::where('organization1_id', $organization_id);
        $count = $shops_query->count();

        $user = new User();
        $password = 'password';
        $roll_id = 4; //店長
        $this->info("$count 個のアカウントを作成します");

        $shops = $shops_query->get();
        if ($this->confirm('作成してよろしいですか?')) {
            try {
                DB::beginTransaction();

                foreach($shops as $shop) {
                    $employee_code = $this->shopid2employeecode($shop->shop_code);
                    $shop_code = $shop->shop_code;
                    $this->info("店舗コード：$shop_code");
                    $this->info("従業員コード：$employee_code");
                    $model = User::firstOrCreate([
                        'employee_code' => $employee_code
                    ],[
                        'name' => $shop->name,
                        'belong_label' => $shop->name,
                        'shop_id' => $shop->id,
                        'employee_code' => $employee_code,
                        'password' => Hash::make($password),
                        'email' => '',
                        'roll_id' => $roll_id,
                    ]);
                    $organization5_id = $shop->organization5_id;
                    $organization4_id = $shop->organization4_id;

                    $message_data = [];
                    // 該当のメッセージを登録
                    $messages = Message::whereHas('roll', function ($query) use ($roll_id) {
                                        $query->where('roll_id', '=', $roll_id);
                                    });
                    if(isset($organization5_id)){
                        $messages = $messages->whereHas('organization5', function ($query) use ($organization5_id) {
                            $query->where('organization5_id', '=', $organization5_id);
                        });
                    }elseif(isset($organization4_id)){
                        $messages = $messages->whereHas('organization4', function ($query) use ($organization4_id) {
                            $query->where('organization4_id', '=', $organization4_id);
                        });
                    }
                    $messages = $messages->get('id')->toArray();
                                    
                    foreach ($messages as $message) {
                        $message_data[$message['id']] = ['shop_id' => $shop->id];
                    }
                    $model->message()->sync($message_data);

                    $brand_id = $shop->brand_id;
                    $manual_data = [];
                    // 該当のマニュアルを登録
                    $manuals = Manual::whereHas('brand', function ($query) use ($brand_id) {
                            $query->where('brand_id', '=', $brand_id);
                        })->get('id')->toArray();
                    foreach ($manuals as $manual) {
                        $manual_data[$manual['id']] = ['shop_id' => $shop->id];
                    }
                    $model->manual()->sync($manual_data);
                } 
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                $th_msg = $th->getMessage();
                $this->info("$th_msg");
                $this->info('データベースエラーです。');
            }
        }else{
            $this->info('cancel');
        }
        $this->info('end');

    }
    private function shopid2employeecode($shop_code)
    {
        $employee_code = $shop_code;
        
        preg_match_all('/[a-zA-Z]/', $shop_code, $matches);
        $brand_code = implode('', $matches[0]);
        $shop_number = substr($shop_code, -4);
        if($brand_code=='tj' || $brand_code=='ycp' || $brand_code=='nib' || $brand_code=="g"){
            $employee_code = 'tag'.$shop_number;
        }

        
        
        return $employee_code;
    }
}
