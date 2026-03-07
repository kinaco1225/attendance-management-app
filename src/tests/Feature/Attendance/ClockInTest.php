<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが正しく機能する
     */
    public function test_user_can_clock_in()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        // ① 出勤前はボタン表示
        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        // ② 出勤処理
        $this->post('/attendance/clock-in');

        // ③ ステータス確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');

        // ④ DB確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 出勤は一日一回のみ
     */
    public function test_user_cannot_clock_in_twice_per_day()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        // 既に出勤済
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        // 出勤ボタンが出ない
        $response->assertDontSee('>出勤<', false);
    }

    /**
     * 出勤時刻が勤怠一覧で確認できる
     */
    public function test_clock_in_time_is_recorded_in_list()
    {
        Carbon::setTestNow(
            Carbon::create(2026, 3, 15, 9, 0, 0)
        );

        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        // 出勤
        $this->post('/attendance/clock-in');

        // 一覧画面
        $response = $this->get('/attendance/list');

        $response->assertSee('09:00');
    }
}
