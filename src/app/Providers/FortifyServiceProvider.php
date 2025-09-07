<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Responses\UserLoginViewResponse;
use App\Http\Responses\UserRegisterViewResponse;
use App\Http\Responses\AdminLoginViewResponse;
use App\Http\Responses\LogoutResponse;
use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;
use Laravel\Fortify\Contracts\RegisterViewResponse as RegisterViewResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;


class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 一般ユーザー用
        $this->app->singleton(LoginViewResponseContract::class, UserLoginViewResponse::class);
        $this->app->singleton(RegisterViewResponseContract::class, UserRegisterViewResponse::class);

        // 管理者用
        $this->app->singleton(AdminLoginViewResponse::class, AdminLoginViewResponse::class);

        // ログアウト
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        // 一般ユーザー登録
        Fortify::createUsersUsing(CreateNewUser::class);

        // 一般ユーザー用ログイン
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // ログイン画面表示
        Fortify::loginView(function () {
            if (request()->is('admin/*')) {
                return view('auth.admin_login');
            }
            return view('auth.login');
        });

        // ログイン処理
        Fortify::authenticateUsing(function (Request $request) {
            if ($request->is('admin/*')) {
                // 管理者用認証処理
                $admin = AdminUser::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    return $admin;
                }
            } else {
                // 一般ユーザー用認証処理
                $user = User::where('email', $request->email)->first();
                if($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            }
            return null;
        });
        app()->bind(FortifyLoginRequest::class, LoginRequest::class);
    }
}
