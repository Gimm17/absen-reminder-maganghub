<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CSRF skipped untuk endpoint API publik (dipakai service worker dari origin yg sama)
        $middleware->validateCsrfTokens(except: [
            'api/subscribe',
            'api/checkin',
        ]);

        // Register route middleware aliases.
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $timezone = config('app.reminder_timezone', 'Asia/Makassar');

        $schedule->command('reminders:send slot-1')
            ->dailyAt(config('app.reminder_slot_1', '04:30'))
            ->timezone($timezone)
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('reminders:send slot-2')
            ->dailyAt(config('app.reminder_slot_2', '08:30'))
            ->timezone($timezone)
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('reminders:send slot-3')
            ->dailyAt(config('app.reminder_slot_3', '11:00'))
            ->timezone($timezone)
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->create();