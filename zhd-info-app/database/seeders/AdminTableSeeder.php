<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admin')->insert(
            [
                [
                    'name' => 'JP担当者',
                    'email' => 'test@email.co.jp',
                    'password' => Hash::make('password'),
                    'employee_code' => 1234567890
                ]
            ]
        );
    }
}
