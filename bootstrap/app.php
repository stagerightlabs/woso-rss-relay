<?php

declare(strict_types=1);

use App\Console\Commands\Gather;
use App\Console\Commands\Prune;
use App\Http\Middleware\CacheControl;
use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [SetSecurityHeaders::class]);
        $middleware->web(append: [CacheControl::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(static function (Throwable $e) {
            if (app()->bound('honeybadger')) {
                app('honeybadger')->notify($e, app('request'));
            }
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(Gather::class)
            ->dailyAt('04:00')
            ->timezone('America/Los_Angeles')
            ->environments('production');
        $schedule->call(Prune::class)
            ->dailyAt('05:00')
            ->timezone('America/Los_Angeles')
            ->environments('production');
    })
    ->create();
