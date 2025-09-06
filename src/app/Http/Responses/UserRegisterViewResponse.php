<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterViewResponse as RegisterViewResponseContract;

class UserRegisterViewResponse implements RegisterViewResponseContract
{
    public function toResponse($request)
    {
        return view('auth.register');
    }
}