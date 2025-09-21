<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeTimeRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

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
        $attendance = Attendance::with('user')->findOrFail($id);
        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();

        return view('detail', compact('attendance', 'breakTimes'));
    }

    public function update(ChangeTimeRequest $request, string $id)
    {
        //
    }
}
