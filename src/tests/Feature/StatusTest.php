<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠ステータスが勤務外
    public function test_show_status_when_user_is_off_the_clock(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSeeText('勤務外');
    }

    // 勤怠ステータスが出勤中
    public function test_show_status_when_user_is_working(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_in'
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in_time' => now()->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertSeeText('出勤中');
    }

    // 勤怠ステータスが休憩中
    public function test_show_status_when_user_is_on_a_break(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_in'
        ]);

        $response = $this->post(route('attendance.store'), [
            'action' => 'break_start'
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'start_at' => now()->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertSeeText('休憩中');
    }

    // 勤怠ステータスが退勤済
    public function test_show_status_when_user_finishes_work(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_in'
        ]);

        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_out'
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_out_time' => now()->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertSeeText('退勤済');
    }
}
