<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeTimeRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面（日次）
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::now()->format('Y-m-d'));
        $currentDay= Carbon::parse($date);

        $users = User::with(['attendances' => function ($query) use ($currentDay) {
            $query->whereDate('work_date', $currentDay);
        }])->select(['id', 'name'])->get();

        $prevDay = $currentDay->copy()->subDay()->format('Y-m-d');
        $nextDay = $currentDay->copy()->addDay()->format('Y-m-d');

        return view('admin.index', compact(
            'currentDay',
            'users',
            'prevDay',
            'nextDay'
        ));
    }

    //　勤怠詳細画面
    public function show(Request $request, string $id)
    {
        $user = null;
        $attendance = null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $id)) {
            // 日付形式の場合 (欠勤日のリンクから来た場合)
            $date = $id;
            $userId = $request->query('user');

            if(!$userId) {
                abort(400, 'ユーザーが指定されていません。');
            }

            $user = User::findOrFail($userId);

            $attendance = Attendance::firstOrNew([
                'user_id' => $user->id,
                'work_date' => $date,
            ]);
        } else {
            // 数値IDの場合 (出勤日のリンクから来た場合)
            $attendance = Attendance::with('user', 'breakTimes', 'pendingRequest')
                        ->findOrFail($id);
            $user = $attendance->user;
        }

        return view('detail', compact('attendance', 'user'));
    }

    // 勤怠更新処理
    public function update(ChangeTimeRequest $request, string $id)
    {
        $validatedData = $request->validated();

        try {
            DB::transaction(function () use($validatedData, $id) {
                $attendance = null;

                if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $id)) {
                    // {id}が日付の場合
                    $workDate = $id;
                    $attendance = Attendance::updateOrCreate(
                        [ // 第1引数：検索条件の配列
                            'user_id'   => $validatedData['user_id'],
                            'work_date' => $workDate,
                        ],
                        [ // 第2引数：登録または更新する値の配列
                            'clock_in_time'  => $validatedData['requested_work_start'],
                            'clock_out_time' => $validatedData['requested_work_end'],
                            'note'           => $validatedData['note'],
                        ]
                    );
                } else {
                    // {id}が数値の場合
                    $attendance = Attendance::findOrFail($id);
                    $attendance->update([
                        'clock_in_time'  => $validatedData['requested_work_start'],
                        'clock_out_time' => $validatedData['requested_work_end'],
                        'note'           => $validatedData['note'],
                    ]);
                }
                // 既存の休憩時間をクリア
                $attendance->breakTimes()->delete();

                if (isset($validatedData['breaks'])) {
                    foreach ($validatedData['breaks'] as $breakData) {
                        if (!empty($breakData['start']) && !empty($breakData['end'])) {
                            $attendance->breakTimes()->create([
                                'start_at' => $breakData['start'],
                                'end_at' => $breakData['end'],
                            ]);
                        }
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
                $clockIn = new Carbon($validatedData['requested_work_start']);
                $clockOut = new Carbon($validatedData['requested_work_end']);
                $totalWorkSeconds = $clockIn->diffInSeconds($clockOut);
                $totalWorkMinutes = floor($totalWorkSeconds / 60) - $totalBreakMinutes;

                // 勤怠情報の更新
                $attendance->update([
                    'total_break_minutes' => $totalBreakMinutes,
                    'total_work_minutes' => $totalWorkMinutes,
                ]);

                return $attendance;
            });
        } catch(\Exception $e) {
            Log::error('勤怠更新エラー: ' . $e->getMessage());
            return redirect()->back()->with('error', '更新に失敗しました');
        }

        return redirect()->back()->with('status', '勤怠情報を修正しました');
    }
}
