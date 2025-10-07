<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // 未認証ユーザーの場合
        if (!$user->hasVerifiedEmail()) {
            auth()->logout();
            session()->put('unauthenticated_user_id', $user->id); // メール再送用にセッション保存
            return to_route('verification.notice');
        }

        return redirect('/attendance')->with('status', 'ログインしました');
    }
}
