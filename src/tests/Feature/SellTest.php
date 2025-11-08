<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SellTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * 商品出品画面にて必要な情報が保存できること（カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
     */
    public function user_can_create_item_with_required_information()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categories = Category::factory()->count(2)->create();

        $this->actingAs($user);

        $file = UploadedFile::fake()->create('item.jpg', 100, 'image/jpeg');

        $postData = [
            'name' => 'Test Item',
            'brand' => 'BrandTest',       // nullable だがテストでは値をセット
            'description' => 'Description text',
            'price' => 1000,
            'condition_id' => 1,
            'categories' => $categories->pluck('id')->implode(','),
            'image' => $file,
        ];

        $response = $this->post(route('sell.store'), $postData);

        $response->assertRedirect(route('items.index'))
            ->assertSessionHas('status', '商品を出品しました');

        $item = Item::where('name', 'Test Item')->first();
        $this->assertNotNull($item, 'Item が DB に保存されていません');

        $this->assertEquals($user->id, $item->user_id);
        $this->assertEquals('BrandTest', $item->brand);
        $this->assertEquals('Description text', $item->description);
        $this->assertEquals(1000, $item->price);
        $this->assertEquals(1, $item->condition_id);

        foreach ($categories as $category) {
            $this->assertDatabaseHas('category_item', [
                'item_id' => $item->id,
                'category_id' => $category->id,
            ]);
        }

        $this->assertTrue(Storage::disk('public')->exists($item->image_path), '画像が保存されていません');
    }
}
