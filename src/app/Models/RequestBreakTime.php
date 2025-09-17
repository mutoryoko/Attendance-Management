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

    public function requestAttendance()
    {
        return $this->belongsTo(RequestAttendance::class);
    }
}
