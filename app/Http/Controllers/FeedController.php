<?php

namespace App\Http\Controllers;

use App\Cache\SimpleCacheBridge;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

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

    /**
     * Show edit feed
     *
     * @param Feed $feed
     * @return View
     */
    public function edit(Feed $feed): View
    {
        return view('feed.edit', compact('feed'));
    }

    /**
     * Update feed
     *
     * @param Request $request
     * @param Feed $feed
     * @return RedirectResponse
     */
    public function update(Request $request, Feed $feed): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string',
            'url' => 'required|string'
        ]);

        $feed->update($data);

        return redirect()->route('feed.show', $feed->id);
    }
}
