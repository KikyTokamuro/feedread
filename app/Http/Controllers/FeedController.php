<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedRequest;
use App\Http\Requests\UpdateFeedRequest;
use App\Jobs\RefreshFeed;
use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function __construct(
        protected FeedService $feedService
    ) {}

    /**
     * Show the new feed form.
     */
    public function add(): View
    {
        return view('feed.add');
    }

    /**
     * Store a feed for the signed in user.
     */
    public function store(StoreFeedRequest $request): RedirectResponse
    {
        $feed = $request->user()->feeds()->create($request->validated());

        // Pull the first batch of articles and the site icon right away so the
        // feed is not empty when the redirect lands.
        RefreshFeed::dispatch($feed);

        return redirect()->route('feed.show', $feed)
            ->with('status', 'Feed added.');
    }

    /**
     * Show a feed with its stored articles.
     */
    public function show(Feed $feed): View
    {
        $this->authorize('view', $feed);

        $items = $feed->items()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('feed.show', [
            'feed' => $feed,
            'items' => $items,
            'unreadCount' => $feed->items()->unread()->count(),
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(Feed $feed): View
    {
        $this->authorize('update', $feed);

        return view('feed.edit', compact('feed'));
    }

    /**
     * Update a feed.
     */
    public function update(UpdateFeedRequest $request, Feed $feed): RedirectResponse
    {
        $urlChanged = $feed->url !== $request->string('url')->trim()->value();

        $feed->update($request->validated());

        if ($urlChanged) {
            // The stored articles and the icon belong to the old address.
            $feed->items()->delete();
            $feed->fill(['favicon_url' => null, 'last_error' => null])->save();

            RefreshFeed::dispatch($feed);
        }

        return redirect()->route('feed.show', $feed)->with('status', 'Feed updated.');
    }

    /**
     * Delete a feed.
     */
    public function delete(Feed $feed): RedirectResponse
    {
        $this->authorize('delete', $feed);

        $feed->delete();

        return redirect()->route('main')->with('status', 'Feed deleted.');
    }

    /**
     * Refresh a single feed.
     */
    public function refresh(Feed $feed): RedirectResponse
    {
        $this->authorize('refresh', $feed);

        $queued = config('queue.default') !== 'sync';

        RefreshFeed::dispatch($feed);

        if ($queued) {
            return back()->with('status', 'Refresh queued.');
        }

        $feed->refresh();

        return $feed->last_error
            ? back()->with('error', $feed->last_error)
            : back()->with('status', 'Feed refreshed.');
    }

    /**
     * Mark every article of a feed as read.
     */
    public function markAllRead(Feed $feed): RedirectResponse
    {
        $this->authorize('update', $feed);

        $count = $this->feedService->markAllRead($feed);

        return back()->with('status', sprintf('Marked %d article(s) as read.', $count));
    }
}
