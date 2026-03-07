<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;


class StaffController extends Controller
{
  public function index()
  {

    $staffs = User::query()
      -> where('role', 'user')
      ->orderBy('id')
      ->get();

    return view('admin.staff.list',compact('staffs'));
  }

  public function exportCsv(User $user, Request $request): StreamedResponse
  {
    $month = $request->get('month', now()->format('Y-m'));

    $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $end   = $start->copy()->endOfMonth();

    $attendances = Attendance::with('breaks')
      ->where('user_id', $user->id)
      ->whereBetween('work_date', [$start, $end])
      ->orderBy('work_date')
      ->get();

    $fileName = 'attendance_' . $user->id . '_' . $month . '.csv';

    $headers = [
      'Content-Type'        => 'text/csv; charset=UTF-8',
      'Content-Disposition' => "attachment; filename={$fileName}",
    ];

    return response()->stream(function () use ($attendances, $user) {

      $handle = fopen('php://output', 'w');

      // ⭐ BOM（Excel文字化け対策）
      fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

      // ヘッダー行
      fputcsv($handle, [
        '名前',
        '日付',
        '出勤',
        '退勤',
        '休憩合計',
        '勤務時間',
      ]);

      foreach ($attendances as $attendance) {

        $breakMinutes = $attendance->breaks->sum(function ($break) {
          if (!$break->break_start || !$break->break_end) {
            return 0;
          }

          return Carbon::parse($break->break_start)
            ->diffInMinutes(Carbon::parse($break->break_end));
        });

        $workMinutes = 0;

        if ($attendance->clock_in && $attendance->clock_out) {
          $workMinutes =
            Carbon::parse($attendance->clock_in)
            ->diffInMinutes(Carbon::parse($attendance->clock_out))
            - $breakMinutes;
        }

        fputcsv($handle, [
          $user->name,
          $attendance->work_date,
          $attendance->clock_in,
          $attendance->clock_out,
          $breakMinutes > 0
            ? gmdate('H:i', $breakMinutes * 60)
            : '',
          $workMinutes > 0 ? gmdate('H:i', $workMinutes * 60) : '',
        ]);
      }

      fclose($handle);
    }, 200, $headers);
  }
}
