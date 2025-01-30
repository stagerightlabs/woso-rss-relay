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
                        'key' => strval($row['_id']),
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
        $article->key = $context['key'];

        // Link
        $article->link = $context['url'];

        // Author
        $article->author = 'Gotham FC';

        // Image
        $image = $container->querySelector('img[sizes="600px md:1400px"]');
        if (!$image) {
            throw new \Exception('Could not find image content');
        }
        // Extract the raw image source
        $raw = (new Collection(explode("\n", $image->getAttribute('srcset') ?? '')))
            ->filter()
            ->first();
        if (!$raw) {
            throw new \Exception('Could not find image content');
        }
        // Manipulate the source URL to create a preferable image URL.
        $image = Str::of($raw)
            ->trim()
            ->before(' ')
            ->after('=')
            ->pipe('urldecode')
            ->pipe(function ($url) {
                $parts = parse_url($url->toString());

                if (!$parts) {
                    return Str::of('');
                }

                return Str::of("{$parts['scheme']}://{$parts['host']}{$parts['path']}");
            })
            ->append('?w=1200&q=75');

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
        if ($image->length() > 0) {
            $tag = $image
                ->prepend("<p><img src=\"")
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
