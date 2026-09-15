<?php

namespace Tests\Feature;

use App\Models\Feed;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every page is rendered once, which is what catches the Blade level mistakes
 * that unit tests never see.
 */
class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in');
    }

    public function test_every_authenticated_page_renders(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'favicon_url' => 'https://example.com/favicon.ico',
        ]);

        Item::factory()->count(3)->create(['feed_id' => $feed->id]);
        Item::factory()->read()->create(['feed_id' => $feed->id]);

        $this->actingAs($user);

        $this->get(route('main'))->assertOk();
        $this->get(route('feed.add'))->assertOk();
        $this->get(route('feed.edit', $feed))->assertOk();
        $this->get(route('opml.index'))->assertOk();
        $this->get(route('settings.show'))->assertOk();
        $this->get(route('users.index'))->assertOk();
        $this->get(route('users.create'))->assertOk();
    }

    public function test_the_feed_page_renders_articles_and_the_stored_icon(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'title' => 'The Verge',
            'url' => 'https://example.com/feed.xml',
            'favicon_url' => 'https://example.com/favicon.ico',
            'last_refreshed_at' => now(),
        ]);

        Item::factory()->create([
            'feed_id' => $feed->id,
            'title' => 'A stored article',
            'content' => '<p>Body text</p>',
        ]);

        $response = $this->actingAs($user)->get(route('feed.show', $feed));

        $response->assertOk();

        // The icon comes from the database, so rendering never touches the
        // network any more.
        $response->assertSee('https://example.com/favicon.ico');
        $response->assertSee('A stored article');
        $response->assertSee('Body text');
    }

    public function test_the_feed_page_explains_an_empty_feed(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'last_refreshed_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('feed.show', $feed))
            ->assertOk()
            ->assertSee('No articles stored yet');
    }

    public function test_the_feed_page_reports_a_failed_refresh(): void
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'last_error' => 'The host could not be resolved.',
        ]);

        $this->actingAs($user)
            ->get(route('feed.show', $feed))
            ->assertOk()
            ->assertSee('The host could not be resolved.');
    }

    public function test_the_account_page_requires_an_administrator(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_a_missing_feed_shows_the_app_styled_error_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/feeds/9999')
            ->assertNotFound()
            ->assertSee('9999');
    }

    public function test_a_guest_asking_for_a_missing_feed_is_sent_to_the_login_page(): void
    {
        $this->get('/feeds/9999')->assertRedirect(route('login'));
    }
}
