<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    // @return array<string, mixed>
    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'start_at' => '12:00:00',
            'end_at' => null,
        ];
    }
}
