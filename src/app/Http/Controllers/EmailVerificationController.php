<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EmailVerificationController extends Controller
{
    // メール認証誘導画面
    public function showNotice()
    {
        $userId = session('unauthenticated_user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return to_route('register')->withErrors('再度登録してください');
        }

        if ($user->hasVerifiedEmail()) {
            return to_route('attendance.create');
        }

        return view('auth.verify-email');
    }

    // 未認証ユーザーに再送信
    public function resendFromSession()
    {
        $userId = session('unauthenticated_user_id');

        if (!$userId) {
            return to_route('register')->withErrors('再送できません。もう一度登録してください。');
        }

        $user = User::find($userId);

        if (!$user || $user->hasVerifiedEmail()) {
            return to_route('login')->with('status', 'すでに認証済みです。');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', '認証メールを再送しました。');
    }

    // 未認証のままログインしようとした場合（念の為）
    public function resend(Request $request)
    {
        // ログイン中のユーザーが認証済みかチェック
        if ($request->user()->hasVerifiedEmail()) {
            return to_route('attendance.create');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', '認証メールを再送しました');
    }

     // メール認証処理
    public function verify($id, $hash)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return redirect('/attendance')->with('status', 'すでに認証済みです');
        }
        // ハッシュが一致するかチェック
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }
        // メール認証完了
        $user->markEmailAsVerified();
        event(new Registered($user));

        Auth::login($user);

        return to_route('attendance.create')->with('status', 'メール認証が完了しました');
    }
}
