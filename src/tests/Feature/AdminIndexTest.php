<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;

class AdminIndexTest extends TestCase
{
    use RefreshDatabase;

    private \Illuminate\Database\Eloquent\Collection $users;
    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = User::factory()
            ->count(3)
            ->state(new Sequence(
                ['name' => '鈴木一郎'],
                ['name' => '佐藤二郎'],
                ['name' => '北島三郎'],
            ))
            ->create();

        $this->admin = AdminUser::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    // その日の全ユーザーの勤怠情報を確認できる
    public function test_admin_can_check_all_attendance_data(): void
    {
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
        ]);

        // 2人目
        $user2 = $this->users->firstWhere('name', '佐藤二郎');
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in_time' => '09:10:00',
            'clock_out_time' => '18:10:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance2->id,
            'start_at' => '12:30:00',
            'end_at' => '13:20:00',
        ]);

        // 3人目
        $user3 = $this->users->firstWhere('name', '北島三郎');
        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in_time' => '08:50:00',
            'clock_out_time' => '17:50:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance3->id,
            'start_at' => '12:00:00',
            'end_at' => '13:30:00',
        ]);

        $response = $this->get(route('admin.index'));
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '鈴木一郎',
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '09:10',
            '18:10',
            '0:50',
            '8:10',
        ]);
        $response->assertSeeTextInOrder([
            '北島三郎',
            '08:50',
            '17:50',
            '1:30',
            '7:30',
        ]);
    }

    // 遷移したら現在の日付が表示される
    public function test_display_current_date_when_admin_access(): void
    {
        $currentDay = Carbon::now()->format('Y年n月j日');

        $response = $this->get(route('admin.index'));
        $response->assertStatus(200);

        $response->assertSee($currentDay);
    }

    // 前日の勤怠情報が表示される
    public function test_display_attendance_data_of_previous_day()
    {
        $prevDay = Carbon::now()->subDay()->format('Y-m-d');

        // 1人目:フルタイム
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => $prevDay,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'start_at' => '12:00:00',
            'end_at' => '13:01:00',
        ]);

        // 2人目：休憩なし
        $user2 = $this->users->firstWhere('name', '佐藤二郎');
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $prevDay,
            'clock_in_time' => '13:00:00',
            'clock_out_time' => '18:10:00',
        ]);

        // 3人目:欠勤
        $user3 = $this->users->firstWhere('name', '北島三郎');

        $response = $this->get(route('admin.index'));
        $response->assertStatus(200);

        $response = $this->get(route('admin.index', ['date' => $prevDay]));
        $response->assertStatus(200);

        $response->assertSee(Carbon::now()->subDay()->format('Y年n月j日'));
        $response->assertSeeInOrder([
            '鈴木一郎',
            '09:00',
            '18:00',
            '1:01',
            '7:59',
        ]);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '13:00',
            '18:10',
            '0:00',
            '5:10',
        ]);
        $response->assertSeeText('北島三郎');
    }

    // 翌日の勤怠情報が表示される
    public function test_display_attendance_data_of_next_day()
    {
        $nextDay = Carbon::now()->addDay()->format('Y-m-d');

        // 1人目:フルタイム
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => $nextDay,
            'clock_in_time' => '10:00:00',
            'clock_out_time' => '20:00:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'start_at' => '14:00:00',
            'end_at' => '15:30:00',
        ]);

        // 2人目：休憩なし
        $user2 = $this->users->firstWhere('name', '佐藤二郎');
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $nextDay,
            'clock_in_time' => '13:00:00',
            'clock_out_time' => '18:30:00',
        ]);

        // 3人目:欠勤
        $this->users->firstWhere('name', '北島三郎');

        $response = $this->get(route('admin.index'));
        $response->assertStatus(200);

        $response = $this->get(route('admin.index', ['date' => $nextDay]));
        $response->assertStatus(200);

        $response->assertSee(Carbon::now()->addDay()->format('Y年n月j日'));
        $response->assertSeeInOrder([
            '鈴木一郎',
            '10:00',
            '20:00',
            '1:30',
            '8:30',
        ]);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '13:00',
            '18:30',
            '0:00',
            '5:30',
        ]);
        $response->assertSeeText('北島三郎');
    }
}
