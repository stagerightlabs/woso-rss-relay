<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Configure Eloquent strictness
        if ($this->app->environment('production')) {
            Model::preventSilentlyDiscardingAttributes();
            Model::preventLazyLoading();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::usePrefetchStrategy('aggressive');

        // Prepare PRAGMA configurations for the current connection.
        // https://nik.software/sqlite-optimisations-in-laravel/
        foreach (['sqlite'] as $connection) {
            DB::connection($connection)
                ->statement(
                    <<<SQL
                    PRAGMA synchronous = NORMAL;
                    PRAGMA mmap_size = 134217728; -- 128 megabytes
                    PRAGMA cache_size = 1000000000;
                    PRAGMA foreign_keys = true;
                    PRAGMA busy_timeout = 5000;
                    PRAGMA temp_store = memory;
                    SQL,
                );
        }
    }
}
