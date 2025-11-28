<?php

namespace App\Providers;

use App\View\Composers\FeedComposer;
use App\View\Composers\SettingsComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Settings
        View::composer('*', SettingsComposer::class);

        // Feeds for sidebar
        View::composer('layouts.main', FeedComposer::class);
    }
}
