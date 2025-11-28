<?php

namespace App\View\Composers;

use App\Settings\GeneralSettings;
use Illuminate\View\View;

class SettingsComposer
{
    /**
     * @param View $view
     */
    public function compose(View $view): void
    {
        $view->with('settings', app(GeneralSettings::class));
    }
}
