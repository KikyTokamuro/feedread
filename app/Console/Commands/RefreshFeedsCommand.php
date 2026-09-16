<?php

namespace App\Console\Commands;

use App\Jobs\RefreshFeed;
use App\Models\Feed;
use Illuminate\Console\Command;

class RefreshFeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:refresh
                            {--feed= : Refresh a single feed by id}
                            {--user= : Only refresh the feeds of this user id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch every feed and collect new articles';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $feeds = Feed::query()
            ->when($this->option('feed'), fn ($query, $id) => $query->whereKey($id))
            ->when($this->option('user'), fn ($query, $id) => $query->where('user_id', $id))
            ->get();

        if ($feeds->isEmpty()) {
            $this->info('No feeds to refresh.');

            return self::SUCCESS;
        }

        // With the default sync queue the job runs right here; with a real
        // queue driver the scheduler stays fast and a worker does the fetching.
        foreach ($feeds as $feed) {
            RefreshFeed::dispatch($feed);
        }

        $this->info(sprintf('Queued %d feed(s) for refresh.', $feeds->count()));

        return self::SUCCESS;
    }
}
