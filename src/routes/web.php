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
    // 勤怠登録ページ表示
    Route::get('/attendance', [AttendanceController::class, 'showAttendancePage'])->name('general.attendance');
    Route::post('/attendance', [AttendanceController::class, 'update'])->name('general.attendance');
    // Route::post('/attendance', [AttendanceController::class, 'clockIn'])->name('general.attendance');
    // // 勤怠一覧ページ
    // Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    // // 出勤処理
    // Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    // // 退勤処理
    // Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    // // 休憩開始
    // Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.startBreak');
    // // 休憩終了
    // Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.endBreak');
});


