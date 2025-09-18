<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\RequestAttendance;

class RequestAttendanceController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            $allRequests = RequestAttendance::all();

            return view('attendance_request', compact('allRequests'));
        }
        elseif (Auth::guard('web')->check()) {
            $user = Auth::user();
            $requests = RequestAttendance::where('applier_id', $user->id);

            return view('attendance_request', compact('requests'));
        }
        else {
            return view('login');
        }
    }
}
