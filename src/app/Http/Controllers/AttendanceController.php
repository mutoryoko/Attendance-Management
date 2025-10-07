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
    public function index(Request $request)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $period = CarbonPeriod::create($startOfMonth, '1 day', $endOfMonth);

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->orderBy('work_date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        return view('index', compact('period', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
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
                    // 合計休憩時間の計算
                    $totalBreakSeconds = 0;
                    foreach ($attendance->refresh()->breakTimes as $break) {
                        if ($break->start_at && $break->end_at) {
                            $start = $break->start_at;
                            $end = $break->end_at;
                            $totalBreakSeconds += $start->diffInSeconds($end);
                        }
                    }
                    $totalBreakMinutes = floor($totalBreakSeconds / 60);

                    $attendance->total_break_minutes = $totalBreakMinutes;
                    $attendance->save();
                }
                break;

            // 退勤
            case 'clock_out':
                if ($attendance && is_null($attendance->clock_out_time)) {
                    $attendance->clock_out_time = $now->toTimeString();

                    // 実労働時間の計算
                    $clockIn = $attendance->clock_in_time;
                    $clockOut = $attendance->clock_out_time;
                    $totalBreakMinutes = $attendance->total_break_minutes;
                    $totalWorkSeconds = $clockIn->diffInSeconds($clockOut);
                    $totalWorkMinutes = floor($totalWorkSeconds / 60) - $totalBreakMinutes;

                    $attendance->total_work_minutes = $totalWorkMinutes;
                    $attendance->save();
                }
                break;
        }

        return back()->with('status', '打刻が完了しました');
    }
}
