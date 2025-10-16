<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // 今日を除く直近30日間分作成
        $endDate = Carbon::yesterday();
        $startDate = Carbon::yesterday()->subDays(29);
        $period = CarbonPeriod::create($startDate, $endDate);

        // 各ユーザーに対して、期間中の各日付のデータを作成
        foreach ($users as $user) {
            foreach ($period as $date) {
                // 80%の確率で出勤データを作成（欠勤日を作るため）
                if (rand(1, 100) <= 80) {
                    Attendance::factory()->clockedOut()->create([
                        'user_id' => $user->id,
                        'work_date' => $date->format('Y-m-d'),
                    ]);
                }
            }
        }
    }
}
