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
        $user = null;

        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $userId = session('unauthenticated_user_id');
            $user = $userId ? User::find($userId) : null;
        }

        if (!$user) {
            return to_route('register')->with('error', 'ユーザー情報が見つかりません。再度登録してください。');
        }

        if ($user->hasVerifiedEmail()) {
            return Auth::check()
                ? to_route('attendance.create')
                : to_route('login')->with('status', 'メール認証は完了しています。ログインしてください。');
        }

        return view('auth.verify-email');
    }

    // メール再送信
    public function resendNotification(Request $request)
    {
        $user = null;

        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $userId = session('unauthenticated_user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        // 対象のユーザーが見つからない場合
        if (!$user) {
            // ログイン状態に応じてリダイレクト先を変更
            return Auth::check() ? redirect('/logout') : to_route('register')
                ->with('error', 'ユーザー情報が見つかりません。もう一度登録してください。');
        }

        // すでに認証済みの場合
        if ($user->hasVerifiedEmail()) {
            return to_route('attendance.create')->with('status', 'すでに認証済みです');
        }

        $user->sendEmailVerificationNotification();

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
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }
        // メール認証完了
        $user->markEmailAsVerified();
        event(new Registered($user));

        Auth::login($user);

        return to_route('attendance.create')->with('status', 'メール認証が完了しました');
    }
}
