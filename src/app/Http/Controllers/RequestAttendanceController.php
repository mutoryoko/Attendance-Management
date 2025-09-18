<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestAttendance;

class RequestAttendanceController extends Controller
{
    // 一般ユーザー・管理者共通
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            $allRequests = RequestAttendance::with(['applier', 'attendance'])->latest()->get();

            return view('attendance_request', compact('allRequests'));
        }
        elseif (Auth::guard('web')->check()) {
            $user = Auth::user();
            $requests = RequestAttendance::where('applier_id', $user->id)
                        ->with('attendance')->latest()->get();

            return view('attendance_request', compact('requests'));
        }
        else {
            return view('login');
        }
    }

    // 管理者用 詳細画面表示
    public function show($id)
    {
        //
    }

    // 管理者用 承認処理
    public function approve()
    {
        //
    }
}
