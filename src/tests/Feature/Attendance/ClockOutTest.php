<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class ClockOutTest extends TestCase
{
    public function test_user_can_clock_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        // 出勤済状態を作る
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-15',
            'clock_in'  => '09:00:00',
        ]);

        $this->actingAs($user);

        // 退勤実行（18:00）
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 18, 0, 0));
        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance');

        // ステータス確認
        $response->assertSee('退勤済');
    }

    public function test_clock_out_time_is_recorded_in_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-15',
            'clock_in'  => '09:00:00',
        ]);

        $this->actingAs($user);

        // 退勤（18:00）
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 18, 0, 0));
        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance/list?month=2026-01');

        // ✅ 一覧に退勤時刻
        $response->assertSee('18:00');
    }
}
