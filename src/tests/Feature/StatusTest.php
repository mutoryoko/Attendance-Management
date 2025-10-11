<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

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

        $response->assertSee('<p class="status">勤務外</p>', false);
        $response->assertDontSee('<p class="status">出勤中</p>', false);
        $response->assertDontSee('<p class="status">休憩中</p>', false);
        $response->assertDontSee('<p class="status">退勤済</p>', false);
    }

    // 勤怠ステータスが出勤中
    public function test_show_status_when_user_is_working(): void
    {
        // テストの開始時に時間を固定する
        $fixedDate = Carbon::create(2025, 10, 11, 9, 0, 0);
        Carbon::setTestNow($fixedDate);

        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in_time' => now()->format('H:i:s'),
        ]);
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<p class="status">出勤中</p>', false);
        $response->assertDontSee('<p class="status">勤務外</p>', false);
        $response->assertDontSee('<p class="status">休憩中</p>', false);
        $response->assertDontSee('<p class="status">退勤済</p>', false);
    }

    // 勤怠ステータスが休憩中
    public function test_show_status_when_user_is_on_a_break(): void
    {
        // テスト開始時の時間を固定
        $fixedDate = Carbon::create(2025, 10, 11, 9, 0, 0);
        Carbon::setTestNow($fixedDate);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in_time' => now()->subHour()->format('H:i:s'),
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start_at' => now()->format('H:i:s'),
        ]);
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<p class="status">休憩中</p>', false);
        $response->assertDontSee('<p class="status">勤務外</p>', false);
        $response->assertDontSee('<p class="status">出勤中</p>', false);
        $response->assertDontSee('<p class="status">退勤済</p>', false);
    }

    // 勤怠ステータスが退勤済
    public function test_show_status_when_user_finishes_work(): void
    {
        $fixedDate = Carbon::create(2025, 10, 11, 9, 0, 0);
        Carbon::setTestNow($fixedDate);

        $user = User::factory()->create();
        Attendance::factory()->clockedOut()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<p class="status">退勤済</p>', false);
        $response->assertDontSee('<p class="status">勤務外</p>', false);
        $response->assertDontSee('<p class="status">出勤中</p>', false);
        $response->assertDontSee('<p class="status">休憩中</p>', false);
    }
}
