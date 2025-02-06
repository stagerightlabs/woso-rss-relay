<?php

declare(strict_types=1);

namespace Relay\Parsing;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Relay\Article;
use Relay\Sites\Nwsl as NwslSite;

final class Nwsl implements Parser
{
    /**
     * Fetch the contents of the source index page.
     */
    public function target(): string
    {
        return 'https://www.nwslsoccer.com/api/dapi/selection/latest-news';
    }

    /**
     * Parse the news content into a list of entries.
     *
     *  @return Collection<array-key, Entry>
     */
    public function entries(Response $response): Collection
    {
        return $response->collect('items')->map(function (array $arr) {
            return new Entry(strval($arr['selfUrl']), strval($arr['_entityId']));
        });
    }

    /**
     * Create an article from rss entry content.
     */
    public function article(Response $response, array $context = []): Article
    {
        $json = $response->json();

        $article = new Article();
        $article->site = NwslSite::slug();
        $article->key = Arr::get($json, '_entityId');
        $article->title = Arr::get($json, 'title');
        $article->link = 'https://www.nwslsoccer.com/news/' . Arr::get($json, 'slug');
        $article->author = Arr::get($json, 'fields.author');
        $article->summary = Arr::get($json, 'summary');
        $content = (new Collection(Arr::get($json, 'parts')))
            ->filter(fn($part) => $part['type'] == 'markdown')
            ->sort(fn($a, $b) => strlen($b['content']) <=> strlen($a['content']))
            ->first();
        $content = Str::of($content['content'] ?? '')->markdown(['html_input' => 'strip']);
        if ($image = $this->image(Arr::get($json, 'thumbnail.thumbnailUrl'), $article->title)) {
            $content = $content->prepend($image);
        }
        $article->content = $content->toString();
        $publishedAt = Arr::get($json, 'contentDate');
        $article->published_at = $publishedAt ? new CarbonImmutable($publishedAt) : null;

        return $article;
    }

    /**
     * Prepare an image URL for the article.
     */
    private function image(string $thumbnail, string $alt): ?string
    {
        $parts = explode("/prd/", $thumbnail);

        if (count($parts) == 1) {
            return null;
        }

        if (Str::contains($parts[1], '/')) {
            return null;
        }

        return Str::of('https://www.nwslsoccer.com/_next/image?url=https%3A%2F%2Fimages.nwslsoccer.com%2Fimage%2Fprivate%2Ft_ratio21_9-size60%2Fprd%2F[STORAGE]&w=1920&q=75')
            ->replace('[STORAGE]', $parts[1])
            ->prepend("<p><img src=\"")
            ->append("\" alt=\"{$alt}\" /></p>\n\n")
            ->toString();
    }
}
