<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Manual_Organization1TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('manual_organization1')->insert(
            [
                [
                    'manual_id' => 1,
                    'organization1' => 1,
                ]
            ]
        );
    }
}
