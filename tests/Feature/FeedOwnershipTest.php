<?php

namespace Tests\Feature;

use App\Jobs\RefreshFeed;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FeedOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_sidebar_only_lists_the_own_feeds(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        Feed::factory()->create(['user_id' => $me->id, 'title' => 'My Feed']);
        Feed::factory()->create(['user_id' => $other->id, 'title' => 'Someone Elses Feed']);

        $this->actingAs($me)
            ->get('/')
            ->assertOk()
            ->assertSee('My Feed')
            ->assertDontSee('Someone Elses Feed');
    }

    public function test_a_foreign_feed_cannot_be_viewed(): void
    {
        $other = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $other->id]);

        $this->actingAs(User::factory()->create())
            ->get(route('feed.show', $feed))
            ->assertForbidden();
    }

    public function test_a_foreign_feed_cannot_be_edited_or_deleted(): void
    {
        $other = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $other->id]);
        $me = User::factory()->create();

        $this->actingAs($me)->get(route('feed.edit', $feed))->assertForbidden();
        $this->actingAs($me)->patch(route('feed.update', $feed), [
            'title' => 'Hijacked',
            'url' => 'http://93.184.216.34/feed',
        ])->assertForbidden();
        $this->actingAs($me)->delete(route('feed.delete', $feed))->assertForbidden();
        $this->actingAs($me)->post(route('feed.refresh', $feed))->assertForbidden();

        $this->assertDatabaseHas('feeds', [
            'id' => $feed->id,
            'title' => $feed->title,
            'deleted_at' => null,
        ]);
    }

    public function test_a_new_feed_belongs_to_the_signed_in_user(): void
    {
        Queue::fake();

        $me = User::factory()->create();

        $this->actingAs($me)->post(route('feed.store'), [
            'title' => 'Example',
            'url' => 'http://93.184.216.34/feed',
        ])->assertRedirect();

        $feed = Feed::query()->firstOrFail();

        $this->assertSame($me->id, $feed->user_id);
        Queue::assertPushed(RefreshFeed::class, fn (RefreshFeed $job) => $job->feed->is($feed));
    }

    public function test_the_same_url_may_be_subscribed_by_two_accounts(): void
    {
        Queue::fake();

        $url = 'http://93.184.216.34/feed';

        Feed::factory()->create(['user_id' => User::factory()->create()->id, 'url' => $url]);

        $this->actingAs(User::factory()->create())
            ->post(route('feed.store'), ['title' => 'Example', 'url' => $url])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('feeds', 2);
    }

    public function test_the_same_url_cannot_be_added_twice_by_one_account(): void
    {
        Queue::fake();

        $url = 'http://93.184.216.34/feed';
        $me = User::factory()->create();

        Feed::factory()->create(['user_id' => $me->id, 'url' => $url]);

        $this->actingAs($me)
            ->post(route('feed.store'), ['title' => 'Example', 'url' => $url])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseCount('feeds', 1);
    }
}
