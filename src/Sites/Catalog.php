<?php

namespace Relay\Sites;

use Illuminate\Support\Collection;

final class Catalog
{
    /**
     * Available sources
     *
     * @var array<string, class-string>
     */
    protected static array $sources = [
        'nwsl' => Nwsl::class,
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
     * Retrieve a site by slug if it exists.
     */
    public function find(string $slug): ?Site
    {
        if (array_key_exists($slug, self::$sources)) {
            /** @var Site */
            return new self::$sources[$slug]();
        }

        return null;
    }
}
