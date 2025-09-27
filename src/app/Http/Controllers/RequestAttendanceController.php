<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RequestAttendance;
use App\Models\RequestBreakTime;
use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;


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
        try {
            DB::transaction(function()use($id) {
                $adminId = Auth::guard('admin')->user()->id;

                $requestAttendance = RequestAttendance::with('attendance')->findOrFail($id);

                $requestBreakTimes = RequestBreakTime::with('requestAttendance')
                                    ->where('request_id', $requestAttendance->id)
                                    ->get();

                // DBに承認者IDと「承認済み」を登録
                $requestAttendance->approver_id = $adminId;
                $requestAttendance->is_approved = true;
                $requestAttendance->save();

                $attendance = Attendance::where('id', $requestAttendance->attendance_id)->first();
                $attendance->breakTimes()->delete(); //休憩時間を削除してから再登録する

                if($requestBreakTimes->isNotEmpty()) {
                    foreach($requestBreakTimes as $requestBreak) {
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'start_at' => $requestBreak->requested_break_start,
                            'end_at' => $requestBreak->requested_break_end,
                        ]);
                    }
                }
                $attendance->load('breakTimes');

                // 合計休憩時間の計算
                $totalBreakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    $totalBreakSeconds += $break->start_at->diffInSeconds($break->end_at);
                }
                $totalBreakMinutes = floor($totalBreakSeconds / 60);

                // 実労働時間の計算
                $clockIn = $requestAttendance->requested_work_start;
                $clockOut = $requestAttendance->requested_work_end;
                $totalWorkSeconds = $clockIn->diffInSeconds($clockOut);
                $totalWorkMinutes = floor($totalWorkSeconds / 60) - $totalBreakMinutes;

                Attendance::where('id', $requestAttendance->attendance->id)->update([
                    'clock_in_time' => $clockIn,
                    'clock_out_time' => $clockOut,
                    'total_break_minutes' => $totalBreakMinutes,
                    'total_work_minutes' => $totalWorkMinutes,
                    'note' => $requestAttendance->note,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('勤怠修正承認エラー: ' . $e->getMessage());
            return redirect()->back()->with('error', '承認に失敗しました');
        }

        return to_route('request', ['status' => 'approved'])->with('status', '承認しました');
    }
}
