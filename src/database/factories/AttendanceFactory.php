<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    // @return array<string, mixed>
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'work_date' => fake()->date(),
            'clock_in_time' => null,
            'clock_out_time' => null,
            'total_break_minutes' => 0,
            'total_work_minutes' => 0,
        ];
    }
    /**
     * モデルファクトリの設定
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Attendance $attendance) {

            $workDate = Carbon::parse($attendance->work_date);

            $clockIn = $workDate->copy()->hour(rand(8, 10))->minute(rand(0, 59))->second(0);
            $clockOut = $workDate->copy()->hour(rand(17, 20))->minute(rand(0, 59))->second(0);

            $totalBreakMinutes = $attendance->total_break_minutes;

            $totalWorkMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;

            $attendance->clock_in_time = $clockIn;
            $attendance->clock_out_time = $clockOut;
            $attendance->total_work_minutes = $totalWorkMinutes > 0 ? $totalWorkMinutes : 0;
        });
    }
}
