<?php

declare(strict_types=1);

namespace Relay\Sites;

use Relay\Parsing\Gotham;

final class GothamFC implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'gotham';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/gotham-fc-logo.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'Gotham FC';
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
        return 'https://www.gothamfc.com';
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
        return new Gotham();
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
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCX5SydaP78jaqqcNx9ZrrtQ';
    }
}
