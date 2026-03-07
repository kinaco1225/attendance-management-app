<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function listUrl(array $params = []): string
    {
        return route('admin.attendance.list', $params);
    }

    /**
     * その日の全ユーザー勤怠が確認できる
     */
    public function test_admin_can_see_all_users_attendance_for_the_day()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->listUrl());
        $response->assertOk();

        // ユーザー名が表示
        $response->assertSee($user->name);

        // 打刻が表示
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 遷移時に現在日付が表示される
     */
    public function test_current_date_is_displayed()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $response = $this->get($this->listUrl());
        $response->assertOk();

        // Blade: Y年n月j日
        $response->assertSee('2026年3月3日');
    }

    /**
     * 前日ボタンで前日の勤怠が表示
     */
    public function test_previous_day_button_works()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-02',
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->listUrl([
            'date' => '2026-03-02',
        ]));

        $response->assertOk();
        $response->assertSee('2026年3月2日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * 翌日ボタンで翌日の勤怠が表示
     */
    public function test_next_day_button_works()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-04',
            'clock_in'  => '08:30:00',
            'clock_out' => '17:30:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->listUrl([
            'date' => '2026-03-04',
        ]));

        $response->assertOk();
        $response->assertSee('2026年3月4日');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }
}
