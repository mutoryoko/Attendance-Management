<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeTimeRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::now()->format('Y-m-d'));
        $currentDay= Carbon::parse($date);

        $attendances = Attendance::with('user')
                        ->whereYear('work_date', $currentDay->year)
                        ->whereMonth('work_date', $currentDay->month)
                        ->whereDay('work_date', $currentDay->day)
                        ->get();

        $prevDay = $currentDay->copy()->subDay()->format('Y-m-d');
        $nextDay = $currentDay->copy()->addDay()->format('Y-m-d');

        return view('admin.index', compact('currentDay', 'attendances', 'prevDay', 'nextDay'));
    }

    public function show(string $id)
    {
        $attendance = Attendance::with('user', 'breakTimes', 'pendingRequest')
                        ->findOrFail($id);

        return view('detail', compact('attendance'));
    }

    public function update(ChangeTimeRequest $request, string $id)
    {
        $validatedData = $request->validated();

        try {
            DB::transaction(function () use($validatedData, $id) {
                $attendance = Attendance::findOrFail($id);

                // 休憩時間は一度削除して再登録
                $attendance->breakTimes()->delete();

                if (isset($validatedData['breaks'])) {
                    foreach ($validatedData['breaks'] as $breakData) {
                        if (!empty($breakData['start']) && !empty($breakData['end'])) {
                            $attendance->breakTimes()->create([
                                'start_at' => $breakData['start'],
                                'end_at' => $breakData['end'],
                            ]);
                        }
                    }
                }
                $attendance->load('breakTimes');

                // 合計休憩時間の計算
                $totalBreakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    $totalBreakSeconds += $break->start_at->diffInSeconds($break->end_at);
                }
                $totalBreakMinutes = floor($totalBreakSeconds / 60);

                // 実労働時間の計算
                $clockIn = new Carbon($validatedData['requested_work_start']);
                $clockOut = new Carbon($validatedData['requested_work_end']);
                $totalWorkSeconds = $clockIn->diffInSeconds($clockOut);
                $totalWorkMinutes = floor($totalWorkSeconds / 60) - $totalBreakMinutes;

                // 勤怠情報の更新
                $attendance->update([
                    'clock_in_time' => $validatedData['requested_work_start'],
                    'clock_out_time' => $validatedData['requested_work_end'],
                    'total_break_minutes' => $totalBreakMinutes,
                    'total_work_minutes' => $totalWorkMinutes,
                    'note' => $validatedData['note'],
                ]);
            });
        } catch(\Exception $e) {
            return redirect()->back()->with('error', '更新に失敗しました');
        }
        return to_route('admin.index')->with('status', '勤怠情報を修正しました');
    }
}
