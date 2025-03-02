<?php

declare(strict_types=1);

namespace Relay\Sites;

use Relay\Parsing\Royals;

final class UtahRoyals implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'utah-royals';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/utah-royals-logo.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'Utah Royals';
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
        return 'https://www.rsl.com/utahroyals/';
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
        return new Royals();
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
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCN835Di4VgkkjCp3GQ4xLPQ';
    }
}
