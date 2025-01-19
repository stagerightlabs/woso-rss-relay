<?php

namespace Relay\Sites;

interface Site
{
    /**
     * The slug used to identify the site.
     */
    public static function slug(): string;

    /**
     * The site logo asset URL.
     */
    public function logo(): string;

    /**
     * The title of the site.
     */
    public function title(): string;

    /**
     * A description of the site.
     */
    public function description(): ?string;

    /**
     * The site URL.
     */
    public function url(): string;

    /**
     * The site's category.
     */
    public function category(): Category;

    /**
     * The parser responsible for reading this site, if applicable.
     */
    public function parser(): ?\Relay\Parsing\Parser;

    /**
     * The RSS feed URL provided by the site.
     */
    public function rss(): ?string;

    /**
     * The relay RSS feed URL.
     */
    public function relay(): ?string;

    /**
     * The YouTube RSS feed URL.
     */
    public function youtube(): ?string;
}
