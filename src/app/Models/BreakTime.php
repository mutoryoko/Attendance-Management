<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'attendance_id',
        'start_at',
        'end_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    // リレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 休憩開始時刻をH:i形式で取得
    public function getFormattedBreakStartAttribute(): ?string
    {
        return $this->start_at ? $this->start_at->format('H:i') : null;
    }

    // 休憩終了時刻をH:i形式で取得
    public function getFormattedBreakEndAttribute(): ?string
    {
        return $this->end_at ? $this->end_at->format('H:i') : null;
    }
}
