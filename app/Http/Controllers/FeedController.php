<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedRequest;
use App\Http\Requests\UpdateFeedRequest;
use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function __construct(
        protected FeedService $feedService
    ) { }

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
     * @param StoreFeedRequest $request
     * @return RedirectResponse
     */
    public function store(StoreFeedRequest $request): RedirectResponse
    {
        $data = $request->validated();
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
        $data = $this->feedService->getFeedItems($feed);

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
     * @param UpdateFeedRequest $request
     * @param Feed $feed
     * @return RedirectResponse
     */
    public function update(UpdateFeedRequest $request, Feed $feed): RedirectResponse
    {
        $data = $request->validated();
        $feed->update($data);

        return redirect()->route('feed.show', $feed->id);
    }
}
