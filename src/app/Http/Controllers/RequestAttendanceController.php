<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestAttendance;
use App\Models\RequestBreakTime;

class RequestAttendanceController extends Controller
{
    // 一般ユーザー・管理者共通
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $isApproved = ($status === 'approved');

        if (Auth::guard('admin')->check()) {
            $requests = RequestAttendance::with(['applier', 'attendance'])
                        ->where('is_approved', $isApproved)
                        ->latest()
                        ->get();

            return view('attendance_request', compact('requests', 'status'));
        }
        elseif (Auth::guard('web')->check()) {
            $user = Auth::user();
            $requests = RequestAttendance::with('attendance')
                        ->where('applier_id', $user->id)
                        ->where('is_approved', $isApproved)
                        ->latest()
                        ->get();

            return view('attendance_request', compact('requests', 'status'));
        }
        else {
            return view('login');
        }
    }

    // 管理者用 詳細画面表示
    public function show($id)
    {
        $request = RequestAttendance::find($id);
        $requestedBreakTimes = RequestBreakTime::with('requestAttendance')->get();

        return view('admin.approve', compact('request', 'requestedBreakTimes'));
    }

    // 管理者用 承認処理
    public function approve($id)
    {
        //
    }
}
