<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_time',
        'clock_out_time',
        'total_break_minutes',
        'total_work_minutes',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
}
