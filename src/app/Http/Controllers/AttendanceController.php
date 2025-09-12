<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    // 勤怠一覧画面表示
    public function index()
    {
        $user = Auth::user();

        $past = Carbon::now()->subDays(60);
        $future = Carbon::now()->addDays(60);
        $period = CarbonPeriod::create($past,'1 day', $future);

        $attendances = Attendance::where('user_id', $user->id)
                    ->whereDate('work_date', '>=', $past)
                    ->whereDate('work_date', '<=', $future)
                    ->get()
                    ->keyBy(function ($item) {
                        return Carbon::parse($item->work_date)->format('Y-m-d');
                    });

        return view('index', compact('period', 'attendances'));
    }

    // 勤怠登録画面表示
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
                    ->whereNull('end_at')
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

    // 勤怠登録処理
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();
        $action = $request->input('action');
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        switch ($action) {
            // 出勤
            case 'clock_in':
                if (is_null($attendance)) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $today,
                        'clock_in_time' => $now->toTimeString(),
                    ]);
                }
                break;

            // 休憩開始
            case 'break_start':
                if ($attendance && is_null($attendance->clock_out_time)) {
                    // 既に開始されている休憩がないか確認
                    $isAlreadyOnBreak = $attendance->breakTimes()
                        ->whereNull('end_at')
                        ->exists();
                    if (!$isAlreadyOnBreak) {
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'start_at' => $now->toTimeString(),
                        ]);
                    }
                }
                break;

            // 休憩終了
            case 'break_end':
                if ($attendance) {
                    $breakTime = $attendance->breakTimes()
                        ->whereNull('end_at')
                        ->first();
                    if ($breakTime) {
                        $breakTime->update([
                            'end_at' => $now->toTimeString(),
                        ]);
                    }
                }
                break;

            // 退勤
            case 'clock_out':
                if ($attendance && is_null($attendance->clock_out_time)) {
                    // 休憩中の場合は先に休憩を終了させる
                    $activeBreak = $attendance->breakTimes()
                        ->whereNull('end_at')
                        ->first();
                    if ($activeBreak) {
                        $activeBreak->update(['end_at' => $now->toTimeString()]);
                        // attendanceを再読み込みして最新の休憩情報を反映
                        $attendance->refresh();
                    }

                    $attendance->clock_out_time = $now->toTimeString();

                    // 合計休憩時間の計算
                    $totalBreakSeconds = 0;
                    foreach ($attendance->breakTimes as $break) {
                        $start = Carbon::parse($break->start_at);
                        $end = Carbon::parse($break->end_at);
                        $totalBreakSeconds += $start->diffInSeconds($end);
                    }
                    $totalBreakMinutes = floor($totalBreakSeconds / 60);

                    // 実労働時間の計算
                    $clockIn = Carbon::parse($attendance->clock_in_time);
                    $clockOut = Carbon::parse($attendance->clock_out_time);
                    $totalWorkSeconds = $clockIn->diffInSeconds($clockOut);
                    $totalWorkMinutes = floor($totalWorkSeconds / 60) - $totalBreakMinutes;

                    $attendance->total_break_minutes = $totalBreakMinutes;
                    $attendance->total_work_minutes = $totalWorkMinutes;
                    $attendance->save();
                }
                break;
        }

        return back()->with('status', '打刻が完了しました');
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
