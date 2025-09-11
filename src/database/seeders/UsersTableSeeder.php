<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => '鈴木一郎',
                'email' => 'ichiro@seeder.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password1'),
            ],
            [
                'name' => '佐藤二郎',
                'email' => 'jiro@seeder.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password2'),
            ],
            [
                'name' => '北島三郎',
                'email' => 'saburo@seeder.com',
                'email_verified_at' => null,
                'password' => Hash::make('password3'),
            ],
        ]);
    }
}