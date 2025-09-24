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

        $attendances = Attendance::where('user_id', $user->id)
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
    public function export(Request $request)
    {
        // $query = Attendance::query();

        // $query = $this->getSearchQuery($request, $query);

        // $csvData = $query->get()->toArray();

        // $csvHeader = [
        //     'id',
        //     'user_id',
        //     'work_date',
        //     'clock_in_time',
        //     'clock_out_time',
        //     'total_break_minutes',
        //     'total_work_minutes',
        //     'note',
        //     'created_at',
        //     'updated_at',
        // ];

        // $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
        //     $createCsvFile = fopen('php://output', 'w');

        //     mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);

        //     fputcsv($createCsvFile, $csvHeader);

        //     foreach ($csvData as $csv) {
        //         $csv['created_at'] = Date::make($csv['created_at'])->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s');
        //         $csv['updated_at'] = Date::make($csv['updated_at'])->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s');
        //         fputcsv($createCsvFile, $csv);
        //     }

        //     fclose($createCsvFile);
        // }, 200, [
        //     'Content-Type' => 'text/csv',
        //     'Content-Disposition' => 'attachment; filename="contacts.csv"',
        // ]);

        // return $response;
    }
}
