<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
  public function run(): void
  {
    // userロールのみ
    $users = User::where('role', 'user')->get();

    $startDate = Carbon::today()->subDays(30);
        $endDate = Carbon::yesterday();

    foreach ($users as $user) {

      for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

        // 土日はスキップ
        if ($date->isWeekend()) {
            continue;
        }

        // ========================
        // 出勤・退勤ランダム
        // ========================

        // 出勤：7:50〜8:10
        $clockIn = $date->copy()->setTime(7, 50)
            ->addMinutes(rand(0, 20));

        // 退勤：16:50〜18:30
        $clockOut = $date->copy()->setTime(16, 50)
            ->addMinutes(rand(0, 100));

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $date->toDateString(),
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'remark'    => null,
        ]);

        // ========================
        // 休憩（1〜2回ランダム）
        // ========================

        $breakCount = rand(1, 2);

        $currentStart = $date->copy()->setTime(11, 30);

        for ($i = 0; $i < $breakCount; $i++) {

            // 開始：11:30〜14:30の間でずらす
            $breakStart = $currentStart->copy()
                ->addMinutes(rand(0, 60));

            // 休憩長さ：30〜60分
            $breakEnd = $breakStart->copy()
                ->addMinutes(rand(30, 60));

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start'   => $breakStart,
                'break_end'     => $breakEnd,
            ]);

            // 次の休憩の基準位置を後ろへ
            $currentStart = $breakEnd->copy()->addMinutes(30);
        }
      }
    }
  }
}
