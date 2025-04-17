<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Roses;
use Tests\TestCase;

class RosesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_roses_index()
    {
        Http::fake(['*' => Http::response($this->stub('roses.html'))]);
        $parser = new Roses();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://en.rosesmtl.ca/news/three-new-players-join-the-squad-and-the-roses-name-co-captains', $entries->first()->url);
        $this->assertEquals('three-new-players-join-the-squad-and-the-roses-name-co-captains', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_roses_article()
    {
        Http::fake(['*' => Http::response($this->stub('roses-article.html'))]);
        $parser = new Roses();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://d2kqzfy3a9h1xl.cloudfront.net/nsl-prod/montreal/APRIL16_NEWS.jpg" alt="Three New Players Join the Squad, and the Roses Name Co-Captains" /></p>\n
        <p>Montreal, April 16, 2025 — Just days before taking the field for their very first official match in the Northern Super League, the Montréal Roses are proud to announce the signing of three new players and the naming of their co-captains for the 2025 season.</p>
        <p>German international Lara Schenk officially signs with the Montréal Roses for their inaugural season. The 26-year-old central defender, known for her versatility and ambidexterity, was trained by VfL Wolfsburg in Germany’s Bundesliga 1. She has played in top European leagues, including Belgium’s Super League with RSC Anderlecht and Club YLA, Spain’s Sporting Club de Huelva, and in the NCAA with Harvard Crimson. Renowned for her game intelligence and her commitment to social causes, especially LGBTQIA+ inclusion, Schenk is seen as a leader both on and off the field.</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Three New Players Join the Squad, and the Roses Name Co-Captains', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('montreal-roses', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Montreal Roses FC', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-04-16', $article->published_at->format('Y-m-d'));
    }
}
