<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RequestAttendance;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestBreakTime>
 */
class RequestBreakTimeFactory extends Factory
{
    // @return array<string, mixed>
    public function definition(): array
    {
        return [
            'request_id' => RequestAttendance::factory(),
            'requested_break_start' => null,
            'requested_break_end' => null,
        ];
    }
}
