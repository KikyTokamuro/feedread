<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public bool $preview = true;
    public bool $dark = false;

    public static function group(): string
    {
        return 'general';
    }
}