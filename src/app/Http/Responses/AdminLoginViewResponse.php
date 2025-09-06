<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;

class AdminLoginViewResponse implements LoginViewResponseContract
{
    public function toResponse($request)
    {
        return view('auth.admin_login');
    }
}