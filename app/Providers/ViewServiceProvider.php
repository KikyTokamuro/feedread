<?php

namespace App\Providers;

use App\View\Composers\FeedComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.main', FeedComposer::class);
    }
}
