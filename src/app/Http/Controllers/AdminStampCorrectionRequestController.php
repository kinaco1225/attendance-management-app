<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;

class AdminStampCorrectionRequestController extends Controller
{
  public function index(Request $request)
  {
    $status = $request->get('status', 'pending');

    $requests = AttendanceRequest::with(['attendance', 'user'])
      ->where('status', $status)
      ->orderBy(
        Attendance::select('work_date')
          ->whereColumn('attendances.id', 'attendance_requests.attendance_id')
      )
      ->get();

    return view('admin.stamp_correction_requests.list', compact('requests', 'status'));
  }

  public function approve(AttendanceRequest $attendance_correct_request)
  {
    // リレーション読み込み
    $attendance_correct_request->load([
      'attendance.breaks',
      'requestBreaks',
      'user',
    ]);

    $attendance = $attendance_correct_request->attendance;
    $user       = $attendance_correct_request->user;

    $clockIn  = $attendance_correct_request->request_clock_in;
    $clockOut = $attendance_correct_request->request_clock_out;
    $remark   = $attendance_correct_request->request_remark;

    $breaks = $attendance_correct_request->requestBreaks;

    return view('admin.stamp_correction_requests.approve', compact(
      'attendance_correct_request',
      'attendance',
      'user',
      'clockIn',
      'clockOut',
      'remark',
      'breaks'
    ));
  }

  public function update(AttendanceRequest $attendance_correct_request)
  {
    DB::transaction(function () use ($attendance_correct_request) {

      // ⭐ 勤怠取得
      $attendance = $attendance_correct_request->attendance;

      // =========================
      // 勤怠更新
      // =========================
      $attendance->update([
        'clock_in'  => $attendance_correct_request->request_clock_in,
        'clock_out' => $attendance_correct_request->request_clock_out,
        'remark'    => $attendance_correct_request->request_remark,
      ]);

      // =========================
      // 既存休憩を一旦削除
      // =========================
      $attendance->breaks()->delete();

      // =========================
      // 申請休憩 → break_times に反映
      // =========================
      foreach ($attendance_correct_request->requestBreaks as $reqBreak) {

        // 両方空はスキップ
        if (!$reqBreak->request_break_start && !$reqBreak->request_break_end) {
          continue;
        }

        BreakTime::create([
          'attendance_id' => $attendance->id,
          'break_start'   => $reqBreak->request_break_start,
          'break_end'     => $reqBreak->request_break_end,
        ]);
      }

      // =========================
      // 申請ステータス更新
      // =========================
      $attendance_correct_request->update([
        'status' => 'approved',
      ]);
    });

    return redirect()
      ->route('admin.stamp_correction_request.list')
      ->with('success', '修正申請を承認しました');
  }
}
