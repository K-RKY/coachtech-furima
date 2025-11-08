<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * ログイン済みユーザーはコメントを送信できる
     */
    public function test_authenticated_user_can_post_comment()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $data = ['content' => 'テストコメント'];

        $response = $this->actingAs($user)
            ->post(route('comments.store', $item->id), $data);

        // Assert
        $response->assertRedirect(route('items.show', $item->id));
        $this->assertDatabaseHas('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'content' => 'テストコメント',
        ]);

        $this->assertEquals(
            1,
            Comment::where('item_id', $item->id)->count(),
            'コメント投稿後、コメント数が1件増えているはずです。'
        );
    }

    /** @test
     * ログイン前ユーザーはコメントを送信できない
     */
    public function test_guest_user_cannot_post_comment()
    {
        $item = Item::factory()->create();
        $data = ['content' => 'ゲストコメント'];

        $response = $this->post(route('comments.store', $item->id), $data);

        // Assert
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'content' => 'ゲストコメント',
        ]);
    }

    /** @test
     * コメントが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_comment_validation_fails_when_empty()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $data = ['content' => '']; // 空欄

        $response = $this->actingAs($user)
            ->post(route('comments.store', $item->id), $data);

        // Assert
        $response->assertSessionHasErrors('content');
        $this->assertDatabaseCount('comments', 0);
    }

    /** @test
     * コメントが255文字を超える場合、バリデーションメッセージが表示される
     */
    public function test_comment_validation_fails_when_content_exceeds_limit()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $data = ['content' => str_repeat('あ', 256)];

        $response = $this->actingAs($user)
            ->post(route('comments.store', $item->id), $data);

        // Assert
        $response->assertSessionHasErrors('content');
        $this->assertDatabaseCount('comments', 0);
    }
}