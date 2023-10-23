<?php 

namespace App\Console\Commands;

use App\Imports\BBShopImport;
use App\Models\Brand;
use App\Models\Manual;
use App\Models\MessageOrganization;
use App\Models\Organization2;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\isEmpty;

class ShopRegisterBBCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shop-register-bb-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bbの店舗をcsvから登録する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('start');
        $this->info('店舗を登録します');
        $file_name = $this->ask('ファイル名を入力してください');
        $path = 'excel/'.$file_name;

        if(!Storage::exists($path)) return;
        $this->info($file_name.'から店舗を登録します');
        // Excel::import(new BBShopImport, storage_path($path));

        $excel_data = (new BBShopImport)->toCollection($path, 'local', \Maatwebsite\Excel\Excel::XLSX);

        DB::beginTransaction();
        try {
            $null_org2 = [];
            $null_org3 = [];
            $null_org4 = [];
            $regiter_shop_id = [];
            $new_shop = [];
            $change_shop = [];

            $bb_shop_list = Shop::query()
                                ->where('organization1_id', 2)
                                ->pluck('id')->toArray();
            // エクセルを読み込む
            foreach ($excel_data[0] as $i => $data) {
                // 店舗番号
                $shop_code = strtolower($data[8]) . $data[6];
                $shop_name = $data[7];
                $brand_id = Brand::where('name', strtolower($data[8]))->value('id');
                $organization1_id = 2; // BB

                // 営業部
                $organization2_id = Organization2::where('name', $data[2])->value('id');
                if (is_null($organization2_id)) {
                    $null_org2[] = $data[2];
                    $organization2 = Organization2::create([
                        "name" => $data[2]
                    ]);
                    $organization2_id = $organization2->id;
                }
                // DS
                $organization3_id = Organization3::where('name', $data[3])->value('id');
                if (is_null($organization3_id)) {
                    $null_org3[] = $data[3];
                    $organization3 = Organization3::create([
                        "name" => $data[3]
                    ]);
                    $organization3_id = $organization3->id;
                }
                // AR
                $organization4_id = Organization4::where('name', $data[5])->value('id');
                if (is_null($organization4_id)) {
                    $null_org2[] = $data[5];
                    $organization4 = Organization4::create([
                        "name" => $data[5]
                    ]);
                    $organization4_id = $organization4->id;
                }

                // 店舗を更新
                $shop_id = Shop::where('shop_code', $shop_code)->value('id');
                $shop = Shop::updateOrCreate([
                    'shop_code' => $shop_code
                    ],
                    [
                    'name' => $shop_name."店",
                    'organization1_id' => $organization1_id,
                    'organization2_id' => $organization2_id,
                    'organization3_id' => $organization3_id,
                    'organization4_id' => $organization4_id,
                    'brand_id' => $brand_id
                ]);

                if($shop->wasChanged()) {
                    // 店舗更新
                    $change_shop[] = $shop;
                    // $this->info("$data[0] $shop_id $shop_name 変更あり");
                    
                } else {
                    // 店舗新規
                    // $this->info("$data[0] $shop_id $shop_name 変更なし");
                }
                $regiter_shop_id[] = $shop_id;

                // 新規店舗の場合
                if(is_null($shop_id)){
                    $new_shop[] = $shop;
                    self::create_user($shop);
                }
            }


            $null_org2_unique = array_unique($null_org2);
            $null_org3_unique = array_unique($null_org3);
            $null_org4_unique = array_unique($null_org4);

            // 削除する店舗一覧のID
            $diff_shop_id = array_diff($bb_shop_list, $regiter_shop_id);
            $diff_shop = Shop::whereIn('id', $diff_shop_id)->get();

            $diff_shop_user = User::query()->whereIn('shop_id', $diff_shop_id)->get();
            foreach ($diff_shop_user as $key => $user) {
                $user->message()->detach();
                $user->manual()->detach();
            }
            User::query()->whereIn('shop_id', $diff_shop_id)->forceDelete();
            Shop::whereIn('id', $diff_shop_id)->delete();



            if (!empty($null_org2_unique)) {
                $this->info("---新規営業部---");
                foreach ($null_org2_unique as $key => $value) {
                    $this->info($value);
                }
            }
            if (!empty($null_org3_unique)) {
                $this->info("---新規DS---");
                foreach ($null_org3_unique as $key => $value) {
                    $this->info($value);
                }
            }
            if (!empty($null_org4_unique)) {
                $this->info("---新規AR---");
                foreach ($null_org4_unique as $key => $value) {
                    $this->info($value);
                }
            }
            if(!empty($new_shop)) {
                $this->info("---新しい店舗---");
                foreach ($new_shop as $s) {
                    $this->info("shopID" . $s->id . " 店舗名" . $s->name);
                }
            }
            if(!empty($change_shop)) {
                $this->info("---変更する店舗---");
                foreach ($change_shop as $s) {
                    $this->info("shopID" . $s->id . " 店舗名" . $s->name);
                }
            }

            if (!$diff_shop->isEmpty()) {
                $this->info("---削除する店舗---");
                foreach($diff_shop as $s) {
                    $this->info("shopID".$s->id." 店舗名".$s->name);
                }
            }

            if ($this->confirm('この内容で実行してよろしいですか?')) {
                DB::commit();
            }else{
                DB::rollBack();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $th_msg  = $th->getMessage();
            $this->info("$th_msg");
        }

        $this->info("end");
    }

    // ユーザー作成
    private function create_user($shop) {
        $ROLL_ID = 4;

        $user = User::create([
            'name' => $shop->name,
            'belong_label' => $shop->name,
            'shop_id' => $shop->id,
            'employee_code' => $shop->shop_code,
            'password' => Hash::make($shop->shop_code),
            'email' => '',
            'roll_id' => $ROLL_ID,
        ]);

        // TODO 掲載終了したものを配布するかどうか。
        $messages = [];
        if (isset($shop->organization5_id)) {
            $messages = MessageOrganization::query()
                ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
                ->select('message_organization.message_id as id')
                ->where('message_organization.organization5_id', $shop->organization5_id)
                ->where('message_brand.brand_id', $shop->brand_id)
                ->get()
                ->toArray();
        } elseif (isset($shop->organization4_id)) {
            $messages = MessageOrganization::query()
                ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
                ->select('message_organization.message_id as id')
                ->where('message_organization.organization4_id', $shop->organization4_id)
                ->where('message_brand.brand_id', $shop->brand_id)
                ->get()
                ->toArray();
        } elseif (isset($shop->organization3_id)) {
            $messages = MessageOrganization::query()
                ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
                ->select('message_organization.message_id as id')
                ->where('message_organization.organization3_id', $shop->organization3_id)
                ->where('message_brand.brand_id', $shop->brand_id)
                ->get()
                ->toArray();
        } elseif (isset($shop->organization2_id)) {
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
    }
}