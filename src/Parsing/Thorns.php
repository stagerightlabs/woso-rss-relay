<?php

declare(strict_types=1);

namespace Relay\Parsing;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Relay\Article;
use Relay\Sites\PortlandThorns;

final class Thorns implements Parser
{
    /**
     * The URL for the index page to be scraped.
     */
    public function target(): string
    {
        return 'https://www.thorns.com/news?mediatype=Articles';
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

        foreach ($dom->querySelectorAll('a.large-link') as $link) {
            $path = $link->getAttribute('href') ?? '';
            $key = (string) Str::of($path)->afterLast('/');
            $url = (string) Str::of((string) $base->withPath($path))->before('?');

            if (Str::contains($url, '/news/')) {
                $entries->push(new Entry($url, $key, ['url' => $url, 'key' => $key]));
            }
        }

        return $entries;
    }

    /**
     * Create an article from a content response.
     *
     * @param array<string,string> $context
     * @return Article
     */
    public function article(Response $response, array $context = []): Article
    {
        $dom = \Dom\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $article = new Article();

        // Slug
        $article->site = PortlandThorns::slug();

        // Title
        $article->title = (string) Str::of($dom->querySelector('h1.blog-post-heading')->textContent ?? '')->squish();

        // Key
        $article->key = $context['key'];

        // Link
        $article->link = $context['url'];

        // Author
        $article->author = 'Portland Thorns';

        // Image
        $node = $dom->querySelector('.background-photo');
        $style = $node ? ($node->getAttribute('style') ?? '') : '';
        $image = Str::of($style)->between("\"", "\"");
        if ($image->isNotEmpty()) {
            $image = $image
                ->prepend("<p><img src=\"")
                ->append("\" alt=\"{$article->title}\" /></p>\n\n");
        }

        // Summary
        $node = $dom->querySelector('.rich-text-style p');
        $summary = $node
            ? (string) Str::of($node->textContent ?? '')->squish()->prepend('<p>')->append('</p>')
            : '';

        $article->summary = $image->isNotEmpty()
            ? (string) $image->append($summary)
            : $summary;

        // Publication Date
        $node = $dom->querySelector('.article-cell .news-grid-meta-wrapper');
        if ($node) {
            $timestamp = (string) Str::of($node->childNodes[3]->textContent ?? '')->trim();
            $article->published_at = new CarbonImmutable($timestamp);
        }


        return $article;
    }
}
