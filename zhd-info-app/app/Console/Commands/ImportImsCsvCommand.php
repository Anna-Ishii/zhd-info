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
use App\Models\MessageShop;
use App\Models\ManualShop;
use App\Models\Organization1;
use App\Models\Organization2;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Organization5;
use App\Models\Shop;
use App\Models\User;
use App\Models\WowtalkShop;
use App\Models\Environment;
use App\Models\UsersRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Utils\SESMailer;

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
     * 業態コード
     */
    const ORG1_ID = [
        'JP'   => 1,
        'BB'   => 2,
        'TAG'  => 3,
        'HY'   => 4,
        'ON'   => 5,
        'AD'   => 6,
        'KN'   => 7,
        'SK'   => 8,
        'FUKU' => 9,
        'MC'   => 10,
        'N'    => 11,
        'Q'    => 12,
        'ST'   => 13,
        'C'    => 14,
        'HS'   => 15,
        'L'    => 16,
        'ZET'  => 17,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('memory_limit', '-1');
        \Log::info('start');
        $ims_log = new ImsSyncLog();
        $ims_log->import_at = new Carbon('now');
        $ims_log->save();

        $now = new Carbon('now');
        $now_str = $now->format("Ymd");

        // 開発環境でのテスト用日付オーバーライド
        // if (app()->environment('local', 'testing')) {
        //     $now_str = config('ims.test_date', $now_str);
        // }

        $organization_filename = "organization_{$now_str}.csv";
        $crews_filename = "crew_{$now_str}.csv";
        $directory = "IMS2/FR_BUSINESS/";
        $organization_path = $directory . $organization_filename;
        $crews_path = $directory . $crews_filename;
        \Log::info($organization_path);
        \Log::info($crews_path);

//デバック　ImportImsCsvCommand状態確認
$filename = __FILE__;
$timestamp = filemtime($filename);
$this->info("組織ファイル:{$organization_filename}");
echo "組織ファイル:{$organization_filename}\n";
$this->info("ファイル: " . basename($filename) );
echo "ファイル: " . basename($filename) . "\n";
$this->info("更新日時: " . date('Y-m-d H:i:s', $timestamp));
echo "更新日時: " . date('Y-m-d H:i:s', $timestamp) . "\n";
$this->info("実行ファイル: " . $filename . ":" .__LINE__);
echo "実行ファイル: " . $filename . ":" . __LINE__ . "\n";

        if (!Storage::disk('s3')->exists($organization_path)) {
            \Log::error("{$organization_path}が存在しません");
            \Log::info("end");
            exit();
        }

        if (!Storage::disk('s3')->exists($crews_path)) {
            \Log::error("{$crews_path}が存在しません");
            \Log::info("end");
            exit();
        }

        DB::beginTransaction();
        try {
            $loadtime = time();

            // 組織情報の取り込み
            try {
                \Log::info("{$organization_filename}ファイルを読み込みます");
                echo "{$organization_filename}ファイルを読み込みます\n";
                $shops_data = (new ShopsIMSImport)->toCollection($organization_path, 's3', \Maatwebsite\Excel\Excel::CSV);
                \Log::info("{$organization_filename}ファイル読み込み完了");
                echo "{$organization_filename}ファイル読み込み完了\n";
                \Log::info("読み込み時間: " . (time() - $loadtime) . "秒");
                echo "読み込み時間: " . (time() - $loadtime) . "秒\n";

                $this->import_shops($shops_data[0], $ims_log->id);

                unset($shops_data);
                $ims_log->import_department_at = new Carbon('now');
                $ims_log->import_department_error = false;
                $ims_log->save();
            } catch (\Throwable $th) {
                $ims_log->import_department_message = $th->getMessage();
                $ims_log->import_department_error = true;
                throw $th;
            }
            \Log::info("組織情報の取り込み完了");

            $loadtime = time();
            // クルーの取り込み
            try {
                \Log::info("{$crews_filename}ファイルを読み込みます");
                echo "{$crews_filename}ファイルを読み込みます\n";
                $crews_data = [];
                (new CrewsIMSImport($crews_data))->import($crews_path, 's3', \Maatwebsite\Excel\Excel::CSV);
                \Log::info("{$crews_filename}ファイル読み込み完了");
                echo "{$crews_filename}ファイル読み込み完了\n";
                \Log::info("読み込み時間: " . (time() - $loadtime) . "秒");
                echo "読み込み時間: " . (time() - $loadtime) . "秒\n";
                $this->import_crews($crews_data, $ims_log->id);
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
            \Log::error("ジョブ失敗: $th_msg", ['trace' => $th->getTraceAsString()]);
            throw $th;
        }
        $ims_log->save();

        // メール送信
        $mailer = new SESMailer();
        $fromName = '業連・動画配信ツール';
        $to = ['yotake@nssx.co.jp', 'skomine@nssx.co.jp'];
        $subject = 'IMSデータ取り込み';
        $attachments = [];
        $types = ['shops'];
        foreach ($types as $type) {
            $path = storage_path("app/imscsv/{$type}_{$ims_log->id}.csv");
            if (file_exists($path)) {
                $attachments[] = $path;
            }
        }
        $messageContent = empty($attachments)
            ? '取り込んだCSVに前回との差分はありませんでした。'
            : '取り込んだCSVに前回との差分がありました。ご確認お願いします。';
        $mailer->sendEmail($fromName, $to, $subject, $messageContent, $attachments);

        \Log::info("end");
    }

    private function import_shops($shops_data, $ims_log_id)
    {
        \Log::info("店舗更新開始");
        echo "店舗更新開始\n";
        $new_shop = []; // 新店舗を格納する配列
        $close_shop = []; // 削除する店舗を格納する配列
        $shop_list = Shop::query()->pluck('id')->toArray();
        $today = Carbon::now();
        $register_shop_id = [];
        $output = [];
        $start = time();

        $environment = Environment::where('command_name', $this->signature)->where('contents', 'prod')->select('id')->first();

        foreach ($shops_data as $index => $shop) {
            $organization1 = Organization1::where('name', $shop[0])->first();
            if (!$organization1) {
                \Log::error("組織1 '{$shop[0]}' が見つかりません");
                continue;
            }
            $organization1_id = $organization1->id;

            $close_date = $this->parseDateTime($shop[35]);
            // 閉店の店舗
            if (empty($close_date) || $today->gte($close_date)) {
                $close_shop[] = Shop::where('organization1_id', $organization1_id)
                    ->where('shop_code', $shop[3])
                    ->value('id');
                continue;
            }
            // 営業部、DS、AR、BLの登録
            $organization2_id = null; // 営業部
            $organization3_id = null; // DS
            $organization4_id = null; // AR
            $organization5_id = null; // BL
            // DM
            $DM_id = null;
            $DM_name = null;
            $DM_email = null;
            // BM
            $BM_id = null;
            $BM_name = null;
            $BM_email = null;
            // AM
            $AM_id = null;
            $AM_name = null;
            $AM_email = null;
            // 4th
            $forth_id = null;
            $forth_name = null;
            $forth_email = null;
            // 5th
            $fifth_id = null;
            $fifth_name = null;
            $fifth_email = null;

            dump('index:' . $index . ' organization1:' . $organization1);

            for ($i = 5; $i < 35; $i += 6) {
                $organization_name = $shop[$i + 1];
                $order_no = (int)$shop[$i + 2];

                $target_id = $shop[$i + 3];
                $target_name = $shop[$i + 4];
                $target_email = $shop[$i + 5];

                if ($shop[$i] == "営業部") {
                    $organization2_id = Organization2::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->value('id');
                    // 初回のみ
                    Organization2::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->update([
                            'order_no' => $order_no,
                            'display_name' => $organization_name,
                            'organization1_id' => $organization1_id
                        ]);

                    if (is_null($organization2_id)) {
                        if (!empty($organization_name)) {
                            $organization2 = Organization2::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $organization_name,
                                'organization1_id' => $organization1_id
                            ]);
                            $organization2_id = $organization2->id;
                        }
                    }

                    if (in_array($organization1_id, [
                        self::ORG1_ID['JP'],
                        self::ORG1_ID['ON'],
                        self::ORG1_ID['HY'],
                    ], true)) {
                        $DM_id    = $target_id;
                        $DM_name  = $target_name;
                        $DM_email = $target_email;
                    }
                }

                if ($shop[$i] == "DS") {
                    $organization3_id = Organization3::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->value('id');
                    // 初回のみ
                    Organization3::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->update([
                            'order_no' => $order_no,
                            'display_name' => $organization_name,
                            'organization1_id' => $organization1_id
                        ]);

                    if (is_null($organization3_id)) {
                        if (!empty($organization_name)) {
                            $organization3 = Organization3::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $organization_name,
                                'organization1_id' => $organization1_id
                            ]);
                            $organization3_id = $organization3->id;
                        }
                    }

                    if (in_array($organization1_id, [
                        self::ORG1_ID['BB'],
                        self::ORG1_ID['TAG'],
                        self::ORG1_ID['SK'],
                        self::ORG1_ID['HS'],
                        self::ORG1_ID['C'],
                        self::ORG1_ID['AD'],
                    ], true)) {
                        $DM_id = $target_id;
                        $DM_name = $target_name;
                        $DM_email = $target_email;
                    }
                }

                if ($shop[$i] == "AR") {
                    $organization4_id = Organization4::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->value('id');
                    // 初回のみ
                    Organization4::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->update([
                            'order_no' => $order_no,
                            'display_name' => $organization_name,
                            'organization1_id' => $organization1_id
                        ]);

                    if (is_null($organization4_id)) {
                        if (!empty($organization_name)) {
                            $organization4 = Organization4::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $organization_name,
                                'organization1_id' => $organization1_id
                            ]);
                            $organization4_id = $organization4->id;
                        }
                    }

                    if (in_array($organization1_id, [
                        self::ORG1_ID['SK'],
                    ], true)) {
                        $BM_id = $target_id;
                        $BM_name = $target_name;
                        $BM_email = $target_email;
                    }

                    if (in_array($organization1_id, [
                        self::ORG1_ID['BB'],
                        self::ORG1_ID['HY'],
                        self::ORG1_ID['JP'],
                        self::ORG1_ID['ON'],
                        self::ORG1_ID['TAG'],
                    ], true)) {
                        $forth_id = $target_id;
                        $forth_name = $target_name;
                        $forth_email = $target_email;
                    }
                }

                if ($shop[$i] == "BL") {
                    $organization5_id = Organization5::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->value('id');
                    // 初回のみ
                    Organization5::where('name', $organization_name)
                        ->where('organization1_id', $organization1_id)
                        ->update([
                            'order_no' => $order_no,
                            'display_name' => $this->formatOrg5Name($organization_name),
                            'organization1_id' => $organization1_id
                        ]);
                    if (is_null($organization5_id)) {
                        if (!empty($organization_name)) {
                            $organization5 = Organization5::create([
                                "name" => $organization_name,
                                "order_no" => $order_no,
                                'display_name' => $this->formatOrg5Name($organization_name),
                                'organization1_id' => $organization1_id
                            ]);
                            $organization5_id = $organization5->id;
                        }
                    }

                    if (in_array($organization1_id, [
                        self::ORG1_ID['BB'],
                        self::ORG1_ID['JP'],
                        self::ORG1_ID['ON'],
                        self::ORG1_ID['HY'],
                        self::ORG1_ID['TAG'],
                        self::ORG1_ID['SK'],
                        self::ORG1_ID['HS'],
                        self::ORG1_ID['C'],
                        self::ORG1_ID['AD'],
                    ], true)) {
                        $BM_id = $target_id;
                        $BM_name = $target_name;
                        $BM_email = $target_email;
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
                    'brand_id' => $brand_id,
                    'shop_code' => $shop_code
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

            // users_rolesテーブルのデータを更新
            if (!empty($environment) && !empty($shop)) {
                $users_roles_model = $this->create_users_roles($shop, $shop_code, $shop_name, $DM_id, $DM_name, $DM_email, $BM_id, $BM_name, $BM_email, $AM_id, $AM_name, $AM_email, $forth_id, $forth_name, $forth_email, $fifth_id, $fifth_name, $fifth_email);

                if ($users_roles_model->wasRecentlyCreated) {
                    $users_roles_insert[] = $users_roles_model;
                } else {
                    $users_roles_update[] = $users_roles_model;
                }
            }

            // 新規店舗の場合
            if (is_null($shop_id)) {
                $new_shop[] = $shop;
            }

            // 店舗の情報が更新された時
            if ($shop->wasChanged()) {
                $change_shop[] = $shop;
            }

            $register_shop_id[] = $shop->id;
        }
        \Log::info("組織データ取り込み完了: 処理時間: " . (time() - $start) . "秒");
        echo "組織データ取り込み完了: 処理時間: " . (time() - $start) . "秒" . "\n";

        // 初回のみパッチ
        DB::insert(
            'insert into message_organization (
                with m_o5 as (
                select distinct m_u.message_id as message_id, s.organization1_id as organization1_id, s.organization5_id as organization5_id from message_user as m_u
                left join users as u on m_u.user_id = u.id
                left join shops as s on u.shop_id = s.id
                inner join organization5 as o5 on s.organization5_id = o5.id
                )
                select message_id, organization1_id, NULL as organization2_id, NULL as organization3_id, NULL as organization4_id, organization5_id, ? as created_at, ? as updated_at from m_o5
                );',
            [new Carbon('now'), new Carbon('now')]
        );

        DB::delete(
            'DELETE FROM message_organization WHERE organization5_id IN (
                select id from organization5 where id not in (
                select distinct organization5_id from shops where organization5_id is not null)
            )'
        );

        // 新店舗のユーザー作成
        foreach ($new_shop as $n_s) {
            $this->create_user($n_s);
        }

        // wowtalk_shopテーブルのデータを更新
        if (!empty($environment) && !empty($new_shop)) {
            foreach ($new_shop as $n_s) {
                $this->create_wowtalk_shop($n_s);
            }
        }

        // 削除する店舗一覧のIDは物理削除しないように修正
        $diff_shop_id = array_diff($shop_list, $register_shop_id);
        // echo "diff_shop_id: " . implode(',', $diff_shop_id) . "\n";
        $diff_shop = Shop::whereIn('id', $diff_shop_id)->pluck('id')->toArray();
        $diff_shop_info = Shop::whereIn('id', $diff_shop_id)->get(); // ログ出力用
        $delete_shop = array_merge($diff_shop, $close_shop);
        $diff_shop_user = User::query()->withTrashed()->whereIn('shop_id', $delete_shop)->get();
        foreach ($diff_shop_user as $key => $user) {
            $user->message()->detach();
            // message_shopのshop_idを削除
            MessageShop::where('shop_id', $user->shop_id)->delete();

            $user->manual()->detach();
            // manual_shopのshop_idを削除
            ManualShop::where('shop_id', $user->shop_id)->delete();
        }
        User::query()->whereIn('shop_id', $delete_shop)->forceDelete();
        Shop::whereIn('id', $delete_shop)->delete();

        // wowtalk_shopテーブルのデータを削除
        if (!empty($environment) && !empty($delete_shop)) {
            WowtalkShop::whereIn('shop_id', $delete_shop)->delete();
        }

        // ログ出力
        // \Log::info("---新しい店舗---");
        // echo "---新しい店舗---\n";
        if (!empty($new_shop)) {
            foreach ($new_shop as $s) {
                $output[] = $this->formatShopCsvRow($s, 'insert');
                // \Log::info("shopID" . $s->id . " 店舗名" . $s->name);
            }
        }
        // \Log::info("---変更する店舗---");
        // echo "---変更する店舗---\n";
        if (!empty($change_shop)) {
            foreach ($change_shop as $s) {
                $output[] = $this->formatShopCsvRow($s, 'update');
                // \Log::info("shopID" . $s->id . " 店舗名" . $s->name);
            }
        }
        // \Log::info("---削除する店舗---");
        // echo "---削除する店舗---\n";
        if (!empty($diff_shop_info)) {
            foreach ($diff_shop_info as $s) {
                $output[] = $this->formatShopCsvRow($s, 'delete');
                // $this->info("shopID" . $s->id . " 店舗名" . $s->name);
            }
        }

        \Log::info("---店舗ログ出力終了---");
        echo "---店舗ログ出力終了---\n";

        if (count($output)) {
            $header = '"差分種別","ID","業態名","業態コード","ブランド名","ブランドコード","店舗名","表示名","店舗コード","組織名2","組織コード2","組織名3","組織コード3","組織名4","組織コード4","組織名5","組織コード5","データ作成日時","データ更新日時",';
            Storage::disk('local')->put('imscsv/shops_' . $ims_log_id . '.csv', mb_convert_encoding($header . "\r\n" . implode("\r\n", $output), "SJIS-win", "UTF-8"));
        }
    }

    // users_rolesテーブルのデータを更新
    private function create_users_roles($shop, $shop_code, $shop_name, $DM_id, $DM_name, $DM_email, $BM_id, $BM_name, $BM_email, $AM_id, $AM_name, $AM_email, $forth_id, $forth_name, $forth_email, $fifth_id, $fifth_name, $fifth_email)
    {
        // users_rolesテーブルのデータを更新
        $model = UsersRole::updateOrCreate(
            [
                'shop_id' => $shop->id,
                'shop_code' => $shop_code
            ],
            [
                'shop_name' => $shop_name,
                'DM_id' => $DM_id,
                'DM_name' => $DM_name,
                'DM_email' => $DM_email,
                'DM_view_notification' => false,
                'BM_id' => $BM_id,
                'BM_name' => $BM_name,
                'BM_email' => $BM_email,
                'BM_view_notification' => false,
                'AM_id' => $AM_id,
                'AM_name' => $AM_name,
                'AM_email' => $AM_email,
                'AM_view_notification' => false,
                '4th_id' => $forth_id,
                '4th_name' => $forth_name,
                '4th_email' => $forth_email,
                '4th_view_notification' => false,
                '5th_id' => $fifth_id,
                '5th_name' => $fifth_name,
                '5th_email' => $fifth_email,
                '5th_view_notification' => false,
            ]
        );

        return $model;
    }

    // ユーザー作成
    private function create_user($shop)
    {
        \Log::info("ユーザー作成開始");
        echo "ユーザー作成開始\n";
        $start = time();
        // 店長ロール
        $ROLL_ID = 4;
        $employee_code = $this->shopid2employeecode($shop);
        // \Log::info("employee_code: {$employee_code}");
        $start = time();
        $user = User::create([
            'name' => $shop->name,
            'belong_label' => $shop->name,
            'shop_id' => $shop->id,
            'employee_code' => $employee_code,
            'password' => Hash::make($employee_code),
            'email' => '',
            'roll_id' => $ROLL_ID,
        ]);
        // \Log::info("name:{$user->name}");
        // \Log::info("belong_label:{$user->belong_label}");
        // \Log::info("shop_id:{$user->shop_id}");
        // \Log::info("employee_code:{$user->employee_code}");
        // \Log::info("password:{$user->password}");
        // \Log::info("email:{$user->email}");
        // \Log::info("roll_id:{$user->roll_id}");

        // $this->info("userオブジェクト定義完了:  処理時間: " . (time() - $start) . "秒");
        $start = time();
        \Log::info("メッセージ配信開始");
        echo "メッセージ配信開始\n";
        $user->distributeMessages();
        \Log::info("メッセージ配信完了: 処理時間: " . (time() - $start) . "秒");
        echo "メッセージ配信完了: 処理時間: " . (time() - $start) . "秒\n";

        // 該当のマニュアルを登録
        // $this->info("マニュアル登録開始");
        $manual_data = [];
        $shop = Shop::find($user->shop_id);

        $manuals = [];
        if (isset($shop->brand_id)) {
            $manuals = ManualShop::query()
                ->select('manual_shop.manual_id as id')
                ->where('manual_shop.selected_flg', 'all')
                ->where('manual_shop.brand_id', $shop->brand_id)
                ->cursor();
        }

        $manual_data = [];
        foreach ($manuals as $manual) {
            $manual_data[$manual['id']] = ['shop_id' => $shop->id];
        }
        // $this->info("マニュアル登録完了");

        // manual_shopテーブルにデータをインサート
        // $this->info("manual_shopデータインサート開始");
        foreach ($manual_data as $manual_id => $data) {
            ManualShop::insert([
                'manual_id' => $manual_id,
                'shop_id' => $data['shop_id'],
                'selected_flg' => 'all',
                'created_at' => now(),
                'updated_at' => now(),
                'brand_id' => $shop->brand_id,
            ]);
        }
        // $this->info("manual_shopデータインサート完了");
        $user->manual()->sync($manual_data);
        \Log::info("ユーザー作成完了");
        echo "ユーザー作成完了\n";
    }

    private function shopid2employeecode(Shop $shop)
    {
        \Log::info("shopid2employeecode実行");
        echo "shopid2employeecode実行\n";
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

    // wowtalk_shopテーブルのデータを更新
    private function create_wowtalk_shop($shop)
    {
        \Log::info("create_wowtalk_shop実行");
        echo "create_wowtalk_shop実行\n";
        $data = $this->wowtalkid2shopcode($shop);
        $chunkSize = 300;
        // データをチャンクして挿入
        collect($data)->chunk($chunkSize)->each(function ($chunk) {
            DB::table('wowtalk_shops')->insert($chunk->toArray());
        });
    }

    // wowtalkIDを作成
    private function wowtalkid2shopcode(Shop $shop)
    {
        \Log::info("wowtalkid2shopcode実行");
        echo "wowtalkid2shopcode実行\n";
        $data = [];

        // JP
        if ($shop->organization1_id == 1) {
            $data[] = [
                'shop_id'                => $shop->id,
                'shop_code'              => $shop->shop_code,
                'shop_name'              => $shop->name,
                'wowtalk1_id'            => '320010' . (string)$shop->shop_code, // 「320010」+「店舗コード4桁」
                'notification_target1'   => false,                               // デフォルト[false]
                'business_notification1' => false,                               // デフォルト[false]
                'wowtalk2_id'            => '320000' . (string)$shop->shop_code, // 「320000」+「店舗コード4桁」
                'notification_target2'   => false,                               // デフォルト[false]
                'business_notification2' => false,                               // デフォルト[false]
            ];

            // ON
        } elseif ($shop->organization1_id == 5) {
            $data[] = [
                'shop_id'                => $shop->id,
                'shop_code'              => $shop->shop_code,
                'shop_name'              => $shop->name,
                'wowtalk1_id'            => '2g0000' . (string)$shop->shop_code, // 「2g0000」+「店舗コード4桁」
                'notification_target1'   => false,                               // デフォルトはなし
                'business_notification1' => false,                               // デフォルトはなし
                'wowtalk2_id'            => '3g0000' . (string)$shop->shop_code, // 「3g0000」+「店舗コード4桁」
                'notification_target2'   => false,                               // デフォルトはなし
                'business_notification2' => false,                               // デフォルトはなし
                'created_at'             => now(),
            ];

            // TAG
        } elseif ($shop->organization1_id == 3) {
            $data[] = [
                'shop_id'                => $shop->id,
                'shop_code'              => $shop->shop_code,
                'shop_name'              => $shop->name,
                'wowtalk1_id'            => 'tag' . (string)$shop->shop_code, // 「tag」+「店舗コード4桁」
                'notification_target1'   => false,                            // デフォルト[false]
                'business_notification1' => false,                            // デフォルト[false]
                'wowtalk2_id'            => '',                               // なし
                'notification_target2'   => false,                            // デフォルト[false]
                'business_notification2' => false,                            // デフォルト[false]
                'created_at'             => now(),
            ];

            // HY
        } elseif ($shop->organization1_id == 4) {
            $data[] = [
                'shop_id'                => $shop->id,
                'shop_code'              => $shop->shop_code,
                'shop_name'              => $shop->name,
                'wowtalk1_id'            => '340000' . (string)$shop->shop_code, // 「340000」+「店舗コード4桁」
                'notification_target1'   => false,                               // デフォルト[false]
                'business_notification1' => false,                               // デフォルト[false]
                'wowtalk2_id'            => '',                                  // なし
                'notification_target2'   => false,                               // デフォルト[false]
                'business_notification2' => false,                               // デフォルト[false]
                'created_at'             => now(),
            ];

            // BB
        } elseif ($shop->organization1_id == 2) {
            $data[] = [
                'shop_id'                => $shop->id,
                'shop_code'              => $shop->shop_code,
                'shop_name'              => $shop->name,
                'wowtalk1_id'            => '30300' . (string)$shop->shop_code . '0', // 「30300」+「店舗コード4桁」+「0」
                'notification_target1'   => false,                                    // デフォルト[false]
                'business_notification1' => false,                                    // デフォルト[false]
                'wowtalk2_id'            => '',                                       // なし
                'notification_target2'   => false,                                    // デフォルト[false]
                'business_notification2' => false,                                    // デフォルト[false]
                'created_at'             => now(),
            ];

            // SK
        } elseif ($shop->organization1_id == 8) {
            $data[] = [
                'shop_id'                => $shop->id,
                'shop_code'              => $shop->shop_code,
                'shop_name'              => $shop->name,
                'wowtalk1_id'            => '',               // なし
                'notification_target1'   => false,            // デフォルト[false]
                'business_notification1' => false,            // デフォルト[false]
                'wowtalk2_id'            => '',               // なし
                'notification_target2'   => false,            // デフォルト[false]
                'business_notification2' => false,            // デフォルト[false]
                'created_at'             => now(),
            ];
        }

        return $data;
    }

    public function import_crews($crews_data, $ims_log_id)
    {
        \Log::info("import_crews実行");
        echo "import_crews実行\n";
        $ROLL_ID = 4;

        $undefind_shop = [];
        $undefind_user = [];
        $register_crews = [];
        $change_crew = [];
        $new_crew = [];
        $deleted_crew = [];

        $crew_output = [];

        // 配列をコレクションに変換
        $crews_data = collect($crews_data);

        // $crews_data->chunk(20000)->each(function ($chunk) use ($ROLL_ID, &$undefind_shop, &$undefind_user, &$register_crews, &$change_crew, &$new_crew) {
        // organization1を取得
        $organization1Map = Organization1::all()->keyBy('name');

        // shopを取得
        $shopMap = Shop::all()->keyBy(function ($shop) {
            return $shop->organization1_id . '_' . $shop->shop_code;
        });

        // userを取得
        $userMap = User::where('roll_id', $ROLL_ID)->get()->keyBy('shop_id');

        $this->info("userを取得");
        echo "userを取得\n";
        $crews_data->chunk(20000)->each(function ($chunk) use ($ROLL_ID, &$undefind_shop, &$undefind_user, &$register_crews, &$change_crew, &$new_crew, $organization1Map, $shopMap, $userMap) {
            $bulkUpsertData = [];
            $existingPartCodes = Crew::whereIn('part_code', $chunk->pluck(13)->toArray())->pluck('part_code')->toArray();

            foreach ($chunk as $crew) {
                // $org1 = Organization1::where('name', $crew[0])->first();
                $org1 = $organization1Map[$crew[0]] ?? null;
                $org1_id = $org1->id;

                // クルーの情報を更新
                // $shop = Shop::query()
                //     ->where('organization1_id', $org1_id)
                //     ->where('shop_code', $crew[16])
                //     ->first();
                // if (empty($shop)) {
                $shopKey = $org1_id . '_' . $crew[16];
                $shop = $shopMap[$shopKey] ?? null;
                if (empty($shop) || $shop->organization1_id !== $org1->id) {
                    $undefind_shop[] = $crew;
                    continue;
                }

                // $user = User::where('shop_id', $shop->id)->where('roll_id', $ROLL_ID)->first();
                $user = $userMap[$shop->id] ?? null;
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

        // コメントアウトされていた部分
        // クルーの削除は物理削除しないように修正
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
        // \Log::info("---新しいクルー---");
        // echo "---新しいクルー---\n";
        if (!empty($new_crew)) {
            foreach ($new_crew as $c) {
                // \Log::info("crewID " . $c);
                $crew_output[] = $this->formatCrewsCsvRow($c, 'insert');
            }
        }
        // \Log::info("---変更するクルー---");
        // echo "---変更するクルー---\n";
        if (!empty($change_crew)) {
            foreach ($change_crew as $c) {
                // \Log::info("crewID " . $c);
                $crew_output[] = $this->formatCrewsCsvRow($c, 'update');
            }
        }
        // \Log::info("---削除するクルー---");
        // echo "---削除するクルー---\n";
        if (!empty($deleted_crew)) {
            foreach ($deleted_crew as $c) {
                $crew_output[] = $this->formatCrewsCsvRow($c['id'], 'delete');
                // \Log::info("crewID" . $c['id'] . " クルー名" . $c['name']);
            }
        }
        // \Log::info("---店舗が見つからないエラー---");
        // echo "---店舗が見つからないエラー---\n";
        // if (!empty($undefind_shop)) {
        //     foreach ($undefind_shop as $c) {
        //         \Log::info("店舗コード" . $c[16] . " 店舗名" . $c[17]);
        //     }
        // }
        // \Log::info("---店舗ユーザーが見つからないエラー---");
        // echo "---店舗ユーザーが見つからないエラー---\n";
        // if (!empty($undefind_user)) {
        //     foreach ($undefind_user as $c) {
        //         Log::info("店舗コード" . $c[16] . " 店舗名" . $c[17]);
        //     }
        // }

        if (count($crew_output)) {
            $header = '"action","id"';
            Storage::disk('local')->put('imscsv/crews_' . $ims_log_id . '.csv', mb_convert_encoding($header . "\r\n" . implode("\r\n", $crew_output), "SJIS-win", "UTF-8"));
        }
    }

    private function parseDateTime($datetime)
    {
        // $this->info("parseDateTime実行");
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function formatShopName($name)
    {
        $trim_words = ["VS", "BB", "ＪＰ", "JO", "NIB", "YCP", "T", "TJ", "ＮＩＢ", "G"];
        $trimed_word = $name;
        $pattern = '/' . implode('|', array_map('preg_quote', $trim_words)) . '/';
        $trimed_word = preg_replace($pattern, '', $name);
        return $trimed_word;
    }

    public function formatOrg5Name($name)
    {
        $trim_words = ["ブロック", "BL", "ON_", "ON"];
        $trimed_word = $name;
        $pattern = '/' . implode('|', array_map('preg_quote', $trim_words)) . '/';
        $trimed_word = preg_replace($pattern, '', $name);
        return $trimed_word;
    }

    private function formatShopCsvRow(Shop $shop, string $action): string
    {
        // organization1のnameを取得
        $organization1_name = '';
        if ($shop->organization1_id) {
            $org1 = Organization1::find($shop->organization1_id);
            $organization1_name = $org1 ? $org1->name : '';
        }
        // organization2のnameを取得
        $organization2_name = '';
        if ($shop->organization2_id) {
            $org2 = Organization2::find($shop->organization2_id);
            $organization2_name = $org2 ? $org2->name : '';
        }
        // organization3のnameを取得
        $organization3_name = '';
        if ($shop->organization3_id) {
            $org3 = Organization3::find($shop->organization3_id);
            $organization3_name = $org3 ? $org3->name : '';
        }
        // organization4のnameを取得
        $organization4_name = '';
        if ($shop->organization4_id) {
            $org4 = Organization4::find($shop->organization4_id);
            $organization4_name = $org4 ? $org4->name : '';
        }
        // organization5のnameを取得
        $organization5_name = '';
        if ($shop->organization5_id) {
            $org5 = Organization5::find($shop->organization5_id);
            $organization5_name = $org5 ? $org5->name : '';
        }
        // brandのnameを取得
        $brand_name = '';
        if ($shop->brand_id) {
            $brands = Brand::find($shop->brand_id);
            $brand_name = $brands ? $brands->name : '';
        }

        return implode(',', [
            $action,
            $shop->id,
            $organization1_name,
            $shop->organization1_id,
            $brand_name,
            $shop->brand_id,
            $shop->name,
            $shop->display_name,
            $shop->shop_code,
            $organization2_name,
            $shop->organization2_id,
            $organization3_name,
            $shop->organization3_id,
            $organization4_name,
            $shop->organization4_id,
            $organization5_name,
            $shop->organization5_id,
            $shop->created_at,
            $shop->updated_at,
        ]);
    }

    private function formatCrewsCsvRow(string $id, string $action)
    {
        return implode(',', [
            $action,
            $id,
        ]);
    }
}
