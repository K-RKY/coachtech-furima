<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;

class ItemShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * 必要な情報が表示される
    */
    public function it_displays_all_required_item_information()
    {
        $seller = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'price' => 19800,
            'description' => 'これはテスト用の商品説明です。',
            'condition_id' => 2,
            'image_path' => 'images/test_item.jpg',
        ]);

        // カテゴリ関連付け
        $categories = Category::factory()->count(2)->create();
        $item->categories()->attach($categories->pluck('id'));

        // いいねを3件作成
        $item->favoritedByUsers()->attach(User::factory()->count(3)->create());

        // コメントを2件作成
        $commentUser1 = User::factory()->create(['name' => 'コメント太郎']);
        $commentUser2 = User::factory()->create(['name' => 'レビュー花子']);
        Comment::factory()->create([
            'user_id' => $commentUser1->id,
            'item_id' => $item->id,
            'content' => 'すばらしい商品でした。',
        ]);
        Comment::factory()->create([
            'user_id' => $commentUser2->id,
            'item_id' => $item->id,
            'content' => '配送が早かったです。',
        ]);

        $response = $this->get(route('items.show', $item->id));

        // Assert
        $response->assertStatus(200);

        // 商品基本情報
        $response->assertSee('テスト商品');
        $response->assertSee('テストブランド');
        $response->assertSee('19800');
        $response->assertSee('これはテスト用の商品説明です。');
        $response->assertSee('目立った傷や汚れなし');
        $response->assertSee('images/test_item.jpg');

        // カテゴリ
        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }

        // いいね数・コメント数
        $response->assertSee('<span class="favorite-count">' . $item->favoritedByUsers->count() . '</span>', false);
        $response->assertSee('<span class="comment-count">' . $item->comments()->count() . '</span>', false);

        // コメント内容
        $response->assertSee('コメント太郎');
        $response->assertSee('レビュー花子');
        $response->assertSee('すばらしい商品でした。');
        $response->assertSee('配送が早かったです。');

        $response->assertSee('コメント(' . $item->comments()->count() . ')');
        $response->assertSee('商品へのコメント');
    }

    /** @test
     * 複数選択されたカテゴリが表示されているか
     */
    public function it_displays_multiple_selected_categories()
    {
        $faker = Faker::create();
        $item = Item::factory()->create(['name' => 'カテゴリテスト商品']);
        $categories = Category::factory()->count(3)->create();
        $item->categories()->attach($categories->pluck('id'));

        $response = $this->get(route('items.show', $item->id));

        // Assert
        $response->assertStatus(200);
        foreach ($categories as $category) {
            $response->assertSee('<span class="item-category">' . e($category->name) . '</span>', false);
        }

        $this->assertEquals(
            $categories->count(),
            $item->categories()->count(),
            '複数カテゴリが関連付けられており、全てが表示されるはずです。'
        );
    }
}
