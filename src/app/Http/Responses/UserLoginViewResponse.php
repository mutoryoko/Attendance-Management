<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;

class UserLoginViewResponse implements LoginViewResponseContract
{
    public function toResponse($request)
    {
        return view('auth.login');
    }
}