<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeTimeRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\RequestAttendance;
use App\Models\RequestBreakTime;
use Illuminate\Support\Facades\Auth;

class EditAttendanceController extends Controller
{
    public function show(string $id)
    {
        $user = Auth::user();
        $attendance = null;
        $date = null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $id)) {
            // 日付形式の場合 (欠勤日のリンクから来た場合)
            $date = $id;
            $attendance = Attendance::firstOrNew([
                'user_id' => $user->id,
                'work_date' => $date,
            ]);
        } else {
            // 数値IDの場合 (出勤日のリンクから来た場合)
            $attendance = Attendance::where('user_id', $user->id)->find($id);
            if (!$attendance) {
                abort(404);
            }
            $date = $attendance->work_date;
        }

        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();

        return view('detail', compact('attendance', 'breakTimes'));
    }

    public function sendRequest(ChangeTimeRequest $request, $id)
    {
        $user = Auth::user();
        $date = null;
        $validatedData = $request->validated();

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $id)) {
            $date = $id;
            $attendance = Attendance::firstOrCreate([
                'user_id'   => $user->id,
                'work_date' => $date,
            ]);
        } else {
            $attendance = Attendance::where('user_id', $user->id)->find($id);
            if (!$attendance) {
                abort(404); // データが見つからなければ404エラー
            }
            $date = $attendance->work_date;
        }

        $requestAttendance = RequestAttendance::create([
            'applier_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'requested_work_start' => $validatedData['requested_work_start'],
            'requested_work_end' => $validatedData['requested_work_end'],
            'note' => $validatedData['note'],
        ]);

        if (isset($validatedData['breaks'])) {
            foreach ($validatedData['breaks'] as $breakData) {
                if (!empty($breakData['start']) && !empty($breakData['end'])) {
                    RequestBreakTime::create([
                        'request_id' => $requestAttendance->id,
                        'requested_break_start' => $breakData['start'],
                        'requested_break_end' => $breakData['end'],
                    ]);
                }
            }
        }

        return to_route('request')->with('status', '申請しました');
    }
}
