<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class CurrentDateTimeTest extends TestCase
{
    use RefreshDatabase;

    // 打刻ページに現在の日時が表示される
    public function test_display_current_datetime(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fixedTime = Carbon::now();
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
