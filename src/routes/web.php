<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminUserController::class, 'loginForm'])->name('loginForm');
    Route::post('/login', [AdminUserController::class, 'login'])->name('login');

    Route::middleware(['auth:admin'])->group(function(){
        Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('index');
        Route::post('/logout', [AdminUserController::class, 'destroy'])->name('logout');
    });
});