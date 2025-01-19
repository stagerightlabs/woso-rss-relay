<?php

namespace Relay\Transform;

use Illuminate\Support\Collection;
use Relay\Article;
use Relay\Sites\Site;

final class Atom
{
    /**
     * @param Collection<int,\Relay\Article> $articles
     */
    public function __construct(private Site $site, private Collection $articles) {}

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
                "<link href=\"{$article->link}\" />",
                "<id>urn:relay:{$this->site->slug()}:{$article->key}</id>",
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

            return "<entry>{$entry->sort()->implode('')}</entry>";
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
            <title>{$this->site->title()}</title>
            <link rel="self" href="{$this->site->relay()}"/>
            <link rel="alternate" href="{$this->site->relay()}"/>
            <updated>{$this->timestamp()}</updated>
            <author>
                <name>{$this->site->title()}</name>
                <name>RSS Relay Service</name>
            </author>
            <generator>RSS Relay Service</generator>
            <id>urn:relay:{$this->site->slug()}</id>
            <icon>{$this->site->logo()}</icon>
            <logo>{$this->site->logo()}</logo>

            {$this->entries()->implode('')}
        </feed>
        XML;
    }
}
