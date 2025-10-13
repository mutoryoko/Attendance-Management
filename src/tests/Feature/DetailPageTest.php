<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;


class DetailPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Attendance $attendance;

    // 共通処理
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => '鈴木一郎',
        ]);
        $this->attendance = Attendance::factory()->clockedOut()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-10-01',
            'clock_in_time' => '09:01:00',
            'clock_out_time' => '18:02:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_at' => '12:30:00',
            'end_at' => '13:30:00',
        ]);
        $this->actingAs($this->user);
    }

    // ユーザーの名前を表示
    public function test_show_user_name(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $response->assertSee('鈴木一郎');
    }

    // 日付が表示される
    public function test_show_work_date(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $response->assertSee('2025年');
        $response->assertSee('10月1日');
    }

    // 出勤・退勤時刻が表示される
    public function test_show_clock_in_and_clock_out_time(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $response->assertSee('09:01');
        $response->assertSee('18:02');
    }

    // 休憩時刻が表示される
    public function test_show_break_time(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $response->assertSee('12:30');
        $response->assertSee('13:30');
    }
}
