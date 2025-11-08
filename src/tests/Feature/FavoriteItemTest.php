<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * いいねアイコンを押下するとお気に入りに登録される
     */
    public function test_user_can_add_item_to_favorites()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post(route('items.favorite', $item->id));

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertEquals(
            1,
            $item->fresh()->favoritedByUsers->count(),
            'いいね追加後は合計値が1件増えているはずです。'
        );
    }

    /** @test
     * いいね済みアイコンは色が変化して表示される
     */
    public function test_favorited_item_displays_filled_star_icon()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // 事前にいいね登録
        $user->favoriteItems()->attach($item->id);

        $response = $this->actingAs($user)->get(route('items.show', $item->id));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('<i class="fa-solid fa-star fa-3x"></i>', false);
        $response->assertDontSee('<i class="fa-regular fa-star fa-3x"></i>', false);
    }

    /** @test
     * いいねアイコンを再度押下するとお気に入りが解除される
     */
    public function test_user_can_remove_item_from_favorites()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // 初期状態：いいね済み
        $user->favoriteItems()->attach($item->id);

        $response = $this->actingAs($user)->post(route('items.favorite', $item->id));

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertEquals(
            0,
            $item->fresh()->favoritedByUsers->count(),
            'いいね解除後は合計値が1件減っているはずです。'
        );
    }
}
