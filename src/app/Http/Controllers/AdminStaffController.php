<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminStaffController extends Controller
{
    // スタッフ一覧
    public function index()
    {
        $users = User::all();

        return view('admin.staff', compact('users'));
    }

    // スタッフ別勤怠一覧
    public function indexByStaff(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $period = CarbonPeriod::create($startOfMonth, '1 day', $endOfMonth);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->orderBy('work_date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        return view('admin.index_by_staff', compact(
            'user',
            'attendances',
            'period',
            'currentMonth',
            'prevMonth',
            'nextMonth'
        ));
    }

    // csvエクスポート
    public function export(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $monthQuery = $request->query('month', Carbon::now()->format('Y-m'));
        $targetDate = Carbon::parse($monthQuery);
        $year = $targetDate->year;
        $month = $targetDate->month;

        $csvHeader = ['ID', '出勤日', '出勤時間', '退勤時間', '合計休憩時間（分）', '実労働時間（分）', '備考', '作成日', '更新日',];

        $fileName = $user->name . '_' . $targetDate->format('Y年m月') . '勤怠情報.csv';

        return response()->streamDownload(function () use ($id, $year, $month, $csvHeader) {
            $file = fopen('php://output', 'w');

            // 文字化け対策（BOM）
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $csvHeader);

            $attendances = Attendance::with('breakTimes')
                ->where('user_id', $id)
                ->whereYear('work_date', $year)
                ->whereMonth('work_date', $month)
                ->orderBy('work_date', 'asc')
                ->get();

            foreach($attendances as $attendance) {
                $row = [
                    $attendance->id,
                    $attendance->work_date->format('Y-m-d'),
                    $attendance->clock_in_time->format('H:i:s'),
                    $attendance->clock_out_time->format('H:i:s'),
                    $attendance->total_break_minutes,
                    $attendance->total_work_minutes,
                    $attendance->note,
                    $attendance->created_at->format('Y-m-d H:i:s'),
                    $attendance->updated_at->format('Y-m-d H:i:s'),
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        }, $fileName);
    }
}