<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/register', [UserController::class, 'registerForm'])->name('registerForm');
Route::get('/login', [UserController::class, 'loginForm'])->name('loginForm');

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.loginForm');