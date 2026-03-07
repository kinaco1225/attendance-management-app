<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampCorrectionRequestListTest extends TestCase
{
    use RefreshDatabase;

    private function listUrl(): string
    {
        // ⚠️ あなたの一覧ルート名に後で合わせる
        return route('stamp_correction_requests.list');
    }

    /**
     * 承認待ちに自分の申請が表示される
     */
    public function test_pending_requests_are_displayed_for_user()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
        ]);

        AttendanceRequest::create([
            'attendance_id'     => $attendance->id,
            'user_id'           => $user->id,
            'request_clock_in'  => '09:00:00',
            'request_clock_out' => '18:00:00',
            'request_remark'    => 'テスト申請',
            'status'            => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->get($this->listUrl());
        $response->assertOk();

        $response->assertSee('テスト申請');
    }

    /**
     * 承認済みに表示される
     */
    public function test_approved_requests_are_displayed()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
        ]);

        AttendanceRequest::create([
            'attendance_id'     => $attendance->id,
            'user_id'           => $user->id,
            'request_clock_in'  => '09:00:00',
            'request_clock_out' => '18:00:00',
            'request_remark'    => '承認済み申請',
            'status'            => 'approved',
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('stamp_correction_requests.list', ['status' => 'approved'])
        );
        $response->assertOk();

        $response->assertSee('承認済み申請');
    }

    /**
     * 詳細押下で勤怠詳細へ遷移
     */
    public function test_click_detail_navigates_to_attendance_detail()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
        ]);

        AttendanceRequest::create([
            'attendance_id'     => $attendance->id,
            'user_id'           => $user->id,
            'request_clock_in'  => '09:00:00',
            'request_clock_out' => '18:00:00',
            'request_remark'    => '詳細テスト',
            'status'            => 'pending',
        ]);

        $this->actingAs($user);

        $this->get($this->listUrl())->assertOk();

        $this->get(route('attendance.detail', $attendance->id))
            ->assertOk();
    }
}
