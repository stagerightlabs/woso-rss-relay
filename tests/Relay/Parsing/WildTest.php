<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Wild;
use Tests\TestCase;

class WildTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_wild_index()
    {
        Http::fake(['*' => Http::response($this->stub('wild.html'))]);
        $parser = new Wild();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://www.calgarywildfc.com/news/calgary-wild-fc-game-day-notes-vancouver-rise-fc-april-16-2025', $entries->first()->url);
        $this->assertEquals('calgary-wild-fc-game-day-notes-vancouver-rise-fc-april-16-2025', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_wild_article()
    {
        Http::fake(['*' => Http::response($this->stub('wild-article.html'))]);
        $parser = new Wild();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://d2kqzfy3a9h1xl.cloudfront.net/nsl-prod/calgary/IMG_4817-Serita-Thurton.jpeg" alt="Calgary Wild FC Rounds Out Player Roster with Addition of Serita Thurton" /></p>\n
        <p>CALGARY – Calgary Wild FC rounded out its 23-player roster on Thursday with the addition of forward, Serita Thurton.</p>
        <p>The promising young talent from Ajax, Ont. becomes the 16th Canadian on Alberta’s first women’s professional sports team that is one of six clubs that will compete in the Northern Super League that kicks off April 16 in Vancouver.</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Calgary Wild FC Rounds Out Player Roster with Addition of Serita Thurton', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('calgary-wild', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Calgary Wild FC', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-04-03', $article->published_at->format('Y-m-d'));
    }
}
