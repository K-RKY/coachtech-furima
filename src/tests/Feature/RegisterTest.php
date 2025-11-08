<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     * @dataProvider registerProvider
     */
    public function test_register_validation($formData, $expectedErrorField, $expectedMessage, $expectRedirect)
    {
        $response = $this->post(route('register'), $formData);

        if ($expectRedirect) {
            $response->assertRedirect(route('verification.notice'));
        } else {
            $response->assertSessionHasErrors([
                $expectedErrorField => $expectedMessage
            ]);
        }
    }

    /**
     * 会員登録のバリデーションテストデータ
     */
    public static function registerProvider(): array
    {
        return [
            '名前が未入力' => [
                [
                    'name' => '',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ],
                'name',
                'お名前を入力してください',
                false,
            ],
            'メールアドレスが未入力' => [
                [
                    'name' => 'テスト太郎',
                    'email' => '',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ],
                'email',
                'メールアドレスを入力してください',
                false,
            ],
            'パスワードが未入力' => [
                [
                    'name' => 'テスト太郎',
                    'email' => 'test@example.com',
                    'password' => '',
                    'password_confirmation' => '',
                ],
                'password',
                'パスワードを入力してください',
                false,
            ],
            'パスワードが7文字以下' => [
                [
                    'name' => 'テスト太郎',
                    'email' => 'test@example.com',
                    'password' => '1234567',
                    'password_confirmation' => '1234567',
                ],
                'password',
                'パスワードは8文字以上で入力してください',
                false,
            ],
            '確認用パスワード不一致' => [
                [
                    'name' => 'テスト太郎',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password321',
                ],
                'password',
                'パスワードと一致しません',
                false,
            ],
            '正常入力' => [
                [
                    'name' => 'テスト太郎',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ],
                null,
                null,
                true,
            ],
        ];
    }
}
