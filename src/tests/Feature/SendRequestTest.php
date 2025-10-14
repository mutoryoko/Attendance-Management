<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

// エラーメッセージ表示
class SendRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

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

    // 出勤時間が退勤時間より後になっている場合のエラー表示
    public function test_show_error_when_clock_in_time_is_later_than_clock_out_time(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.send', ['id' => $this->attendance->id]), [
            'requested_work_start' => '19:00',
            'requested_work_end' => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'requested_work_start' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // 休憩開始時間が退勤時間より後になっている場合のエラー表示
    public function test_show_error_when_break_start_time_is_later_than_clock_out_time(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $postData = [
            'requested_work_start' => '09:00',
            'requested_work_end'   => '18:00',
            'breaks' => [
                [
                    'start' => '20:00',
                    'end' => '21:00',
                ],
            ],
        ];
        $response = $this->post(route('attendance.send', ['id' => $this->attendance->id]), $postData);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    // 休憩終了時間が退勤時間より後になっている場合のエラー表示
    public function test_show_error_when_break_end_time_is_later_than_clock_out_time(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $postData = [
            'requested_work_start' => '09:00',
            'requested_work_end'   => '18:00',
            'breaks' => [
                [
                    'start' => '17:00',
                    'end' => '19:00',
                ],
            ],
        ];
        $response = $this->post(route('attendance.send', ['id' => $this->attendance->id]), $postData);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // 備考欄が未入力の場合のエラー表示
    public function test_show_error_when_note_is_missing(): void
    {
        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.send', ['id' => $this->attendance->id]),[
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
