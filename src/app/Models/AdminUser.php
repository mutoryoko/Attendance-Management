<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Authenticatable
{
    use HasFactory;

    protected $guard = 'admin';

    protected $fillable = ['email', 'password'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
