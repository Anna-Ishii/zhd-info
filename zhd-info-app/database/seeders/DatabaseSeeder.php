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
        $this->call(Orgaization1Seeder::class); //業態
        $this->call(Orgaization2Seeder::class); //会社
        $this->call(Orgaization3Seeder::class); //DS
        $this->call(Orgaization4Seeder::class); //BL
        $this->call(ShopSeeder::class); //店舗
        $this->call(RollTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
        $this->call(MessageTableSeeder::class);
        $this->call(Message_RollTableSeeder::class);
        $this->call(Message_UserTableSeeder::class);
    }
}
