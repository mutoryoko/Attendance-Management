<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'requested_break_start',
        'requested_break_end',
    ];

    protected $casts = [
        'requested_break_start' => 'datetime',
        'requested_break_end' => 'datetime',
    ];

    // リレーション
    public function requestAttendance()
    {
        return $this->belongsTo(RequestAttendance::class, 'request_id');
    }

    // 出勤時刻（修正）をH:i形式で取得
    public function getFormattedRequestedBreakStartAttribute(): ?string
    {
        return $this->requested_break_start ? $this->requested_break_start->format('H:i') : null;
    }

    // 退勤時刻（修正）をH:i形式で取得
    public function getFormattedRequestedBreakEndAttribute(): ?string
    {
        return $this->requested_break_end ? $this->requested_break_end->format('H:i') : null;
    }
}
