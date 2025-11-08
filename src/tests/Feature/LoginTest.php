<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 一回だけユーザーを作成
     */
    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * @dataProvider loginProvider
     */
    public function test_login_validation($formData, $expectedErrorField, $expectedMessage, $expectRedirect)
    {
        $response = $this->post(route('login'), $formData);

        if ($expectRedirect) {
            $response->assertRedirect(route('items.index'));
        } else {
            $response->assertSessionHasErrors([
                $expectedErrorField => $expectedMessage,
            ]);
        }
    }

    /**
     * ログイン認証のバリデーションテストデータ
     */
    public static function loginProvider(): array
    {
        return [
            'メールアドレス未入力' => [
                ['email' => '', 'password' => 'password123'],
                'email',
                'メールアドレスを入力してください',
                false,
            ],
            'パスワード未入力' => [
                ['email' => 'test@example.com', 'password' => ''],
                'password',
                'パスワードを入力してください',
                false,
            ],
            'メールアドレスまたはパスワードが間違い' => [
                ['email' => 'wrong@example.com', 'password' => 'wrongpassword'],
                'email',
                'ログイン情報が登録されていません',
                false,
            ],
            '正常ログイン' => [
                ['email' => 'test@example.com', 'password' => 'password123'],
                null,
                null,
                true,
            ],
        ];
    }

    /** @test
     * ログアウトテスト
     */
    public function test_logout_function()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
