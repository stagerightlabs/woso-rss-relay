<?php

declare(strict_types=1);

namespace Relay\Sites;

use Relay\Parsing\Nsl as NslParser;

final class Nsl implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'nsl';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/northern-super-league-logo.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'Northern Super League';
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
        return 'https://www.nsl.ca/';
    }

    /**
     * The site's category.
     */
    public function category(): Category
    {
        return Category::NSL;
    }

    /**
     * The parser responsible for reading this site, if applicable.
     *
     * @phpstan-ignore return.unusedType
     */
    public function parser(): ?\Relay\Parsing\Parser
    {
        return new NslParser();
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
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCQVAAHTnJv8AwC2fwY_XFbQ';
    }
}
