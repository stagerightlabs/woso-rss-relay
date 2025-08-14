<?php

declare(strict_types=1);

namespace Relay\Sites;

enum Category: string
{
    case NWSL = 'nwsl';
    case NSL = 'nsl';
    case NATIONAL = 'national';

    public function title(): string
    {
        return match ($this) {
            self::NWSL => "National Women's Soccer League",
            self::NSL => "Northern Super League",
            self::NATIONAL => "National Teams",
        };
    }
}
