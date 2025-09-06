<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', fn() => view('auth.admin_login'))->name('loginForm');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['guest:admin'])
        ->name('login.store');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware(['auth:admin'])
        ->name('admin.logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendances', fn() => '勤怠一覧')->name('index');
    });
});

Route::get('/attendance', fn() => view('attendance_create'));