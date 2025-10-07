<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EditAttendanceController;
use App\Http\Controllers\RequestAttendanceController;
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;


// 一般ユーザー
Route::middleware('auth', 'verified')->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'create'])->name('create');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::get('/list', [AttendanceController::class, 'index'])->name('index');
    Route::get('/detail/{id}', [EditAttendanceController::class, 'show'])->name('detail');
    Route::post('/detail/{id}', [EditAttendanceController::class, 'sendRequest'])->name('send');
});

Route::prefix('email')->name('verification.')
    ->controller(EmailVerificationController::class)->group(function () {
        // 認証処理
        Route::get('/verify/{id}/{hash}', 'verify')
            ->middleware('signed')
            ->name('verify');
        // メール認証誘導画面
        Route::get('/verify', 'showNotice')
            ->name('notice');
        // 認証メールの再送信
        Route::post('/verification-notification', 'resendNotification')
            ->middleware('throttle:6,1')
            ->name('resend');
});

// 一般ユーザー・管理者
Route::prefix('stamp_correction_request')->controller(RequestAttendanceController::class)->group(function () {
    Route::get('/list', 'index')->middleware('auth.any')->name('request');
    Route::get('/approve/{attendance_correct_request}', 'show')->middleware('auth.any')->name('request.detail');
    Route::patch('/approve/{attendance_correct_request}', 'approve')->middleware('auth:admin')->name('admin.approve');
});

// 管理者
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminUserController::class, 'loginForm'])->name('loginForm');
    Route::post('/login', [AdminUserController::class, 'login'])->name('login');

    Route::middleware('auth:admin')->group(function(){
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('index');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('detail');
        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('update');

        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff');
        Route::get('/attendance/staff/{id}', [AdminStaffController::class, 'indexByStaff'])->name('attendance.staff');
        Route::post('/attendance/staff/{id}/export', [AdminStaffController::class, 'export'])->name('attendance.export');

        Route::post('/logout', [AdminUserController::class, 'destroy'])->name('logout');
    });
});