<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeedComposer
{
    /**
     * Share the sidebar feeds of the signed in user with the layout.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();

        $view->with('feeds', $user
            ? $user->feeds()
                ->withCount(['items as unread_count' => fn ($query) => $query->whereNull('read_at')])
                ->orderBy('title')
                ->get()
            : collect());
    }
}
