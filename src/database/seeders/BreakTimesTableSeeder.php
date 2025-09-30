<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;


class BreakTimesTableSeeder extends Seeder
{
    public function run(): void
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $totalBreakMinutes = 0;

            $break1_start = Carbon::parse('12:00:00');
            $break1_end = Carbon::parse('13:00:00');
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => $break1_start,
                'end_at' => $break1_end,
            ]);
            $totalBreakMinutes += $break1_end->diffInMinutes($break1_start);

            if (rand(1, 100) <= 40) {
                $break2_start = Carbon::parse('15:00:00');
                $break2_end = Carbon::parse('15:30:00');
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'start_at' => $break2_start,
                    'end_at' => $break2_end,
                ]);
                $totalBreakMinutes += $break2_end->diffInMinutes($break2_start);
            }

            $attendance->total_break_minutes = $totalBreakMinutes;
            $attendance->save();
        }
    }
}
