<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 休憩入が正しく機能
     */
    public function test_user_can_start_break()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        // 出勤済状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        // ボタン表示確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩入
        $this->post('/attendance/break/start');

        // ステータス確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');

        // DB確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => Attendance::first()->id,
        ]);
    }

    /**
     * 休憩は一日何回でもできる
     */
    public function test_user_can_take_break_multiple_times()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        // 1回目
        $this->post('/attendance/break/start');
        $this->post('/attendance/break/end');

        // 再度「休憩入」が表示される
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻が正しく機能
     */
    public function test_user_can_end_break()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        $this->actingAs($user);

        // 休憩戻
        $this->post('/attendance/break/end');

        // ステータスが出勤中へ
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 休憩戻は何回でもできる
     */
    public function test_user_can_end_break_multiple_times()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        // 1回目
        $this->post('/attendance/break/start');
        $this->post('/attendance/break/end');

        // 再度休憩入
        $this->post('/attendance/break/start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * 休憩時刻が一覧で確認できる
     */
    public function test_break_time_is_recorded_in_list()
    {
        // まず基準日を固定（一覧の月と合わせる）
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-01-15',
            'clock_in'  => '09:00:00',
        ]);

        $this->actingAs($user);

        // 休憩開始 12:00
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 12, 0, 0));
        $this->post('/attendance/break/start');

        // 休憩終了 13:00（＝休憩60分 → 一覧表示は 01:00）
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 13, 0, 0));
        $this->post('/attendance/break/end');

        $response = $this->get('/attendance/list?month=2026-01');

        $response->assertSee('01:00');
    }
}
