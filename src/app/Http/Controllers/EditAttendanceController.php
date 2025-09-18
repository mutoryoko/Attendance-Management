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
    public function show($id)
    {
        $attendance = Attendance::find($id);

        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();

        return view('detail', compact('attendance', 'breakTimes'));
    }

    public function sendRequest(ChangeTimeRequest $request, $id)
    {
        $attendance = Attendance::find($id);
        $validatedData = $request->validated();

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
