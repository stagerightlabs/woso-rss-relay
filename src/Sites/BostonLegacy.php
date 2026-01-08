<?php

namespace Relay\Sites;

use Relay\Parsing\Parser;

class BostonLegacy implements Site
{
    public static function slug(): string
    {
        return 'boston-legacy';
    }

    public function logo(): string
    {
       return asset('images/boston-legacy-fc-crest.svg');
    }

    public function title(): string
    {
        return 'Boston Legacy';
    }

    public function description(): ?string
    {
        return null;
    }

    public function url(): string
    {
       return 'https://bostonlegacyfc.com/';
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
        return 'https://bostonlegacyfc.com/blogs/press.atom';
    }

    public function relay(): ?string
    {
        return null;
    }

    public function youtube(): ?string
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id=UCldSagc3AQQciyc49xhVsnw';
    }

}
