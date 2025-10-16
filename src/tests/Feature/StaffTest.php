<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Carbon\Carbon;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    private \Illuminate\Database\Eloquent\Collection $users;
    private AdminUser $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->users = User::factory()
            ->count(3)
            ->state(new Sequence(
                ['name' => '鈴木一郎', 'email' => 'ichiro@test.com'],
                ['name' => '佐藤二郎', 'email' => 'jiro@test.com'],
                ['name' => '北島三郎', 'email' => 'saburo@test.com'],
            ))
            ->create();

        $this->admin = AdminUser::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    // 全一般ユーザーの氏名とメールアドレスを確認できる
    public function test_admin_can_check_user_name_and_email(): void
    {
        $response = $this->get(route('admin.staff'));
        $response->assertStatus(200);

        $response->assertSeeText([
            '鈴木一郎',
            'ichiro@test.com',
            '佐藤二郎',
            'jiro@test.com',
            '北島三郎',
            'saburo@test.com',
        ]);
    }

    // 選択したユーザーの勤怠情報が表示される
    public function test_show_attendance_data_of_the_selected_user(): void
    {
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $startDate = Carbon::now()->startOfMonth();

        // 勤怠情報を5件作る
        for ($i = 0; $i < 5; $i++) {
            $workDate = $startDate->copy()->addDays($i);

            $attendance = Attendance::factory()->clockedOut()->create([
                'user_id' => $user1->id,
                'work_date' => $workDate->format('Y-m-d'),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => '12:00:00',
                'end_at' => '13:00:00',
            ]);
        }

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user1->id)
            ->get();

        $response = $this->get(route('admin.attendance.staff', ['id' => $user1->id]));
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

    // 前月の情報が表示される
    public function test_display_attendance_data_of_previous_month(): void
    {
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $startDate = Carbon::now()->subMonth()->startOfMonth();

        // 勤怠情報を5件作る
        for ($i = 0; $i < 5; $i++) {
            $workDate = $startDate->copy()->addDays($i);

            $attendance = Attendance::factory()->clockedOut()->create([
                'user_id' => $user1->id,
                'work_date' => $workDate->format('Y-m-d'),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => '12:00:00',
                'end_at' => '13:00:00',
            ]);
        }

        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user1->id)
            ->get();

        $response = $this->get(route('admin.attendance.staff', ['id' => $user1->id]));
        $response->assertStatus(200);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user1->id,
            'month' => $prevMonth,
        ]));
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
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $startDate = Carbon::now()->addMonth()->startOfMonth();

        // 勤怠情報を5件作る
        for ($i = 0; $i < 5; $i++) {
            $workDate = $startDate->copy()->addDays($i);

            $attendance = Attendance::factory()->clockedOut()->create([
                'user_id' => $user1->id,
                'work_date' => $workDate->format('Y-m-d'),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_at' => '12:00:00',
                'end_at' => '13:00:00',
            ]);
        }

        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user1->id)
            ->get();

        $response = $this->get(route('admin.attendance.staff', ['id' => $user1->id]));
        $response->assertStatus(200);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user1->id,
            'month' => $nextMonth,
        ]));
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
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $attendance = Attendance::factory()->clockedOut()->create([
            'user_id' => $user1->id,
            'work_date' => Carbon::now()->format('Y-m-d'),
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
        ]);

        $response = $this->get(route('admin.attendance.staff', ['id' => $user1->id]));
        $response->assertStatus(200);

        $response->assertSeeText('詳細');

        $expectedUrl = route('admin.detail', ['id' => $attendance->id]);
        $response = $this->get($expectedUrl);
        $response->assertStatus(200);
    }
}
