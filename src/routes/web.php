<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;


// 会員登録画面（一般ユーザー）
Route::get('/register', [RegisterController::class, 'showRegisterForm']);
// 会員登録処理（一般ユーザー）
Route::post('/register', [RegisterController::class, 'register']);

// ログイン画面（一般ユーザー）
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// ログイン処理（一般ユーザー）
Route::post('/login', [LoginController::class, 'login']);

// ログイン必須（一般ユーザー）
Route::middleware('auth')->group(function(){
    // ログアウト
    Route::post('/logout', function() {
        Auth::logout();
        return redirect('/login');
    })->name('logout');
    // 勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'showAttendancePage'])->name('general.attendance');
    // 勤怠登録処理
    Route::post('/attendance', [AttendanceController::class, 'update'])->name('general.attendance');
    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList']);
    // 勤怠詳細画面
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');

});


