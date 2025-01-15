<?php

namespace Relay\Sources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Relay\Article;
use Relay\Feed;
use Relay\Link;

interface Source
{
    /**
     * Fetch the contents of the source index page.
     *
     * @return Response
     */
    public function index(): Response;

    /**
     * Fetch the details of a single source article.
     */
    public function content(string $url): Response;

    /**
     * Parse an index response into a list of entries.
     *
     * @return Collection<array-key, Link>
     */
    public function links(Response $response): Collection;

    /**
     * Create an article from a content response
     *
     * @return Article
     */
    public function article(Response $response): Article;

    /**
     * The feed associated with the source.
     *
     * @return Feed
     */
    public function feed(): Feed;
}
