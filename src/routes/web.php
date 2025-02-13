<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;

// 会員登録画面（一般ユーザー）
Route::get('/register', [RegisterController::class, 'showRegisterForm']);

