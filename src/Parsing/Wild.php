<?php

declare(strict_types=1);

namespace Relay\Parsing;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Relay\Article;
use Relay\Sites\CalgaryWild;

final class Wild implements Parser
{
    /**
     * Fetch the contents of the source index page.
     */
    public function target(): string
    {
        return 'https://www.calgarywildfc.com/news';
    }

    /**
     * Parse the news content into a list of entries.
     *
     *  @return Collection<array-key, Entry>
     */
    public function entries(Response $response): Collection
    {
        $dom = \Dom\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $entries = new Collection();

        $count = 0;
        foreach ($dom->querySelectorAll('.entries-list-item') as $entry) {
            $count++;
            if ($count > 6) {
                break;
            }

            $link = $entry->querySelector('a');
            $path = $link ? ($link->getAttribute('href') ?? '') : '';
            $key = (string) Str::of($path)->afterLast('/');
            $url = (string) Str::of($path)->before('?');

            if (Str::contains($url, '/news/')) {
                $entries->push(new Entry($url, $key, ['url' => $url, 'key' => $key]));
            }
        }

        return $entries;
    }

    /**
     * Create an article from rss entry content.
     */
    public function article(Response $response, array $context = []): Article
    {
        $dom = \Dom\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $article = new Article();

        // Slug
        $article->site = CalgaryWild::slug();

        // Title
        $article->title = (string) Str::of($dom->querySelector('.news-article h3')->textContent ?? '')->squish();

        // Key
        $article->key = $context['key'];

        // Link
        $article->link = $context['url'];

        // Author
        $article->author = 'Calgary Wild FC';

        // Image
        $node = $dom->querySelector('.news-article img');
        $image = $node
            ? Str::of($node->getAttribute('src') ?? '')->trim()
            : Str::of('');
        if ($image->isNotEmpty()) {
            $image = $image
                ->prepend("<p><img src=\"")
                ->append("\" alt=\"{$article->title}\" /></p>\n\n");
        }

        // Prepare Summary
        $nodes = $dom->querySelectorAll('.news-article .rich-text p');
        $summary = Str::of('');

        // First paragraph
        if ($p = $nodes->item(0)) {
            $paragraph = (string) Str::of($p->textContent ?? '')->squish()->prepend('<p>')->append("</p>\n");
            $summary = $summary->append($paragraph);
        }

        // Second paragraph
        if ($p = $nodes->item(1)) {
            $paragraph = (string) Str::of($p->textContent ?? '')->squish()->prepend('<p>')->append("</p>\n");
            $summary = $summary->append($paragraph);
        }

        $article->summary = $image->isNotEmpty()
            ? $image->append($summary->toString())->toString()
            : $summary->toString();

        // Publication Date
        $node = $dom->querySelector('.news-article div.text-tag');
        if ($node) {
            $timestamp = (string) Str::of($node->textContent ?? '')->trim();
            $article->published_at = new CarbonImmutable($timestamp);
        }

        return $article;
    }
}
