<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AttendanceFunctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        // $user = User::factory()->create();
        // $this->actingAs($user);

        // $response = $this->get(route('attendance.create'));

        // $response->assertStatus(200);
        // $response->assertSee('出勤');
    }
}
