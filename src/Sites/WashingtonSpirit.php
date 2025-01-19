<?php

namespace Relay\Sites;

final class WashingtonSpirit implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'washington-spirit';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/washington-spirit-logo.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'Washington Spirit';
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
        return 'https://washingtonspirit.com/';
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
        return 'https://washingtonspirit.com/feed/';
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
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCMlOyKf_b7toiIWFUAQ5ISQ';
    }
}
