<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class BreakFunctionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \Illuminate\Support\Carbon
     */
    protected $fixedDate;

    private User $user;
    private Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        //　各テストの共通処理（現在時刻の固定）
        $this->fixedDate = Carbon::create(2025, 10, 11, 9, 0, 0);
        Carbon::setTestNow($this->fixedDate);

        // 出勤中のユーザーがログインする
        $this->user = User::factory()->create();
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in_time' => now()->format('H:i'),
        ]);
        $this->actingAs($this->user);
    }

    // 休憩入ボタンが表示され、休憩入処理後、ステータスが休憩中になる
    public function test_display_break_start_button_and_function_properly(): void
    {
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<button class="break__btn" type="submit" name="action" value="break_start">休憩入</button>', false);

        Carbon::setTestNow(Carbon::create(2025, 10, 11, 12, 0, 0));
        $breakStartTime = now();
        $response = $this->post(route('attendance.store'), [
            'action' => 'break_start',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $this->attendance->id,
            'start_at' => $breakStartTime->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<p class="status">休憩中</p>', false);
        $response->assertDontSee('<p class="status">勤務外</p>', false);
        $response->assertDontSee('<p class="status">出勤中</p>', false);
        $response->assertDontSee('<p class="status">退勤済</p>', false);
    }

    // 一日に何回でも休憩できる（休憩入）
    public function test_user_can_take_break_start_several_times_a_day(): void
    {
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.store'), [
            'action' => 'break_start',
        ]);
        $response = $this->post(route('attendance.store'), [
            'action' => 'break_end',
        ]);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<button class="break__btn" type="submit" name="action" value="break_start">休憩入</button>', false);
        $response->assertDontSee('<button class="break__btn" type="submit" name="action" value="break_end">休憩戻</button>', false);
    }

    // 休憩戻ボタンが表示され、休憩戻処理後、ステータスが出勤中になる
    public function test_display_break_end_button_and_function_properly(): void
    {
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        Carbon::setTestNow(Carbon::create(2025, 10, 11, 12, 0, 0));
        $breakStartTime = now();
        $response = $this->post(route('attendance.store'), [
            'action' => 'break_start',
        ]);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<button class="break__btn" type="submit" name="action" value="break_end">休憩戻</button>', false);

        Carbon::setTestNow(Carbon::create(2025, 10, 11, 13, 0, 0));
        $breakEndTime = now();
        $response = $this->post(route('attendance.store'), [
            'action' => 'break_end',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $this->attendance->id,
            'start_at' => $breakStartTime->format('H:i:s'),
            'end_at' => $breakEndTime->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<p class="status">出勤中</p>', false);
        $response->assertDontSee('<p class="status">勤務外</p>', false);
        $response->assertDontSee('<p class="status">休憩中</p>', false);
        $response->assertDontSee('<p class="status">退勤済</p>', false);
    }

    // 一日に何回でも休憩できる（休憩戻）
    public function test_user_can_take_break_end_several_times_a_day(): void
    {
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.store'), [
            'action' => 'break_start',
        ]);

        $response = $this->post(route('attendance.store'), [
            'action' => 'break_end',
        ]);

        $response = $this->post(route('attendance.store'), [
            'action' => 'break_start',
        ]);

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        $response->assertSee('<button class="break__btn" type="submit" name="action" value="break_end">休憩戻</button>', false);
        $response->assertDontSee('<button class="break__btn" type="submit" name="action" value="break_start">休憩入</button>', false);
    }

    // 休憩時刻を一覧で確認できる
    public function test_user_can_check_the_break_time_on_index_page(): void
    {
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        Carbon::setTestNow(Carbon::create(2025, 10, 11, 12, 0, 0));
        $breakStartTime = now();
        $response = $this->post(route('attendance.store'), [
            'action' => 'break_start',
        ]);

        Carbon::setTestNow(Carbon::create(2025, 10, 11, 13, 0, 0));
        $breakEndTime = now();
        $response = $this->post(route('attendance.store'), [
            'action' => 'break_end',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $this->attendance->id,
            'start_at' => $breakStartTime->format('H:i:s'),
            'end_at' => $breakEndTime->format('H:i:s'),
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $totalBreakTime = '1:00'; // 12:00:00〜13:00:00の差分
        $response->assertSee($totalBreakTime);
    }
}
