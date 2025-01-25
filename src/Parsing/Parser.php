<?php

declare(strict_types=1);

namespace Relay\Parsing;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Relay\Article;

interface Parser
{
    /**
     * The URL for the index page to be scraped.
     */
    public function target(): string;

    /**
     * Parse an index response into a list of entries.
     *
     * @return Collection<array-key, Entry>
     */
    public function entries(Response $response): Collection;

    /**
     * Create an article from a content response
     *
     * @param array<string, string> $context
     * @return Article
     */
    public function article(Response $response, array $context = []): Article;
}
