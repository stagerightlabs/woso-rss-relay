<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Factory;
use Illuminate\Log\LogManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Relay\Article;
use Relay\Sites\Catalog;
use Relay\Sites\Site;

final class Gather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gather {site?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gather content from registered sources';

    public function __construct(private Factory $http, private LogManager $log)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        foreach ($this->sources() as $site) {
            $this->gather($site);
        }

        return self::SUCCESS;
    }

    /**
     * Check a news source for new articles.
     */
    public function gather(Site $site): void
    {
        $parser = $site->parser();
        if (!$parser) {
            return;
        }

        // Look at the source index for a list of articles
        $response = $this->http->get($parser->target());
        if (!$response->ok()) {
            $this->log->error("Received {$response->getStatusCode()} error when checking '{$parser->target()}'");
            return;
        }

        // Loop through the articles found and fetch content for new entries
        foreach ($parser->entries($response) as $entry) {
            // Does the article exist?
            if (Article::where('key', $entry['key'])->where('site', $site->slug())->exists()) {
                continue;
            }

            // If not, attempt to create a new entry.
            $response = $this->http->get($entry['url']);
            if (!$response->ok()) {
                Log::error("Received {$response->getStatusCode()} error when checking '{$entry['url']}'");
                continue;
            }

            $article = $parser->article($response);
            $article->save();

            if ($this->getOutput()->isVerbose()) {
                $this->info("New Article for {$site->title()}: {$article->title}");
            }
        }
    }

    /**
     * The list of sources to check.
     *
     * @return Collection<string,Site>
     */
    public function sources(): Collection
    {
        return Catalog::all()
            ->filter(fn($site) => !is_null($site->parser()))
            ->when($this->argument('site'), function (Collection $collection) {
                return $collection->filter(
                    fn($site) => $site->slug() == $this->argument('site'),
                );
            });
    }
}
