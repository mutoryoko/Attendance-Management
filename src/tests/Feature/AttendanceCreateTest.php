<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class AttendanceCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_displays_current_datetime_on_attendance_create_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fixedTime = Carbon::now(); // 現在時刻の場合
        // $fixedTime = Carbon::create(2025, 10, 10, 9, 0, 0); //時間を固定する場合
        $this->travelTo($fixedTime);

        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[$fixedTime->dayOfWeek];

        $expectedDate = $fixedTime->format('Y年n月j日') . '（' . $dayOfWeek . '）';

        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 画面に表示されているはずの「日付」と「時刻」の文字列を準備
        $expectedDate = $fixedTime->format('Y年n月j日');
        $expectedTime = $fixedTime->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSeeText($expectedTime);
    }
}
