<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
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
        Model::shouldBeStrict(!app()->isProduction());

        // Eliminate the need for the $fillable property in models
        Model::unguard();

        // Block destructive commands in production
        DB::prohibitDestructiveCommands(app()->isProduction());

        // Use immutable dates by default
        Date::use(CarbonImmutable::class);
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
