<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Background Refresh Interval
    |--------------------------------------------------------------------------
    |
    | How often (in minutes) the scheduler should queue a refresh of every
    | feed. Values above 59 fall back to hourly scheduling, so keep this
    | between 1 and 59.
    |
    */

    'refresh_interval_minutes' => (int) env('FEED_REFRESH_INTERVAL_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | First Administrator
    |--------------------------------------------------------------------------
    |
    | Used by the database seeder on a fresh install. When no password is
    | configured, a random one is generated and printed while seeding.
    |
    */

    'admin' => [
        'name' => env('FEEDREAD_ADMIN_NAME', 'admin'),
        'email' => env('FEEDREAD_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('FEEDREAD_ADMIN_PASSWORD'),
    ],

];
