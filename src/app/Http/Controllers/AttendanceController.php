<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 今日の勤怠記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $status = '';

        if (is_null($attendance)) {
            // 出勤前
            $status = 'not_clocked_in';
        } else {
            if (is_null($attendance->clock_out_time)) {
                // 休憩中かどうかを判定
                $onBreak = $attendance->breakTimes()
                    ->whereNull('break_end_time')
                    ->exists();

                if ($onBreak) {
                    $status = 'on_break';
                } else {
                    $status = 'working';
                }
            } else {
                // 退勤後
                $status = 'clocked_out';
            }
        }

        return view('attendance_create', compact('attendance', 'status'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();
        $action = $request->input('action');

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'work_date' => $today->toDateString(),
        ]);
        $break_time = BreakTime::firstOrNew([
            //
        ]);
    }

    public function show()
    {
        //
    }

    public function update()
    {
        //
    }
}
