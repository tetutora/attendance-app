<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;

// 会員登録画面（一般ユーザー）
Route::get('/register', [RegisterController::class, 'showRegisterForm']);

// ログイン画面（一般ユーザー）
Route::get('/login', [LoginController::class, 'showLoginForm']);

