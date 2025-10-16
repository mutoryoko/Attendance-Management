<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\RequestAttendance;
use App\Models\RequestBreakTime;
use Illuminate\Database\Eloquent\Factories\Sequence;


class AdminRequestTest extends TestCase
{
    use RefreshDatabase;

    private \Illuminate\Database\Eloquent\Collection $users;
    private AdminUser $admin;
    private RequestAttendance $requestAttendance;

    public function setUp(): void
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

    // 承認待ちの申請が表示される
    public function test_show_all_pending_approvals(): void
    {
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $attendance1 = Attendance::factory()->clockedOut()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-10-01',
        ]);
        RequestAttendance::factory()->create([
            'applier_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'note' => '申請1',
        ]);

        $user2 = $this->users->firstWhere('name', '佐藤二郎');
        $attendance2 = Attendance::factory()->clockedOut()->create([
            'user_id' => $user2->id,
            'work_date' => '2025-10-10',
        ]);
        RequestAttendance::factory()->create([
            'applier_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'note' => '申請2',
        ]);

        $user3 = $this->users->firstWhere('name', '北島三郎');
        $attendance3 = Attendance::factory()->clockedOut()->create([
            'user_id' => $user3->id,
            'work_date' => '2025-10-15',
        ]);
        RequestAttendance::factory()->create([
            'applier_id' => $user3->id,
            'attendance_id' => $attendance3->id,
            'note' => '申請3',
        ]);

        $response = $this->get(route('request'));
        $response->assertStatus(200);

        $response = $this->get(route('request', ['status' => 'pending']));
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '鈴木一郎',
            '2025/10/01',
            '申請1',
        ]);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '2025/10/10',
            '申請2',
        ]);
        $response->assertSeeInOrder([
            '北島三郎',
            '2025/10/15',
            '申請3',
        ]);
    }

    // 承認済みの申請が表示される
    public function test_show_all_approved_requests(): void
    {
        $user1 = $this->users->firstWhere('name', '鈴木一郎');
        $attendance1 = Attendance::factory()->clockedOut()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-10-01',
        ]);
        RequestAttendance::factory()->create([
            'applier_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'approver_id' => $this->admin->id,
            'note' => '申請1',
            'is_approved' => true,
        ]);

        $user2 = $this->users->firstWhere('name', '佐藤二郎');
        $attendance2 = Attendance::factory()->clockedOut()->create([
            'user_id' => $user2->id,
            'work_date' => '2025-10-10',
        ]);
        RequestAttendance::factory()->create([
            'applier_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'approver_id' => $this->admin->id,
            'note' => '申請2',
            'is_approved' => true,
        ]);

        $user3 = $this->users->firstWhere('name', '北島三郎');
        $attendance3 = Attendance::factory()->clockedOut()->create([
            'user_id' => $user3->id,
            'work_date' => '2025-10-15',
        ]);
        RequestAttendance::factory()->create([
            'applier_id' => $user3->id,
            'attendance_id' => $attendance3->id,
            'approver_id' => $this->admin->id,
            'note' => '申請3',
            'is_approved' => true,
        ]);

        $response = $this->get(route('request'));
        $response->assertStatus(200);

        $response = $this->get(route('request', ['status' => 'approved']));
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '鈴木一郎',
            '2025/10/01',
            '申請1',
        ]);
        $response->assertSeeInOrder([
            '佐藤二郎',
            '2025/10/10',
            '申請2',
        ]);
        $response->assertSeeInOrder([
            '北島三郎',
            '2025/10/15',
            '申請3',
        ]);
    }

    // 申請の詳細が正しく表示される
    public function test_show_detail_of_request(): void
    {
        $user = $this->users->firstWhere('name', '鈴木一郎');
        $attendance = Attendance::factory()->clockedOut()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-01',
        ]);
        $requestAttendance = RequestAttendance::factory()->create([
            'applier_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_work_start' => '10:31:00',
            'requested_work_end' => '20:35:00',
            'note' => '修正しました',
        ]);
        RequestBreakTime::factory()->create([
            'request_id' => $requestAttendance->id,
            'requested_break_start' => '13:10:00',
            'requested_break_end' => '14:15:00',
        ]);

        $response = $this->get(route('request'));
        $response->assertStatus(200);

        $response = $this->get(route('request.detail', [
            'attendance_correct_request' => $requestAttendance->id
        ]));
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '鈴木一郎',
            '2025年',
            '10月1日',
            '10:31',
            '20:35',
            '13:10',
            '14:15',
            '修正しました',
        ]);
    }

    // 申請が承認され、勤怠情報が更新される
    public function test_approve_the_request_and_update_the_attendance_data(): void
    {
        $user = $this->users->firstWhere('name', '鈴木一郎');
        $attendance = Attendance::factory()->clockedOut()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-01',
        ]);
        $requestAttendance = RequestAttendance::factory()->create([
            'applier_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_work_start' => '10:30:00',
            'requested_work_end' => '20:30:00',
            'note' => '修正しました',
        ]);
        RequestBreakTime::factory()->create([
            'request_id' => $requestAttendance->id,
            'requested_break_start' => '13:05:00',
            'requested_break_end' => '14:10:00',
        ]);

        $response = $this->get(route('request.detail', [
            'attendance_correct_request' => $requestAttendance->id
        ]));
        $response->assertStatus(200);

        $response = $this->patch(route('admin.approve',[
            'attendance_correct_request' => $requestAttendance->id
        ]));

        $this->assertDatabaseHas('request_attendances', [
            'attendance_id' => $attendance->id,
            'approver_id' => $this->admin->id,
            'is_approved' => true,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $user->id,
            'work_date' => '2025-10-01',
            'clock_in_time' => '10:30:00',
            'clock_out_time' => '20:30:00',
            'total_break_minutes' => 65,
            'total_work_minutes' => 535,
        ]);
    }
}
