<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', fn() => view('auth.admin_login'))->name('loginForm');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::middleware(['auth:admin'])->group(function(){
        Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('index');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});