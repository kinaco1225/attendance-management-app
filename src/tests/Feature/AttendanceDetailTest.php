<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_detail_page_shows_logged_in_user_name()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertOk();

        $response->assertSee('山田 太郎');
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_detail_page_shows_selected_date()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => null,
            'clock_out' => null,
            'remark'    => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertOk();

        $response->assertSee('2026年');
        $response->assertSee('3月3日');
    }

    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_detail_page_shows_clock_in_and_out_times()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:15:00',
            'clock_out' => '18:45:00',
            'remark'    => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertOk();

        $response->assertSee('09:15');
        $response->assertSee('18:45');
    }

    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_detail_page_shows_break_times()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => null,
        ]);

        // 休憩を1件作る（あなたの controller が breaks() を使っているので同じ方法）
        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end'   => '13:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertOk();

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
