<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Message_RollTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('message_roll')->insert(
            [
                [
                    'message_id' => 1,
                    'roll_id' => 1,
                ]
            ]
        );
    }
}
