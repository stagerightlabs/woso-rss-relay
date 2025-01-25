<?php

declare(strict_types=1);

namespace Relay\Parsing;

final class Entry
{
    /**
     * @param array<string, string> $context
     */
    public function __construct(public string $url, public string $key, public array $context = []) {}
}
