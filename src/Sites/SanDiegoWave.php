<?php

namespace Relay\Sites;

final class SanDiegoWave implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'san-diego-wave';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/san-diego-wave-logo.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'San Diego Wave';
    }

    /**
     * A description of the site.
     */
    public function description(): ?string
    {
        return null;
    }

    /**
     * The site URL.
     */
    public function url(): string
    {
        return 'https://sandiegowavefc.com/';
    }

    /**
     * The site's category.
     */
    public function category(): Category
    {
        return Category::NWSL;
    }

    /**
     * The parser responsible for reading this site, if applicable.
     */
    public function parser(): ?\Relay\Parsing\Parser
    {
        return null;
    }

    /**
     * The RSS feed URL provided by the site.
     *
     * @phpstan-ignore return.unusedType
     */
    public function rss(): ?string
    {
        return 'https://sandiegowavefc.com/feed/';
    }

    /**
     * The relay RSS feed URL.
     */
    public function relay(): ?string
    {
        return null;
    }

    /**
     * The YouTube RSS feed URL.
     *
     * @phpstan-ignore return.unusedType
     */
    public function youtube(): ?string
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCrYvZ78qIaVZJY9BVexw8bw';
    }
}
