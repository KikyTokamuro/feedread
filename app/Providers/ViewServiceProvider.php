<?php

namespace App\Providers;

use App\View\Composers\FeedComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Feeds for the sidebar and for the empty state of the welcome page.
        View::composer(['layouts.main', 'main'], FeedComposer::class);
    }
}
