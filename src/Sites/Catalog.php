<?php

namespace Relay\Sites;

use Illuminate\Support\Collection;

final class Catalog
{
    /**
     * Available sources
     *
     * @var array<int, class-string>
     */
    protected static array $sources = [
        Nwsl::class,
        AngelCity::class,
        BayFC::class,
        ChicagoStars::class,
    ];

    /**
     * Return a collection of available sites.
     *
     * @return Collection<array-key,Site>
     */
    public static function all(): Collection
    {
        return (new Collection(self::$sources))
            ->map(function ($site) {
                /** @var Site */
                return new $site();
            })
            ->sort(fn($a, $b) => $a->title() <=> $b->title());
    }

    /**
     * Retrieve a site by slug if it exists.
     */
    public function find(string $slug): ?Site
    {
        return (new Collection(self::$sources))
            ->filter(fn($site) => $site::slug() == $slug)
            ->map(function ($site) {
                /** @var Site */
                return new $site();
            })
            ->first();
    }
}
