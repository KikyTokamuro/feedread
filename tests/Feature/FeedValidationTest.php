<?php

namespace Tests\Feature;

use App\Models\Feed;
use App\Models\User;
use App\Services\FeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FeedValidationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('blockedUrls')]
    public function test_it_refuses_urls_that_reach_inside_the_network(string $url): void
    {
        Queue::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('feed.store'), ['title' => 'Bad feed', 'url' => $url])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseCount('feeds', 0);
        Queue::assertNothingPushed();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function blockedUrls(): array
    {
        return [
            'loopback' => ['http://127.0.0.1/feed'],
            'cloud metadata' => ['http://169.254.169.254/latest/meta-data/'],
            'docker service' => ['http://10.0.0.5/internal/feed'],
            'home router' => ['http://192.168.1.1/feed'],
            'ipv6 loopback' => ['http://[::1]/feed'],
            'local file' => ['file:///etc/passwd'],
            'ftp' => ['ftp://198.51.100.7/feed'],
            'credentials' => ['http://user:pass@93.184.216.34/feed'],
            'nonsense' => ['nonsense'],
        ];
    }

    public function test_it_accepts_a_plain_public_address(): void
    {
        Queue::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('feed.store'), [
                'title' => 'Good feed',
                'url' => 'https://93.184.216.34/feed.xml',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('feeds', 1);
    }

    public function test_a_refused_feed_records_an_error_instead_of_throwing(): void
    {
        $feed = Feed::factory()->create([
            'user_id' => User::factory()->create()->id,
            'url' => 'http://127.0.0.1:9/feed',
        ]);

        // The guard rejects the address before SimplePie ever makes a request,
        // so no feed can be pointed at the loopback interface.
        $ok = app(FeedService::class)->refresh($feed);

        $this->assertFalse($ok);
        $this->assertNotNull($feed->fresh()->last_error);
        $this->assertNull($feed->fresh()->last_refreshed_at);
    }
}
