<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreakTime;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{

  public function create()
  {
    $attendance = Attendance::where('user_id', Auth::id())
      ->where('work_date', today())
      ->first();

    if (!$attendance || (!$attendance->clock_in && !$attendance->clock_out)) {
      $status = 'before_work';
    } elseif ($attendance->clock_in && !$attendance->clock_out) {

      $onBreak = $attendance->breaks()
        ->whereNull('break_end')
        ->exists();

      $status = $onBreak ? 'break' : 'working';
    } else {
      // clock_in && clock_out
      $status = 'finished';
    }

    return view('user.attendance.create', compact('status', 'attendance'));
  }

  public function clockIn()
  {
    $attendance = Attendance::firstOrCreate(
      [
        'user_id'   => Auth::id(),
        'work_date' => today(),
      ]
    );

    if (is_null($attendance->clock_in)) {
      $attendance->update([
        'clock_in' => now()->format('H:i:s'),
      ]);
    }

    return redirect()->route('attendance');
  }


  public function breakStart()
  {
    $attendance = Attendance::where('user_id', Auth::id())
      ->where('work_date', today())
      ->firstOrFail();

    $attendance->breaks()->create([
      'break_start' => now()->format('H:i:s'),
    ]);

    return redirect()->route('attendance');
  }

  public function breakEnd()
  {
    $attendance = Attendance::where('user_id', Auth::id())
      ->where('work_date', today())
      ->firstOrFail();

    $break = $attendance->breaks()
      ->whereNull('break_end')
      ->latest()
      ->first();

    if ($break) {
      $break->update([
        'break_end' => now()->format('H:i:s'),
      ]);
    }

    return redirect()->route('attendance');
  }

  public function clockOut()
  {
    $attendance = Attendance::where('user_id', Auth::id())
      ->where('work_date', today())
      ->firstOrFail();

    if (is_null($attendance->clock_out)) {
      $attendance->update([
        'clock_out' => now()->format('H:i:s'),
      ]);
    }

    return redirect()->route('attendance');
  }

  public function list(Request $request)
  {
    $month = $request->get('month', now()->format('Y-m'));
    $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $end   = $start->copy()->endOfMonth();

    // その月の日付分 attendance を必ず作る
    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        Attendance::firstOrCreate([
            'user_id'   => Auth::id(),
            'work_date' => $date->toDateString(),
        ]);
    }

    // 改めて取得
    $attendances = Attendance::with('breaks')
        ->where('user_id', Auth::id())
        ->whereBetween('work_date', [$start, $end])
        ->orderBy('work_date')
        ->get();

    return view('user.attendance.list', [
        'attendances'   => $attendances,
        'currentMonth' => $start,
        'prevMonth'    => $start->copy()->subMonth()->format('Y-m'),
        'nextMonth'    => $start->copy()->addMonth()->format('Y-m'),
    ]);
  }

  public function detail(Attendance $attendance)
  {
    if ($attendance->user_id !== auth()->id()) {
      abort(403);
    }

    // 最新の申請を取得
    $latestRequest = $attendance->requests()
      ->latest()
      ->first();

    $isPending = $latestRequest && $latestRequest->status === 'pending';

    // 申請中なら申請データを表示
    if ($isPending) {

      $clockIn  = $latestRequest->request_clock_in;
      $clockOut = $latestRequest->request_clock_out;
      $remark   = $latestRequest->request_remark;

      $breaks = $latestRequest->requestBreaks()
        ->orderBy('request_break_start')
        ->get();
    } else {

      $clockIn  = $attendance->clock_in;
      $clockOut = $attendance->clock_out;
      $remark   = $attendance->remark;

      $breaks = $attendance->breaks()
        ->orderBy('break_start')
        ->get();
    }

    return view('user.attendance.detail', compact(
      'attendance',
      'breaks',
      'clockIn',
      'clockOut',
      'remark',
      'isPending'
    ));
  }


  public function update(AttendanceUpdateRequest $request, Attendance $attendance)
  {
    // 承認待ち中なら再申請不可
    if ($attendance->requests()->where('status', 'pending')->exists()) {
      return back()->withErrors([
        'error' => '現在承認待ちの申請があります。',
      ]);
    }

    DB::transaction(function () use ($request, $attendance) {

      /*
        ============================================
        ① 勤怠修正申請を作成
        ============================================
        */
      $attendanceRequest = AttendanceRequest::create([
        'attendance_id'     => $attendance->id,
        'user_id'           => auth()->id(),
        'request_clock_in'  => $request->clock_in,
        'request_clock_out' => $request->clock_out,
        'request_remark'    => $request->remark,
        'status'            => 'pending',
      ]);

      /*
        ============================================
        ② 休憩修正申請を保存
        ============================================
        */
      if ($request->breaks) {

        foreach ($request->breaks as $break) {

          $start = $break['start'] ?? null;
          $end   = $break['end'] ?? null;

          // 両方空ならスキップ
          if (!$start && !$end) {
            continue;
          }

          AttendanceRequestBreakTime::create([
            'attendance_request_id' => $attendanceRequest->id,
            'request_break_start'   => $start,
            'request_break_end'     => $end,
          ]);
        }
      }
    });

    return redirect()
      ->route('attendance.detail', $attendance->id)
      ->with('success', '修正申請を行いました。');
  }

}
