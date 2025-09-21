<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

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

    public function show()
    {
        //
    }

    public function update()
    {
        //
    }
}
