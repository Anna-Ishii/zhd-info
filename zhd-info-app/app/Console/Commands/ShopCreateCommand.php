<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Manual;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\Organization2;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Organization5;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ShopCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shop-create-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '店舗を作成するコマンドです。';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('start');
        $this->info('店舗を作成します');

        $organization1_list = Organization1::get();
        foreach ($organization1_list as $key => $value) {
            $this->info("業態番号: $value->id , 業態名: $value->name");
        }
        $organization1_id = $this->ask('業態番号を入力してください');

        $brand_list = Brand::get();
        foreach ($brand_list as $key => $value) {
            $this->info("ブランド番号: $value->id , ブランド名: $value->name");
        }
        $brand_id = $this->ask('ブランド番号を入力してください');

        $organization2_list = Organization2::get();
        foreach ($organization2_list as $key => $value) {
            $this->info("部署ID: $value->id , 部署名: $value->name");
        }
        $organization2_id = $this->ask('部署番号を入力してください');

        $organization3_list = Organization3::get();
        foreach ($organization3_list as $key => $value) {
            $this->info("DSID: $value->id , DS名: $value->name");
        }
        $organization3_id = $this->ask('DS番号を入力してください');

        $organization4_list = Organization4::get();
        foreach ($organization4_list as $key => $value) {
            $this->info("ARID: $value->id , AR名: $value->name");
        }
        $organization4_id = $this->ask('AR番号を入力してください');

        $organization5_list = Organization5::get();
        foreach ($organization5_list as $key => $value) {
            $this->info("BLID: $value->id , BL名: $value->name");
        }
        $organization5_id = $this->ask('BL番号を入力してください');

        $shop_name = $this->ask('店舗名を入力してください');
        $shop_number = $this->ask('店舗コードを入力してください(数字4桁)');
        $brand = Brand::find($brand_id);
        $shop_code = strtolower($brand->name). (string)$shop_number;

        $shop = new Shop();
        $shop->create([
            'name' => $shop_name,
            'shop_code' => $shop_code,
            'brand_id' => $brand_id,
            'organization5_id' => $organization5_id,
            'organization4_id' => $organization4_id,
            'organization3_id' => $organization3_id,
            'organization2_id' => $organization2_id,
            'organization1_id' => $organization1_id,
        ]);

        $this->info("店舗を作成しました");
    }
}
