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

    /**
     * @var \Illuminate\Support\Carbon
     */
    protected $fixedDate;

    private \Illuminate\Database\Eloquent\Collection $users;
    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 各テストの共通処理（現在時刻の固定）
        $this->fixedDate = Carbon::create(2025, 10, 11, 20, 0, 0);
        Carbon::setTestNow($this->fixedDate);

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
    public function test_admin_can_check_all_attendances(): void
    {
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
        ]);

        // 佐藤二郎 (実労働 8時間05分)
        $user2 = $this->users->firstWhere('name', '佐藤二郎');
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in_time' => '09:05:00',
            'clock_out_time' => '18:10:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance2->id,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
        ]);

        // 北島三郎 (実労働 7時間55分)
        $user3 = $this->users->firstWhere('name', '北島三郎');
        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in_time' => '08:55:00',
            'clock_out_time' => '17:50:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance3->id,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
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
            '09:05',
            '18:10',
            '1:00',
            '8:05',
        ]);
        $response->assertSeeTextInOrder([
            '北島三郎',
            '08:55',
            '17:50',
            '1:00',
            '7:55',
        ]);
    }
}
