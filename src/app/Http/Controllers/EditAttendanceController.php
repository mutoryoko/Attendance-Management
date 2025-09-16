<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeTimeRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\RequestAttendance;
use App\Models\RequestBreakTime;

class EditAttendanceController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::find($id);
        $workDate = Carbon::parse($attendance->work_date);

        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();

        return view('detail', compact('attendance', 'workDate', 'breakTimes'));
    }

    public function sendRequest(ChangeTimeRequest $request, $id)
    {
        $attendance = Attendance::find($id);
        $validatedData = $request->validated();

        $requestAttendance = RequestAttendance::create([
            'attendance_id' => $attendance->id,
            'requested_work_start' => $validatedData['requested_work_start'],
            'requested_work_end' => $validatedData['requested_work_end'],
            'note' => $validatedData['note'],
        ]);

        RequestBreakTime::create([
            'request_id' => $requestAttendance->id,
            'requested_break_start' => $validatedData['requested_break_start'],
            'requested_break_end' => $validatedData['requested_break_end'],
        ]);

        return to_route('attendance.index')->with('status', '申請しました');
    }
}
