<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailCustom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, MustVerifyEmailTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // メール認証
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailCustom);
    }

    // リレーション
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function requests()
    {
        return $this->hasMany(RequestAttendance::class);
    }
}
