<?php

namespace App\Console\Commands;

use App\Imports\CrewsIMSImport;
use Illuminate\Console\Command;
use App\Imports\ShopsIMSImport;
use App\Models\Brand;
use App\Models\Crew;
use App\Models\Manual;
use App\Models\MessageOrganization;
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

class ImportImsCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-ims-csv-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ims情報をcsvでimportします';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('start');

        if (!Storage::disk('s3')->exists('Depertment.csv')) {
            $this->error('Depertment.csvが存在しません');

        }
        if (!Storage::disk('s3')->exists('Crew.csv')) {
            $this->error('Crew.csvが存在しません');
        }

        $this->info("csvファイルを読み込みます");
        $shops_data = (new ShopsIMSImport)
                            ->toCollection('Depertment.csv', 's3', \Maatwebsite\Excel\Excel::CSV);
        $crews_data =  (new CrewsIMSImport)
                            ->toCollection('Crew.csv', 's3', \Maatwebsite\Excel\Excel::CSV);
        $this->info("csv読み込み完了");

        DB::beginTransaction();
        try {
            $this->import_shops($shops_data[0]);
            $this->import_crews($crews_data[0]);

            DB::commit();     
        }catch(\Throwable $th){

            DB::rollBack();
            $th_msg  = $th->getMessage();
            $this->info("$th_msg");
        }

        $this->info('end');
    }

    private function import_shops($shops_data)
    {
        $new_shop = []; // 新店舗を格納する配列
        $shop_list = Shop::query()->pluck('id')->toArray();
        foreach ($shops_data as $index => $shop) {
            // 営業部、DS、AR、BLの登録  
            $organization2_id = null; // 営業部
            $organization3_id = null; // DS
            $organization4_id = null; // AR
            $organization5_id = null; // BL

            for ($i=4; $i < 24; $i+=4) {
                if (!isset($shop[$i])) break;
                $this->info($index.$shop[$i]);
                $this->info($shop[$i+1]);

                $organization_name = $shop[$i + 1];
                if ($shop[$i] == "営業部") {
                    $organization2_id = Organization2::where('name', $shop[$i + 1])->value('id');
                    if(is_null($organization2_id)){
                        $organization2 = Organization2::create(["name" => $organization_name]);
                        $organization2_id = $organization2->id;
                    }
                }
                if ($shop[$i] == "DS") {
                    $organization3_id = Organization3::where('name', $shop[$i + 1])->value('id');
                    if (is_null($organization3_id)) {
                        $organization3 = Organization3::create(["name" => $organization_name]);
                        $organization3_id = $organization3->id;
                    }
                }
                if ($shop[$i] == "AR") {
                    $organization4_id = Organization4::where('name', $shop[$i + 1])->value('id');
                    if (is_null($organization4_id)) {
                        $organization4 = Organization4::create(["name" => $organization_name]);
                        $organization4_id = $organization4->id;
                    }
                }
                if ($shop[$i] == "BL") {
                    $organization5_id = Organization5::where('name', $shop[$i + 1])->value('id');
                    if (is_null($organization5_id)) {
                        $organization5 = Organization5::create(["name" => $organization_name]);
                        $organization5_id = $organization5->id;
                    }
                }
            }

            $brand_code = $shop[0];
            $brand = Brand::where('brand_code', $brand_code);
            $brand_id = $brand->id;
            $organization1_id = $brand->organization1->id;
            $shop_code = $shop[2];
            $shop_name = $shop[3];
            $shop_id = Shop::query()
                            ->where('shop_code', $shop_code)
                            ->where('brand_id', $brand_id)    
                            ->value('id');
            // 店舗を更新
            $shop = Shop::updateOrCreate(
                [
                    'shop_code' => $shop_code,
                    'brand_id' => $brand_id
                ],
                [
                    'name' => $shop_name,
                    'organization1_id' => $organization1_id,
                    'organization2_id' => $organization2_id,
                    'organization3_id' => $organization3_id,
                    'organization4_id' => $organization4_id,
                    'organization5_id' => $organization5_id,
                    'brand_id' => $brand_id
                ]
            );

            // 新規店舗の場合
            if (is_null($shop_id)) {
                $new_shop[] = $shop;
                $this->create_user($shop);
            }

            if ($shop->wasChanged()) {
                // 店舗更新
                $change_shop[] = $shop;
            } 

            $regiter_shop_id[] = $shop->id;
        }

        // 削除する店舗一覧のID
        $diff_shop_id = array_diff($shop_list, $regiter_shop_id);
        $diff_shop = Shop::whereIn('id', $diff_shop_id)->get();
        $diff_shop_user = User::query()->whereIn('shop_id', $diff_shop_id)->get();
        foreach ($diff_shop_user as $key => $user) {
            $user->message()->detach();
            $user->manual()->detach();
        }
        User::query()->whereIn('shop_id', $diff_shop_id)->forceDelete();
        Shop::whereIn('id', $diff_shop_id)->delete();

        // ログ出力
        $this->info("---新しい店舗---");
        if (!empty($new_shop)) {
            foreach ($new_shop as $s) {
                $this->info("shopID" . $s->id . " 店舗名" . $s->name);
            }
        }

        $this->info("---変更する店舗---");
        if (!empty($change_shop)) {
            foreach ($change_shop as $s) {
                $this->info("shopID" . $s->id . " 店舗名" . $s->name);
            }
        }

        $this->info("---削除する店舗---");
        if (!$diff_shop->isEmpty()) {
            foreach ($diff_shop as $s) {
                $this->info("shopID" . $s->id . " 店舗名" . $s->name);
            }
        }
    }

    // ユーザー作成
    private function create_user($shop)
    {
        // 店長ロール
        $ROLL_ID = 4;
        $employee_code = $this->shopid2employeecode($shop);
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

    private function shopid2employeecode(Shop $shop)
    {
        $shop_code = $shop->shop_code;
        $brand_name = $shop->brand->name;

        $brand_label = strtolower($brand_name);
        $shop_number = substr($shop_code, -4); // 店舗コード
        if ($shop->organization1_id == 3) { // tagの場合
            $employee_code = 'tag' . $shop_number;
        }else {
            $employee_code = $brand_label.$shop_number;
        }

        return $employee_code;
    }

    
    private function import_crews($crews_data)
    {
        $ROLL_ID = 4;

        $undefind_shop = [];
        $undefind_user = [];
        $register_crews = [];
        $change_crew = [];
        $new_crew = [];
        
        foreach ($crews_data as $index => $crew) {
            $brand = Brand::where('brand_code', $crew[0])->first();
            $brand_id = $brand->id;
            // クルーの情報を更新
            $shop = Shop::query()
                            ->where('brand_id', $brand_id)
                            ->where('shop_code', $crew[14])
                            ->first();
            if (empty($shop)) {
                $undefind_shop[] = $crew;
                continue;
            }
            $user = User::where('shop_id', $shop->id)->where('roll_id', $ROLL_ID)->first();

            if(empty($user)) {
                $undefind_user[] = $crew;
                continue;
            }

            $part_code = $crew[12];
            $name = $crew[13];
            $birth_date = $this->parseDateTime($crew[16]);
            $register_date = $this->parseDateTime($crew[17]);
            $crew_id = Crew::query()
                            ->where('part_code', $part_code)
                            ->value('id');
            $crew = Crew::updateOrCreate(
                [
                    'part_code' => $part_code,
                ],
                [
                    'user_id' => $user->id,
                    'name' => $name,
                    'birth_date' =>  $birth_date,
                    'register_date' => $register_date
                ]
            );
            if ($crew->wasChanged()) {
                // 店舗更新
                $change_crew[] = $crew;
            }
            // 新規店舗の場合
            if (is_null($crew_id)) {
                $new_crew[] = $crew;
            }

            $register_crews[] = $part_code;
        }
        // クルーの削除
        $crew_list = Crew::query()
                ->pluck('part_code')
                ->toArray();
        $diff_crew_id = array_diff($crew_list, $register_crews);
        $diff_crew = Crew::whereIn('part_code', $diff_crew_id)->get();
        Crew::whereIn('part_code', $diff_crew_id)->delete();

        // ログ出力
        $this->info("---新しいクルー---");
        if (!empty($new_crew)) {
            foreach ($new_crew as $c) {
                $this->info("crewID" . $c->id . " クルー名" . $c->name);
            }
        }
        $this->info("---変更するクルー---");
        if (!empty($change_crew)) {
            foreach ($change_crew as $c) {
                $this->info("crewID" . $c->id . " クルー名" . $c->name);
            }
        }
        $this->info("---削除するクルー---");
        if (!$diff_crew->isEmpty()) {
            foreach ($diff_crew as $c) {
                $this->info("crewID" . $c->id . " クルー名" . $c->name);
            }
        }
        $this->info("---店舗が見つからないエラー---");
        if (!empty($undefind_shop)) {
            foreach ($undefind_shop as $c) {
                $this->info("店舗コード" . $c[14] . " 店舗名" . $c[15]);
            }
        }
        $this->info("---店舗ユーザーが見つからないエラー---");
        if (!empty($undefind_user)) {
            foreach ($undefind_user as $c) {
                $this->info("店舗コード" . $c[14] . " 店舗名" . $c[15]);
            }
        }
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

}
