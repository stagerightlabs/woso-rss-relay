<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
            ->limit(100)
            ->pluck('id');

        Article::whereNotIn('id', $recent)->delete();

        if ($this->getOutput()->isVerbose()) {
            $this->info("Pruned {$site} articles");
        }
    }
}
