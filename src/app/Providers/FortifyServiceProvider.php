<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Responses\LoginViewResponse;
use App\Http\Responses\RegisterViewResponse;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Http\Responses\RegisterResponse as CustomRegisterResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;
use Laravel\Fortify\Contracts\RegisterViewResponse as RegisterViewResponseContract;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;


class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 一般ユーザー用
        $this->app->singleton(LoginViewResponseContract::class, LoginViewResponse::class);
        $this->app->singleton(RegisterViewResponseContract::class, RegisterViewResponse::class);
        //　会員登録
        $this->app->singleton(RegisterResponse::class, CustomRegisterResponse::class);
        // ログイン
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        // ログアウト
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        // 一般ユーザー登録
        Fortify::createUsersUsing(CreateNewUser::class);

        //メール認証
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        // ログイン画面表示
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 不正アクセス制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // ログイン処理
        Fortify::authenticateUsing(function (Request $request) {
            // 一般ユーザー用認証処理
            $user = User::where('email', $request->email)->first();

            if($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
        });
        app()->bind(FortifyLoginRequest::class, LoginRequest::class);
    }
}
