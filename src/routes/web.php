<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EditAttendanceController;
use App\Http\Controllers\RequestAttendanceController;
use App\Models\RequestAttendance;
use Illuminate\Support\Facades\Route;


// 一般ユーザー
Route::middleware(['auth'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'create'])->name('create');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::get('/list', [AttendanceController::class, 'index'])->name('index');
    Route::get('/detail/{id}', [EditAttendanceController::class, 'show'])->name('detail');
    Route::post('/detail/{id}', [EditAttendanceController::class, 'sendRequest'])->name('send');
});

// 一般ユーザー・管理者共通
Route::prefix('stamp_correction_request')->controller(RequestAttendanceController::class)->group(function () {
    Route::get('/list', 'index')->middleware('auth.any')->name('request');
    Route::get('/approve/{attendance_correct_request}', 'show')->middleware('admin')->name('admin.request.detail');
    Route::post('/approve/{attendance_correct_request}', 'approve')->middleware('admin')->name('admin.approve');
});

// 管理者
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminUserController::class, 'loginForm'])->name('loginForm');
    Route::post('/login', [AdminUserController::class, 'login'])->name('login');

    Route::middleware(['auth:admin'])->group(function(){
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('index');
        Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'show'])->name('detail');
        Route::post('/attendance/detail/{id}', [AdminAttendanceController::class, 'update'])->name('update');

        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff');
        Route::get('/attendance/staff/{id}', [AdminStaffController::class, 'indexByStaff'])->name('attendance.staff');

        Route::post('/logout', [AdminUserController::class, 'destroy'])->name('logout');
    });
});