<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Rapid;
use Tests\TestCase;

class RapidTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_rapid_index()
    {
        Http::fake(['*' => Http::response($this->stub('rapid.html'))]);
        $parser = new Rapid();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://www.rapidfc.ca/news/ottawa-rapid-fc-secures-national-1-license-ahead-of-historic-inaugural-season', $entries->first()->url);
        $this->assertEquals('ottawa-rapid-fc-secures-national-1-license-ahead-of-historic-inaugural-season', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_rapid_article()
    {
        Http::fake(['*' => Http::response($this->stub('rapid-article.html'))]);
        $parser = new Rapid();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://d2kqzfy3a9h1xl.cloudfront.net/nsl-prod/ottawa/Untitled-design-2.png" alt="Ottawa Rapid FC Secures National 1 License ahead of Historic Inaugural Season" /></p>\n
        <p>Press Release // Communiqué de presse</p>
        <p>Ottawa – Ottawa Rapid FC is proud to announce that the club has officially been awarded a National 1 License by Canada Soccer. This prestigious designation, granted to all six Northern Super League (NSL) clubs, solidifies Ottawa Rapid FC’s position as a premier professional women’s soccer team and marks a significant milestone for the sport in the National Capital Region.</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Ottawa Rapid FC Secures National 1 License ahead of Historic Inaugural Season', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('ottawa-rapid', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Ottawa Rapid FC', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-04-02', $article->published_at->format('Y-m-d'));
    }
}
