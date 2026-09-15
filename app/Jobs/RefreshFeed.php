<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Refreshes one feed.
 *
 * Failures are recorded on the feed itself (last_error) rather than thrown, so
 * a dead feed does not spam the failed jobs table or block the others.
 */
class RefreshFeed implements ShouldQueue
{
    use Queueable;

    /**
     * A feed that cannot be fetched is not worth retrying immediately; the next
     * scheduled run will try again.
     */
    public int $tries = 1;

    public function __construct(
        public Feed $feed
    ) {}

    public function handle(FeedService $feedService): void
    {
        $feedService->refresh($this->feed);
    }
}
