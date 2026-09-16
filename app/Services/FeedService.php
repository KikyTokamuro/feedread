<?php

namespace App\Services;

use App\Cache\SimpleCacheBridge;
use App\Models\Feed;
use App\Models\Item;
use App\Support\PublicUrl;
use AshAllenDesign\FaviconFetcher\Facades\Favicon;
use Illuminate\Support\Str;
use SimplePie\SimplePie;
use Throwable;

class FeedService
{
    /**
     * Articles we keep from a single feed. SimplePie drops the rest while
     * parsing, which also keeps memory use bounded for very large feeds.
     */
    public const ITEM_LIMIT = 100;

    /**
     * Fetch a feed, store new articles and refresh the feed metadata.
     *
     * @return bool Whether the feed could be fetched.
     */
    public function refresh(Feed $feed): bool
    {
        // The URL comes from user input, so make sure we are not being pointed
        // at the private network before we let SimplePie make the request.
        if (($reason = PublicUrl::rejectionReason($feed->url)) !== null) {
            return $this->markFailed($feed, 'Feed URL rejected: '.$reason);
        }

        $simplepie = new SimplePie;
        $simplepie->set_cache(new SimpleCacheBridge);
        $simplepie->set_feed_url($feed->url);
        $simplepie->set_timeout(15);
        $simplepie->set_item_limit(self::ITEM_LIMIT);
        $simplepie->set_useragent(config('app.name', 'FeedRead').' feed reader');

        try {
            $success = $simplepie->init();
        } catch (Throwable $e) {
            return $this->markFailed($feed, $e->getMessage());
        }

        if (! $success) {
            return $this->markFailed($feed, (string) ($simplepie->error() ?: 'Unable to fetch the feed.'));
        }

        $this->storeItems($feed, $simplepie);

        $feed->fill([
            'last_refreshed_at' => now(),
            'last_error' => null,
        ])->save();

        $this->refreshFavicon($feed);

        return true;
    }

    /**
     * Store the articles of a parsed feed.
     *
     * Existing rows are matched on the feed's own item id, so a refresh never
     * touches read state and never creates duplicates.
     *
     * @return int Number of articles parsed.
     */
    protected function storeItems(Feed $feed, SimplePie $simplepie): int
    {
        $now = now();
        $rows = [];

        foreach ($simplepie->get_items() as $item) {
            if (($guid = $this->resolveGuid($item)) === '') {
                continue;
            }

            $rows[] = [
                'feed_id' => $feed->id,
                'guid' => $guid,
                'guid_hash' => sha1($guid),
                'title' => Str::limit(trim(strip_tags((string) $item->get_title())), 250, ''),
                'url' => $item->get_permalink() ?: null,
                'content' => $item->get_content() ?: $item->get_description(),
                'published_at' => $item->get_gmdate('Y-m-d H:i:s'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows === []) {
            return 0;
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            Item::upsert(
                $chunk,
                ['feed_id', 'guid_hash'],
                ['guid', 'title', 'content', 'published_at', 'updated_at']
            );
        }

        return count($rows);
    }

    /**
     * Work out a stable identifier for an article.
     */
    protected function resolveGuid(mixed $item): string
    {
        // get_id(true) hashes a deterministic set of fields when the feed does
        // not provide an id.
        $guid = (string) $item->get_id(true);

        if ($guid === '') {
            $guid = (string) ($item->get_permalink() ?: $item->get_title().'|'.$item->get_gmdate('Y-m-d H:i:s'));
        }

        return trim($guid);
    }

    /**
     * Resolve and remember the site icon, once.
     *
     * Previously this ran during every page render and re-fetched the icon on
     * every request whenever the site had none, which made every page load wait
     * on a network round trip per feed.
     */
    public function refreshFavicon(Feed $feed): void
    {
        if (filled($feed->favicon_url)) {
            return;
        }

        try {
            $favicon = Favicon::fetchOr($feed->url, null);
        } catch (Throwable) {
            // A missing or broken icon must never break adding a feed.
            return;
        }

        if ($favicon === null) {
            return;
        }

        $feed->fill(['favicon_url' => $favicon->getFaviconUrl()])->save();
    }

    /**
     * Mark every article of a feed as read.
     */
    public function markAllRead(Feed $feed): int
    {
        return $feed->items()->unread()->update(['read_at' => now()]);
    }

    /**
     * Record why a feed could not be refreshed.
     */
    protected function markFailed(Feed $feed, string $message): bool
    {
        $feed->fill([
            'last_error' => Str::limit($message, 500, ''),
        ])->save();

        return false;
    }
}
