<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 登録ユーザーに認証メールが送信される
     */
    public function test_verification_email_is_sent()
    {
        Notification::fake();

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // あなたの resend/send 経路に合わせる
        $this->post(route('verification.send'));

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /**
     * 認証誘導画面が表示される
     */
    public function test_verification_notice_page_is_displayed()
    {
        /** @var User $user */
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));

        $response->assertOk();
    }

    /**
     * 認証完了後、勤怠画面へリダイレクト
     */
    public function test_user_can_verify_email_and_redirected_to_attendance()
    {
        /** @var User $user */
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('attendance'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
