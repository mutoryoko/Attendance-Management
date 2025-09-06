<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'start_at',
        'end_at',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
