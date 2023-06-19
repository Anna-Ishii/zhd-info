<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 店舗
        DB::table('shops')->insert(
            [
                [
                    'name' => '札幌発寒店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '小樽築港店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '札幌本町店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '江別店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '帯広西店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '旭川永山店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '北見店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '釧路店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '宇都宮平松本町店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '真岡店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '郡山富田店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '新潟松崎店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '奥州水沢',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '北上藤沢',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大崎古川店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '石巻あゆみ野店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '仙台大和町店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '仙台中野店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => 'ひたちなか東石川店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '仙台泉店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '名取杜せきのした店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '長岡今朝白店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '水戸南店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '山形南館店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => 'ライフガーデン新浦安店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '竜ケ崎店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '八千代店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '稲毛海岸店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '千葉登戸店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '八柱店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '柏店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '王子台店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],

                [
                    'name' => '茂原店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '若松店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '市川北方店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '南流山店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鎌ヶ谷店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '千葉寺店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '我孫子店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '市原白金店',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '足立六町店',
                    'organization4_id' => '4',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '草加市立病院前店',
                    'organization4_id' => '4',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '新小岩店',
                    'organization4_id' => '4',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東門前店',
                    'organization4_id' => '4',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '昭島店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '羽村店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '小平店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東久留米店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '所沢店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '熊谷店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '朝霞台店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東大和店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '川越店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '花小金井店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => 'めじろ台店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '太田飯塚店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '入間店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '武蔵村山店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],

                [
                    'name' => '北野店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '西浦和店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '府中西原店',
                    'organization4_id' => '5',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '戸塚立場店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '川崎生田店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '武蔵小杉店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '本牧店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '南本宿店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => 'あざみ野店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '港南台店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '久里浜店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '成瀬店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],

                [
                    'name' => '青葉台店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '上和田店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '今宿店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '中山店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '瀬谷店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '荏田店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '戸塚平戸店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鶴見中央店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '北山田店',
                    'organization4_id' => '6',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '相模原店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '南大沢店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '甲府和戸通り店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '甲府昭和通り店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '茅ヶ崎店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '相模原城山店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '藤沢柄沢店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '甲府千塚店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '厚木林店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '足柄店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '厚木店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '四之宮店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '秦野店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大磯店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '湘南ＬＴ店',
                    'organization4_id' => '7',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '名護店',
                    'organization4_id' => '8',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '糸満店',
                    'organization4_id' => '8',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => 'ＡＢＬＯうるま店',
                    'organization4_id' => '8',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '北谷店',
                    'organization4_id' => '8',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '那覇真嘉比店',
                    'organization4_id' => '8',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '御殿場店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大仁店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '浜松西インター店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '掛川インター店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '清水鳥坂店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '富士インター店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '袋井店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '焼津店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '中田本町店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '富士吉田店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '藤枝店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '中吉田店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '浜松中沢店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '磐田店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => 'アクロスプラザ富士宮店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '静岡平和店',
                    'organization4_id' => '9',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '岐阜北方店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '岡崎三崎町店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '豊川店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '四日市羽津店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大治店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '一宮尾西店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '瀬戸店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '蒲郡店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '名古屋東茶屋店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '岐阜東店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '小牧店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鈴鹿店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大垣加賀野店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '三重川越店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '可児店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '豊橋小松店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '高浜店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東海店',
                    'organization4_id' => '10',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '飯田店',
                    'organization4_id' => '11',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '敦賀店',
                    'organization4_id' => '11',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '七尾店',
                    'organization4_id' => '11',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '坂井春江店',
                    'organization4_id' => '11',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '高山店',
                    'organization4_id' => '11',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '白山店',
                    'organization4_id' => '11',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '伏見店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '桂店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '山科店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '西九条店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '高槻店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '高槻西町店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '木幡店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '向日店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '近江大橋店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '亀岡店',
                    'organization4_id' => '12',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '茨木店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '佐太中町店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '芦屋店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '枚方招提店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '沢良宜店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '塚口店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '吹田岸部店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '荒牧店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '箕面店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '尼崎浜田',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '門真店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '池田店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '川西店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '日生中央店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '西宮店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '宝塚南店',
                    'organization4_id' => '13',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東住吉店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大東店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '生駒店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '押熊店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '橿原店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '葛城店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '香芝インター店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東大阪店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '平野店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '八尾店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '都島店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '横小路店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鶴見店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '生野店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '今米店',
                    'organization4_id' => '14',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '田辺新庄店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '泉北店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大仙店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '中百舌鳥店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '天王寺店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '岸和田店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大正店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '榎原店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '和泉店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '阪南店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '泉大津店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '福田店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '岩室店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '三宝町店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => 'フォレストモール岩出店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '松原店',
                    'organization4_id' => '15',
                    'organization3_id' => '2',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鈴蘭台',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東灘店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '六甲店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '播磨店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '離宮公園店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '学園都市店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '西明石店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '三田店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '三木店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '須磨店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '加古川店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '姫路中地店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '姫路辻井店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '有野インター店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '伊川谷店',
                    'organization4_id' => '16',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '東尾道店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '今治喜田村店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '新居浜西喜光地店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '岡山平島店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '青江店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '倉敷店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '津島西坂店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大野辻店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '蔵王店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '原尾島店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '野上店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '堀南店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '三原店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '駅家店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '津山店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '高松レインボーロード店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '徳島住吉店',
                    'organization4_id' => '17',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '府中店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '西条店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '段原店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '庚午店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '呉店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '皆実町店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '祇園新道店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '舟入南店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '海田店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '楽々園店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '五日市インター店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '広島楽々園店',
                    'organization4_id' => '18',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '出雲姫原店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '松江学園店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '米子米原店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '徳山店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '柳井店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '光店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '宇部店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '山口店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '防府店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '飯塚柏の森店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '中津店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '行橋店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '直方店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '下関店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '白萩店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '陣原店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '徳力店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '葛原店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '門司店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '別府北浜店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '萩原店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '折尾店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '森町店',
                    'organization4_id' => '20',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '福岡中尾店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '福岡石丸店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '久留米中央公園店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '二又瀬店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '月隈店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '佐賀西店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '筑紫野店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '文教町店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '福岡清水店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鳥栖店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '八女店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '大橋店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '宗像店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '和白店',
                    'organization4_id' => '21',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '八代店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '宮崎日向店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '薩摩川内店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鹿屋寿店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '霧島隼人店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '南延岡店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '都城店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鹿児島宇宿店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鹿児島ベイサイド店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '上水前寺店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '浮之城店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '谷山店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '南高江店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '熊本インター店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '鹿児島天文館店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '上熊本店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '熊本武蔵ケ丘店',
                    'organization4_id' => '22',
                    'organization3_id' => '3',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ]
            ]
        );
    }
}
