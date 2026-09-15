<?php

namespace Tests\Feature;

use App\Models\Feed;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnreadTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_sidebar_counts_unread_articles(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        Item::factory()->count(2)->create(['feed_id' => $feed->id]);
        Item::factory()->read()->create(['feed_id' => $feed->id]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('<span class="feed-link__count">2</span>', false);
    }

    public function test_opening_an_article_marks_it_read_and_redirects_to_it(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item = Item::factory()->create([
            'feed_id' => $feed->id,
            'url' => 'https://example.com/article',
        ]);

        $this->actingAs($user)
            ->get(route('item.open', $item))
            ->assertRedirect('https://example.com/article');

        $this->assertNotNull($item->fresh()->read_at);
    }

    public function test_opening_an_article_never_redirects_to_a_private_address(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item = Item::factory()->create([
            'feed_id' => $feed->id,
            'url' => 'http://127.0.0.1/admin',
        ]);

        $this->actingAs($user)
            ->get(route('item.open', $item))
            ->assertRedirect(route('feed.show', $feed));
    }

    public function test_a_whole_feed_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        Item::factory()->count(3)->create(['feed_id' => $feed->id]);
        Item::factory()->read()->create(['feed_id' => $feed->id]);

        $this->actingAs($user)->post(route('feed.read-all', $feed))->assertRedirect();

        $this->assertSame(0, $feed->items()->unread()->count());
        $this->assertSame(4, $feed->items()->count());
    }

    public function test_an_article_can_be_marked_read_and_unread_again(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item = Item::factory()->create(['feed_id' => $feed->id]);

        $this->actingAs($user)->post(route('item.read', $item))->assertRedirect();
        $this->assertNotNull($item->fresh()->read_at);

        $this->actingAs($user)->post(route('item.unread', $item))->assertRedirect();
        $this->assertNull($item->fresh()->read_at);
    }

    public function test_a_foreign_article_cannot_be_marked(): void
    {
        $feed = Feed::factory()->create(['user_id' => User::factory()->create()->id]);
        $item = Item::factory()->create(['feed_id' => $feed->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('item.read', $item))
            ->assertForbidden();

        $this->assertNull($item->fresh()->read_at);
    }

    public function test_refreshing_a_feed_does_not_reset_read_state(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item = Item::factory()->read()->create(['feed_id' => $feed->id]);

        // upsert() must leave read_at alone when an article is seen again.
        Item::upsert([[
            'feed_id' => $feed->id,
            'guid' => $item->guid,
            'guid_hash' => $item->guid_hash,
            'title' => 'Updated title',
            'content' => 'updated',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]], ['feed_id', 'guid_hash'], ['guid', 'title', 'content', 'published_at', 'updated_at']);

        $this->assertNotNull($item->fresh()->read_at);
        $this->assertSame('Updated title', $item->fresh()->title);
    }
}
