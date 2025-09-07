<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        if ($request->is('admin/*')) {
            return redirect('/admin/login')->with('status', 'ログアウトしました');
        }
        return redirect('/login')->with('status', 'ログアウトしました');
    }
}
