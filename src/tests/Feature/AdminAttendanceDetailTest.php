<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function detailUrl(Attendance $attendance): string
    {
        return route('admin.attendance.detail', [
            'attendance' => $attendance->id,
            'user_id' => $attendance->user_id,
            'date' => $attendance->work_date,
        ]);
    }

    private function updateUrl(Attendance $attendance): string
    {
        return route('admin.attendance.update', [
            'attendance' => $attendance->id,
        ]);
    }

    /**
     * 詳細画面に選択データが表示される
     */
    public function test_admin_can_view_attendance_detail()
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
            'remark'    => '管理者確認テスト',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->detailUrl($attendance));
        $response->assertOk();

        $response->assertSee('管理者確認テスト');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee($user->name);
    }

    /**
     * 出勤＞退勤でエラー
     */
    public function test_validation_error_when_clock_in_after_clock_out()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
        ]);

        $this->actingAs($admin);

        $response = $this->put($this->updateUrl($attendance), [
            'clock_in'  => '19:00',
            'clock_out' => '18:00',
            'remark'    => 'test',
            'breaks'    => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 休憩開始＞退勤でエラー
     */
    public function test_validation_error_when_break_start_after_clock_out()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
        ]);

        $this->actingAs($admin);

        $response = $this->put($this->updateUrl($attendance), [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'remark'    => 'test',
            'breaks' => [
                ['start' => '19:00', 'end' => '19:10'],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /**
     * 休憩終了＞退勤でエラー
     */
    public function test_validation_error_when_break_end_after_clock_out()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
        ]);

        $this->actingAs($admin);

        $response = $this->put($this->updateUrl($attendance), [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'remark'    => 'test',
            'breaks' => [
                ['start' => '17:50', 'end' => '18:10'],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 備考未入力でエラー
     */
    public function test_validation_error_when_remark_is_empty()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
        ]);

        $this->actingAs($admin);

        $response = $this->put($this->updateUrl($attendance), [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'remark'    => '',
            'breaks'    => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'remark' => '備考を記入してください',
        ]);
    }
}
