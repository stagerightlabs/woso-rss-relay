<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Nsl;
use Tests\TestCase;

class NslTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_nsl_index()
    {
        Http::fake(['*' => Http::response($this->stub('nsl.html'))]);
        $parser = new Nsl();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://www.nsl.ca/news/md-1-it-all-begins-here', $entries->first()->url);
        $this->assertEquals('md-1-it-all-begins-here', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_an_nsl_article()
    {
        Http::fake(['*' => Http::response($this->stub('nsl-article.html'))]);
        $parser = new Nsl();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://d2kqzfy3a9h1xl.cloudfront.net/nsl-prod/league/NSL-MD-1.png" alt="MD-1: It All Begins Here" /></p>\n
        <p>History is only made once.</p>
        <p>After years of dreaming, planning, building, and believing, the Northern Super League is finally ready to take the pitch. Tomorrow, on April 16, professional women’s soccer officially kicks off in Canada with the first match of the NSL’s inaugural season, marking a seismic moment for sport, equity, and culture in this country.</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('MD-1: It All Begins Here', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('nsl', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Northern Super League', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-04-15', $article->published_at->format('Y-m-d'));
    }
}
