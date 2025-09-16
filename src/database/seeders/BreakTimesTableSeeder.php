<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;


class BreakTimesTableSeeder extends Seeder
{
    public function run(): void
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => '13:00:00',
                'end_at' => '14:00:00',
            ]);
        }
    }
}
