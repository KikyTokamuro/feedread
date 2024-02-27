<?php

namespace App\Http\Controllers;

use App\Cache\SimpleCacheBridge;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FeedController extends Controller
{
    /**
     * Show new feed page
     *
     * @return View
     */
    public function add(): View
    {
        return view('feed.add');
    }

    /**
     * Store new feed
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string',
            'url' => 'required|string'
        ]);

        $feed = Feed::create($data);

        return redirect()->route('feed.show', $feed->id);
    }

    /**
     * Show feed
     *
     * @param Feed $feed
     * @return View
     */
    public function show(Feed $feed): View
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
            $data['items'] = $simplepie->get_items();
        }

        return view('feed.show', compact('feed', 'data'));
    }

    /**
     * Delete feed
     *
     * @param Feed $feed
     * @return RedirectResponse
     */
    public function delete(Feed $feed): RedirectResponse
    {
        $feed->delete();

        return redirect()->route('main');
    }
}
