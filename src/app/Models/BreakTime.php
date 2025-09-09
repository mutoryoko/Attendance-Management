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

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
