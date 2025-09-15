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

        RequestAttendance::create([
            'attendance_id' => $request->input($attendance->id),
            'request_work_start' => $validatedData['request_work_start'],
            'request_work_end' => $validatedData['request_work_end'],
        ]);
    }
}
