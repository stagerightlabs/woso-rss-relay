<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Relay\Sites\Catalog;
use Relay\Sites\Site;

final class UpdateFeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Catalog $catalog): Response
    {
        $sites = $catalog->sorted();
        $timestamp = config('relay.release_date');

        return response($this->feed($sites, $timestamp), 200, [
            'Content-Type' => 'application/rss+xml',
        ]);
    }


    /**
     * Perform string conversion.
     *
     * @param Collection<int,Site> $sites
     */
    private function feed(Collection $sites, string $timestamp): string
    {
        $self = route('updates');
        return <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>WoSo RSS Relay Updates</title>
            <link rel="self" href="{$self}"/>
            <updated>{$timestamp}</updated>
            <author>
                <name>RSS Relay Service</name>
            </author>
            <generator>RSS Relay Service</generator>
            <id>urn:relay:updates</id>

            {$this->entries($sites)->implode('')}
        </feed>
        XML;
    }

    /**
     * Convert the articles into RSS entries.
     *
     * @param Collection<int,Site> $sites
     * @return Collection<int,non-falsy-string>
     */
    private function entries(Collection $sites): Collection
    {
        return $sites->map(function (Site $site) {
            $slug = $site->slug();
            $url = route('feed', $slug);
            $entry = (new Collection([
                "<title><![CDATA[{$site->title()}]]></title>",
                "<link href=\"{$url}\" />",
                "<id>urn:relay:updates:{$site->slug()}</id>",
                "<summary><![CDATA[Now available: {$site->title()}]]></summary>",
                "<content><![CDATA[Now available: {$site->title()}]]></content>",
                "<updated>2025-01-19T21:32:47+00:00</updated>",
            ]));

            return "<entry>{$entry->sort()->implode('')}</entry>";
        });
    }
}
