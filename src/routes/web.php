<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;

// 会員登録画面（一般ユーザー）
Route::get('/register', [RegisterController::class, 'showRegisterForm']);
// 会員登録処理（一般ユーザー）
Route::post('/register', [RegisterController::class, 'register']);

// ログイン画面（一般ユーザー）
Route::get('/login', [LoginController::class, 'showLoginForm']);
// ログイン処理（一般ユーザー）
Route::post('/login', [LoginController::class, 'login']);

