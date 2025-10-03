<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if (!$user->hasVerifiedEmail()) {
            auth()->logout(); // ログイン直後に強制ログアウト
            session()->put('unauthenticated_user_id', $user->id); // 再送用にセッション保存
            return to_route('verification.notice');
        }

        return redirect('/attendance')->with('status', 'ログインしました');
    }
}
