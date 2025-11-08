<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後に認証メールが送信される
     */
    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        // 1. 会員登録
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 2. 認証メールが送信される
        $user = User::first();

        Notification::assertSentTo($user, VerifyEmail::class);

        $response->assertRedirect(route('verification.notice'));
    }

    /**
     * メール認証誘導画面で「認証はこちらから」を押すとMailHogに遷移する
     */
    public function test_verify_notice_page_has_mailhog_link()
    {
        $user = User::factory()->create();

        // ログイン状態でメール認証誘導画面を表示
        $response = $this->actingAs($user)->get(route('verification.notice'));

        // MailHogのURLが存在するか確認
        $response->assertStatus(200)
            ->assertSee('http://localhost:8025')
            ->assertSee('認証はこちらから');
    }

    /**
     * 認証リンクをクリックしたらプロフィール設定画面へ遷移する
     */
    public function test_user_can_verify_email_and_redirect_to_profile_page()
    {
        $user = User::factory()->unverified()->create();

        // 有効な署名付きURLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // メール認証リンクを踏む（認証処理）
        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('mypage.profile')); // プロフィール画面へ遷移
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
