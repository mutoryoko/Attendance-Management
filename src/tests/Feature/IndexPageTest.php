<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class IndexPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_all_attendances_on_index_page(): void
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->startOfMonth();

        // 勤怠情報を5件作る
        for ($i = 0; $i < 5; $i++) {
            $workDate = $startDate->copy()->addDays($i);

            $attendance = Attendance::factory()->clockedOut()->create([
                'user_id' => $user->id,
                'work_date' => $workDate->format('Y-m-d'),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => '12:00:00',
                'end_at' => '13:00:00',
            ]);
        }
        $this->actingAs($user);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->get();

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $attendance->refresh();

            $response->assertSee(Carbon::parse($attendance->work_date)->format('m/d'));
            $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
            $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));

            // 分から時刻形式の文字列を生成
            $breakInterval = CarbonInterval::minutes($attendance->total_break_minutes)->cascade();
            $workInterval = CarbonInterval::minutes($attendance->total_work_minutes)->cascade();

            $response->assertSee(sprintf('%d:%02d', $breakInterval->h, $breakInterval->i));
            $response->assertSee(sprintf('%d:%02d', $workInterval->h, $workInterval->i));
        }
    }

    // 現在の月が表示される
    public function test_display_current_month_when_user_accesses_to_index_page()
    {
        $currentMonth = Carbon::now()->format('Y/m');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $response->assertSee($currentMonth);
    }

    // 前月の情報が表示される
    public function test_display_attendances_of_previous_month()
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->subMonth()->startOfMonth();

        // 勤怠情報を5件作る
        for ($i = 0; $i < 5; $i++) {
            $workDate = $startDate->copy()->addDays($i);

            $attendance = Attendance::factory()->clockedOut()->create([
                'user_id' => $user->id,
                'work_date' => $workDate->format('Y-m-d'),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => '12:00:00',
                'end_at' => '13:00:00',
            ]);
        }
        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->get();

        $response = $this->get(route('attendance.index', ['month' => $prevMonth]));
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $attendance->refresh();

            $response->assertSee(Carbon::parse($attendance->work_date)->format('m/d'));
            $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
            $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));

            // 分から時刻形式の文字列を生成
            $breakInterval = CarbonInterval::minutes($attendance->total_break_minutes)->cascade();
            $workInterval = CarbonInterval::minutes($attendance->total_work_minutes)->cascade();

            $response->assertSee(sprintf('%d:%02d', $breakInterval->h, $breakInterval->i));
            $response->assertSee(sprintf('%d:%02d', $workInterval->h, $workInterval->i));
        }
    }

    // 翌月の情報が表示される
    public function test_display_attendances_of_next_month()
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->addMonth()->startOfMonth();

        // 勤怠情報を5件作る
        for ($i = 0; $i < 5; $i++) {
            $workDate = $startDate->copy()->addDays($i);

            $attendance = Attendance::factory()->clockedOut()->create([
                'user_id' => $user->id,
                'work_date' => $workDate->format('Y-m-d'),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => '12:00:00',
                'end_at' => '13:00:00',
            ]);
        }
        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->get();

        $response = $this->get(route('attendance.index', ['month' => $nextMonth]));
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $attendance->refresh();

            $response->assertSee(Carbon::parse($attendance->work_date)->format('m/d'));
            $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
            $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));

            // 分から時刻形式の文字列を生成
            $breakInterval = CarbonInterval::minutes($attendance->total_break_minutes)->cascade();
            $workInterval = CarbonInterval::minutes($attendance->total_work_minutes)->cascade();

            $response->assertSee(sprintf('%d:%02d', $breakInterval->h, $breakInterval->i));
            $response->assertSee(sprintf('%d:%02d', $workInterval->h, $workInterval->i));
        }
    }

    // 詳細画面へ遷移する
    public function test_take_user_to_the_detail_page()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->clockedOut()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->format('Y-m-d'),
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
    }
}
