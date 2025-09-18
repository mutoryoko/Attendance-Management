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
}
