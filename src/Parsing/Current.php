<?php

declare(strict_types=1);

namespace Relay\Parsing;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Relay\Article;
use Relay\Sites\KansasCityCurrent;

final class Current implements Parser
{
    /**
     * The URL for the index page to be scraped.
     */
    public function target(): string
    {
        return 'https://www.kansascitycurrent.com/news';
    }

    /**
     * Parse an index response into a list of entries.
     *
     * @return Collection<array-key, Entry>
     */
    public function entries(Response $response): Collection
    {
        $dom = \Dom\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $entries = new Collection();
        $base = Uri::of($this->target());

        foreach ($dom->querySelectorAll('a.news-story-tile') as $link) {
            $path = $link->getAttribute('href') ?? '';
            $key = (string) Str::of($path)->afterLast('/');
            $url = (string) $base->withPath($path);

            $entries->push(new Entry($url, $key, ['url' => $url, 'key' => $key]));
        }

        return $entries;
    }

    /**
     * Create an article from a content response.
     *
     * @param array<string,string> $context
     * @return Article
     */
    public function article(Response $response, $context = []): Article
    {
        $dom = \Dom\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $article = new Article();

        // Slug
        $article->site = KansasCityCurrent::slug();

        // Title
        $article->title = (string) Str::of($dom->querySelector('h1.news-article-headline')->textContent ?? '')->trim();

        // Key
        $article->key = $context['key'];

        // Link
        $article->link = $context['url'];

        // Author
        $article->author = 'Kansas City Current';

        // Image
        $node = $dom->querySelector('.news-hero-background img');
        $image = $node
            ? Str::of($node->getAttribute('src') ?? '')->trim()
            : Str::of('');
        if ($image->isNotEmpty()) {
            $image = $image
                ->prepend("<p><img src=\"https://www.kansascitycurrent.com")
                ->append("\" alt=\"{$article->title}\" /></p>\n\n");
        }

        // Summary
        $node = $dom->querySelector('.page-modules .html-area p');
        $summary = $node
            ? (string) Str::of($node->textContent ?? '')->trim()->prepend('<p>')->append('</p>')
            : '';

        $article->summary = $image->isNotEmpty()
            ? (string) $image->append($summary)
            : $summary;

        // Publication Date
        $node = $dom->querySelector('p.news-article-date');
        if ($node) {
            $timestamp = (string) Str::of($node->textContent ?? '')->after('|')->trim();
            $article->published_at = new CarbonImmutable($timestamp);
        }

        return $article;
    }
}
