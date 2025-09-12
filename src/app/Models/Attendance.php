<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];

    // リレーション
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function request()
    {
        return $this->hasOne(Request::class);
    }

    // 出勤時刻をH:i形式で取得
    public function getFormattedClockInTimeAttribute()
    {
        return $this->clock_in_time ? $this->clock_in_time->format('H:i') : null;
    }

    // 退勤時刻をH:i形式で取得
    public function getFormattedClockOutTimeAttribute()
    {
        return $this->clock_out_time ? $this->clock_out_time->format('H:i') : null;
    }
}
