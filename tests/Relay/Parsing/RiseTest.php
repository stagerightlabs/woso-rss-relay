<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Rise;
use Tests\TestCase;

class RiseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_rise_index()
    {
        Http::fake(['*' => Http::response($this->stub('rise.html'))]);
        $parser = new Rise();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://www.vanrisefc.com/news/schedule-update-april-24-match-moved-to-april-27', $entries->first()->url);
        $this->assertEquals('schedule-update-april-24-match-moved-to-april-27', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_rise_article()
    {
        Http::fake(['*' => Http::response($this->stub('rise-article.html'))]);
        $parser = new Rise();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://d2kqzfy3a9h1xl.cloudfront.net/nsl-prod/league/4-Canada-Soccer-by-Martin-Bazyl.jpg" alt="Vancouver Rise FC Sign Canadian National Team Star Quinn" /></p>\n
        <p>Photo Credit: Canada Soccer by Martin Bazyl</p>
        <p>DOWNLOAD: Quinn photos and video</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Vancouver Rise FC Sign Canadian National Team Star Quinn', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('vancouver-rise', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Vancouver Rise FC', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-01-16', $article->published_at->format('Y-m-d'));
    }
}
