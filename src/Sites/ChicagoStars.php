<?php

declare(strict_types=1);

namespace Relay\Sites;

final class ChicagoStars implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'chicago-stars';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/chicago-stars-logo.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'Chicago Stars';
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
        return 'https://chicagostars.com/';
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
        return 'https://chicagoredstars.com/feed/';
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
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCINHu5PX7gxiGRQzRirh2wQ';
    }
}
