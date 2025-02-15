<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Relay\Article;

final class Prune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove older articles from the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::table('articles')
            ->select('site')
            ->distinct()
            ->pluck('site')
            ->each(fn($site) => $this->pruneSite($site));
    }

    /**
     * Keep the past 100 site articles in place and remove the rest.
     */
    private function pruneSite(string $site): void
    {
        $recent = Article::where('site', $site)
            ->latest('published_at')
            ->limit(50)
            ->pluck('id');

        $qualified = Article::where('site', $site)
            ->whereNotIn('id', $recent);

        /** @phpstan-ignore larastan.noUnnecessaryCollectionCall */
        Log::info("Removing {$qualified->get()->count()} old articles for {$site}");

        $qualified->delete();
    }
}
