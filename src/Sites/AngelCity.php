<?php

namespace Relay\Sites;

final class AngelCity implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'angel-city';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/angel-city-logo.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'Angel City FC';
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
        return 'https://angelcity.com/';
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
     */
    public function rss(): ?string
    {
        return 'https://angelcity.com/acfc-post/rss.xml';
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
     */
    public function youtube(): ?string
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCmL3eLNeL2Ux5hh5tUHLzng';
    }
}
