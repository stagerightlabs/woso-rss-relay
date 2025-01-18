<?php

namespace Relay;

use Illuminate\Support\Collection;

final class Atom
{
    private Website $website;

    /**
     * @param Feed $feed
     * @param Collection<int,\Relay\Article> $articles
     */
    public function __construct(Feed $feed, private Collection $articles)
    {
        $this->website = Website::where('feed', $feed)->firstOrFail();
    }

    /**
     * The most recent publication date as an atom datetime string.
     */
    private function timestamp(): string
    {
        return $this->articles->pluck('published_at')->max()->toAtomString();
    }

    /**
     * Convert the articles into RSS entries.
     *
     * @return Collection<array-key,non-falsy-string>
     */
    private function entries(): Collection
    {
        return $this->articles->map(function (Article $article) {
            $entry = (new Collection([
                "<title><![CDATA[{$article->title}]]></title>",
                "<link href=\"{$article->link}\"/>",
                "<id>urn:relay:{$article->key}</id>",
                "<summary><![CDATA[{$article->summary}]]></summary>",
                "<content><![CDATA[{$article->content}]]></content>",
            ]));

            // Publication dates
            if ($article->published_at) {
                $entry
                    ->add("<updated>{$article->published_at->toAtomString()}</updated>")
                    ->add("<published>{$article->published_at->toAtomString()}</published>");
            }

            // Logo
            if ($article->image) {
                $entry->add("<logo><![CDATA[{$article->image}]]></logo>");
            }

            // Author
            if ($article->author) {
                $entry->add("<author><name>{$article->author}</name></author>");
            }

            return "<entry>{$entry->implode('')}</entry>";
        });
    }

    /**
     * Perform string conversion.
     */
    public function __toString(): string
    {
        return <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>{$this->website->name}</title>
            <link rel="self" href="{$this->website->relay}"/>
            <link rel="alternate" href="{$this->website->link}"/>
            <updated>{$this->timestamp()}</updated>
            <author>
                <name>{$this->website->name}</name>
                <name>RSS Relay Service</name>
            </author>
            <generator>RSS Relay Service</generator>
            <id>{$this->website->relay}</id>

            {$this->entries()->implode('')}
        </feed>
        XML;
    }
}
