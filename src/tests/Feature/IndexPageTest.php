<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;

class IndexPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_all_attendances_on_index_page(): void
    {
        $user = User::factory()->create();
        $endDate = Carbon::today();
        $startDate = Carbon::now()->startOfMonth();
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $attendance = Attendance::factory()->clockedOut()->create([
                'user_id' => $user->id,
                'work_date' => $date->format('Y-m-d'),
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

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ])
            ->get();

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
}
