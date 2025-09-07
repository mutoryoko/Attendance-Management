<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminUsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $param = [
            'email' => 'admin@seeder.com',
            'password' => Hash::make('admin-pass'),
        ];
        AdminUser::create($param);
    }
}
