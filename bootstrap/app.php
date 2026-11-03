<?php

declare(strict_types=1);

use App\Http\Middleware\SetCacheHeaders;
use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('static')
                ->group(base_path('routes/static.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [SetSecurityHeaders::class]);

        // Public, cacheable routes: no session, cookies or CSRF
        $middleware->group('static', [
            SubstituteBindings::class,
            SetSecurityHeaders::class,
            SetCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(static function (Throwable $e) {
            if (app()->bound('honeybadger')) {
                app('honeybadger')->notify($e, app('request'));
            }
        });
    })
    ->create();
