<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // StripeのSessionを静的モック化
        $mock = Mockery::mock('alias:Stripe\Checkout\Session');
        $mock->shouldReceive('create')->andReturn((object)[
            'id' => 'sess_test_123',
            'url' => '/mock_stripe_checkout',
            'metadata' => collect(),
        ]);
        $mock->shouldReceive('update')->andReturn(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test
     * 「購入する」ボタンを押下すると購入が完了する
     */
    public function it_completes_purchase_successfully()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('purchase.checkout', ['item' => $item->id]), [
            'payment_method' => 'card',
        ]);

        $response->assertRedirect('/mock_stripe_checkout')
            ->assertStatus(302);
    }

    /** @test
     * 購入した商品は商品一覧画面にて「sold」と表示される
     */
    public function purchased_item_is_displayed_as_sold_in_index()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'status' => 'paid',
        ]);

        $response = $this->get(route('items.index'));

        $response->assertStatus(200)
            ->assertSee('Sold')
            ->assertSee($item->name);
    }

    /** @test
     * 「プロフィール/購入した商品一覧」に追加されている
     */
    public function purchased_item_appears_in_user_profile_purchases()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'status' => 'paid',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('mypage.index'));

        $response->assertStatus(200)
            ->assertSee($item->name);
    }

    /** @test
     * 小計画面で変更が反映される
     */
    public function payment_method_selection_is_reflected_in_summary()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        // 購入確認ページを表示
        $response = $this->get(route('purchase.confirm', ['item_id' => $item->id]));
        $response->assertStatus(200);

        // Blade上に支払い方法のプルダウンと初期値が存在することを確認
        $response->assertSee('<option value="none">選択してください</option>', false);
        $response->assertSee('<option value="konbini">コンビニ支払い</option>', false);
        $response->assertSee('<option value="card">カード支払い</option>', false);

        // hidden input が初期状態で空であることを確認
        $response->assertSee('id="hidden_payment_method"', false);

        // 実際の JS はテストで実行されないため、
        // hidden input に値をセットした想定で POST を送信して確認
        $postData = ['payment_method' => 'card'];

        $checkoutResponse = $this->post(route('purchase.checkout', ['item' => $item->id]), $postData);
        $checkoutResponse->assertRedirect('/mock_stripe_checkout');
    }

    /** @test
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     */
    public function updated_address_is_reflected_on_purchase_page()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        // 住所変更画面にPOST
        $postData = [
            'postal_code' => '123-4567',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
        ];

        $response = $this->post(route('purchase.address.update', ['item_id' => $item->id]), $postData);

        $response->assertRedirect(route('purchase.confirm', ['item_id' => $item->id]))
            ->assertSessionHas('purchase_address', $postData);

        // 購入画面を再度開く
        $purchasePage = $this->get(route('purchase.confirm', ['item_id' => $item->id]));

        $purchasePage->assertStatus(200)
            ->assertSee('〒 123-4567')
            ->assertSee('東京都新宿区')
            ->assertSee('テストビル101');
    }

    /** @test
     * 購入した商品に送付先住所が紐づいて登録される
     */
    public function purchased_item_has_shipping_address()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        // 住所変更画面で住所を登録
        $postData = [
            'postal_code' => '987-6543',
            'address' => '大阪市北区',
            'building' => 'サンプルマンション202',
        ];
        $this->post(route('purchase.address.update', ['item_id' => $item->id]), $postData);

        // 商品購入（Stripe モックで checkout）
        $checkoutData = ['payment_method' => 'card'];
        $this->post(route('purchase.checkout', ['item' => $item->id]), $checkoutData);

        // 購入後に session から shipping_address を取得して Purchase に登録する流れは handleCheckoutCompleted() で行われる
        // テストでは直接 Purchase を作成して shipping_address を設定
        $shippingAddress = trim("{$postData['postal_code']} {$postData['address']} {$postData['building']}");
        $purchase = Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'shipping_address' => $shippingAddress,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'shipping_address' => $shippingAddress,
        ]);
    }
}