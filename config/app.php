<?php

return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Makassar'),
    'locale' => env('APP_LOCALE', 'id'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'id_ID'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(explode(',', env('APP_PREVIOUS_KEYS', ''))),
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    // Reminder config (custom)
    'reminder_timezone' => env('REMINDER_TIMEZONE', 'Asia/Makassar'),
    'reminder_slot_1' => env('REMINDER_SLOT_1', '16:30'),
    'reminder_slot_2' => env('REMINDER_SLOT_2', '20:30'),
    'reminder_slot_3' => env('REMINDER_SLOT_3', '23:00'),
    'maganghub_dashboard_url' => env('MAGANGHUB_DASHBOARD_URL', 'https://monev.maganghub.kemnaker.go.id/dashboard/riwayat'),
    'maganghub_login_url' => env('MAGANGHUB_LOGIN_URL', 'https://account.kemnaker.go.id/auth/login'),
];