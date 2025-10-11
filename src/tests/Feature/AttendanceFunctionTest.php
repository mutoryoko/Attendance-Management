<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFunctionTest extends TestCase
{
    use RefreshDatabase;

    // 出勤ボタンの表示し、出勤処理後、ステータスが「出勤中」になる
    public function test_display_attendance_button_and_user_can_clock_in(): void
    {
        $fixedDate = Carbon::create(2025, 10, 11, 9, 0, 0);
        Carbon::setTestNow($fixedDate);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<button class="clock__btn" type="submit" name="action" value="clock_in">出勤</button>', false);

        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_in'
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in_time' => now()->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<p class="status">出勤中</p>', false);
        $response->assertDontSee('<p class="status">勤務外</p>', false);
        $response->assertDontSee('<p class="status">休憩中</p>', false);
        $response->assertDontSee('<p class="status">退勤済</p>', false);
    }

    // 出勤は一日一回のみできる
    public function test_user_can_clock_in_once_a_day(): void
    {
        $fixedDate = Carbon::create(2025, 10, 11, 18, 0, 0);
        Carbon::setTestNow($fixedDate);

        $user = User::factory()->create();
        Attendance::factory()->clockedOut()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('お疲れ様でした。');
        $response->assertDontSee('<button class="clock__btn" type="submit" name="action" value="clock_in">出勤</button>', false);
    }

    // 出勤時刻を一覧画面で確認できる
    public function test_user_can_check_the_attendance_on_index_page(): void
    {
        $fixedDate = Carbon::create(2025, 10, 11, 9, 0, 0);
        Carbon::setTestNow($fixedDate);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_in'
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in_time' => now()->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $response->assertSee(Carbon::now()->format('m/d'));
        $response->assertSee(Carbon::now()->format('H:i'));
    }
}