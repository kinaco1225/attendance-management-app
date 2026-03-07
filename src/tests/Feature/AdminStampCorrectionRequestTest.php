<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function listUrl(array $params = []): string
    {
        return route('admin.stamp_correction_request.list', $params);
    }

    private function detailUrl(int $id): string
    {
        return route('admin.stamp_correction_request.approve', $id);
    }

    /**
     * 承認待ちが全て表示
     */
    public function test_pending_requests_are_displayed_for_admin()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-03',
        ]);

        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_clock_in' => '09:00',
            'request_clock_out' => '18:00',
            'request_remark' => '修正申請',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->listUrl([
            'status' => 'pending',
        ]));

        $response->assertOk();
        $response->assertSee('承認待ち');
        $response->assertSee($user->name);
    }

    /**
     * 承認済みが表示
     */
    public function test_approved_requests_are_displayed_for_admin()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-03',
        ]);

        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_clock_in' => '09:00',
            'request_clock_out' => '18:00',
            'request_remark' => '承認済み申請',
            'status' => 'approved',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->listUrl([
            'status' => 'approved',
        ]));

        $response->assertOk();
        $response->assertSee('承認済み');
        $response->assertSee($user->name);
    }

    /**
     * 詳細内容が正しく表示
     */
    public function test_request_detail_is_displayed_correctly()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-03',
        ]);

        $request = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_clock_in' => '09:10',
            'request_clock_out' => '18:20',
            'request_remark' => '内容確認テスト',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->get($this->detailUrl($request->id));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee('09:10');
        $response->assertSee('18:20');
        $response->assertSee('内容確認テスト');
    }

    /**
     * 承認処理が正しく行われる
     */
    public function test_admin_can_approve_request_and_attendance_is_updated()
    {
        Carbon::setTestNow('2026-03-03');

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-03',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'remark' => 'before',
        ]);

        $request = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_clock_in' => '09:30',
            'request_clock_out' => '18:30',
            'request_remark' => 'after',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->put(
            route('admin.stamp_correction_request.update', $request->id),
            [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'remark' => 'after',
                'breaks' => [],
            ]
        );

        $response->assertStatus(302);

        // 申請が承認済みになる
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        // 勤怠が更新される
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'remark' => 'after',
        ]);
    }
}
