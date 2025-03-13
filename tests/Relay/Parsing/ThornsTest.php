<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Thorns;
use Tests\TestCase;

class ThornsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_thorns_index()
    {
        Http::fake(['*' => Http::response($this->stub('thorns.html'))]);
        $parser = new Thorns();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(24, $entries);
        $this->assertEquals('https://www.thorns.com/news/portland-thorns-fc-announce-roster-ahead-of-2025-season', $entries->first()->url);
        $this->assertEquals('portland-thorns-fc-announce-roster-ahead-of-2025-season', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_thorns_article()
    {
        Http::fake(['*' => Http::response($this->stub('thorns-article.html'))]);
        $parser = new Thorns();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://cdn.prod.website-files.com/66639c7ab3a5a58aa7e1fb30/67d0cb347ba1e83a17278866_Roster_News_16x9.png" alt="Portland Thorns FC Announce Roster Ahead of 2025 Season" /></p>\n
        <p>PORTLAND, Ore. (March 12, 2025) – Portland Thorns FC announced today its official roster ahead of their 2025 NWSL Season Opener this Saturday, March 15 at Kansas City Current.</p>
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Portland Thorns FC Announce Roster Ahead of 2025 Season', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('portland-thorns', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Portland Thorns', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-03-12', $article->published_at->format('Y-m-d'));
    }
}
