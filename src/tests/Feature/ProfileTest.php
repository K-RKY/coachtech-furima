<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     */
    public function mypage_displays_user_info_and_items_correctly()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'image_path' => 'profile_images/test.png',
        ]);

        $exhibitionItem = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '出品商品',
        ]);

        $purchaseItem = Item::factory()->create();
        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $purchaseItem->id,
            'status' => 'paid',
        ]);

        $this->actingAs($user);

        // 出品商品タブ
        $responseExhibition = $this->get(route('mypage.index', ['tab' => 'exhibitions']));
        $responseExhibition->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('profile_images/test.png')
            ->assertSee($exhibitionItem->name);

        // デフォルトタブ（購入商品）
        $responsePurchase = $this->get(route('mypage.index'));
        $responsePurchase->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('profile_images/test.png')
            ->assertSee($purchaseItem->name);
    }

    /** @test
     * 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
     */
    public function profile_form_displays_existing_values_as_initial_values()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'postal_code' => '123-4567',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->get(route('mypage.profile'));

        // Assert: フォーム初期値が正しいか確認
        $response->assertStatus(200)
            ->assertSee('value="テストユーザー"', false)
            ->assertSee('value="123-4567"', false)
            ->assertSee('value="東京都新宿区"', false)
            ->assertSee('value="テストビル101"', false);
    }
}
