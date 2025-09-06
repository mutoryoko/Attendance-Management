<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    use HasFactory;

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
