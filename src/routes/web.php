<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminStampCorrectionRequestController;
/*
|--------------------------------------------------------------------------
| メール認証関連（未ログインでも表示可）
|--------------------------------------------------------------------------
*/

// 認証メール送信完了画面
Route::get('/email/verify/send', [EmailVerificationController::class, 'sent'])
    ->name('verification.sent');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /**
         * リスト画面
         */
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('attendance.list');

        /**
         * 勤怠詳細画面
         */
        Route::get(
            '/attendance/detail/{attendance?}',
            [AdminAttendanceController::class, 'detail']
        )->name('attendance.detail');

        /**
         * 勤怠更新
         */
        Route::put(
            '/attendance/update/{attendance}',
            [AdminAttendanceController::class, 'update']
        )->name('attendance.update');

        /**
         * 勤怠追加
         */
        Route::post(
            '/attendance/store',
            [AdminAttendanceController::class, 'store']
        )->name('attendance.store');

        /**
         * スタッフ一覧
         */
        Route::get('/staff/list', [StaffController::class, 'index'])
            ->name('staff.list');

        /**
         * スタッフ別勤怠一覧
         */
        Route::get(
            '/attendance/staff/{user}',
            [AdminAttendanceController::class, 'staff']
        )->name('attendance.staff');

        /**
         * 申請一覧
         */
        Route::get(
            '/list',
            [AdminStampCorrectionRequestController::class, 'index']
        )->name('stamp_correction_request.list');

        /**
         * 承認画面
         */
        Route::get(
            '/approve/{attendance_correct_request}',
            [AdminStampCorrectionRequestController::class, 'approve']
        )->name('stamp_correction_request.approve');

        /**
         * 承認実行
         */
        Route::put(
            '/approve/{attendance_correct_request}',
            [AdminStampCorrectionRequestController::class, 'update']
        )->name('stamp_correction_request.update');

        /**
         * csv出力
         */
        Route::get(
            '/staff/{user}/attendance/csv',
            [StaffController::class, 'exportCsv']
        )->name('staff.attendance.csv');
    });





/*
|--------------------------------------------------------------------------
| User（ログイン必須）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | メール認証（ログイン後・未認証OK）
    |--------------------------------------------------------------------------
    */

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| User（ログイン + メール認証 必須）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /**
     * 勤怠画面
     */
    Route::get('/attendance', [AttendanceController::class, 'create'])
        ->name('attendance');

    /**
     * 勤怠一覧画面
     */
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    /**
     * 勤怠詳細画面
     */
    Route::get(
        '/attendance/detail/{attendance}',
        [AttendanceController::class, 'detail']
    )
        ->name('attendance.detail');

    /**
     * 申請一覧画面
     */
    Route::get(
        '/stamp-correction-requests',
        [StampCorrectionRequestController::class, 'index']
    )->name('stamp_correction_requests.list');

    /**
     * 出勤
     */
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clock_in');

    /**
     * 休憩入
     */
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break.start');

    /**
     * 休憩戻
     */
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break.end');

    /**
     * 退勤
     */
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clock_out');

    /**
     * 修正申請（保存）
     */
    Route::put(
        '/attendance/{attendance}/update',
        [AttendanceController::class, 'update']
    )
        ->name('attendance.update');
});

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});
