<?php

namespace Relay;

/**
 * A value object that represents a content link to be scraped.
 */
final class Link
{
    public function __construct(public string $url, public string $key)
    {
        $this->url = $url;
        $this->key = $key;
    }
}
