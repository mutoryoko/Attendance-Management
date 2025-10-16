<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AdminUser;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestAttendance>
 */
class RequestAttendanceFactory extends Factory
{
    // @return array<string, mixed>
    public function definition(): array
    {
        return [
            'applier_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'approver_id' => null,
            'requested_work_start' => null,
            'requested_work_end' => null,
            'note' => '修正しました',
            'is_approved' => false,
        ];
    }
}
