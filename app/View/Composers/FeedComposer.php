<?php

namespace App\View\Composers;

use App\Models\Feed;
use Illuminate\View\View;

class FeedComposer
{
    /**
     * @param View $view
     */
    public function compose(View $view)
    {
        $view->with('feeds', Feed::all());
    }
}
