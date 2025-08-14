<?php

declare(strict_types=1);

namespace Relay\Sites;

use Relay\Parsing\Parser;

final class UsSoccer implements Site
{
    public static function slug(): string
    {
        return 'us-soccer';
    }

    public function logo(): string
    {
        return asset('images/us-soccer-logo.png');
    }

    public function title(): string
    {
        return 'US Soccer';
    }

    public function description(): ?string
    {
        return null;
    }

    public function url(): string
    {
        return 'https://www.ussoccer.com/';
    }

    public function category(): Category
    {
        return Category::NATIONAL;
    }

    public function parser(): ?Parser
    {
        return null;
    }

    public function rss(): ?string
    {
        return null;
    }

    /**
     * @phpstan-ignore return.unusedType
     */
    public function relay(): ?string
    {
        return route('feed', $this->slug());
    }

    /**
     * @phpstan-ignore return.unusedType
     */
    public function youtube(): ?string
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCk1pcWQ5E19g0Cgp4c1eI1w';
    }
}
