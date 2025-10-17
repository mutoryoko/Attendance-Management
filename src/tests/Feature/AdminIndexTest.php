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
            '<td class="table-data">09:00</td>',
            '<td class="table-data">18:00</td>',
            '<td class="table-data">1:00</td>',
            '<td class="table-data">8:00</td>',
        ], false);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '<td class="table-data">09:10</td>',
            '<td class="table-data">18:10</td>',
            '<td class="table-data">0:50</td>',
            '<td class="table-data">8:10</td>',
        ], false);
        $response->assertSeeInOrder([
            '北島三郎',
            '<td class="table-data">08:50</td>',
            '<td class="table-data">17:50</td>',
            '<td class="table-data">1:30</td>',
            '<td class="table-data">7:30</td>',
        ], false);
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
        $this->users->firstWhere('name', '北島三郎');

        $response = $this->get(route('admin.index'));
        $response->assertStatus(200);

        $response->assertSeeText('前日');

        $expectedUrl = route('admin.index', ['date' => $prevDay]);
        $response = $this->get($expectedUrl);
        $response->assertStatus(200);

        $response->assertSee(Carbon::now()->subDay()->format('Y年n月j日'));
        $response->assertSeeInOrder([
            '鈴木一郎',
            '<td class="table-data">09:00</td>',
            '<td class="table-data">18:00</td>',
            '<td class="table-data">1:01</td>',
            '<td class="table-data">7:59</td>',
        ], false);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '<td class="table-data">13:00</td>',
            '<td class="table-data">18:10</td>',
            '<td class="table-data"></td>',
            '<td class="table-data">5:10</td>',
        ], false);
        $response->assertSeeInOrder([
            '北島三郎',
            '<td class="table-data"></td>',
            '<td class="table-data"></td>',
            '<td class="table-data"></td>',
            '<td class="table-data"></td>',
        ], false);
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

        $response->assertSeeText('翌日');

        $expectedUrl = route('admin.index', ['date' => $nextDay]);
        $response = $this->get($expectedUrl);
        $response->assertStatus(200);

        $response->assertSee(Carbon::now()->addDay()->format('Y年n月j日'));
        $response->assertSeeInOrder([
            '鈴木一郎',
            '<td class="table-data">10:00</td>',
            '<td class="table-data">20:00</td>',
            '<td class="table-data">1:30</td>',
            '<td class="table-data">8:30</td>',
        ], false);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '<td class="table-data">13:00</td>',
            '<td class="table-data">18:30</td>',
            '<td class="table-data"></td>',
            '<td class="table-data">5:30</td>',
        ], false);
        $response->assertSeeInOrder([
            '北島三郎',
            '<td class="table-data"></td>',
            '<td class="table-data"></td>',
            '<td class="table-data"></td>',
            '<td class="table-data"></td>',
        ], false);
    }
}
