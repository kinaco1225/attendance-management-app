<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    private function staffListUrl(): string
    {
        return route('admin.staff.list'); // ← もし違ったら後で直す
    }

    private function staffAttendanceUrl(User $user, array $params = []): string
    {
        return route('admin.attendance.staff', array_merge([
            'user' => $user->id,
        ], $params));
    }

    /**
     * 管理者が全一般ユーザーの氏名とメールを確認できる
     */
    public function test_admin_can_see_all_staffs()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->staffListUrl());
        $response->assertOk();

        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
    }

    /**
     * スタッフ別勤怠が表示される
     */
    public function test_staff_attendance_is_displayed()
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

        $response = $this->get($this->staffAttendanceUrl($user));
        $response->assertOk();

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 前月ボタン
     */
    public function test_previous_month_button_works()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->staffAttendanceUrl($user, [
            'month' => '2026-02',
        ]));

        $response->assertOk();
        $response->assertSee('2026/02');
        $response->assertSee('10:00');
    }

    /**
     * 翌月ボタン
     */
    public function test_next_month_button_works()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-04-10',
            'clock_in'  => '08:30:00',
            'clock_out' => '17:30:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->staffAttendanceUrl($user, [
            'month' => '2026-04',
        ]));

        $response->assertOk();
        $response->assertSee('2026/04');
        $response->assertSee('08:30');
    }

    /**
     * 詳細遷移
     */
    public function test_click_detail_navigates_to_detail()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($admin);

        $this->get($this->staffAttendanceUrl($user))->assertOk();

        $this->get(route('admin.attendance.detail', [
            'attendance' => $attendance->id,
        ]))->assertOk();
    }
}
