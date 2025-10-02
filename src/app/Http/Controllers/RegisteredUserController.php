<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Actions\Fortify\CreateNewUser;

class RegisteredUserController extends Controller
{
    public function store(Request $request, CreateNewUser $creator)
    {
        $user = $creator->create($request->all());
        // 登録イベントを発火。 認証メールが送信される
        event(new Registered($user));
        // 未認証ユーザーをセッションに保存
        session()->put('unauthenticated_user', $user->id);

        return to_route('verification.notice');
    }
}
