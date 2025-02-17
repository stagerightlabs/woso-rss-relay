<?php

declare(strict_types=1);

namespace Relay\Sites;

final class OrlandoPride implements Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string
    {
        return 'orlando-pride';
    }

    /**
     * The site logo asset URL.
     */
    public function logo(): string
    {
        return asset('images/orlando-pride.png');
    }

    /**
     * The title of the site.
     */
    public function title(): string
    {
        return 'Orlando Pride';
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
        return 'https://www.orlandocitysc.com/pride/';
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
        return null;
    }

    /**
     * The relay RSS feed URL.
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
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCTB6D04JMYyqoJu6WoDVciw';
    }
}
