<?php

namespace Relay\Sites;

use Relay\Parsing\Nwsl as NwslParser;

final class Nwsl implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'nwsl';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/nwsl-logo.svg');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'NWSL Soccer';
    }

    /**
     * A description of the site.
     *
     * @phpstan-ignore return.unusedType
     */
    public function description(): ?string
    {
        return '';
    }

    /**
     * The site URL.
     */
    public function url(): string
    {
        return 'https://www.nwslsoccer.com/';
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
     *
     * @phpstan-ignore return.unusedType
     */
    public function parser(): ?\Relay\Parsing\Parser
    {
        return new NwslParser();
    }

    /**
     * The RSS feed URL provided by the site.
     */
    public function rss(): ?string
    {
        return null;
    }

    /**
     * The relay RSS feed URL.
     *
     * @phpstan-ignore return.unusedType
     */
    public function relay(): ?string
    {
        return route('feed', $this->slug());
    }

    /**
     * The YouTube RSS feed URL.
     *
     * @phpstan-ignore return.unusedType
     */
    public function youtube(): ?string
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCL4xu08EDu0ZFZsBJUB0chw';
    }
}
