<?php

namespace App\View\Composers;

use App\Models\Feed;
use Illuminate\View\View;

class FeedComposer
{
    /**
     * @param View $view
     */
    public function compose(View $view): void
    {
        $view->with('feeds', Feed::orderBy('created_at', 'desc')->get());
    }
}
