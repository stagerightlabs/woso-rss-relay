<?php

declare(strict_types=1);

namespace Relay\Sites;

use Relay\Parsing\Parser;

class DenverSummit implements Site
{
    public static function slug(): string
    {
        return 'denver-summit';
    }

    public function logo(): string
    {
        return asset('images/denver-summit-crest.png');
    }

    public function title(): string
    {
        return 'Denver Summit';
    }

    public function description(): ?string
    {
        return null;
    }

    public function url(): string
    {
        return 'https://www.denversummitfc.com';
    }

    public function category(): Category
    {
        return Category::NWSL;
    }

    public function parser(): ?Parser
    {
        return null;
    }

    public function rss(): ?string
    {
        return 'https://www.denversummitfc.com/blogs/news.atom';
    }

    public function relay(): ?string
    {
        return null;
    }

    public function youtube(): ?string
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UC55HcqZQqqdnkjsWWrD01Wg';
    }

}
