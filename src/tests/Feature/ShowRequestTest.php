<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AdminUser;
use App\Models\RequestAttendance;
use Carbon\Carbon;

// 申請処理と内容表示
class ShowRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 各テストの申請日時
        Carbon::setTestNow(Carbon::create(2025, 10, 11, 9, 0, 0));

        $this->user = User::factory()->create([
            'name' => '鈴木一郎'
        ]);
        $this->attendance = Attendance::factory()->clockedOut()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-10-01',
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
        ]);
        $this->actingAs($this->user);
    }

    // 修正申請処理が実行され、管理者の承認画面と申請一覧に表示される
    public function test_user_can_send_request_and_admin_can_check(): void
    {
        $requestData = [
            'requested_work_start' => '10:00',
            'requested_work_end'   => '20:00',
            'breaks' => [
                [
                    'start' => '13:30',
                    'end' => '14:30',
                ],
            ],
            'note' => '修正しました',
        ];

        $response = $this->post(route('attendance.send', ['id' => $this->attendance->id]), $requestData);

        $requestAttendance = RequestAttendance::where('applier_id', $this->user->id)
            ->where('attendance_id', $this->attendance->id)
            ->first();

        $response = $this->post(route('logout'));
        $this->assertGuest();

        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');

        // 申請一覧画面
        $response = $this->get(route('request'));
        $response->assertStatus(200);

        $response->assertSeeTextInOrder([
            '承認待ち',
            '鈴木一郎',
            '2025/10/01', //　対象日
            '修正しました',
            '2025/10/11', // 申請日
        ]);

        // 承認画面
        $response = $this->get(route('request.detail', ['attendance_correct_request' => $requestAttendance->id]));
        $response->assertStatus(200);

        $response->assertSeeTextInOrder([
            '鈴木一郎',
            '2025年',
            '10月1日',
            '10:00',
            '20:00',
            '13:30',
            '14:30',
            '修正しました',
        ]);
    }

    // 自分の申請内容が申請一覧画面に表示されている
    public function test_user_can_send_requests_and_check(): void
    {
        $attendance2 = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-10-10',
        ]);

        $requestDataSet = [
            [
                'id' => $this->attendance->id,
                'data' => [
                    'requested_work_start' => '10:00',
                    'requested_work_end'   => '20:00',
                    'note' => '申請1',
                ],
            ],
            [
                'id' => $attendance2->id,
                'data' => [
                    'requested_work_start' => '13:00',
                    'requested_work_end'   => '18:00',
                    'note' => '申請2',
                ],
            ],
        ];

        foreach ($requestDataSet as $request) {
            $this->post(route('attendance.send', ['id' => $request['id']]), $request['data']);
        }

        $response = $this->get(route('request',['status' => 'pending']));
        $response->assertStatus(200);

        $response->assertSeeTextInOrder([
            '承認待ち',
            '鈴木一郎',
            '2025/10/01', //　対象日
            '申請1',
            '2025/10/11', // 申請日
            // 2件目
            '承認待ち',
            '鈴木一郎',
            '2025/10/10', //　対象日
            '申請2',
            '2025/10/11', // 申請日
        ]);
    }

    // 承認済みの申請が申請一覧画面に表示されている
    public function test_approved_requests_and_check(): void
    {
        $admin = AdminUser::factory()->create();

        $attendance2 = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-10-10',
        ]);

        $requestDataSet = [
            [
                'id' => $this->attendance->id,
                'data' => [
                    'requested_work_start' => '10:00',
                    'requested_work_end'   => '20:00',
                    'note' => '申請1',
                ],
            ],
            [
                'id' => $attendance2->id,
                'data' => [
                    'requested_work_start' => '13:00',
                    'requested_work_end'   => '18:00',
                    'note' => '申請2',
                ],
            ],
        ];

        foreach ($requestDataSet as $request) {
            $this->post(route('attendance.send', ['id' => $request['id']]), $request['data']);
        }

        RequestAttendance::where('applier_id', $this->user->id)
            ->update([
                'approver_id' => $admin->id,
                'is_approved' => true,
            ]);

        $response = $this->get(route('request', ['status' => 'approved']));
        $response->assertStatus(200);

        $response->assertSeeTextInOrder([
            '承認済み',
            '鈴木一郎',
            '2025/10/01', //　対象日
            '申請1',
            '2025/10/11', // 申請日
            //2件目
            '承認済み',
            '鈴木一郎',
            '2025/10/10', //　対象日
            '申請2',
            '2025/10/11', // 申請日
        ]);
    }

    // 申請一覧画面から詳細画面へ遷移する
    public function test_take_user_to_the_detail_page(): void
    {
        $requestData = [
            'requested_work_start' => '10:00',
            'requested_work_end'   => '20:00',
            'breaks' => [
                [
                    'start' => '13:30',
                    'end' => '14:30',
                ],
            ],
            'note' => '修正しました',
        ];

        $response = $this->post(route('attendance.send', ['id' => $this->attendance->id]), $requestData);

        $response = $this->get(route('request'));
        $response->assertStatus(200);

        $requestAttendance = RequestAttendance::where('attendance_id', $this->attendance->id)->first();
        $expectedUrl = route('request.detail', ['attendance_correct_request' => $requestAttendance->id]);

        $response = $this->get($expectedUrl);
        $response->assertStatus(200);
    }
}