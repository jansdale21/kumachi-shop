<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shop settings
    |--------------------------------------------------------------------------
    |
    | Business hours are used to validate scheduled pickup/delivery times.
    | Times are local to the app timezone (config('app.timezone')).
    |
    */

    'hours' => [
        // ISO-8601 weekday numbers: 1=Mon ... 7=Sun
        1 => ['open' => '07:00', 'close' => '20:00'],
        2 => ['open' => '07:00', 'close' => '20:00'],
        3 => ['open' => '07:00', 'close' => '20:00'],
        4 => ['open' => '07:00', 'close' => '20:00'],
        5 => ['open' => '07:00', 'close' => '20:00'],
        6 => ['open' => '08:00', 'close' => '21:00'],
        7 => ['open' => '08:00', 'close' => '21:00'],
    ],

    // How far ahead customers may schedule.
    'schedule_max_days' => 7,

    // Increment minutes for the time picker (UI hint).
    'schedule_step_minutes' => 15,
];

