<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;


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
    // 勤怠修正処理
    Route::post('/attendance/{id}/update', [AttendanceController::class, 'updateAttendance'])->name('general.attendance.update');
    // 勤怠修正申請処理
    Route::get('/attendance/{id}/detail', [AttendanceController::class, 'showDetail'])->name('general.attendance_detail');
    // 勤怠修正一覧画面
    Route::get('stamp_correction_request/list', [AttendanceController::class, 'showRequest']);
});

// ログイン画面（管理者用）
Route::get('/admin/login', [AdminLoginController::class, 'admin_login'])->name('admin.login');
// ログイン処理（管理者用）
Route::post('/admin/login', [AdminLoginController::class, 'admin_authenticate'])->name('admin.authenticate');

Route::middleware([AdminMiddleware::class])->group(function () {
    // ログアウト
    Route::post('/admin/logout', function() {
        Auth::logout();
        return redirect('/admin/login');
    })->name('logout');
    // 勤怠一覧画面（管理者用）
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.attendance-list');
    // スタッフ一覧表示
    Route::get('/admin/staff/list', [AdminController::class, 'showStaffList'])->name('admin.staff-list');
    // スタッフ別勤怠一覧画面
    Route::get('admin/attendance/staff{id}', [AdminController::class, 'showStaffAttendanceList'])->name('admin.staff-attendance');
});


