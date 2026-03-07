<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
  public function list(Request $request)
  {
    // 日付取得（デフォルトは今日）
    $date = $request->get('date', now()->toDateString());
    $currentDate = Carbon::parse($date);

    // 全ユーザー取得（admin除外）
    $users = User::where('role', 'user')->get();

    // その日の勤怠をまとめて取得
    $attendances = Attendance::with('breaks')
      ->where('work_date', $currentDate->toDateString())
      ->get()
      ->keyBy('user_id');

    return view('admin.attendance.list', [
      'users'        => $users,
      'attendances'  => $attendances,
      'currentDate'  => $currentDate,
      'prevDate'     => $currentDate->copy()->subDay()->toDateString(),
      'nextDate'     => $currentDate->copy()->addDay()->toDateString(),
    ]);
  }


  public function detail(Request $request, $attendance = null)
  {
    $attendance = Attendance::find($attendance);

    $userId = $request->get('user_id');
    $user = $attendance?->user ?? ($userId ? User::find($userId) : null);

    $workDate = $attendance?->work_date ?? $request->get('date');

    if (!$attendance) {
      return view('admin.attendance.detail', [
        'attendance' => null,
        'user'       => $user,
        'workDate'   => $workDate,
        'breaks'     => collect(),
        'clockIn'    => null,
        'clockOut'   => null,
        'remark'     => null,
        'isPending'  => false,
      ]);
    }

    $latestRequest = $attendance->requests()->latest()->first();
    $isPending = $latestRequest && $latestRequest->status === 'pending';

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

    return view('admin.attendance.detail', compact(
      'attendance',
      'user',
      'workDate',
      'breaks',
      'clockIn',
      'clockOut',
      'remark',
      'isPending'
    ));
  }

  public function update(AttendanceUpdateRequest $request, Attendance $attendance)
  {
    /* dd($request->all()); */
    $attendance->update([
      'clock_in'  => $request->clock_in,
      'clock_out' => $request->clock_out,
      'remark'    => $request->remark,
    ]);

    foreach ($request->input('breaks', []) as $breakData) {

      // 両方空ならスキップ
      if (empty($breakData['start']) && empty($breakData['end'])) {
        continue;
      }

      // 既存 → 更新
      if (!empty($breakData['id'])) {

        $break = $attendance->breaks()
          ->where('id', $breakData['id'])
          ->first();

        if ($break) {
          $break->update([
            'break_start' => $breakData['start'],
            'break_end'   => $breakData['end'],
          ]);
        }
      } else {
        $attendance->breaks()->create([
          'break_start' => $breakData['start'],
          'break_end'   => $breakData['end'],
        ]);
      }
    }

    $from  = $request->input('from');
    $month = $request->input('month');
    $date  = $request->input('date');

    if ($from === 'staff') {
      return redirect()
        ->route('admin.attendance.staff', [
          'user'  => $attendance->user_id,
          'month' => $month ?? now()->format('Y-m'),
        ])
        ->with('success', '勤怠を更新しました');
    }

    return redirect()
      ->route('admin.attendance.list', [
        'date' => $date ?? $attendance->work_date,
      ])
      ->with('success', '勤怠を更新しました');
  }

  public function staff(Request $request, User $user)
  {
    $month = $request->get('month', now()->format('Y-m'));
    $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $end   = $start->copy()->endOfMonth();

    // その月の日付分 attendance を必ず作る
    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
      Attendance::firstOrCreate([
        'user_id' => $user->id,
        'work_date' => $date->toDateString(),
      ]);
    }

    // 改めて取得
    $attendances = Attendance::with('breaks')
      ->where('user_id', $user->id)
      ->whereBetween('work_date', [$start, $end])
      ->orderBy('work_date')
      ->get();

    return view('admin.attendance.staff', [
      'user' => $user,
      'attendances' => $attendances,
      'currentMonth' => $start,
      'prevMonth' => $start->copy()->subMonth()->format('Y-m'),
      'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
    ]);
  }

  public function store(AttendanceUpdateRequest $request)
  {
    $attendance = Attendance::create([
      'user_id'   => $request->user_id,
      'work_date' => $request->date,
      'clock_in'  => $request->clock_in,
      'clock_out' => $request->clock_out,
      'remark'    => $request->remark,
    ]);

    foreach ($request->input('breaks', []) as $breakData) {

      if (empty($breakData['start']) && empty($breakData['end'])) {
        continue;
      }

      $attendance->breaks()->create([
        'break_start' => $breakData['start'],
        'break_end'   => $breakData['end'],
      ]);
    }

    $from  = $request->input('from');
    $month = $request->input('month');
    $date  = $request->input('date');

    if ($from === 'staff') {
      return redirect()
        ->route('admin.attendance.staff', [
          'user'  => $attendance->user_id,
          'month' => $month ?? now()->format('Y-m'),
        ])
        ->with('success', '勤怠を作成しました');
    }

    return redirect()
      ->route('admin.attendance.list', [
        'date' => $date ?? $attendance->work_date,
      ])
      ->with('success', '勤怠を作成しました');
  }

}
