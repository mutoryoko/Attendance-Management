<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_time',
        'clock_out_time',
        'total_break_minutes',
        'total_work_minutes',
        'note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_time' => 'datetime:H:i:s',
        'clock_out_time' => 'datetime:H:i:s',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function requestAttendances()
    {
        return $this->hasMany(RequestAttendance::class);
    }

    public function pendingRequest()
    {
        return $this->hasOne(RequestAttendance::class)->where('is_approved', false);
    }

    // 最新の勤怠修正申請を1件取得する
    public function latestRequest()
    {
        return $this->hasOne(RequestAttendance::class)->latestOfMany();
    }

    // 出勤時刻をH:i形式で取得
    public function getFormattedWorkStartAttribute(): ?string
    {
        return $this->clock_in_time ? $this->clock_in_time->format('H:i') : null;
    }

    // 退勤時刻をH:i形式で取得
    public function getFormattedWorkEndAttribute(): ?string
    {
        return $this->clock_out_time ? $this->clock_out_time->format('H:i') : null;
    }

    // 合計休憩時間をH:i形式で取得
    public function getFormattedBreakTimeAttribute(): ?string
    {
        $totalMinutes = $this->total_break_minutes;

        if (empty($totalMinutes)) {
            return null;
        }

        return sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
    }

    // 実労働時間をH:i形式で取得
    public function getFormattedWorkTimeAttribute(): ?string
    {
        $totalMinutes = $this->total_work_minutes;

        if (empty($totalMinutes)) {
            return null;
        }

        return sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
    }

    // 合計休憩時間（分）を動的に計算する
    protected function totalBreakMinutes(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // breakTimesリレーションが読み込まれていれば計算する
                if ($this->relationLoaded('breakTimes')) {
                    $totalSeconds = 0;
                    foreach ($this->breakTimes as $break) {
                        $totalSeconds += $break->end_at->diffInSeconds($break->start_at);
                    }
                    return (int) floor($totalSeconds / 60);
                }
                // 読み込まれていなければ、DBに保存された値を返す
                return $value;
            }
        );
    }

    // 実労働時間（分）を動的に計算する
    protected function totalWorkMinutes(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // 出退勤時刻がなければ計算しない
                if (!$this->clock_in_time || !$this->clock_out_time) {
                    return 0;
                }

                $totalMinutes = $this->clock_out_time->diffInMinutes($this->clock_in_time);;
                $breakMinutes = $this->total_break_minutes;

                return $totalMinutes - $breakMinutes;
            }
        );
    }
}
