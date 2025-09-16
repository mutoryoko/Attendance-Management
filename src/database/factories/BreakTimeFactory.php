<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestBreakTime>
 */
class BreakTimeFactory extends Factory
{
    // @return array<string, mixed>
    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'start_at' => '13:00:00',
            'end_at' => '14:00:00',
        ];
    }
}
