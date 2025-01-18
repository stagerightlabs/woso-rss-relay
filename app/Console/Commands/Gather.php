<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Relay\Article;
use Relay\Feed;
use Relay\Sources\Nwsl;
use Relay\Sources\Source;

class Gather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gather {feed?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gather content from registered sources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        foreach ($this->sources() as $class) {
            $this->gather(new $class());
        }

        return self::SUCCESS;
    }

    /**
     * Check a news source for new articles.
     */
    public function gather(Source $source): void
    {
        // Look at the source index for a list of articles
        $response = $source->index();
        if (!$response->ok()) {
            $target = $response->transferStats ?
                strval($response->transferStats->getRequest()->getUri())
                : $source->feed()->value;
            Log::error("Received {$response->getStatusCode()} error when checking '{$target}'");
            return;
        }

        // Loop through the articles found and fetch content for new entries
        foreach ($source->links($response) as $link) {
            // Does the article exist?
            if (Article::where('key', $link->key)->where('feed', $source->feed())->exists()) {
                continue;
            }

            // If not, attempt to create a new entry.
            $response = $source->content($link->url);
            if (!$response->ok()) {
                Log::error("Received {$response->getStatusCode()} error when checking '{$link->url}'");
                continue;
            }

            $article = $source->article($response);
            $article->save();

            if ($this->getOutput()->isVerbose()) {
                $this->info("New Article for {$source->feed()->value}: {$article->title}");
            }
        }
    }

    /**
     * The list of sources to check.
     *
     * @return Collection<string,class-string<Source>>
     */
    public function sources(): Collection
    {
        $feed = $this->argument('feed');

        /** @phpstan-ignore return.type */
        return collect([
            Feed::NWSL->value => Nwsl::class,
        ])->when($feed, function (Collection $collection) use ($feed) {
            $collection->filter(fn($entry) => $collection[$feed] == $entry);
        });
    }
}
