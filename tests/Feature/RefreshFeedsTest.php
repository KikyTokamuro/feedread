<?php

namespace Tests\Feature;

use App\Jobs\RefreshFeed;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RefreshFeedsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_queues_every_feed(): void
    {
        Queue::fake();

        $feed = Feed::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->artisan('feeds:refresh')->assertSuccessful();

        Queue::assertPushed(RefreshFeed::class, 1);
        Queue::assertPushed(RefreshFeed::class, fn (RefreshFeed $job) => $job->feed->is($feed));
    }

    public function test_it_can_refresh_a_single_feed(): void
    {
        Queue::fake();

        $wanted = Feed::factory()->create(['user_id' => User::factory()->create()->id]);
        Feed::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->artisan('feeds:refresh', ['--feed' => $wanted->id])->assertSuccessful();

        Queue::assertPushed(RefreshFeed::class, 1);
        Queue::assertPushed(RefreshFeed::class, fn (RefreshFeed $job) => $job->feed->is($wanted));
    }

    public function test_it_can_refresh_the_feeds_of_one_account(): void
    {
        Queue::fake();

        $me = User::factory()->create();
        Feed::factory()->count(2)->create(['user_id' => $me->id]);
        Feed::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->artisan('feeds:refresh', ['--user' => $me->id])->assertSuccessful();

        Queue::assertPushed(RefreshFeed::class, 2);
    }

    public function test_it_reports_when_there_is_nothing_to_do(): void
    {
        Queue::fake();

        $this->artisan('feeds:refresh')
            ->expectsOutput('No feeds to refresh.')
            ->assertSuccessful();

        Queue::assertNothingPushed();
    }

    public function test_it_is_registered_with_the_scheduler(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains((string) $event->command, 'feeds:refresh'));

        $this->assertCount(1, $events, 'feeds:refresh is not scheduled.');
        $this->assertSame(
            sprintf('*/%d * * * *', config('feedread.refresh_interval_minutes')),
            $events->first()->expression
        );
    }
}
