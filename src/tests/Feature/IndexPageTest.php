<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class IndexPageTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠情報がすべて表示される
    public function test_display_all_attendance_data(): void
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
            $response->assertSee([
                $attendance->work_date->format('m/d'),
                $attendance->clock_in_time->format('H:i'),
                $attendance->clock_out_time->format('H:i'),
                $attendance->formatted_break_time,
                $attendance->formatted_work_time,
            ]);
        }
    }

    // 現在の月が表示される
    public function test_display_current_month_when_user_accesses(): void
    {
        $currentMonth = Carbon::now()->format('Y/m');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $response->assertSee($currentMonth);
    }

    // 前月の情報が表示される
    public function test_display_attendance_data_of_previous_month(): void
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

        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->get();

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $response->assertSeeText('前月');

        $expectedUrl = route('attendance.index', ['month' => $prevMonth]);
        $response = $this->get($expectedUrl);
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee([
                $attendance->work_date->format('m/d'),
                $attendance->clock_in_time->format('H:i'),
                $attendance->clock_out_time->format('H:i'),
                $attendance->formatted_break_time,
                $attendance->formatted_work_time,
            ]);
        }
    }

    // 翌月の情報が表示される
    public function test_display_attendance_data_of_next_month(): void
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

        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->get();

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $response->assertSeeText('翌月');

        $expectedUrl = route('attendance.index', ['month' => $nextMonth]);
        $response = $this->get($expectedUrl);
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee([
                $attendance->work_date->format('m/d'),
                $attendance->clock_in_time->format('H:i'),
                $attendance->clock_out_time->format('H:i'),
                $attendance->formatted_break_time,
                $attendance->formatted_work_time,
            ]);
        }
    }

    // 詳細画面へ遷移する
    public function test_take_user_to_the_detail_page(): void
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

        $response->assertSeeText('詳細');

        $expectedUrl = route('attendance.detail', ['id' => $attendance->id]);
        $response = $this->get($expectedUrl);
        $response->assertStatus(200);
    }
}
