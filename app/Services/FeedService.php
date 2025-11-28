<?php

namespace App\Services;

use App\Cache\SimpleCacheBridge;
use App\Models\Feed;
use AshAllenDesign\FaviconFetcher\Facades\Favicon;
use Illuminate\Support\Str;

class FeedService
{
    /**
     * Try get url favicon
     *
     * @param Feed $feed
     * @return string|null
     */
    static public function getFaviconUrl(Feed $feed): ?string
    {
        $favicon = Favicon::fetchOr(
            $feed->url,
            '/favicon.ico'
        );
        
        if (is_string($favicon)) {
            return $favicon;
        }

        return $favicon->cache(now()->addWeek())->getFaviconUrl();
    }

    /**
     * Try get feed items (title, link, content, date)
     *
     * @param Feed $feed
     * @return array
     */
    public function getFeedItems(Feed $feed): array
    {
        $data = [];

        // Simple cache
        $cache = new SimpleCacheBridge();

        // Create rss parser
        $simplepie = new \SimplePie\SimplePie();
        $simplepie->set_cache($cache);
        $simplepie->set_feed_url($feed->url);
        $success = $simplepie->init();

        // Check parsing state
        if ($success) {
            $simplepie->handle_content_type();

            $data['description'] = $simplepie->get_description();

            foreach ($simplepie->get_items() as $item)
            {
                $data['items'][] = [
                  'title' => $item->get_title(),
                  'link' => $item->get_permalink(),
                  'content' => strip_tags(Str::limit($item->get_content(), 1000)),
                  'date' => $item->get_date('j M Y, g:i a'),
                ];
            }
        }

        return $data;
    }
}