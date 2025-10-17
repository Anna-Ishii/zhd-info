<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Manual;
use App\Models\Message;
use App\Models\MessageOrganization;
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
    protected $description = '選択した業態のユーザーを作成するコマンドです。ユーザーの作成と同時に対象の業務連絡と動画マニュアルを登録します。';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('start');
        $this->info('店舗ユーザーを作成します');
        $shop_code = $this->ask('店舗コードを入力してください');

        $shop = Shop::where('shop_code', $shop_code)->first();
        $ROLL_ID = 4;

        if ($this->confirm($shop->name.'の店舗アカウント作成してよろしいですか?')) {
            try {
            DB::beginTransaction();

            $employee_code = $this->shopid2employeecode($shop->shop_code);
            $user = User::create([
                'name' => $shop->name,
                'belong_label' => $shop->name,
                'shop_id' => $shop->id,
                'employee_code' => $employee_code,
                'password' => Hash::make($employee_code),
                'email' => '',
                'roll_id' => $ROLL_ID,
            ]);

            // TODO 掲載終了したものを配布するかどうか。
            $messages = [];
            if(isset($shop->organization5_id)) {
                $messages = MessageOrganization::query()
                                    ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
                                    ->select('message_organization.message_id as id')
                                    ->where('message_organization.organization5_id', $shop->organization5_id)
                                    ->where('message_brand.brand_id', $shop->brand_id)
                                    ->get()
                                    ->toArray();
            }elseif(isset($shop->organization4_id)) {
                $messages = MessageOrganization::query()
                                    ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
                                    ->select('message_organization.message_id as id')
                                    ->where('message_organization.organization4_id', $shop->organization4_id)
                                    ->where('message_brand.brand_id', $shop->brand_id)
                                    ->get()
                                    ->toArray();

            }elseif(isset($shop->organization3_id)) {
                $messages = MessageOrganization::query()
                                    ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
                                    ->select('message_organization.message_id as id')
                                    ->where('message_organization.organization3_id', $shop->organization3_id)
                                    ->where('message_brand.brand_id', $shop->brand_id)
                                    ->get()
                                    ->toArray();

            }elseif(isset($shop->organization2_id)) {
                $messages = MessageOrganization::query()
                                    ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
                                    ->select('message_organization.message_id as id')
                                    ->where('message_organization.organization2_id', $shop->organization2_id)
                                    ->where('message_brand.brand_id', $shop->brand_id)
                                    ->get()
                                    ->toArray();
            }

            $message_data = [];
            foreach ($messages as $message) {
                $message_data[$message['id']] = ['shop_id' => $shop->id];
            }

            $user->message()->sync($message_data);

            $manual_data = [];
            $_brand_id = $shop->brand_id;
            // 該当のマニュアルを登録
            $manuals = Manual::whereHas('brand', function ($query) use ($_brand_id) {
                    $query->where('brand_id', '=', $_brand_id);
                })->get('id')->toArray();
            foreach ($manuals as $manual) {
                $manual_data[$manual['id']] = ['shop_id' => $shop->id];
            }
            $user->manual()->sync($manual_data);

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
