<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'applier_id',
        'attendance_id',
        'approver_id',
        'requested_work_start',
        'requested_work_end',
        'is_approved',
        'note',
    ];

    protected $casts = [
        'requested_work_start' => 'datetime',
        'requested_work_end' => 'datetime',
    ];

    // リレーション
    public function applier()
    {
        return $this->belongsTo(User::class, 'applier_id');
    }

    public function approver()
    {
        return $this->belongsTo(AdminUser::class, 'approver_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestBreakTimes()
    {
        return $this->hasMany(RequestBreakTime::class);
    }

    // 出勤時刻（修正）をH:i形式で取得
    public function getFormattedRequestedWorkStartAttribute(): ?string
    {
        return $this->requested_work_start ? $this->requested_work_start->format('H:i') : null;
    }

    // 退勤時刻（修正）をH:i形式で取得
    public function getFormattedRequestedWorkEndAttribute(): ?string
    {
        return $this->requested_work_end ? $this->requested_work_end->format('H:i') : null;
    }
}
