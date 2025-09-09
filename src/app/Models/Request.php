<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'admin_user_id',
        'requested_clock_in',
        'requested_clock_out',
        'is_approved',
        'note',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
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
