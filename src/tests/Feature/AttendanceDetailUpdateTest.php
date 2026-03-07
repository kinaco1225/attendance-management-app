<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 更新URLをここで統一（あなたのルートに合わせてどちらかにする）
     */
    private function updateUrl(Attendance $attendance): string
    {
        return route('attendance.update', ['attendance' => $attendance->id]);
    }

    /**
     * 出勤時間が退勤時間より後 → 「出勤時間が不適切な値です」
     */
    public function test_validation_error_when_clock_in_is_after_clock_out()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($user);

        $response = $this->post($this->updateUrl($attendance), [
            '_method'   => 'PUT',
            'clock_in'  => '19:00',
            'clock_out' => '18:00',
            'remark'    => '備考あり',
            'breaks'    => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 休憩開始が退勤より後 → 「休憩時間が不適切な値です」
     */
    public function test_validation_error_when_break_start_is_after_clock_out()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($user);

        $response = $this->post($this->updateUrl($attendance), [
            '_method'   => 'PUT',
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'remark'    => '備考あり',
            'breaks'    => [
                ['start' => '19:00', 'end' => '19:10'],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /**
     * 休憩終了が退勤より後 → 「休憩時間もしくは退勤時間が不適切な値です」
     */
    public function test_validation_error_when_break_end_is_after_clock_out()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($user);

        $response = $this->put($this->updateUrl($attendance), [
            '_method'   => 'PUT',
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'remark'    => '備考あり',
            'breaks'    => [
                ['start' => '17:50', 'end' => '18:10'],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 備考が未入力 → 「備考を記入してください」
     */
    public function test_validation_error_when_remark_is_empty()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($user);

        $response = $this->put($this->updateUrl($attendance), [
            '_method'   => 'PUT',
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

    /**
     * 修正申請処理が実行される（DBに申請が作成される）
     */
    public function test_update_creates_correction_request_and_break_requests()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-03-03',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remark'    => 'test',
        ]);

        $this->actingAs($user);

        $response = $this->put($this->updateUrl($attendance), [
            '_method'   => 'PUT',
            'clock_in'  => '09:10',
            'clock_out' => '18:20',
            'remark'    => '修正申請します',
            'breaks'    => [
                ['start' => '12:00', 'end' => '13:00'],
                ['start' => '15:00', 'end' => '15:10'],
            ],
        ]);

        $response->assertStatus(302);

        // 申請が作られる（テーブル名/カラム名がこの通りである前提）
        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id'     => $attendance->id,
            'user_id'           => $user->id,
            'request_clock_in'  => '09:10:00',
            'request_clock_out' => '18:20:00',
            'request_remark'    => '修正申請します',
            'status'            => 'pending',
        ]);

        // 休憩申請も作られる（2件）
        $this->assertDatabaseHas('attendance_request_break_times', [
            'request_break_start' => '12:00:00',
            'request_break_end'   => '13:00:00',
        ]);
        $this->assertDatabaseHas('attendance_request_break_times', [
            'request_break_start' => '15:00:00',
            'request_break_end'   => '15:10:00',
        ]);
    }
}
