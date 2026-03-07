<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 現在の日時がUIと一致する
     */
    public function test_current_datetime_is_displayed_correctly()
    {
        // 時刻固定
        Carbon::setTestNow(
            Carbon::create(2024, 1, 15, 9, 30, 0)
        );

        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        // 日付（Blade完全一致）
        $expectedDate = Carbon::now()
            ->locale('ja')
            ->isoFormat('YYYY年M月D日（dd）');

        // 時刻（Blade完全一致）
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
