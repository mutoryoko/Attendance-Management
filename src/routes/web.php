<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', fn() => view('auth.admin_login'))->name('loginForm');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('admin.logout');
    Route::get('/attendances', fn() => view('auth.admin_index'))->name('index');
});

Route::get('/attendance', fn() => view('attendance_create'));