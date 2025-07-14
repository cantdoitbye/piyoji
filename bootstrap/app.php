<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Scheduling\Schedule;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
          $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
        ]);
    })
     ->withSchedule(function (Schedule $schedule) {
        // Expire old contracts daily at 2:00 AM
        $schedule->command('contracts:expire-old')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/contracts-expiry.log'));

        // Send contract expiry alerts every Monday at 9:00 AM
        $schedule->command('contracts:expire-old')
                 ->weeklyOn(1, '09:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/contract-alerts.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
