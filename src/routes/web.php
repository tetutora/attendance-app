<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Auth\VerificationController;

// メール認証
Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// CSV出力
Route::get('/attendance/csv/{userId}', [AdminController::class, 'exportCSV'])->name('attendance.exportCSV');


// 会員登録画面（一般ユーザー）
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
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
    Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('general.attendance_list');
    // 勤怠詳細画面
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('general.attendance-detail');
    // 勤怠修正処理
    Route::post('/attendance/{id}/update', [AttendanceController::class, 'updateAttendance'])->name('general.attendance.update');
    // 勤怠修正申請処理
    Route::get('/attendance/{id}/detail', [AttendanceController::class, 'showDetail'])->name('general.attendance_detail');
    // 申請一覧画面
    Route::get('stamp_correction_request/list', [AttendanceController::class, 'showRequest'])->name('general.correction-requests');
});

// ログイン画面（管理者用）
Route::get('/admin/login', [AdminLoginController::class, 'admin_login'])->name('admin.login');
// ログイン処理（管理者用）
Route::post('/admin/login', [AdminLoginController::class, 'admin_authenticate'])->name('admin.authenticate');

// ログイン必須（管理者）
Route::middleware([AdminMiddleware::class])->group(function () {
    // ログアウト
    Route::post('/admin/logout', function() {
        Auth::logout();
        return redirect('/admin/login');
    })->name('logout');
    // 勤怠一覧画面（管理者用）
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.attendance-list');
    // 勤怠詳細画面
    Route::get('/admin/attendance/{id}', [AdminController::class, 'showDetail']);
    // スタッフ一覧表示
    Route::get('/admin/staff/list', [AdminController::class, 'showStaffList'])->name('admin.staff-list');
    // スタッフ別勤怠一覧画面
    Route::get('admin/attendance/staff/{id}', [AdminController::class, 'showStaffAttendanceList'])->name('admin.staff-attendance');
    // 申請一覧画面
    Route::get('/admin/stamp_correction_request/list', [AdminController::class, 'showCorrectionRequests'])->name('admin.correction-requests');
    // 勤怠詳細画面
    Route::get('/admin/attendance-detail/{attendance}', [AdminController::class, 'showAttendanceDetail'])
    ->name('admin.attendance-detail');
    // 勤怠詳細修正画面
    Route::post('/admin/attendance-detail/{attendance}', [AdminController::class, 'updateAttendanceDetail'])
    ->name('admin.attendance-detail.update');
    // 勤怠修正申請詳細画面
    Route::get('/admin/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'showRequestAttendanceDetail'])
    ->name('admin.stamp_correction_request.approve');
    // 承認処理
    Route::post('/admin/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'approve'])
    ->name('admin.attendance.approve');
});
