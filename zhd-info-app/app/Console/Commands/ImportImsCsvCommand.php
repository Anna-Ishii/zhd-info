<?php

namespace App\Console\Commands;

use App\Imports\CrewsIMSImport;
use Illuminate\Console\Command;
use App\Imports\ShopsIMSImport;
use App\Models\Brand;
use App\Models\Crew;
use App\Models\ImsSyncLog;
use App\Models\Manual;
use App\Models\MessageOrganization;
use App\Models\Organization1;
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
        ini_set('memory_limit', '-1');
        $this->info('start');
        $ims_log = new ImsSyncLog();
        $ims_log->import_at = new Carbon('now');
        $ims_log->save();

        $now = new Carbon('now');
        $now_str = $now->format("Ymd");
        $organization_filename = "organization_{$now_str}.csv";
        $crews_filename = "crew_{$now_str}.csv";
        $directory = "IMS2/FR_BUSINESS/";
        $organization_path = $directory . $organization_filename;
        $crews_path = $directory . $crews_filename;
        $this->info($organization_path);
        $this->info($crews_path);

        if (!Storage::disk('s3')->exists($organization_path)) {
            $this->error("{$organization_path}が存在しません");
            $this->info('end');
            exit();
        }

        if (!Storage::disk('s3')->exists($crews_path)) {
            $this->error("{$crews_path}が存在しません");
            $this->info('end');
            exit();
        }

        DB::beginTransaction();
        try {
            // 組織情報の取り込み
            try {
                $this->info("{$organization_filename}ファイルを読み込みます");
                $shops_data = (new ShopsIMSImport)
                    ->toCollection($organization_path, 's3', \Maatwebsite\Excel\Excel::CSV);
                $this->info("{$organization_filename}ファイル読み込み完了");
                $this->import_shops($shops_data[0]);
                unset($shops_data);
                $ims_log->import_department_at = new Carbon('now');
                $ims_log->import_department_error = false;
            } catch (\Throwable $th) {
                $ims_log->import_department_message = $th->getMessage();
                $ims_log->import_department_error = true;
                throw $th;
            }
            // クルーの取り込み
            try {
                $this->info("{$crews_filename}ファイルを読み込みます");
                $crews_data = [];
                (new CrewsIMSImport($crews_data))->import($crews_path, 's3', \Maatwebsite\Excel\Excel::CSV);
                $this->info("{$crews_filename}ファイル読み込み完了");
                $this->import_crews($crews_data);
                unset($crews_data);
                $ims_log->import_crew_at = new Carbon('now');
                $ims_log->import_crew_error = false;
            } catch (\Throwable $th) {
                $ims_log->import_crew_message = $th->getMessage();
                $ims_log->import_crew_error = true;
                throw $th;
            }

            DB::commit();
        } catch (\Throwable $th) {

            DB::rollBack();
            $th_msg  = $th->getMessage();
            $this->info("$th_msg");
        }
        $ims_log->save();
        $this->info('end');
    }

    private function import_shops($shops_data)
    {
        $new_shop = []; // 新店舗を格納する配列
        $close_shop = []; // 削除する店舗を格納する配列
        $shop_list = Shop::query()->pluck('id')->toArray();
        $today = Carbon::now();
        $register_shop_id = [];
        $deleted_shops = [];

        // 配列をコレクションに変換
        $shops_data = collect($shops_data);

        $shops_data->chunk(20000)->each(function ($chunk) use ($today, &$new_shop, &$close_shop, &$change_shop, &$register_shop_id) {

            foreach ($chunk as $shop) {
                $organization1_id = Organization1::where('name', $shop[0])->value('id');

                $close_date = $this->parseDateTime($shop[30]);
                // 閉店の店舗
                if (is_null($close_date) || $today->gte($close_date)) {
                    $close_shop[] = Shop::where('organization1_id', $organization1_id)->where('shop_code', $shop[3])->value('id');
                    continue;
                }
                // 営業部、DS、AR、BLの登録
                $organization2_id = null; // 営業部
                $organization3_id = null; // DS
                $organization4_id = null; // AR
                $organization5_id = null; // BL

                for ($i = 5; $i < 30; $i += 5) {
                    $organization_name = $shop[$i + 1];
                    $order_no = (int)$shop[$i + 2];
                    if ($shop[$i] == "営業部") {
                        $organization2_id = Organization2::where('name', $shop[$i + 1])->value('id');
                        // 初回のみ
                        Organization2::where('name', $shop[$i + 1])->update([
                            'order_no' => $order_no,
                            'display_name' => $organization_name
                        ]);
                        if (is_null($organization2_id)) {
                            $organization2 = Organization2::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $organization_name
                            ]);
                            $organization2_id = $organization2->id;
                        }
                    }
                    if ($shop[$i] == "DS") {
                        $organization3_id = Organization3::where('name', $shop[$i + 1])->value('id');
                        // 初回のみ
                        Organization3::where('name', $shop[$i + 1])->update([
                            'order_no' => $order_no,
                            'display_name' => $organization_name
                        ]);
                        if (is_null($organization3_id)) {
                            $organization3 = Organization3::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $organization_name
                            ]);
                            $organization3_id = $organization3->id;
                        }
                    }
                    if ($shop[$i] == "AR") {
                        $organization4_id = Organization4::where('name', $shop[$i + 1])->value('id');
                        // 初回のみ
                        Organization4::where('name', $shop[$i + 1])->update([
                            'order_no' => $order_no,
                            'display_name' => $organization_name
                        ]);
                        if (is_null($organization4_id)) {
                            $organization4 = Organization4::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $organization_name
                            ]);
                            $organization4_id = $organization4->id;
                        }
                    }
                    if ($shop[$i] == "BL") {
                        $organization5_id = Organization5::where('name', $shop[$i + 1])->value('id');
                        // 初回のみ
                        Organization5::where('name', $shop[$i + 1])->update([
                            'order_no' => $order_no,
                            'display_name' => $this->formatOrg5Name($organization_name)
                        ]);
                        if (is_null($organization5_id)) {
                            $organization5 = Organization5::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $this->formatOrg5Name($organization_name)
                            ]);
                            $organization5_id = $organization5->id;
                        }
                    }
                }

                $brand_name = $shop[2];
                if ($brand_name == "S-VS") $brand_name = "VS";
                if ($brand_name == "S-BB") $brand_name = "BB";
                $brand = Brand::where('name', $brand_name)->first();
                if (!isset($brand)) continue;

                $brand_id = $brand->id;
                $shop_code = $shop[3];
                $shop_name = $shop[4];

                //店舗コードを更新(IMS連携の初回のみ)
                Shop::update_shopcode($shop_code, $brand_id);
                // 店舗が存在するか
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
                        'display_name' => $this->formatShopName($shop_name),
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
                }

                // 店舗の情報が更新された時
                if ($shop->wasChanged()) {
                    // 店舗更新
                    $change_shop[] = $shop;
                }

                $register_shop_id[] = $shop->id;
            }
        });

        // 初回のみパッチ
        DB::insert('insert into message_organization (
                with m_o5 as (
                select distinct m_u.message_id as message_id, s.organization1_id as organization1_id, s.organization5_id as organization5_id from message_user as m_u
                left join users as u on m_u.user_id = u.id
                left join shops as s on u.shop_id = s.id
                inner join organization5 as o5 on s.organization5_id = o5.id
                )
                select message_id, organization1_id, NULL as organization2_id, NULL as organization3_id, NULL as organization4_id, organization5_id, ? as created_at, ? as updated_at from m_o5
                );', [new Carbon('now'), new Carbon('now')]);
        // 初回のみパッチ
        DB::delete(
            'DELETE FROM message_organization WHERE organization5_id IN (
                    select id from organization5 where id not in (
                        select distinct organization5_id from shops where organization5_id is not null
                    )
                )'
        );

        // 新店舗のユーザー作成
        foreach ($new_shop as $n_s) {
            $this->create_user($n_s);
        }

        // 削除する店舗一覧のID
        $diff_shop_id = array_diff($shop_list, $register_shop_id);
        $delete_shop = array_merge($diff_shop_id, $close_shop);

        // 1000件ごとにチャンクして削除処理
        collect($delete_shop)->chunk(1000)->each(function ($chunk) use (&$deleted_shops) {
            $shopsToDelete = Shop::whereIn('id', $chunk)->get();
            $deleted_shops = array_merge($deleted_shops, $shopsToDelete->toArray()); // 削除する店舗を保存
            $diff_shop_user = User::query()->withTrashed()->whereIn('shop_id', $chunk)->get();
            foreach ($diff_shop_user as $user) {
                $user->message()->detach();
                $user->manual()->detach();
            }
            User::query()->whereIn('shop_id', $chunk)->forceDelete();
            Shop::whereIn('id', $chunk)->delete();
        });

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
        if (!empty($deleted_shops)) {
            foreach ($deleted_shops as $s) {
                $this->info("shopID" . $s['id'] . " 店舗名" . $s['name']);
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

        $user->distributeMessages();

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
        } else {
            $employee_code = $brand_label . $shop_number;
        }

        return $employee_code;
    }

    public function import_crews($crews_data)
    {
        $ROLL_ID = 4;

        $undefind_shop = [];
        $undefind_user = [];
        $register_crews = [];
        $change_crew = [];
        $new_crew = [];
        $deleted_crew = [];

        // 配列をコレクションに変換
        $crews_data = collect($crews_data);

        $crews_data->chunk(20000)->each(function ($chunk) use ($ROLL_ID, &$undefind_shop, &$undefind_user, &$register_crews, &$change_crew, &$new_crew) {
            $bulkUpsertData = [];
            $existingPartCodes = Crew::whereIn('part_code', $chunk->pluck(13)->toArray())->pluck('part_code')->toArray();

            foreach ($chunk as $crew) {
                $org1 = Organization1::where('name', $crew[0])->first();
                $org1_id = $org1->id;

                // クルーの情報を更新
                $shop = Shop::query()
                    ->where('organization1_id', $org1_id)
                    ->where('shop_code', $crew[16])
                    ->first();
                if (empty($shop)) {
                    $undefind_shop[] = $crew;
                    continue;
                }

                $user = User::where('shop_id', $shop->id)->where('roll_id', $ROLL_ID)->first();
                if (empty($user)) {
                    $undefind_user[] = $crew;
                    continue;
                }

                $part_code = $crew[13];
                $name = $crew[14];
                $name_kana = $crew[15];
                $my_number = $crew[12];
                $birth_date = $this->parseDateTime($crew[18]);
                $register_date = $this->parseDateTime($crew[19]);

                // upsert用データ作成
                $bulkUpsertData[] = [
                    'part_code' => (string)$part_code,
                    'user_id' => $user->id,
                    'name' => $name,
                    'name_kana' => $name_kana,
                    'my_number' => (string)$my_number,
                    'birth_date' => $birth_date,
                    'register_date' => $register_date,
                ];

                $register_crews[] = $part_code;

                // 新規作成かどうかの判別
                if (in_array($part_code, $existingPartCodes)) {
                    $change_crew[] = $part_code; // 更新対象
                } else {
                    $new_crew[] = $part_code; // 新規作成対象
                }
            }

            // 1000件ごとにチャンクしてupsert
            collect($bulkUpsertData)->chunk(1000)->each(function ($data) {
                Crew::upsert($data->toArray(), ['part_code'], ['user_id', 'name', 'name_kana', 'my_number', 'birth_date', 'register_date']);
            });
        });

        // クルーの削除
        $crew_list = Crew::query()
            ->pluck('part_code')
            ->toArray();
        $diff_crew_id = array_diff($crew_list, $register_crews);

        // 1000件ごとにチャンクして削除処理
        collect($diff_crew_id)->chunk(1000)->each(function ($chunk) use (&$deleted_crew) {
            $crewsToDelete = Crew::whereIn('part_code', $chunk)->get();
            $deleted_crew = array_merge($deleted_crew, $crewsToDelete->toArray()); // 削除するクルーを保存
            Crew::whereIn('part_code', $chunk)->delete();
        });

        // ログ出力
        $this->info("---新しいクルー---");
        if (!empty($new_crew)) {
            foreach ($new_crew as $c) {
                $this->info("crewID " . $c);
            }
        }
        $this->info("---変更するクルー---");
        if (!empty($change_crew)) {
            foreach ($change_crew as $c) {
                $this->info("crewID " . $c);
            }
        }
        $this->info("---削除するクルー---");
        if (!empty($deleted_crew)) {
            foreach ($deleted_crew as $c) {
                $this->info("crewID" . $c['id'] . " クルー名" . $c['name']);
            }
        }
        $this->info("---店舗が見つからないエラー---");
        if (!empty($undefind_shop)) {
            foreach ($undefind_shop as $c) {
                $this->info("店舗コード" . $c[16] . " 店舗名" . $c[17]);
            }
        }
        $this->info("---店舗ユーザーが見つからないエラー---");
        if (!empty($undefind_user)) {
            foreach ($undefind_user as $c) {
                $this->info("店舗コード" . $c[16] . " 店舗名" . $c[17]);
            }
        }
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function formatShopName($name)
    {
        $trim_words = ["VS", "BB", "ＪＰ", "JO", "NIB", "YCP", "T", "TJ", "ＮＩＢ", "G"];
        $trimed_word = $name;
        // 正規表現のパターンを生成
        $pattern = '/' . implode('|', array_map('preg_quote', $trim_words)) . '/';

        $trimed_word = preg_replace($pattern, '', $name);

        return $trimed_word;
    }

    public function formatOrg5Name($name)
    {
        $trim_words = ["ブロック", "BL", "ON_", "ON"];
        $trimed_word = $name;
        // 正規表現のパターンを生成
        $pattern = '/' . implode('|', array_map('preg_quote', $trim_words)) . '/';

        $trimed_word = preg_replace($pattern, '', $name);

        return $trimed_word;
    }
}
