<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EditAttendanceController;
use Illuminate\Support\Facades\Route;


// 一般ユーザー（スタッフ）
Route::middleware(['auth'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'create'])->name('create');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::get('/list', [AttendanceController::class, 'index'])->name('index');
    Route::get('/detail/{id}', [EditAttendanceController::class, 'show'])->name('detail');
    Route::post('/detail/{id}', [EditAttendanceController::class, 'sendRequest'])->name('send');
});

// 管理ユーザー
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminUserController::class, 'loginForm'])->name('loginForm');
    Route::post('/login', [AdminUserController::class, 'login'])->name('login');

    Route::middleware(['auth:admin'])->group(function(){
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('index');
        Route::post('/logout', [AdminUserController::class, 'destroy'])->name('logout');
    });
});