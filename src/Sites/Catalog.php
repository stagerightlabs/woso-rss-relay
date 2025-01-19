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
        NorthCarolinaCourage::class,
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
            });
    }

    /**
     * Return a collection of available sites sorted by name.
     *
     * @return Collection<array-key,Site>
     */
    public static function sorted(): Collection
    {
        return self::all()->sort(fn($a, $b) => strnatcasecmp($a->title(), $b->title()));
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
