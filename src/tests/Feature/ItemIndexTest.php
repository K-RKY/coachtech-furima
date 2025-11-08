<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品一覧取得
     */

    /** @test
     * 全商品を取得できる
     */
    public function it_retrieves_all_items()
    {
        $items = Item::factory()->count(3)->create();

        $response = $this->get(route('items.index'));
        $response->assertStatus(200);
        $html = $response->getContent();

        // Assert
        foreach ($items as $item) {
            $response->assertSee($item->name);
        }

        $displayedCount = substr_count($html, 'class="item-container"');
        $this->assertEquals(
            $items->count(),
            $displayedCount,
            'すべての商品を閲覧できるはずです。'
        );
    }

    /** @test
     *購入済み商品は「Sold」と表示される
     */
    public function it_displays_sold_label_for_purchased_items()
    {
        $soldItem = Item::factory()->create(['name' => 'Sold Item']);
        $unsoldItem = Item::factory()->create(['name' => 'Unsold Item']);

        Purchase::factory()->create([
            'item_id' => $soldItem->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->get(route('items.index'));
        $response->assertStatus(200);
        $html = $response->getContent();

        // Assert
        $this->assertMatchesRegularExpression(
            '/Sold[\s\S]*Sold Item|Sold Item[\s\S]*Sold/',
            $html,
            '購入済みの商品に「Sold」ラベルが表示されていません。'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/Unsold Item[\s\S]{0,50}Sold|Sold[\s\S]{0,50}Unsold Item/',
            $html,
            '未購入の商品に誤って「Sold」ラベルが表示されています。'
        );
    }

    /** @test
     * 自分が出品した商品は表示されない
     */
    public function it_does_not_display_items_listed_by_the_user()
    {
        $user = User::factory()->create();
        $myItem = Item::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Item',
        ]);
        $otherItems = Item::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('items.index'));
        $response->assertStatus(200);
        $html = $response->getContent();

        // Assert
        $response->assertDontSee('My Item');
        foreach ($otherItems as $item) {
            $response->assertSee($item->name);
        }

        $displayedCount = substr_count($html, 'class="item-container"');
        $this->assertEquals(
            $otherItems->count(),
            $displayedCount,
            'ログインユーザーの出品商品は一覧に表示されないはずです。'
        );
    }


    /**
     * マイリスト一覧取得
     */

    /** @test
     * いいねした商品だけが表示される
     */
    public function it_displays_only_favorited_items_in_mylist_tab()
    {
        $user = User::factory()->create();
        $liked = Item::factory()->create(['name' => 'Liked Item']);
        $unliked = Item::factory()->create(['name' => 'Unliked Item']);
        $user->favoriteItems()->attach($liked->id);

        $response = $this->actingAs($user)->get(route('items.index', ['tab' => 'mylist']));
        $response->assertStatus(200);
        $html = $response->getContent();

        // Assert
        $response->assertSee('Liked Item');
        $response->assertDontSee('Unliked Item');

        $displayedCount = substr_count($html, 'class="item-container"');
        $this->assertEquals(
            1,
            $displayedCount,
            'マイリストには「いいね」した商品だけが表示されるはずです。'
        );
    }

    /** @test
     * 購入済み商品は「Sold」と表示される
     */
    public function it_displays_sold_label_for_purchased_favorited_items_in_mylist_tab()
    {
        $user = User::factory()->create();
        $soldLiked = Item::factory()->create(['name' => 'Sold Liked']);
        $unsoldLiked = Item::factory()->create(['name' => 'Unsold Liked']);

        Purchase::factory()->create([
            'item_id' => $soldLiked->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $user->favoriteItems()->attach([$soldLiked->id, $unsoldLiked->id]);

        $response = $this->actingAs($user)->get(route('items.index', ['tab' => 'mylist']));
        $response->assertStatus(200);
        $html = $response->getContent();

        // Assert
        $this->assertMatchesRegularExpression(
            '/Sold[\s\S]*Sold Liked|Sold Liked[\s\S]*Sold/',
            $html,
            '購入済みの「いいね」商品に「Sold」ラベルが表示されていません。'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/Unsold Liked[\s\S]{0,50}Sold|Sold[\s\S]{0,50}Unsold Liked/',
            $html,
            '未購入の「いいね」商品に誤って「Sold」ラベルが表示されています。'
        );
    }

    /** @test
     * 未認証の場合は何も表示されない
     */
    public function it_displays_no_items_in_mylist_tab_for_guests()
    {
        $items = Item::factory()->count(3)->create();

        $response = $this->get(route('items.index', ['tab' => 'mylist']));
        $response->assertStatus(200);
        $html = $response->getContent();

        // Assert
        foreach ($items as $item) {
            $response->assertDontSee($item->name);
        }

        $displayedCount = substr_count($html, 'class="item-container"');
        $this->assertEquals(
            0,
            $displayedCount,
            '未ログインユーザーがマイリストを開いた場合、商品は表示されないはずです。'
        );
    }

    /** @test
     * 「商品名」で部分一致検索ができる
     */
    public function it_can_search_items_by_partial_name_match()
    {
        $keyword = 'iPhone';
        Item::factory()->create(['name' => 'iPhone 15 Pro']);
        Item::factory()->create(['name' => 'Galaxy S24']);
        Item::factory()->create(['name' => 'iPhone Case']);

        $response = $this->get(route('items.index', ['keyword' => $keyword]));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('iPhone 15 Pro');
        $response->assertSee('iPhone Case');
        $response->assertDontSee('Galaxy S24');

        $this->assertStringContainsString(
            $keyword,
            $response->getContent(),
            '商品名に部分一致するアイテムのみが表示されるはずです。'
        );
    }

    /** @test
     * 検索状態がマイリストでも保持されている
     */
    public function it_keeps_search_keyword_when_switching_to_mylist_tab()
    {
        $user = User::factory()->create();
        $keyword = 'Nike';

        $likedItem = Item::factory()->create(['name' => 'Nike Shoes']);
        $unlikedItem = Item::factory()->create(['name' => 'Adidas Jacket']);

        // ユーザーがいいねした商品を登録
        $user->favoriteItems()->attach($likedItem->id);

        // おすすめタブで検索
        $responseRecommended = $this->actingAs($user)->get(route('items.index', ['keyword' => $keyword]));
        $responseRecommended->assertStatus(200);
        $responseRecommended->assertSee('Nike Shoes');
        $responseRecommended->assertDontSee('Adidas Jacket');

        // マイリストタブへ遷移
        $responseMylist = $this->actingAs($user)->get(route('items.index', ['tab' => 'mylist', 'keyword' => $keyword]));

        // Assert
        $responseMylist->assertStatus(200);
        $responseMylist->assertSee('Nike Shoes');
        $responseMylist->assertDontSee('Adidas Jacket');
        $responseMylist->assertSee($keyword);

        $this->assertStringContainsString(
            $keyword,
            $responseMylist->getContent(),
            'マイリストタブに遷移しても検索キーワードが保持されるはずです。'
        );
    }
}
