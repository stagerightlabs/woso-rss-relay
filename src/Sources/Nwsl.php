<?php

namespace Relay\Sources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Relay\Article;
use Relay\Feed;
use Relay\Link;

final class Nwsl implements Source
{
    public const INDEX = 'https://www.nwslsoccer.com/api/dapi/selection/latest-news';

    /**
     * Fetch the contents of the source index page.
     */
    public function index(): Response
    {
        return Http::get(self::INDEX, ['limit' => 16]);
    }

    /**
     * Fetch the details of a single source article.
     */
    public function content(string $url): Response
    {
        return Http::get($url);
    }

    /**
     * Parse the news content into a list of entries.
     *
     *  @return Collection<array-key, Link>
     */
    public function links(Response $response): Collection
    {
        return $response->collect('items')->map(function (array $arr) {
            return new Link(
                url: $arr['selfUrl'],
                key: $arr['_entityId'],
            );
        });
    }

    /**
     * Create an article from rss entry content.
     */
    public function article(Response $response): Article
    {
        $content = $response->json();

        $article = new Article();
        $article->feed = $this->feed();
        $article->key = Arr::get($content, '_entityId');
        $article->title = Arr::get($content, 'title');
        $article->link = 'https://www.nwslsoccer.com/news/' . Arr::get($content, 'slug');
        $article->image = $this->image(Arr::get($content, 'thumbnail.thumbnailUrl'));
        $article->summary = Arr::get($content, 'summary');
        $content = (new Collection(Arr::get($content, 'parts')))
            ->filter(fn($part) => $part['type'] == 'markdown')
            ->sort(fn($a, $b) => strlen($b['content']) <=> strlen($a['content']))
            ->first();
        $article->content = $content ? Str::markdown($content['content']) : null;
        $publishedAt = Arr::get($content, 'contentDate');
        $article->published_at = $publishedAt ? new \DateTimeImmutable($publishedAt) : null;

        return $article;
    }

    /**
     * The feed associated with the source.
     *
     * @return Feed
     */
    public function feed(): Feed
    {
        return Feed::NWSL;
    }

    /**
     * Prepare an image URL for the article.
     */
    public function image(string $thumbnail): ?string
    {
        $parts = explode("/prd/", $thumbnail);

        if (count($parts) == 1) {
            return null;
        }

        if (Str::contains($parts[1], '/')) {
            return null;
        }

        return str_replace(
            '[STORAGE]',
            $parts[1],
            'https://www.nwslsoccer.com/_next/image?url=https%3A%2F%2Fimages.nwslsoccer.com%2Fimage%2Fprivate%2Ft_ratio21_9-size60%2Fprd%2F[STORAGE]&w=1920&q=75',
        );
    }
}
