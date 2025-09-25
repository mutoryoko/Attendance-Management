<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RequestAttendance;
use App\Models\RequestBreakTime;
use App\Models\BreakTime;
use App\Models\Attendance;

class RequestAttendanceController extends Controller
{
    // 一般ユーザー・管理者共通
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $isApproved = ($status === 'approved');

        if (Auth::guard('admin')->check()) {
            $requestAttendances = RequestAttendance::with(['applier', 'attendance'])
                        ->where('is_approved', $isApproved)
                        ->latest()
                        ->get();

            return view('attendance_request', compact('requestAttendances', 'status'));
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::user();
            $requestAttendances = RequestAttendance::with('attendance')
                        ->where('applier_id', $user->id)
                        ->where('is_approved', $isApproved)
                        ->latest()
                        ->get();

            return view('attendance_request', compact('requestAttendances', 'status'));
        } else {
            return view('login');
        }
    }

    // 詳細画面表示
    public function show($id)
    {
        $requestAttendance = RequestAttendance::with('attendance')->findOrFail($id);
        $requestBreakTimes = RequestBreakTime::with('requestAttendance')
                                ->where('request_id', $requestAttendance->id)
                                ->get();

        return view('approve', compact('requestAttendance', 'requestBreakTimes'));
    }

    // 管理者用 承認処理
    public function approve($id)
    {
        DB::transaction(function()use($id) {
            $adminId = Auth::guard('admin')->user()->id;

            $requestAttendance = RequestAttendance::with('attendance')->findOrFail($id);

            $requestBreakTimes = RequestBreakTime::with('requestAttendance')
                                ->where('request_id', $requestAttendance->id)
                                ->get();

            $requestAttendance->approver_id = $adminId;
            $requestAttendance->is_approved = true;
            $requestAttendance->save();

            // ここに一旦休憩時間を削除して再登録の処理入れる予定。

            if($requestBreakTimes->isNotEmpty()) {
                $attendanceId = $requestAttendance->attendance_id;

                foreach($requestBreakTimes as $requestBreak) {
                    BreakTime::create([
                        'attendance_id' => $attendanceId,
                        'start_at' => $requestBreak->requested_break_start,
                        'end_at' => $requestBreak->requested_break_end,
                    ]);
                }
            }

            // ここに合計休憩時間と実労働時間の計算入れる予定。

            Attendance::where('id', $requestAttendance->attendance->id)->update([
                'clock_in_time' => $requestAttendance->requested_work_start,
                'clock_out_time' => $requestAttendance->requested_work_end,
                'note' => $requestAttendance->note,
                //合計休憩時間と実労働時間も入れる予定。
            ]);
        });
        return to_route('request', ['status' => 'approved'])->with('status', '承認しました');
    }
}
