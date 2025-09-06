<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Responses\UserLoginViewResponse;
use App\Http\Responses\UserRegisterViewResponse;
use App\Http\Responses\AdminLoginViewResponse;
use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;
use Laravel\Fortify\Contracts\RegisterViewResponse as RegisterViewResponseContract;


class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 一般ユーザー
        $this->app->singleton(LoginViewResponseContract::class, UserLoginViewResponse::class);
        $this->app->singleton(RegisterViewResponseContract::class, UserRegisterViewResponse::class);

        // 管理者用
        $this->app->singleton(AdminLoginViewResponse::class, AdminLoginViewResponse::class);
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

        // 管理者用ログイン
        Fortify::loginView(function () {
            if (request()->is('admin/*')) {
                return view('auth.admin_login');
            }
            return view('auth.login');
        });

        // 管理者用認証処理
        Fortify::authenticateUsing(function (Request $request) {
            if ($request->is('admin/*')) {
                $admin = AdminUser::where('email', $request->email)->first();

                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    return $admin;
                }
            }
            return null;
        });

        app()->bind(FortifyLoginRequest::class, LoginRequest::class);
    }
}
