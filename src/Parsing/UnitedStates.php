<?php

declare(strict_types=1);

namespace Relay\Parsing;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Relay\Article;
use Relay\Sites\UsSoccer;

final class UnitedStates implements Parser
{
    /**
     * The target URL for the parser.
     */
    public function target(): string
    {
        return 'https://www.ussoccer.com/teams/uswnt/stories';
    }

    /**
     * Parse the entries from the response.
     */
    public function entries(Response $response): Collection
    {
        $dom = \Dom\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $entries = new Collection();
        $base = Uri::of($this->target());

        foreach ($dom->querySelectorAll('[class^="StoryGrid_hideOnMobile"] section[class^="StoryCard_Card__"]') as $link) {
            $path = $link->querySelector('a')?->getAttribute('href') ?? '';
            $key = (string) Str::of($path)->afterLast('/');
            $url = (string) $base->withPath($path);

            $entries->push(new Entry($url, $key, ['url' => $url, 'key' => $key]));
        }

        return $entries;
    }

    /**
     * Parse the article from the response.
     */
    public function article(Response $response, array $context = []): Article
    {
        $dom = \Dom\HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);
        $article = new Article();
        $article->site = UsSoccer::slug();
        $article->key = $context['key'];
        $article->link = $context['url'];
        $article->author = 'US Soccer';
        $article->title = (string) Str::of($dom->querySelector('.P1Story_wrap__PSpnZ h1')->textContent ?? '')->squish();

        // Summary
        $article->summary = (string) Str::of($dom->querySelector('.P1Story_wrap__PSpnZ span')->textContent ?? '')->squish();

        // Image
        $node = $dom->querySelector('.P1Story_desktopImageContainer__YRc1m figure img');
        $image = $node
            ? Str::of($node->getAttribute('src') ?? '')->trim()
            : Str::of('');
        if ($image->isNotEmpty()) {
            $image = $image
                ->prepend("<p><img src=\"")
                ->append("\" alt=\"{$article->title}\" /></p>\n\n");
        }

        // Prepare Summary
        $nodes = $dom->querySelectorAll('.TextBlock_container__kb82B div p');
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

        // Third paragraph
        if ($p = $nodes->item(2)) {
            $paragraph = (string) Str::of($p->textContent ?? '')->squish()->prepend('<p>')->append("</p>\n");
            $summary = $summary->append($paragraph);
        }

        $article->summary = $image->isNotEmpty()
            ? $image->append($summary->toString())->toString()
            : $summary->toString();

        // Publication Date
        $node = $dom->querySelector('.P1Story_meta__GrYkU time');
        if ($node) {
            $timestamp = (string) Str::of($node->textContent ?? '')->trim();
            $article->published_at = new CarbonImmutable($timestamp);
        }

        return $article;
    }
}
