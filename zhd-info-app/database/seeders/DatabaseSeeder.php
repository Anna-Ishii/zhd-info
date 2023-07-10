<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(Organization1Seeder::class); //業態
        $this->call(Organization2Seeder::class); //部署
        $this->call(Organization3Seeder::class); //DS
        $this->call(Organization4Seeder::class); //AR
        $this->call(Organization5Seeder::class); //BL
        $this->call(BrandSeeder::class); //ブランド
        $this->call(ShopSeeder::class); //店舗
        $this->call(RollTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(AdminTableSeeder::class);
        $this->call(MessageCategoriesTableSeeder::class);
        // $this->call(MessageTableSeeder::class);
        // $this->call(Message_RollTableSeeder::class);
        // $this->call(Message_UserTableSeeder::class);
        // $this->call(ManualsTableSeeder::class);
        // $this->call(ManualContentsTableSeeder::class);
        $this->call(ManualCategoriesTableSeeder::class);
        // $this->call(Manual_Organization1TableSeeder::class);
    }
}
