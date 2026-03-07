<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外
     */
    public function test_status_is_off_duty()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    /**
     * 出勤中
     */
    public function test_status_is_working()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    /**
     * 休憩中
     */
    public function test_status_is_on_break()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    /**
     * 退勤済
     */
    public function test_status_is_finished()
    {
        Carbon::setTestNow(now());

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }
}
