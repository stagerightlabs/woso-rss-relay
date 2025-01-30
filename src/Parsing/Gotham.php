<?php

declare(strict_types=1);

namespace Relay\Parsing;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Relay\Article;
use Relay\Sites\GothamFC;

final class Gotham implements Parser
{
    /**
     * The URL for the index page to be scraped.
     */
    public function target(): string
    {
        return 'https://8mpkhoms.apicdn.sanity.io/v2023-01-01/data/query/production?returnQuery=false&query='
            . urlencode('*[_type == "news"] | order(publishedAt desc)[0...6]{_id,publishedAt,slug}');
    }

    /**
     * Parse an index response into a list of entries.
     *
     * @return Collection<array-key, Entry>
     */
    public function entries(Response $response): Collection
    {
        return (new Collection($response->json('result')))
            ->map(function ($row) {
                return new Entry(
                    url: 'https://www.gothamfc.com' . $row['slug']['current'],
                    key: strval($row['_id']),
                    context: [
                        'date' => $row['publishedAt'],
                        'url' => 'https://www.gothamfc.com' . $row['slug']['current'],
                    ],
                );
            });
    }

    /**
     * Create an article from a content response
     *
     * @param array<string,string> $context
     * @return Article
     */
    public function article(Response $response, $context = []): Article
    {

        $html = \DOM\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $container = $html->querySelector('[data-sentry-component="NewsItemContainer"]');
        if (!$container) {
            throw new \Exception('Could not find article content');
        }

        $sections = $container->querySelectorAll('[data-sentry-component="Padder"]');

        // Create a new article
        $article = new Article();
        $article->site = GothamFC::slug();

        // Title
        $hero = $sections->item(0);
        if (!$hero) {
            throw new \Exception('Could not find hero content');
        }
        $h1 = $hero->querySelector('h1');
        if (!$h1) {
            throw new \Exception('Could not find hero H1 content');
        }
        $article->title = Str::of($h1->textContent ?? '')->squish()->toString();

        // Key
        $path = parse_url($context['url'], PHP_URL_PATH);
        if (!$path) {
            throw new \Exception('Could not resolve article path');
        }
        $article->key = Str::of($path)->trim('/')->replace('/', '-')->toString();
        if (empty($article->key)) {
            throw new \Exception('Could not resolve key from article path');
        }

        // Link
        $article->link = $context['url'];

        // Author
        $article->author = 'Gotham FC';

        // Image
        $image = $container->querySelector('img[sizes="600px md:1400px"]');
        if (!$image) {
            throw new \Exception('Could not find image content');
        }
        $src = (new Collection(explode("\n", $image->getAttribute('srcset') ?? '')))
            ->filter(fn($src) => Str::contains($src, '&w=1200', true))
            ->map(fn($str) => Str::of($str))
            ->first();

        // Content
        $paragraphs = $sections->item(1)?->querySelectorAll('div p');
        if (!$paragraphs) {
            throw new \Exception('Could not find paragraph content');
        }
        $content = LazyCollection::make($paragraphs)->map(function ($node) {
            return Str::of($node->innerHTML)
                ->replaceMatches('/class=".*?"/i', '')
                ->replaceMatches('/style=".*?"/i', '')
                ->prepend('<p>')
                ->append('</p>')
                ->squish()
                ->toString();
        })->implode("\n");
        if ($src) {
            $tag = $src->trim()
                ->before(' ')
                ->prepend("<p><img src=\"https://www.gothamfc.com")
                ->append("\" alt=\"{$article->title}\" /></p>\n\n")
                ->toString();
            $content = $tag . $content;
        }
        $article->content = $content;

        // Summary
        $summary = $paragraphs->item(0);
        if (!$summary) {
            throw new \Exception('Could not find summary content');
        }
        $article->summary = Str::of($summary->textContent ?? '')
            ->replaceMatches('/class=".*?"/i', '')
            ->replaceMatches('/style=".*?"/i', '')
            ->squish()
            ->toString();

        $article->published_at = new CarbonImmutable($context['date']);

        return $article;
    }
}
