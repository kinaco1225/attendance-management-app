<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     *   その月の「日付行」が全て表示されることを確認する。
     */
    public function test_user_attendances_are_all_displayed_on_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.list'));
        $response->assertOk();

        // 月の日付が表示される（3月は31日まで）
        $response->assertSee('03/01（日）');
        $response->assertSee('03/31（火）');
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_on_list_page()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.list'));
        $response->assertOk();

        $response->assertSee('2026/03');
    }

    /**
     * 「前日」（=前月）を押下した時に表示月の前月の情報が表示される
     */
    public function test_prev_month_link_shows_previous_month()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2026-03 を見ている状態から「前月」へ（Bladeの prevMonth と同じ）
        $prev = $this->get(route('attendance.list', ['month' => '2026-02']));
        $prev->assertOk();

        // 月表示は Y/m
        $prev->assertSee('2026/02');

        // 2月の日付が出ている（曜日がズレる可能性があるので、まずは日付だけ）
        $prev->assertSee('02/01');
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_link_shows_next_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 翌月へ
        $next = $this->get(route('attendance.list', ['month' => '2026-04']));
        $next->assertOk();

        $next->assertSee('2026/04');
        $next->assertSee('04/01');
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_click_detail_navigates_to_that_days_detail_page()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 3, 10, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('attendance.list'))->assertOk();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', '2026-03-03')
            ->firstOrFail();

        $detail = $this->get(route('attendance.detail', $attendance->id));
        $detail->assertOk();
    }
}
