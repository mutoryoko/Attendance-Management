<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    // モデルのデフォルト（出勤中）
    public function definition(): array
    {
        $workDate = Carbon::parse($this->faker->dateTimeThisMonth()->format('Y-m-d'));
        $clockInTime = $workDate->copy()->hour(rand(9, 11))->minute(rand(0, 59))->second(0);

        return [
            'user_id' => User::factory(),
            'work_date' => $workDate->format('Y-m-d'),
            'clock_in_time' => $clockInTime->format('H:i:s'),
            'clock_out_time' => null,
            'total_break_minutes' => 0,
            'total_work_minutes' => null,
        ];
    }

    /**
     * モデルが「退勤済」状態
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function clockedOut()
    {
        return $this->state(function (array $attributes) {
            $clockIn = Carbon::parse($attributes['work_date'] . ' ' . $attributes['clock_in_time']);

            $clockOut = $clockIn->copy()->hour(rand(17, 20))->minute(rand(0, 59))->second(0);

            $totalBreakMinutes = $attributes['total_break_minutes'] ?? 0;
            $totalWorkMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;

            return [
                'clock_out_time' => $clockOut->format('H:i:s'),
                'total_work_minutes' => $totalWorkMinutes > 0 ? $totalWorkMinutes : 0,
            ];
        });
    }
}
