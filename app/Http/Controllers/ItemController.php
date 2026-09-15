<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Support\PublicUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Open an article: remember that it has been read, then hand the browser
     * over to the original site.
     */
    public function open(Item $item): RedirectResponse
    {
        $this->authorize('view', $item);

        if ($item->isUnread()) {
            $item->forceFill(['read_at' => now()])->save();
        }

        // Only ever bounce to a public http(s) address, so a feed cannot turn
        // this endpoint into an open redirect to javascript: or file:// URLs.
        if (! PublicUrl::isWellFormed($item->url) || ! PublicUrl::isAllowed($item->url)) {
            return redirect()->route('feed.show', $item->feed_id);
        }

        return redirect()->away($item->url);
    }

    /**
     * Mark an article as read.
     */
    public function read(Request $request, Item $item): RedirectResponse|JsonResponse
    {
        return $this->setReadState($request, $item, true);
    }

    /**
     * Mark an article as unread.
     */
    public function unread(Request $request, Item $item): RedirectResponse|JsonResponse
    {
        return $this->setReadState($request, $item, false);
    }

    /**
     * Persist the read state of an article.
     */
    protected function setReadState(Request $request, Item $item, bool $read): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $item);

        $item->forceFill(['read_at' => $read ? now() : null])->save();

        if ($request->expectsJson()) {
            return response()->json(['read' => $read]);
        }

        return back();
    }
}
