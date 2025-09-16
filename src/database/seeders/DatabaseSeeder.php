<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUsersTableSeeder::class,
            UsersTableSeeder::class,
            AttendancesTableSeeder::class,
            BreakTimesTableSeeder::class,
        ]);
    }
}
